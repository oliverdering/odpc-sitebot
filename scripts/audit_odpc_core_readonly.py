#!/usr/bin/env python3
"""Read-only ODPC core integrity investigation via pinned IONOS SFTP."""
import os,sys,hashlib,base64,hmac,posixpath,json
from urllib.request import urlopen,Request
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/clickandbuilds/Dering"
FILES=["liesmich.html","wp-config-sample.php","wp-includes/version.php"]
def main():
    pw=os.getenv("ODPC_SFTP_PASSWORD")
    if not pw:raise RuntimeError("SFTP secret unavailable")
    tr=paramiko.Transport((HOST,22));tr.start_client(timeout=20)
    try:
        k="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(k,PIN):raise RuntimeError("SFTP host fingerprint mismatch")
        tr.auth_password(USER,pw)
        if not tr.is_authenticated():raise RuntimeError("IONOS login denied")
        sf=paramiko.SFTPClient.from_transport(tr)
        try:
            expected={}
            try:
                url="https://api.wordpress.org/core/checksums/1.0/?version=7.1.3&locale=de_DE"
                req=Request(url,headers={"User-Agent":"ODPC-Security-Audit/1"})
                with urlopen(req,timeout=18) as f:data=json.load(f)
                expected=data.get("checksums",{})
            except Exception as error:
                print("Official checksums could not be loaded:",type(error).__name__)
            for file in FILES:
                with sf.open(posixpath.join(ROOT,file),"rb") as f:content=f.read(150000)
                actual=hashlib.md5(content).hexdigest()
                official=expected.get(file)
                match=actual==official if official else None
                signatures=[x for x in ["eval(","base64_decode(","shell_exec(","system(","passthru(","assert("] if x.encode() in content]
                print("File",file,"bytes",len(content),"matches official md5",match,"suspicious signatures",signatures)
                if file.endswith("version.php"):
                    for pat in [b"$wp_version",b"$wp_db_version"]:
                        print("WordPress version assignment present:",pat.decode(),pat in content)
            # Sample PHP ini placement without printing path contents or secret values.
            sample=[ROOT+"/wp-includes/php.ini",ROOT+"/wp-admin/php.ini",ROOT+"/wp-admin/css/php.ini"]
            for file in sample:
                try:
                    with sf.open(file,"rb") as h:b=h.read(10000)
                    lines=[x.strip() for x in b.decode("utf-8","replace").splitlines() if x.strip() and not x.strip().startswith(";")]
                    keys=[x.split("=",1)[0].strip() for x in lines if "=" in x]
                    print("PHP ini sample present, length",len(b),"configuration keys",keys[:15],"hash8",hashlib.sha256(b).hexdigest()[:8])
                except OSError:
                    print("PHP ini sample missing")
            print("PASS: read-only security investigation completed, no server files changed")
        finally:sf.close()
    finally:tr.close()
if __name__=="__main__":
    try:main()
    except Exception as exc:print("AUDIT ERROR",str(exc),file=sys.stderr);sys.exit(1)
