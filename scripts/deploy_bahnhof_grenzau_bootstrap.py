#!/usr/bin/env python3
import os,base64,hashlib,hmac,io,pathlib,posixpath,stat,urllib.request,secrets
import paramiko
HOST="home119567219.1and1-data.host";USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/Bahnhof-Grenzau"
def main():
 password=os.environ["ODPC_SFTP_PASSWORD"]
 url="https://wordpress.org/latest.zip"
 with urllib.request.urlopen(url,timeout=120) as r: archive=r.read()
 if len(archive)<10000000 or not archive[:2]==b"PK": raise RuntimeError("Invalid WordPress archive")
 php=pathlib.Path("site-deploy/bahnhof-grenzau/install.php").read_bytes()
 token=secrets.token_urlsafe(32)
 php=php.replace(b"__INSTALL_TOKEN__",token.encode())
 tr=paramiko.Transport((HOST,22));tr.start_client(timeout=30)
 try:
  fp="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
  if not hmac.compare_digest(fp,PIN):raise RuntimeError("SSH host fingerprint mismatch")
  tr.auth_password(USER,password)
  if not tr.is_authenticated():raise RuntimeError("SFTP authentication failed")
  s=paramiko.SFTPClient.from_transport(tr)
  try:
   if not stat.S_ISDIR(s.stat(ROOT).st_mode):raise RuntimeError("Target directory missing")
   existing=set(s.listdir(ROOT))
   if existing-{"wordpress-latest.zip","install.php"}:
    raise RuntimeError("Target not empty; refusing to overwrite: "+", ".join(sorted(existing)[:12]))
   for name,data in [("wordpress-latest.zip",archive),("install.php",php)]:
    with s.open(ROOT+"/"+name+".uploading","wb") as f:f.write(data)
    s.rename(ROOT+"/"+name+".uploading",ROOT+"/"+name)
   print("BOOTSTRAP DEPLOYED: "+ROOT)
   print("Open https://bahnhof-grenzau.odpc.de/install.php?token="+token)
   print("After extraction, delete install.php and wordpress-latest.zip; then complete wp-admin/install.php")
  finally:s.close()
 finally:tr.close()
if __name__=="__main__":main()
