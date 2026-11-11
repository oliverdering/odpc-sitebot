#!/usr/bin/env python3
"""Upload ODPC Universal theme only; never touch active theme, core, or database."""
import base64, hashlib, hmac, os, pathlib, posixpath, stat
import paramiko

HOST = "home119567219.1and1-data.host"
USER = "acc1461323326"
PIN = "SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
BASE = "/YourBusiness/wp-content/themes"
TARGET = BASE + "/odpc-universal"
SOURCE = pathlib.Path(__file__).resolve().parents[1] / "yourbusiness/theme/odpc-universal"
ALLOWED = {"style.css", "theme.json", "templates/index.html", "templates/page.html", "parts/header.html", "parts/footer.html"}

def main():
    password = os.environ.get("ODPC_SFTP_PASSWORD")
    if not password:
        raise RuntimeError("Missing ODPC_SFTP_PASSWORD")
    files = {str(p.relative_to(SOURCE)): p for p in SOURCE.rglob("*") if p.is_file()}
    if files.keys() != ALLOWED:
        raise RuntimeError(f"Unexpected theme file set: {sorted(files.keys() ^ ALLOWED)}")
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=30)
    try:
        got = "SHA256:" + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(got, PIN):
            raise RuntimeError("Host fingerprint mismatch")
        transport.auth_password(USER, password)
        if not transport.is_authenticated():
            raise RuntimeError("SFTP authentication failed")
        sftp = paramiko.SFTPClient.from_transport(transport)
        try:
            if not stat.S_ISDIR(sftp.stat(BASE).st_mode):
                raise RuntimeError("YourBusiness theme directory missing")
            for directory in [TARGET, TARGET + "/templates", TARGET + "/parts"]:
                try:
                    sftp.mkdir(directory)
                except OSError:
                    if not stat.S_ISDIR(sftp.stat(directory).st_mode):
                        raise
            for relative, local in sorted(files.items()):
                remote = posixpath.join(TARGET, relative)
                with local.open("rb") as source, sftp.open(remote, "wb") as dest:
                    while block := source.read(65536):
                        dest.write(block)
                if sftp.stat(remote).st_size != local.stat().st_size:
                    raise RuntimeError(f"Size mismatch: {relative}")
                print("Uploaded and verified:", relative)
            print("ODPC Universal deployed. Theme NOT activated.")
        finally:
            sftp.close()
    finally:
        transport.close()

if __name__ == "__main__":
    main()
