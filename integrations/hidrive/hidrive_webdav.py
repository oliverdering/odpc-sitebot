#!/usr/bin/env python3
"""Append-only HiDrive transfer. No third-party dependencies, redirects or deletes."""
import argparse
import base64
import hashlib
import http.client
import json
import os
from pathlib import Path, PurePosixPath
import re
import ssl
import sys
import uuid
from datetime import datetime, timezone
from urllib.parse import quote
import xml.etree.ElementTree as ET

HOST = "webdav.hidrive.ionos.com"
MAX_BYTES = 100 * 1024 * 1024


class StorageError(Exception):
    pass


def safe_root(value):
    if (not value.startswith("/") or value.startswith("//")
            or any(c in value for c in ("%", "\\", "?", "#"))
            or any(ord(c) < 32 for c in value)
            or any(p in {".", ".."} for p in value.split("/"))):
        raise StorageError("Invalid HiDrive root path")
    value = value.rstrip("/")
    if not value or PurePosixPath(value).name != "ODPC-AI-STORAGE":
        raise StorageError("Root must be the approved ODPC-AI-STORAGE directory")
    return value


class WebDAV:
    def __init__(self):
        self.root = safe_root(os.environ.get("HIDRIVE_ROOT_PATH", ""))
        user = os.environ.get("HIDRIVE_USERNAME", "")
        password = os.environ.get("HIDRIVE_PASSWORD", "")
        if not user or not password or ":" in user:
            raise StorageError("Missing or invalid HiDrive credentials")
        token = base64.b64encode((user + ":" + password).encode()).decode()
        self.authorization = "Basic " + token

    def request(self, method, suffix="", data=None, headers=None):
        if method not in {"PROPFIND", "MKCOL", "PUT", "GET"}:
            raise StorageError("Operation is not allowed")
        if suffix and not re.fullmatch(r"(?:/[A-Za-z0-9_.-]+)+/?", suffix):
            raise StorageError("Invalid relative path")
        if any(p in {".", ".."} for p in suffix.split("/")):
            raise StorageError("Path traversal refused")
        request_headers = {"Authorization": self.authorization}
        request_headers.update(headers or {})
        connection = http.client.HTTPSConnection(HOST, 443, timeout=60,
                                                 context=ssl.create_default_context())
        try:
            connection.request(method, quote(self.root + suffix, safe="/"),
                               body=data, headers=request_headers)
            response = connection.getresponse()
            status = response.status
            # Never follow redirects, and never print remote errors or headers.
            if status not in {200, 201, 204, 207}:
                raise StorageError(f"HiDrive rejected {method}: HTTP {status}")
            content = response.read(MAX_BYTES + 1)
            if len(content) > MAX_BYTES:
                raise StorageError("Response exceeds transfer limit")
            return status, content
        finally:
            connection.close()

    def probe(self):
        body = b'<?xml version="1.0"?><d:propfind xmlns:d="DAV:"><d:prop><d:resourcetype/></d:prop></d:propfind>'
        status, content = self.request("PROPFIND", data=body,
                                       headers={"Depth": "0", "Content-Type": "application/xml"})
        if status != 207:
            raise StorageError("Expected WebDAV multistatus")
        if b"<!DOCTYPE" in content.upper() or b"<!ENTITY" in content.upper():
            raise StorageError("Unsafe XML refused")
        try:
            doc = ET.fromstring(content)
        except ET.ParseError:
            raise StorageError("Invalid WebDAV response") from None
        ok = any(" 200 " in (p.findtext("{DAV:}status") or "")
                 and p.find("{DAV:}prop/{DAV:}resourcetype/{DAV:}collection") is not None
                 for p in doc.findall(".//{DAV:}propstat"))
        if not ok:
            raise StorageError("Approved root is not an accessible directory")

    def put_verified(self, suffix, data):
        if len(data) > MAX_BYTES:
            raise StorageError("File exceeds 100 MiB transfer limit")
        status, _ = self.request("PUT", suffix, data,
                                  {"If-None-Match": "*", "Content-Type": "application/octet-stream"})
        if status != 201:
            raise StorageError("Server did not confirm a new file")
        _, returned = self.request("GET", suffix)
        if hashlib.sha256(returned).digest() != hashlib.sha256(data).digest():
            raise StorageError("Readback hash mismatch; incomplete run retained")
        return {"bytes": len(data), "sha256": hashlib.sha256(data).hexdigest()}

    def create_run(self):
        # Parent is deliberately provisioned by the owner; never create arbitrary parents.
        run = "/07_BACKUPS/" + datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ-") + uuid.uuid4().hex
        status, _ = self.request("MKCOL", run)
        if status != 201:
            raise StorageError("Unique run folder was not created")
        return run


def run(mode, files):
    paths = [Path(f) for f in files]
    if len(paths) > 20:
        raise StorageError("At most 20 selected files per transfer")
    names = [p.name for p in paths]
    if len(set(names)) != len(names) or any(
            not re.fullmatch(r"[A-Za-z0-9][A-Za-z0-9_.-]*", n) or n in {"manifest.json", "COMPLETE.json"}
            for n in names):
        raise StorageError("Invalid, duplicate or reserved file names")
    data = []
    for p in paths:
        if p.is_symlink() or not p.is_file() or p.stat().st_size > MAX_BYTES:
            raise StorageError("Input must be a regular file of at most 100 MiB")
        data.append(p.read_bytes())
    if mode == "plan":
        print(json.dumps({"mode": "plan", "network_used": False, "files": len(paths),
                          "bytes": sum(map(len, data))}))
        return
    if mode in {"test", "backup"} and os.environ.get("HIDRIVE_PRIVATE_TARGET_VERIFIED") != "true":
        raise StorageError("Owner must verify an unshared target before enabling writes")
    if mode == "backup" and not paths:
        raise StorageError("Explicit file selection is required")
    client = WebDAV()
    client.probe()
    if mode == "probe":
        print("PASS: authenticated root probe; no writes")
        return
    folder = client.create_run()
    if mode == "test":
        paths = [Path("odpc-connection-test.txt")]
        data = [("ODPC AI Storage – Verbindungstest\nDatum: " + datetime.now(timezone.utc).date().isoformat()
                 + "\nSystem: GitHub Actions / Python WebDAV\nStatus: Schreib- und Lesetest\n").encode()]
    manifest = {"version": 1, "files": []}
    for p, payload in zip(paths, data):
        entry = client.put_verified(folder + "/" + p.name, payload)
        manifest["files"].append({"name": p.name, **entry})
    client.put_verified(folder + "/manifest.json", json.dumps(manifest).encode())
    client.put_verified(folder + "/COMPLETE.json", b'{"verified":true}\n')
    print(json.dumps({"status": "PASS", "mode": mode, "files": len(data),
                      "readback_verified": True}))


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("mode", choices=["plan", "probe", "test", "backup"], default="plan", nargs="?")
    parser.add_argument("files", nargs="*")
    args = parser.parse_args()
    try:
        run(args.mode, args.files)
    except StorageError as exc:
        print("FAILED: " + str(exc), file=sys.stderr)
        sys.exit(1)
    except Exception:
        print("FAILED: local or network error; details suppressed to protect credentials", file=sys.stderr)
        sys.exit(1)
