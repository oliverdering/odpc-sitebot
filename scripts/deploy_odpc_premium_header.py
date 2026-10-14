#!/usr/bin/env python3
"""Publish only the approved ODPC header mu-plugin; smoke test and rollback."""
import base64
import hashlib
import hmac
import os
from pathlib import Path
import posixpath
import stat
import sys
import time
from urllib.request import Request, urlopen

import paramiko

HOST = "home119567219.1and1-data.host"
USER = "acc1461323326"
KEY = "SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT = "/clickandbuilds/Dering"
FILE = "odpc-premium-header.php"
SOURCE = Path("site-deploy/odpc.de") / FILE
BACKUP = Path("odpc-header-backup")
REMOTE_DIR = posixpath.join(ROOT, "wp-content", "mu-plugins")
REMOTE = posixpath.join(REMOTE_DIR, FILE)
TEMP = REMOTE + ".deploying"


def site_has_marker():
    url = "https://odpc.de/?odpc_header_qa=" + str(int(time.time()))
    req = Request(url, headers={"User-Agent": "ODPC-Deployment-SmokeTest/1.0", "Cache-Control": "no-cache"})
    with urlopen(req, timeout=30) as response:
        html = response.read().decode("utf-8", "replace")
    return "id=\"odpc-navigation\"" in html and "id=\"odpc-nav-panel\"" in html and "odpc-premium-header-style" in html


def main():
    pw = os.environ.get("ODPC_SFTP_PASSWORD", "")
    if not pw:
        raise RuntimeError("Missing ODPC_SFTP_PASSWORD GitHub secret")
    if not SOURCE.is_file() or SOURCE.stat().st_size > 30000:
        raise RuntimeError("Expected reviewed ODPC PHP module missing or oversized")
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=20)
    try:
        actual = "SHA256:" + base64.b64encode(
            hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()
        ).decode().rstrip("=")
        if not hmac.compare_digest(KEY, actual):
            raise RuntimeError("IONOS host key changed; refusing deployment")
        transport.auth_password(USER, pw)
        if not transport.is_authenticated():
            raise RuntimeError("IONOS authentication refused")
        sftp = paramiko.SFTPClient.from_transport(transport)
        try:
            sftp.stat(posixpath.join(ROOT, "wp-content", "themes", "astra", "style.css"))
            try:
                sftp.stat(REMOTE_DIR)
            except FileNotFoundError:
                sftp.mkdir(REMOTE_DIR)
            BACKUP.mkdir(exist_ok=True)
            previous = BACKUP / "previous.php"
            existed = False
            try:
                entry = sftp.lstat(REMOTE)
                if not stat.S_ISREG(entry.st_mode) or entry.st_size > 30000:
                    raise RuntimeError("Unexpected existing target; refusing overwrite")
                sftp.get(REMOTE, str(previous))
                existed = True
            except FileNotFoundError:
                pass
            print("PREDEPLOY existing ODPC module:", existed)
            sftp.put(str(SOURCE), TEMP, confirm=True)
            try:
                sftp.posix_rename(TEMP, REMOTE)
            finally:
                try:
                    sftp.remove(TEMP)
                except OSError:
                    pass
            print("DEPLOYED one ODPC mu-plugin; pages/themes/database untouched")
            try:
                found = False
                for attempt in range(3):
                    time.sleep(2)
                    if site_has_marker():
                        found = True
                        break
                if not found:
                    raise RuntimeError("Live site does not expose new navigation module")
                print("LIVE SMOKE TEST PASS: new header/navigation module in public homepage HTML")
            except Exception as error:
                print("LIVE TEST FAILED:", error, "; rolling back immediately")
                if existed:
                    restore = REMOTE + ".rollback"
                    sftp.put(str(previous), restore, confirm=True)
                    sftp.posix_rename(restore, REMOTE)
                else:
                    sftp.remove(REMOTE)
                print("ROLLBACK COMPLETE")
                raise
        finally:
            sftp.close()
    finally:
        transport.close()


if __name__ == "__main__":
    try:
        main()
    except Exception as error:
        print("ODPC UI DEPLOY FAILED:", error, file=sys.stderr)
        sys.exit(1)
