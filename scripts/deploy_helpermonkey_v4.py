#!/usr/bin/env python3
"""Atomic, backed-up deployment of one ODPC Helper Monkey mu-plugin to ai.odpc.de."""
import base64, hashlib, hmac, os, posixpath, stat, time, sys
from pathlib import Path
from urllib.request import Request, urlopen
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
SLUG="helpermonkey-business-v34-child"
SRC=Path("site-deploy/ai.odpc.de/odpc-helpermonkey-v4.php")
BACKUP=Path("backup-helpermonkey-v4")
def find_theme(sftp):
    configured=os.environ.get("AI_ODPC_THEME_PATH","").strip()
    if configured:
        configured=posixpath.normpath(configured)
        if not configured.endswith("/wp-content/themes/"+SLUG):
            raise RuntimeError("Configured AI theme path is not the expected helpermonkey child theme")
        if not stat.S_ISREG(sftp.stat(configured+"/style.css").st_mode):
            raise RuntimeError("Configured theme missing style.css")
        return configured
    starts=[sftp.normalize("."),"/clickandbuilds"]
    queue=[(p,0) for p in dict.fromkeys(starts)]
    found=[]
    visited=set()
    while queue and len(visited)<220:
        directory,depth=queue.pop(0)
        directory=posixpath.normpath(directory)
        if directory in visited:continue
        visited.add(directory)
        candidate=posixpath.join(directory,"wp-content/themes",SLUG)
        try:
            if stat.S_ISREG(sftp.stat(candidate+"/style.css").st_mode):
                found.append(candidate)
        except OSError:pass
        if depth>=5:continue
        try: entries=sftp.listdir_attr(directory)
        except OSError:continue
        for e in entries[:100]:
            if stat.S_ISDIR(e.st_mode) and not e.filename.startswith(".") and e.filename not in {"wp-content","node_modules","vendor","logs","tmp","cache","uploads","backup","backups"}:
                queue.append((posixpath.join(directory,e.filename),depth+1))
    if len(set(found))!=1:raise RuntimeError("AI WordPress theme must be uniquely identified before deployment")
    return found[0]
def live():
    req=Request("https://ai.odpc.de/?hm_v4_release="+str(int(time.time())),headers={"User-Agent":"HelperMonkey-V4-Release-Check/1","Cache-Control":"no-cache"})
    with urlopen(req,timeout=25) as f:
        body=f.read(300000).decode("utf-8","replace")
    return 'id="hm-v4-global-js"' in body and 'id="hm-v4-helpbtn"' in body
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD","")
    if not password:raise RuntimeError("Missing protected ODPC_SFTP_PASSWORD in GitHub Actions")
    if not SRC.is_file() or SRC.stat().st_size>60000:raise RuntimeError("Missing or oversized source")
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=25)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual,PIN):raise RuntimeError("Remote host key mismatch, deployment aborted")
        transport.auth_password(USER,password)
        if not transport.is_authenticated():raise RuntimeError("IONOS SFTP authentication refused")
        sftp=paramiko.SFTPClient.from_transport(transport)
        try:
            theme=find_theme(sftp)
            root=theme.split("/wp-content/themes/")[0]
            if not root.startswith("/") or not root:raise RuntimeError("Unsafe WordPress path")
            # Verified theme and WordPress files must coexist before any write.
            for p in [root+"/wp-content",root+"/wp-config.php",theme+"/functions.php"]:
                sftp.stat(p)
            mu=posixpath.join(root,"wp-content","mu-plugins")
            try:
                if not stat.S_ISDIR(sftp.stat(mu).st_mode):raise RuntimeError("Unexpected mu-plugins target")
            except FileNotFoundError:
                sftp.mkdir(mu)
            dest=posixpath.join(mu,"odpc-helpermonkey-v4.php")
            tmp=dest+".uploading"
            BACKUP.mkdir(exist_ok=True)
            backup=BACKUP/"previous.php"
            prior=False
            try:
                st=sftp.lstat(dest)
                if not stat.S_ISREG(st.st_mode) or st.st_size>60000:raise RuntimeError("Unsafe existing plugin target")
                sftp.get(dest,str(backup)); prior=True
            except FileNotFoundError: pass
            print("Preflight success; previous v4 module present:",prior)
            try:
                sftp.put(str(SRC),tmp,confirm=True)
                sftp.posix_rename(tmp,dest)
                ok=False
                for k in range(4):
                    time.sleep(3)
                    try:
                        if live():ok=True;break
                    except Exception as error:print("Smoke test delayed:",type(error).__name__)
                if not ok:raise RuntimeError("Public live markup did not show V4 global controls")
                print("PASS: Helper Monkey V4 navigation, help and WhatsApp visible on live homepage")
            except Exception:
                print("Deployment verification failed. Performing rollback.")
                if prior:
                    recovery=dest+".rollback"
                    sftp.put(str(backup),recovery,confirm=True)
                    sftp.posix_rename(recovery,dest)
                else:
                    try:sftp.remove(dest)
                    except FileNotFoundError:pass
                raise
            finally:
                try:sftp.remove(tmp)
                except OSError:pass
        finally:sftp.close()
    finally:transport.close()
if __name__=="__main__":
    try:main()
    except Exception as exc:
        print("HELPER MONKEY V4 DEPLOY FAILED:",str(exc),file=sys.stderr)
        sys.exit(1)
