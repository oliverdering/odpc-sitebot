"""Deploy only D.R.O.L. to its existing /drol IONOS directory; never touch ODPC."""
import base64,hashlib,hmac,os,posixpath,stat
from pathlib import Path
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN
ROOT="/drol"
SOURCE=Path("site-deploy/drol.odpc.de")
def main():
    if not (SOURCE/"index.html").is_file():raise RuntimeError("Missing D.R.O.L. homepage")
    payload=(SOURCE/"index.html").read_bytes()
    if b"Wir machen Ihre Drohne intelligenter." not in payload:raise RuntimeError("Invalid release")
    transport=paramiko.Transport((HOST,22));transport.start_client(timeout=25)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual,PIN):raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
        sf=paramiko.SFTPClient.from_transport(transport)
        try:
            if not stat.S_ISDIR(sf.lstat(ROOT).st_mode):raise RuntimeError("User-created /drol directory missing")
            for source in sorted(SOURCE.glob("*.html")):
                target=ROOT+"/"+source.name
                payload=source.read_bytes()
                try:
                    with sf.open(target,"rb") as f:old=f.read()
                    if b"D.R.O.L." not in old:raise RuntimeError("Refusing to overwrite unrelated content: "+target)
                except FileNotFoundError:old=None
                temp=target+".uploading"
                with sf.open(temp,"wb") as f:f.write(payload)
                sf.chmod(temp,0o644)
                sf.posix_rename(temp,target)
                with sf.open(target,"rb") as f:verified=f.read()
                if hashlib.sha256(verified).digest()!=hashlib.sha256(payload).digest():
                    if old is not None:
                        with sf.open(temp,"wb") as f:f.write(old)
                        sf.posix_rename(temp,target)
                    else:sf.remove(target)
                    raise RuntimeError("Remote checksum mismatch: "+target)
                print("D.R.O.L. uploaded and checksum verified:",target)
        finally:sf.close()
    finally:transport.close()
if __name__=="__main__":main()
