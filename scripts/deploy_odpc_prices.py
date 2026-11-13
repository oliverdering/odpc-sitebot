#!/usr/bin/env python3
"""Deploy only ODPC pricing mu-plugin with backup and public smoke test."""
import base64, hashlib, hmac, os, posixpath, stat, sys, time
from pathlib import Path
from urllib.request import Request, urlopen
import paramiko

HOST="home119567219.1and1-data.host"
USER="acc1461323326"
KEY="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/clickandbuilds/Dering"
SOURCE=Path("site-deploy/odpc.de/odpc-prices.php")
REMOTE=posixpath.join(ROOT,"wp-content/mu-plugins/odpc-prices.php")
BACKUP=Path("odpc-prices-backup/previous.php")

def smoke():
    req=Request("https://odpc.de/preise-angebote/?odpc_price_check="+str(int(time.time())),headers={"User-Agent":"ODPC-Price-Release/1.0","Cache-Control":"no-cache"})
    with urlopen(req,timeout=35) as response:
        html=response.read().decode("utf-8","replace")
        return response.status==200 and all(x in html for x in ("id=\"odpc-pricing\"","59 €","89 €","odpc-prices-nav","odpc-appearance-switch"))

def main():
    pw=os.environ.get("ODPC_SFTP_PASSWORD")
    if not pw: raise RuntimeError("ODPC_SFTP_PASSWORD secret missing")
    if not SOURCE.is_file() or SOURCE.stat().st_size>100000: raise RuntimeError("Unexpected source")
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=20)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(KEY,actual): raise RuntimeError("Host key mismatch")
        transport.auth_password(USER,pw)
        if not transport.is_authenticated(): raise RuntimeError("Authentication failed")
        sftp=paramiko.SFTPClient.from_transport(transport)
        try:
            sftp.stat(posixpath.join(ROOT,"wp-content/themes/astra/style.css"))
            exists=False
            try:
                entry=sftp.lstat(REMOTE)
                if not stat.S_ISREG(entry.st_mode) or entry.st_size>100000: raise RuntimeError("Unexpected remote file")
                BACKUP.parent.mkdir(parents=True,exist_ok=True)
                sftp.get(REMOTE,str(BACKUP));exists=True
            except FileNotFoundError: pass
            temp=REMOTE+".deploying"
            sftp.put(str(SOURCE),temp,confirm=True)
            sftp.posix_rename(temp,REMOTE)
            try:
                for i in range(5):
                    time.sleep(3)
                    if smoke():
                        print("PASS: public pricing page, both prices, navigation hook, appearance switch")
                        return
                raise RuntimeError("Live smoke test failed")
            except Exception:
                if exists:
                    restore=REMOTE+".rollback"
                    sftp.put(str(BACKUP),restore,confirm=True)
                    sftp.posix_rename(restore,REMOTE)
                else:sftp.remove(REMOTE)
                print("ROLLBACK completed")
                raise
        finally:sftp.close()
    finally:transport.close()

if __name__=="__main__":
    try:main()
    except Exception as e:
        print("ODPC PRICES DEPLOY FAILED:",e,file=sys.stderr)
        sys.exit(1)
