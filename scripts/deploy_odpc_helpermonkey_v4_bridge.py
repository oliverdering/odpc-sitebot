#!/usr/bin/env python3
"""Publish the minimal ODPC Helper Monkey bridge with atomic rollback."""
import os,sys,stat,posixpath,hashlib,base64,hmac,time
from urllib.request import Request,urlopen
from pathlib import Path
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/clickandbuilds/Dering"
SOURCE=Path("site-deploy/odpc.de/odpc-helpermonkey-v4-bridge.php")
BACKUP=Path("backup-odpc-v4-bridge")
def smoke():
    request=Request("https://odpc.de/?odpc_v4_bridge_check="+str(int(time.time())),headers={"User-Agent":"ODPC-V4-Release-Check","Cache-Control":"no-cache"})
    with urlopen(request,timeout=25) as resp:
        body=resp.read(350000).decode("utf-8","replace")
    return 'id="odpc-hm-v4-js"' in body and 'id="odpc-hm-v4-helpbtn"' in body
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD","")
    if not password:raise RuntimeError("Missing SFTP deployment secret")
    if not SOURCE.is_file() or SOURCE.stat().st_size>30000:raise RuntimeError("Source not present or too large")
    tr=paramiko.Transport((HOST,22))
    tr.start_client(timeout=20)
    try:
        key="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(key,PIN):raise RuntimeError("Host fingerprint mismatch")
        tr.auth_password(USER,password)
        if not tr.is_authenticated():raise RuntimeError("IONOS authentication failure")
        sftp=paramiko.SFTPClient.from_transport(tr)
        try:
            sftp.stat(ROOT+"/wp-content/themes/astra/style.css")
            sftp.stat(ROOT+"/wp-config.php")
            mu=ROOT+"/wp-content/mu-plugins"
            try:
                if not stat.S_ISDIR(sftp.stat(mu).st_mode):raise RuntimeError("Unexpected mu-plugin directory")
            except FileNotFoundError:sftp.mkdir(mu)
            remote=mu+"/odpc-helpermonkey-v4-bridge.php"
            temp=remote+".uploading"
            BACKUP.mkdir(exist_ok=True)
            previous=BACKUP/"previous.php"
            existed=False
            try:
                st=sftp.lstat(remote)
                if not stat.S_ISREG(st.st_mode) or st.st_size>30000:raise RuntimeError("Unsafe existing target")
                sftp.get(remote,str(previous))
                existed=True
            except FileNotFoundError:pass
            try:
                sftp.put(str(SOURCE),temp,confirm=True)
                sftp.posix_rename(temp,remote)
                good=False
                for i in range(4):
                    time.sleep(3)
                    try:
                        if smoke():good=True;break
                    except Exception as exc:print("Smoke test pending:",type(exc).__name__)
                if not good:raise RuntimeError("Expected global markup absent on live ODPC homepage")
                print("PASS: ODPC cross-site Helper Monkey V4 bridge is publicly visible")
            except Exception:
                if existed:
                    temp2=remote+".restore"
                    sftp.put(str(previous),temp2,confirm=True)
                    sftp.posix_rename(temp2,remote)
                else:
                    try:sftp.remove(remote)
                    except FileNotFoundError:pass
                print("ROLLBACK after failed deployment verification")
                raise
            finally:
                try:sftp.remove(temp)
                except OSError:pass
        finally:sftp.close()
    finally:tr.close()
if __name__=="__main__":
    try:main()
    except Exception as exc:
        print("ODPC BRIDGE DEPLOY FAILED:",str(exc),file=sys.stderr)
        sys.exit(1)
