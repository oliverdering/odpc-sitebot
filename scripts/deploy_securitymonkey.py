#!/usr/bin/env python3
"""Deploy the Security Monkey page and its entry independently, with rollback."""
import base64
import hashlib
import hmac
import os
import posixpath
import stat
import time
from pathlib import Path
from urllib.request import Request, urlopen

import paramiko
from scripts.deploy_helpermonkey_v4 import HOST, USER, PIN, find_theme

PAGE = Path("site-deploy/ai.odpc.de/securitymonkey/index.html")
ENTRY = Path("site-deploy/ai.odpc.de/odpc-securitymonkey-entry.php")

def public_check(url, needles):
    req = Request(url + "?security_monkey_check=" + str(time.time_ns()), headers={"Cache-Control": "no-cache", "User-Agent": "ODPC-Security-Monkey-Deployment/1"})
    with urlopen(req, timeout=30) as response:
        html = response.read(1_000_000).decode("utf-8", "replace")
        if response.status != 200:
            raise RuntimeError("Unexpected HTTP status")
    if not all(n in html for n in needles):
        raise RuntimeError("Required public markers missing at " + url)

def read_previous(sftp, path):
    try:
        info = sftp.lstat(path)
    except FileNotFoundError:
        return None
    if not stat.S_ISREG(info.st_mode) or info.st_size > 500_000:
        raise RuntimeError("Unsafe existing target " + path)
    with sftp.open(path, "rb") as f:
        return f.read()

def put_atomic(sftp, destination, data):
    temp = destination + ".uploading"
    try:
        with sftp.open(temp, "wb") as f:
            f.write(data)
        sftp.posix_rename(temp, destination)
    finally:
        try:
            sftp.remove(temp)
        except FileNotFoundError:
            pass

def main():
    password = os.environ.get("ODPC_SFTP_PASSWORD")
    if not password:
        raise RuntimeError("Missing ODPC_SFTP_PASSWORD")
    page, entry = PAGE.read_bytes(), ENTRY.read_bytes()
    if len(page) > 500_000 or len(entry) > 100_000:
        raise RuntimeError("Unexpectedly large source")
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=25)
    try:
        key = "SHA256:" + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(key, PIN):
            raise RuntimeError("Host key mismatch")
        transport.auth_password(USER, password)
        if not transport.is_authenticated():
            raise RuntimeError("SFTP authentication failed")
        sftp = paramiko.SFTPClient.from_transport(transport)
        try:
            theme = find_theme(sftp)
            root = theme.split("/wp-content/themes/")[0]
            if not root.startswith("/") or not root:
                raise RuntimeError("Unsafe target root")
            for path in (root + "/wp-config.php", root + "/wp-content", theme + "/functions.php"):
                sftp.stat(path)
            directory = posixpath.join(root, "securitymonkey")
            mu = posixpath.join(root, "wp-content", "mu-plugins")
            for directory_path in (directory, mu):
                try:
                    mode = sftp.lstat(directory_path).st_mode
                    if not stat.S_ISDIR(mode):
                        raise RuntimeError("Unsafe target directory " + directory_path)
                except FileNotFoundError:
                    sftp.mkdir(directory_path)
            targets = [(directory + "/index.html", page), (mu + "/odpc-securitymonkey-entry.php", entry)]
            originals = {path: read_previous(sftp, path) for path, _ in targets}
            changed = []
            try:
                put_atomic(sftp, targets[0][0], targets[0][1])
                changed.append(targets[0][0])
                public_check("https://ai.odpc.de/securitymonkey/", ("Security Monkey", "Welche Geräte verwendest du", "odpc-ai-unified-dock-js", "odpc-ai-language", "odpc-ai-reference-dock-js", "security_monkey_language_return", "powered by"))
                put_atomic(sftp, targets[1][0], targets[1][1])
                changed.append(targets[1][0])
                # WordPress cache is bypassed with the request query. Both the new
                # card and existing controls must still appear on the homepage.
                public_check("https://ai.odpc.de/", ("odpc-security-monkey-entry", "hm-v4-global-js"))
                print("PASS: Security Monkey and existing global UI are publicly reachable")
            except Exception:
                for path in reversed(changed):
                    old = originals[path]
                    if old is None:
                        try:
                            sftp.remove(path)
                        except FileNotFoundError:
                            pass
                    else:
                        put_atomic(sftp, path, old)
                raise
        finally:
            sftp.close()
    finally:
        transport.close()

if __name__ == "__main__":
    main()
