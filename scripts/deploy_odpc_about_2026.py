#!/usr/bin/env python3
"""Deploy additive /about-me/ MU plugin with backup, checksum and rollback."""
import base64, hashlib, hmac, os, posixpath, stat, time
from pathlib import Path
from urllib.request import Request, urlopen
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/clickandbuilds/Dering"
SOURCE=Path("site-deploy/odpc.de/odpc-about-me-2026.php")
REMOTE=ROOT+"/wp-content/mu-plugins/odpc-about-me-2026.php"
BACKUP=Path("odpc-about-backup")
def check():
    url="https://odpc.de/about-me/?odpc_about_qa="+str(time.time_ns())
    with urlopen(Request(url,headers={"User-Agent":"ODPC About 2026 QA","Cache-Control":"no-cache"}),timeout=35) as r:
        html=r.read(1000000).decode("utf-8","replace")
    required=["odpc-about26-heading","AdminMonkey","D.R.O.L.","odpc-reader-mic","odpc-unified-dock-js"]
    missing=[x for x in required if x not in html]
    if missing:raise RuntimeError("Missing live markers: "+", ".join(missing))
    print("PASS: about page, projects, unchanged shared dock markers")
def main():
    if not SOURCE.is_file() or SOURCE.stat().st_size>18000:raise RuntimeError("Source absent or oversized")
    pw=os.environ["ODPC_SFTP_PASSWORD"]
    t=paramiko.Transport((HOST,22));t.start_client(timeout=20)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual,PIN):raise RuntimeError("Host fingerprint mismatch")
        t.auth_password(USER,pw)
        if not t.is_authenticated():raise RuntimeError("SFTP authentication failed")
        s=paramiko.SFTPClient.from_transport(t)
        try:
            s.stat(ROOT+"/wp-config.php");s.stat(ROOT+"/wp-content/mu-plugins")
            BACKUP.mkdir(exist_ok=True)
            old=None
            try:
                info=s.lstat(REMOTE)
                if not stat.S_ISREG(info.st_mode) or info.st_size>18000:raise RuntimeError("Unexpected remote file")
                with s.open(REMOTE,"rb") as f:old=f.read()
                (BACKUP/"previous.php").write_bytes(old)
            except FileNotFoundError:pass
            s.put(str(SOURCE),REMOTE+".upload",confirm=True)
            with s.open(REMOTE+".upload","rb") as f:uploaded=f.read()
            if hashlib.sha256(uploaded).digest()!=hashlib.sha256(SOURCE.read_bytes()).digest():raise RuntimeError("Upload checksum mismatch")
            s.posix_rename(REMOTE+".upload",REMOTE)
            try:
                for attempt in range(3):
                    try:check();break
                    except Exception:
                        if attempt==2:raise
                        time.sleep(4)
            except Exception:
                if old is None:s.remove(REMOTE)
                else:
                    with s.open(REMOTE+".rollback","wb") as f:f.write(old)
                    s.posix_rename(REMOTE+".rollback",REMOTE)
                print("ROLLBACK completed");raise
            print("DEPLOY PASS: additive MU plugin only; original page/CV/voice files unchanged")
        finally:s.close()
    finally:t.close()
if __name__=="__main__":main()
