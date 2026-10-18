#!/usr/bin/env python3
"""Deploy two narrowly scoped brand mu-plugins via pinned IONOS SFTP; backup and rollback."""
import os,base64,hashlib,hmac,posixpath,stat,time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
SITES=[
("odpc.de","/clickandbuilds/Dering","site-deploy/odpc.de/odpc-brand-palette.php","odpc-corporate-blue-2026"),
("ai.odpc.de",None,"site-deploy/ai.odpc.de/odpc-brand-palette.php","helpermonkey-corporate-green-2026")]
def verify(domain,marker):
    req=Request("https://"+domain+"/?odpc_brand_check="+str(time.time_ns()),headers={"Cache-Control":"no-cache","User-Agent":"ODPC-brand-deploy-check"})
    with urlopen(req,timeout=25) as f:
        return marker in f.read(400000).decode("utf-8","replace")
def ai_root(sftp):
    configured=os.environ.get("AI_ODPC_THEME_PATH","").strip()
    if configured:
        if not configured.endswith("/wp-content/themes/helpermonkey-business-v34-child"):raise RuntimeError("Unexpected AI theme path")
        sftp.stat(configured+"/style.css")
        return configured.split("/wp-content/themes/")[0]
    root="/ai-odpc"
    sftp.stat(root+"/wp-content/themes/helpermonkey-business-v34-child/style.css")
    return root
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD")
    if not password:raise RuntimeError("ODPC_SFTP_PASSWORD missing")
    t=paramiko.Transport((HOST,22))
    t.start_client(timeout=25)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(PIN,actual):raise RuntimeError("SFTP host fingerprint mismatch")
        t.auth_password(USER,password)
        if not t.is_authenticated():raise RuntimeError("SFTP authentication failed")
        s=paramiko.SFTPClient.from_transport(t)
        try:
            for domain,root,source,marker in SITES:
                if root is None:root=ai_root(s)
                s.stat(root+"/wp-config.php")
                source=Path(source)
                if not source.is_file() or source.stat().st_size>12000:raise RuntimeError("Invalid brand module")
                folder=posixpath.join(root,"wp-content","mu-plugins")
                try:s.stat(folder)
                except FileNotFoundError:s.mkdir(folder)
                target=folder+"/odpc-brand-palette.php"
                tmp=target+".deploying"
                backup=Path("brand-backups")/domain
                backup.mkdir(parents=True,exist_ok=True)
                previous=backup/"previous.php"
                existed=False
                try:
                    st=s.lstat(target)
                    if not stat.S_ISREG(st.st_mode) or st.st_size>12000:raise RuntimeError("Unexpected existing brand module")
                    s.get(target,str(previous))
                    existed=True
                except FileNotFoundError:pass
                try:
                    s.put(str(source),tmp,confirm=True)
                    s.posix_rename(tmp,target)
                    if not any(verify(domain,marker) for _ in range(3)):raise RuntimeError("Live marker missing: "+domain)
                    print("PASS:",domain,"brand marker found")
                except Exception:
                    if existed:
                        recovery=target+".rollback"
                        s.put(str(previous),recovery,confirm=True)
                        s.posix_rename(recovery,target)
                    else:
                        try:s.remove(target)
                        except FileNotFoundError:pass
                    raise
                finally:
                    try:s.remove(tmp)
                    except OSError:pass
        finally:s.close()
    finally:t.close()
if __name__=="__main__":main()
