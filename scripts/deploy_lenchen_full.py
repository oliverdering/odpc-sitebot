#!/usr/bin/env python3
"""Deploy/update full Tante Lenchen theme, limited to one WordPress theme directory."""
import os,base64,hashlib,hmac,posixpath,stat,pathlib
import paramiko
HOST="home119567219.1and1-data.host";USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/Lenchen";THEME=ROOT+"/wp-content/themes/tante-lenchen"
SOURCE=pathlib.Path("site-deploy/tante-lenchen")
def main():
 pw=os.environ.get("ODPC_SFTP_PASSWORD")
 if not pw:raise RuntimeError("Missing SFTP secret")
 files=list(SOURCE.glob("*"))
 if len(files)<7 or any(not p.is_file() or p.suffix not in (".php",".css") for p in files):raise RuntimeError("Incomplete or unexpected theme payload")
 tr=paramiko.Transport((HOST,22));tr.start_client(timeout=30)
 try:
  fp="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
  if not hmac.compare_digest(fp,PIN):raise RuntimeError("SSH fingerprint mismatch")
  tr.auth_password(USER,pw)
  if not tr.is_authenticated():raise RuntimeError("Authentication failed")
  s=paramiko.SFTPClient.from_transport(tr)
  try:
   for p in (ROOT,ROOT+"/wp-admin",ROOT+"/wp-includes",ROOT+"/wp-content/themes"):
    if not stat.S_ISDIR(s.stat(p).st_mode):raise RuntimeError("Missing WordPress directory: "+p)
   try:
    if not stat.S_ISDIR(s.stat(THEME).st_mode):raise RuntimeError("Theme target not directory")
    with s.open(THEME+"/style.css","r") as f: existing=f.read().decode("utf-8","replace")
    if "Tante Lenchen" not in existing and "Tante Lenchen" not in existing.replace("Café ",""):raise RuntimeError("Refusing overwrite of unrelated theme")
   except FileNotFoundError:s.mkdir(THEME)
   for p in files:
    target=THEME+"/"+p.name;temporary=target+".odpc-new"
    with s.open(temporary,"wb") as f:f.write(p.read_bytes())
    s.rename(temporary,target) if not hasattr(s,"posix_rename") else s.posix_rename(temporary,target)
   print("PASS: full Tante Lenchen theme deployed, files:",len(files),"target:",THEME)
   print("Activation required once in WordPress admin; dish editor seeds on activation.")
  finally:s.close()
 finally:tr.close()
if __name__=="__main__":main()
