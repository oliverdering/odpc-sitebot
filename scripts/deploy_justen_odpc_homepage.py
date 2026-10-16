#!/usr/bin/env python3
"""Deploy a single standalone ODPC Justen preview without modifying WordPress."""
import os,sys,stat,posixpath,hashlib,base64,hmac,time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/justen"
SOURCE=Path("site-deploy/ra-justen.odpc.de/kanzlei-layout-preview.html")
BACKUP=Path("backup-justen-preview")
URL="https://ra-justen.odpc.de/"
def verify():
    with urlopen(Request(URL+"?v="+str(int(time.time())),headers={"Cache-Control":"no-cache","User-Agent":"ODPC-Justen-Preview-QA"}),timeout=25) as resp:
        html=resp.read(300000).decode("utf-8","replace")
    return all(s in html for s in ("Anne-Kathrin Justen", "class=\"profile\"", "PraeLex.ai", "ra-justen.de/impressum/"))
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD","")
    if not password: raise RuntimeError("ODPC_SFTP_PASSWORD secret missing")
    if not SOURCE.is_file() or SOURCE.stat().st_size>100000: raise RuntimeError("Preview file missing or too large")
    tr=paramiko.Transport((HOST,22));tr.start_client(timeout=20)
    try:
        fingerprint="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(fingerprint,PIN): raise RuntimeError("Unexpected SFTP host key")
        tr.auth_password(USER,password)
        if not tr.is_authenticated():raise RuntimeError("IONOS SFTP authentication failed")
        sftp=paramiko.SFTPClient.from_transport(tr)
        try:
            candidates=["/justen","/clickandbuilds/Dering/justen","/clickandbuilds/justen","/justen/","/homepages/justen"]
            print("SFTP current directory:",sftp.getcwd() or "(server default)")
            try:
                print("SFTP root entries:",[e.filename for e in sftp.listdir_attr(".") if e.filename.lower() in ("justen","clickandbuilds","htdocs","html","www")])
            except OSError as exc: print("Root listing unavailable:",type(exc).__name__)
            available=[]
            for candidate in candidates:
                try:
                    if stat.S_ISDIR(sftp.stat(candidate).st_mode):available.append(candidate)
                except OSError:pass
            print("Matching known directory candidates:",available)
            if len(available)!=1:raise RuntimeError("Cannot safely determine unique Kanzlei subdomain target; no files modified")
            ROOT=available[0]
            remote=posixpath.join(ROOT,"index.html")
            temp=remote+".uploading"
            BACKUP.mkdir(exist_ok=True)
            prior=BACKUP/"previous-index.html"
            existed=False
            try:
                st=sftp.lstat(remote)
                if not stat.S_ISREG(st.st_mode) or st.st_size>100000:raise RuntimeError("Unsafe preview target")
                sftp.get(remote,str(prior));existed=True
            except FileNotFoundError:pass
            htaccess=posixpath.join(ROOT,".htaccess")
            htbackup=BACKUP/"previous-htaccess"
            ht_exists=False
            try:
                htstat=sftp.lstat(htaccess)
                if not stat.S_ISREG(htstat.st_mode) or htstat.st_size>100000: raise RuntimeError("Unsafe .htaccess")
                sftp.get(htaccess,str(htbackup)); ht_exists=True
            except FileNotFoundError: pass
            old_ht=htbackup.read_bytes() if ht_exists else b""
            new_ht=old_ht if b"DirectoryIndex index.html index.php" in old_ht else b"DirectoryIndex index.html index.php\n"+old_ht
            try:
                sftp.put(str(SOURCE),temp,confirm=True)
                sftp.posix_rename(temp,remote)
                if new_ht!=old_ht:
                    ht_tmp=htaccess+".odpc-uploading"
                    with sftp.open(ht_tmp,"wb") as hf: hf.write(new_ht)
                    sftp.posix_rename(ht_tmp,htaccess)
                for attempt in range(4):
                    time.sleep(3)
                    try:
                        if verify():
                            print("PASS: deployed preview "+URL)
                            return
                    except Exception as exc:print("Verification pending",type(exc).__name__,str(exc)[:180])
                raise RuntimeError("Public URL verification failed; rollback")
            except Exception:
                if existed:
                    restore=remote+".restore"
                    sftp.put(str(prior),restore,confirm=True)
                    sftp.posix_rename(restore,remote)
                else:
                    try:sftp.remove(remote)
                    except FileNotFoundError:pass
                if ht_exists:
                    restore_ht=htaccess+".odpc-restore"
                    sftp.put(str(htbackup),restore_ht,confirm=True)
                    sftp.posix_rename(restore_ht,htaccess)
                else:
                    try:sftp.remove(htaccess)
                    except FileNotFoundError:pass
                print("ROLLBACK: homepage and .htaccess restored")
                raise
            finally:
                try:sftp.remove(temp)
                except OSError:pass
        finally:sftp.close()
    finally:tr.close()
if __name__=="__main__":
    try:main()
    except Exception as exc:
        print("JUSTEN PREVIEW DEPLOY FAILED:",exc,file=sys.stderr)
        sys.exit(1)
