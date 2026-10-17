#!/usr/bin/env python3
"""Read-only SFTP probe for Tante Lenchen; never modifies remote files."""
import base64,hashlib,hmac,os,posixpath,stat
import paramiko
host=os.environ["ODPC_SFTP_HOST"]
expected=os.environ["ODPC_SFTP_HOSTKEY_SHA256"]
t=paramiko.Transport((host,22))
try:
 t.start_client(timeout=25)
 actual="SHA256:"+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
 if not hmac.compare_digest(actual,expected): raise RuntimeError("Host key mismatch")
 t.auth_password(os.environ["ODPC_SFTP_USER"],os.environ["ODPC_SFTP_PASSWORD"])
 if not t.is_authenticated(): raise RuntimeError("Authentication failed")
 s=paramiko.SFTPClient.from_transport(t)
 try:
  root=s.normalize(".")
  print("SFTP authenticated; server home:",root)
  for candidate in ("/Lenchen",posixpath.join(root,"Lenchen")):
   try:
    a=s.listdir_attr(candidate)
    names=[x.filename for x in a]
    print("LENCHEN PATH FOUND:",candidate)
    print("WordPress installed:",all(x in names for x in ("wp-admin","wp-includes","wp-content")))
    print("Theme folder exists:","wp-content" in names)
    print("Root item names:",", ".join(names[:35]))
   except OSError as e: print("LENCHEN PATH UNAVAILABLE:",candidate,type(e).__name__)
 finally:s.close()
finally:t.close()
