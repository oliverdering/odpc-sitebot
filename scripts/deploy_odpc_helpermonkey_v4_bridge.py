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
    required=('id="odpc-hm-v4-js"','id="odpc-hm-v4-helpbtn"','id="odpc-reader-language"','id="odpc-reader-voice"','id="odpc-reader-test"')
    missing=[marker for marker in required if marker not in body]
    if missing: print('LIVE MARKUP MISSING:', ', '.join(missing), flush=True)
    return not missing
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD","")
    if not password:raise RuntimeError("Missing SFTP deployment secret")
    if not SOURCE.is_file() or SOURCE.stat().st_size>100000:raise RuntimeError("Source not present or too large")
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
                if not stat.S_ISREG(st.st_mode) or st.st_size>100000:raise RuntimeError("Unsafe existing target")
                sftp.get(remote,str(previous))
                existed=True
            except FileNotFoundError:pass
            try:
                sftp.put(str(SOURCE),temp,confirm=True)
                sftp.posix_rename(temp,remote)
                # Verify uploaded bytes independently of the public HTTP endpoint.
                with sftp.open(remote,"rb") as uploaded:
                    digest=hashlib.sha256()
                    while True:
                        chunk=uploaded.read(65536)
                        if not chunk:break
                        digest.update(chunk)
                expected=hashlib.sha256(SOURCE.read_bytes()).digest()
                if not hmac.compare_digest(digest.digest(),expected):
                    raise RuntimeError("Remote SFTP checksum mismatch")
                print("PASS: deployed SFTP file SHA256 matches repository source",flush=True)
                good=False
                reachable=False
                for i in range(5):
                    time.sleep(3)
                    try:
                        result=smoke()
                        reachable=True
                        if result:good=True;break
                    except Exception as exc:
                        print("Public smoke test temporarily unreachable:",type(exc).__name__,str(exc),flush=True)
                if reachable and not good:
                    raise RuntimeError("Public site reachable but required markup missing")
                # WP Fastest Cache can serve cached HTML before WordPress/MU plugins run.
                # Its own purge routine renames cache/all into cache/tmpWpfc; perform
                # the same atomic rotation over SFTP, without deleting any files.
                cache_base=ROOT+"/wp-content/cache"
                cache_all=cache_base+"/all"
                cache_tmp=cache_base+"/tmpWpfc"
                try:
                    old=sftp.stat(cache_all)
                    if not stat.S_ISDIR(old.st_mode):
                        raise RuntimeError("WP Fastest Cache 'all' path is not a directory")
                    try:
                        if not stat.S_ISDIR(sftp.stat(cache_tmp).st_mode):
                            raise RuntimeError("Unexpected WPFC tmp directory type")
                    except FileNotFoundError:
                        sftp.mkdir(cache_tmp)
                    target=cache_tmp+"/"+str(int(time.time()))
                    sftp.rename(cache_all,target)
                    print("PASS: rotated WP Fastest Cache HTML directory atomically; old cache retained for WPFC cleanup",flush=True)
                except FileNotFoundError:
                    print("WARNING: expected WP Fastest Cache path missing; NOT claiming cache was cleared",flush=True)
                    # Diagnose actual cache layout without deleting or renaming anything.
                    for candidate in (ROOT+"/wp-content/cache",ROOT+"/wp-content",ROOT+"/wp-content/cache/wpfc-minified",ROOT+"/wp-content/cache/wp-optimize",ROOT+"/wp-content/cache/all"):
                        try:
                            entries=sftp.listdir_attr(candidate)
                            names=[e.filename+("/" if stat.S_ISDIR(e.st_mode) else "") for e in entries]
                            print("CACHE PATH DIAGNOSTIC",candidate,"entries:",names[:35],flush=True)
                        except (OSError,IOError) as diag:
                            print("CACHE PATH DIAGNOSTIC",candidate,type(diag).__name__,str(diag),flush=True)
                except Exception as cache_exc:
                    print("WARNING: WP Fastest Cache HTML rotation failed:",str(cache_exc),flush=True)
                if good:
                    print("PASS: ODPC bridge publicly visible",flush=True)
                else:
                    print("WARNING: public site unreachable; SFTP checksum verified, retain deployed file and recheck public HTML later",flush=True)
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
