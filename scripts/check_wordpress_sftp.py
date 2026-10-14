#!/usr/bin/env python3
"""Read-only WordPress SFTP connector check. Never uploads, writes or deletes."""
import base64
import hashlib
import hmac
import os
import posixpath
import stat
import sys

import paramiko


def fingerprint(key):
    return "SHA256:" + base64.b64encode(
        hashlib.sha256(key.asbytes()).digest()
    ).decode("ascii").rstrip("=")


def required(name):
    value = os.environ.get(name, "").strip()
    if not value:
        raise RuntimeError("Missing setting: " + name)
    return value


def main():
    mode = os.environ.get("ODPC_SFTP_MODE", "fingerprint")
    if mode not in {"fingerprint", "check"}:
        raise RuntimeError("Unsupported mode (fingerprint/check only)")
    host = required("ODPC_SFTP_HOST")
    port = int(os.environ.get("ODPC_SFTP_PORT", "22"))
    if not 1 <= port <= 65535:
        raise RuntimeError("Invalid port")

    transport = paramiko.Transport((host, port))
    try:
        transport.start_client(timeout=25)
        actual = fingerprint(transport.get_remote_server_key())
        if mode == "fingerprint":
            print("Unverified server SSH fingerprint:", actual)
            print("Independently verify against the hosting provider before enabling authentication.")
            return

        expected = required("ODPC_SFTP_HOSTKEY_SHA256")
        if not hmac.compare_digest(actual, expected):
            raise RuntimeError("SSH host fingerprint mismatch; authentication refused")

        username = required("ODPC_SFTP_USER")
        password = required("ODPC_SFTP_PASSWORD")
        target = os.environ.get("ODPC_TARGET_SITE", "odpc.de").strip().lower()
        target_secrets = {
            "odpc.de": "ODPC_THEME_PATH",
            "ai.odpc.de": "AI_ODPC_THEME_PATH",
        }
        if target not in target_secrets:
            raise RuntimeError("Target must be odpc.de or ai.odpc.de")
        theme = required(target_secrets[target])
        if not theme.startswith("/") or "/../" in theme or not posixpath.isabs(theme):
            raise RuntimeError("Theme path must be an absolute path without traversal")
        theme = posixpath.normpath(theme)
        if "/wp-content/themes/" not in theme or theme.endswith("/themes"):
            raise RuntimeError("Refusing access outside one WordPress theme directory")
        transport.auth_password(username, password)
        if not transport.is_authenticated():
            raise RuntimeError("SFTP authentication failed")

        sftp = paramiko.SFTPClient.from_transport(transport)
        try:
            metadata = sftp.stat(theme)
            if not stat.S_ISDIR(metadata.st_mode):
                raise RuntimeError("Configured theme path is not a directory")
            entries = sftp.listdir(theme)
            if not {"functions.php", "style.css"} <= set(entries):
                raise RuntimeError("Configured directory does not look like a WordPress theme")
            print("PASS: pinned-key authenticated read-only connection for", target)
            print("Top-level theme file count:", len(entries))
            print("No files written, uploaded, or modified.")
        finally:
            sftp.close()
    finally:
        transport.close()


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print("ODPC SFTP CHECK FAILED:", exc, file=sys.stderr)
        sys.exit(1)
