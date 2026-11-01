"""Deploy only ODPC TV to an explicitly configured existing WordPress installation."""
import base64,hashlib,hmac,os,stat
from pathlib import Path
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN
source=Path("site-deploy/odpc.de/odpc-tv-lab.php").read_bytes()
if not any(marker in source for marker in (b"Plugin Name: ODPC TV Lab", b"Plugin Name: ODPC TV\n")):raise RuntimeError("Invalid TV plugin")
root=os.environ.get("ODPC_WORDPRESS_ROOT","").rstrip("/")
if not root.startswith("/") or root=="/":raise RuntimeError("Set ODPC_WORDPRESS_ROOT to verified WordPress directory")
transport=paramiko.Transport((HOST,22))
try:
 transport.start_client(timeout=25)
 fingerprint="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
 if not hmac.compare_digest(fingerprint,PIN):raise RuntimeError("SFTP host fingerprint mismatch")
 transport.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
 if not transport.is_authenticated():raise RuntimeError("SFTP authentication failed")
 sftp=paramiko.SFTPClient.from_transport(transport)
 try:
  for item in ("wp-admin","wp-content","wp-includes"):
   if not stat.S_ISDIR(sftp.stat(root+"/"+item).st_mode):raise RuntimeError("Not WordPress root")
  directory=root+"/wp-content/mu-plugins"
  try:
   if not stat.S_ISDIR(sftp.stat(directory).st_mode):raise RuntimeError("Invalid mu-plugins directory")
  except FileNotFoundError:sftp.mkdir(directory)
  target=directory+"/odpc-tv-lab.php"
  try:
   with sftp.open(target,"rb") as f:existing=f.read()
   if b"Plugin Name: ODPC TV Lab" not in existing:raise RuntimeError("Refusing to replace unrelated file")
  except FileNotFoundError:pass
  temp=target+".uploading"
  with sftp.open(temp,"wb") as f:f.write(source)
  sftp.posix_rename(temp,target)
  with sftp.open(target,"rb") as f:actual=f.read()
  if hashlib.sha256(actual).digest()!=hashlib.sha256(source).digest():raise RuntimeError("Upload checksum mismatch")
  print("ODPC TV deployment verified")
 finally:sftp.close()
finally:transport.close()
