#!/usr/bin/env python3
"""Install WordPress core only into a verified empty /Lenchen directory."""
import base64,hashlib,hmac,io,os,posixpath,stat,urllib.request,zipfile
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
TARGET="/Lenchen"
def main():
 password=os.environ.get("ODPC_SFTP_PASSWORD","")
 if not password: raise RuntimeError("Missing existing ODPC_SFTP_PASSWORD secret")
 tr=paramiko.Transport((HOST,22));tr.start_client(timeout=30)
 try:
  got="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
  if not hmac.compare_digest(got,PIN):raise RuntimeError("SSH host key mismatch")
  tr.auth_password(USER,password)
  if not tr.is_authenticated():raise RuntimeError("SFTP authentication failed")
  s=paramiko.SFTPClient.from_transport(tr)
  try:
   if not stat.S_ISDIR(s.stat(TARGET).st_mode):raise RuntimeError("Lenchen directory missing")
   names=s.listdir(TARGET)
   if names:raise RuntimeError("Safety stop: /Lenchen is not empty: "+", ".join(names[:12]))
   print("Confirmed empty /Lenchen. Downloading official WordPress core.")
   req=urllib.request.Request("https://wordpress.org/latest.zip",headers={"User-Agent":"ODPC-Lenchen-Installer/1.0"})
   with urllib.request.urlopen(req,timeout=90) as response: blob=response.read(32000000)
   if len(blob)>31000000:raise RuntimeError("Archive unexpectedly large")
   z=zipfile.ZipFile(io.BytesIO(blob))
   files=[x for x in z.infolist() if not x.is_dir() and x.filename.startswith("wordpress/")]
   if not any(x.filename=="wordpress/wp-settings.php" for x in files):raise RuntimeError("Invalid WordPress archive")
   dirs=set()
   for item in files:
    relative=item.filename[len("wordpress/"):]
    if not relative or relative.startswith("/") or ".." in relative.split("/"):raise RuntimeError("Unsafe ZIP entry")
    remote=posixpath.join(TARGET,relative)
    parent=posixpath.dirname(remote)
    stack=[]
    while parent not in (TARGET,"/"):
     stack.append(parent);parent=posixpath.dirname(parent)
    for d in reversed(stack):
     if d not in dirs:
      try:s.mkdir(d)
      except OSError:
       if not stat.S_ISDIR(s.stat(d).st_mode):raise
      dirs.add(d)
    with z.open(item) as src, s.open(remote,"wb") as dst:
     while True:
      block=src.read(131072)
      if not block:break
      dst.write(block)
   print("WordPress core transferred. File count:",len(files))
   print("NEXT: Configure MySQL via browser installer. No database secrets stored or wp-config.php created.")
  finally:s.close()
 finally:tr.close()
if __name__=="__main__":main()
