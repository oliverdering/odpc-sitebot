#!/usr/bin/env python3
"""Safely deploy ODPC remote help as isolated WordPress MU plugin + HTML."""
import os,base64,hashlib,hmac,stat,posixpath,urllib.request
from pathlib import Path
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/clickandbuilds/Dering"
FILES={"site-deploy/odpc.de/odpc-remote-landing.php":"odpc-remote-landing.php","remote-fernwartung/index.html":"odpc-remote-landing.html"}
def main():
 t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
 try:
  fingerprint="SHA256:"+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
  if not hmac.compare_digest(fingerprint,PIN):raise RuntimeError("Host key mismatch")
  t.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
  if not t.is_authenticated():raise RuntimeError("Authentication failed")
  s=paramiko.SFTPClient.from_transport(t)
  try:
   s.stat(ROOT+"/wp-config.php")
   dest=ROOT+"/wp-content/mu-plugins"
   if not stat.S_ISDIR(s.stat(dest).st_mode):raise RuntimeError("MU plugin directory missing")
   backups={}
   for source,name in FILES.items():
    data=Path(source).read_bytes()
    if len(data)>100000:raise RuntimeError("File too large")
    target=posixpath.join(dest,name)
    try:
     with s.open(target,"rb") as f:backups[target]=f.read()
    except FileNotFoundError:backups[target]=None
   changed=[]
   try:
    for source,name in FILES.items():
     target=posixpath.join(dest,name);data=Path(source).read_bytes()
     with s.open(target+".upload","wb") as f:f.write(data)
     with s.open(target+".upload","rb") as f:
      if hashlib.sha256(f.read()).digest()!=hashlib.sha256(data).digest():raise RuntimeError("Checksum mismatch")
     s.posix_rename(target+".upload",target);changed.append(target)
    req=urllib.request.Request("https://odpc.de/remote-fernwartung/?qa=1",headers={"User-Agent":"ODPC-Remote-QA"})
    with urllib.request.urlopen(req,timeout=35) as resp:html=resp.read(300000).decode("utf-8","replace")
    if not all(x in html for x in ("ODPC Fernhilfe","releaseAPI","WhatsApp")):raise RuntimeError("Live content verification failed")
    print("DEPLOY VERIFIED https://odpc.de/remote-fernwartung/")
   except Exception:
    for target in reversed(changed):
     old=backups[target]
     if old is None:s.remove(target)
     else:
      with s.open(target+".rollback","wb") as f:f.write(old)
      s.posix_rename(target+".rollback",target)
    print("ROLLBACK COMPLETED")
    raise
  finally:s.close()
 finally:t.close()
if __name__=="__main__":main()
