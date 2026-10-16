#!/usr/bin/env python3
"""Deploy scoped ODPC article featured-image MU plugin with verified SFTP and rollback."""
import os,base64,hashlib,hmac,stat,posixpath,sys
from pathlib import Path
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/clickandbuilds/Dering"
SOURCE=Path("site-deploy/odpc.de/odpc-helpermonkey-featured-image.php")
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD")
    if not password or not SOURCE.is_file():raise RuntimeError("Missing credential or source")
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=25)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual,PIN):raise RuntimeError("Host key mismatch")
        transport.auth_password(USER,password)
        if not transport.is_authenticated():raise RuntimeError("SFTP authentication refused")
        sftp=paramiko.SFTPClient.from_transport(transport)
        try:
            sftp.stat(ROOT+"/wp-config.php")
            sftp.stat(ROOT+"/wp-content/themes/astra/style.css")
            directory=ROOT+"/wp-content/mu-plugins"
            if not stat.S_ISDIR(sftp.stat(directory).st_mode):raise RuntimeError("MU plugin directory missing")
            dest=directory+"/odpc-helpermonkey-featured-image.php"
            tmp=dest+".uploading"
            try:
                sftp.lstat(dest)
                raise RuntimeError("Target already exists; refusing overwrite")
            except FileNotFoundError:pass
            try:
                sftp.put(str(SOURCE),tmp,confirm=True)
                sftp.posix_rename(tmp,dest)
                print("PASS: scoped featured-image MU plugin installed")
            finally:
                try:sftp.remove(tmp)
                except OSError:pass
        finally:sftp.close()
    finally:transport.close()
if __name__=="__main__":
    try:main()
    except Exception as exc:
        print("DEPLOY FAILED:",exc,file=sys.stderr)
        sys.exit(1)
