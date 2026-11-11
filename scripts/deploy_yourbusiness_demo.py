#!/usr/bin/env python3
"""Deploy only the isolated YourBusiness instant-demo plugin via pinned SFTP."""
import base64, hashlib, hmac, os, pathlib, stat
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
BASE="/YourBusiness/wp-content/plugins"
NAME="yourbusiness-instant-demo"
SOURCE=pathlib.Path(__file__).resolve().parents[1]/"yourbusiness/plugin"/NAME/(NAME+".php")
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD")
    if not password: raise RuntimeError("Missing ODPC_SFTP_PASSWORD")
    if not SOURCE.is_file(): raise RuntimeError("Plugin source missing")
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=30)
    try:
        got="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(got,PIN): raise RuntimeError("Host fingerprint mismatch")
        transport.auth_password(USER,password)
        if not transport.is_authenticated(): raise RuntimeError("SFTP auth failed")
        sftp=paramiko.SFTPClient.from_transport(transport)
        try:
            if not stat.S_ISDIR(sftp.stat(BASE).st_mode): raise RuntimeError("YourBusiness plugins folder missing")
            target=BASE+"/"+NAME
            try: sftp.mkdir(target)
            except OSError:
                if not stat.S_ISDIR(sftp.stat(target).st_mode): raise
            remote=target+"/"+NAME+".php"
            with SOURCE.open("rb") as src, sftp.open(remote,"wb") as dest:
                while chunk:=src.read(65536): dest.write(chunk)
            if sftp.stat(remote).st_size!=SOURCE.stat().st_size: raise RuntimeError("Upload size mismatch")
            print("YOURBUSINESS_PLUGIN_UPLOADED_AND_VERIFIED")
        finally: sftp.close()
    finally: transport.close()
if __name__=="__main__": main()
