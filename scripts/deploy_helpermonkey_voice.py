#!/usr/bin/env python3
"""Deploy voice module as WordPress mu-plugin with backup, verification and rollback."""
import base64,hashlib,hmac,os,posixpath,stat,sys,time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
from deploy_helpermonkey_v4 import find_theme,HOST,USER,PIN
SRC=Path("site-deploy/ai.odpc.de/odpc-monkey-voice-interview.php")
def main():
    password=os.environ.get("ODPC_SFTP_PASSWORD","")
    if not password: raise RuntimeError("Missing ODPC_SFTP_PASSWORD; no deployment")
    if not SRC.is_file() or SRC.stat().st_size>60000: raise RuntimeError("Invalid source")
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=25)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual,PIN): raise RuntimeError("SSH fingerprint mismatch")
        transport.auth_password(USER,password)
        if not transport.is_authenticated(): raise RuntimeError("SFTP authentication failed")
        sftp=paramiko.SFTPClient.from_transport(transport)
        try:
            theme=find_theme(sftp)
            root=theme.split("/wp-content/themes/")[0]
            for path in (root+"/wp-config.php",theme+"/functions.php"): sftp.stat(path)
            folder=root+"/wp-content/mu-plugins"
            try:
                if not stat.S_ISDIR(sftp.stat(folder).st_mode): raise RuntimeError("Unsafe mu-plugins path")
            except OSError as exc:
                if getattr(exc,"errno",None) == 2: sftp.mkdir(folder)
                else: raise
            target=posixpath.join(folder,"odpc-monkey-voice-interview.php")
            temp=target+".uploading"
            backup=Path("backup-helpermonkey-voice/previous.php")
            existed=False
            try:
                info=sftp.lstat(target)
                if not stat.S_ISREG(info.st_mode) or info.st_size>60000: raise RuntimeError("Unsafe existing file")
                backup.parent.mkdir(exist_ok=True)
                sftp.get(target,str(backup))
                existed=True
            except OSError as exc:
                if getattr(exc,"errno",None) != 2: raise
            try:
                sftp.put(str(SRC),temp,confirm=True)
                sftp.posix_rename(temp,target)
                verified=False
                for attempt in range(4):
                    time.sleep(3)
                    req=Request("https://ai.odpc.de/?hm_voice_check="+str(int(time.time())),headers={"Cache-Control":"no-cache"})
                    try:
                        with urlopen(req,timeout=25) as response:
                            html=response.read(800000).decode("utf-8","replace")
                        if 'id="odpc-voice-open"' in html and 'id="odpc-voice-mic"' in html:
                            verified=True
                            break
                    except Exception as exc: print("Smoke check retry:",type(exc).__name__)
                if not verified: raise RuntimeError("Voice UI absent from public homepage")
                print("PASS: voice module is visible in live homepage HTML")
            except Exception:
                print("Verification failed: rolling back voice module")
                if existed:
                    sftp.put(str(backup),target+".rollback",confirm=True)
                    sftp.posix_rename(target+".rollback",target)
                else:
                    try: sftp.remove(target)
                    except OSError: pass
                raise
            finally:
                try: sftp.remove(temp)
                except OSError: pass
        finally: sftp.close()
    finally: transport.close()
if __name__=="__main__":
    try: main()
    except Exception as exc:
        print("VOICE DEPLOY FAILED:",exc,file=sys.stderr)
        sys.exit(1)
