#!/usr/bin/env python3
"""Strictly isolated, additive SFTP deployment of Kleine Freiheit media.

Never writes outside /YourBusiness/wp-content/uploads/kleine-freiheit-2026.
Never overwrites an existing file. Does not change WordPress settings.
"""
import base64
import hashlib
import hmac
import os
from pathlib import Path
import stat
import paramiko

HOST = "home119567219.1and1-data.host"
USER = "acc1461323326"
PIN = "SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT = "/YourBusiness/wp-content/uploads"
TARGET = ROOT + "/kleine-freiheit-2026"
LOCAL = Path(__file__).resolve().parents[1] / "yourbusiness" / "kleine-freiheit" / "assets"
EXPECTED = ["original-logo.png"] + [f"foto-{i:02d}.webp" for i in range(1, 12)]

def main():
    password = os.environ.get("ODPC_SFTP_PASSWORD")
    if not password:
        raise RuntimeError("ODPC_SFTP_PASSWORD unavailable; nothing uploaded")
    for name in EXPECTED:
        if not (LOCAL / name).is_file():
            raise RuntimeError(f"Required original media absent: {name}; nothing uploaded")
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=30)
    try:
        fingerprint = "SHA256:" + base64.b64encode(
            hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()
        ).decode().rstrip("=")
        if not hmac.compare_digest(fingerprint, PIN):
            raise RuntimeError("Host key mismatch")
        transport.auth_password(USER, password)
        if not transport.is_authenticated():
            raise RuntimeError("Authentication failed")
        sftp = paramiko.SFTPClient.from_transport(transport)
        try:
            if not stat.S_ISDIR(sftp.stat(ROOT).st_mode):
                raise RuntimeError("Isolated YourBusiness uploads directory not found")
            try:
                sftp.stat(TARGET)
            except FileNotFoundError:
                sftp.mkdir(TARGET)
            if not stat.S_ISDIR(sftp.stat(TARGET).st_mode):
                raise RuntimeError("Target is not a directory")
            for name in EXPECTED:
                remote = TARGET + "/" + name
                try:
                    sftp.stat(remote)
                except FileNotFoundError:
                    pass
                else:
                    raise RuntimeError(f"Refusing to overwrite existing file: {name}")
            for name in EXPECTED:
                remote = TARGET + "/" + name
                with (LOCAL / name).open("rb") as source, sftp.open(remote, "xb") as dest:
                    while chunk := source.read(65536):
                        dest.write(chunk)
                if sftp.stat(remote).st_size != (LOCAL / name).stat().st_size:
                    raise RuntimeError(f"Size mismatch: {name}")
                print("Verified", remote)
        finally:
            sftp.close()
    finally:
        transport.close()

if __name__ == "__main__":
    main()
