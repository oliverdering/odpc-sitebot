#!/usr/bin/env python3
"""Read-only SFTP inventory for Bahnhof Grenzau; never output secrets or file contents."""
import os,base64,hashlib,hmac,stat,paramiko
HOST="home119567219.1and1-data.host"; USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/Bahnhof-Grenzau"
tr=paramiko.Transport((HOST,22));tr.start_client(timeout=30)
try:
 fp="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
 if not hmac.compare_digest(fp,PIN):raise RuntimeError("Host key mismatch")
 tr.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
 if not tr.is_authenticated():raise RuntimeError("Authentication failed")
 s=paramiko.SFTPClient.from_transport(tr)
 try:
  for folder in [ROOT,ROOT+"/wp-admin",ROOT+"/wp-includes",ROOT+"/wp-content",ROOT+"/.odpc-staging"]:
   try:
    entries=s.listdir_attr(folder)
    print("DIR",folder,"items",len(entries),"mode",oct(stat.S_IMODE(s.stat(folder).st_mode)))
    for e in sorted(entries,key=lambda x:x.filename)[:35]:
     if folder==ROOT or e.filename in ("themes","plugins","uploads","install.php","upgrade.php","version.php","index.php","wordpress-latest.zip"):
      print(" ",e.filename,"dir" if stat.S_ISDIR(e.st_mode) else "file","bytes",e.st_size)
   except FileNotFoundError:print("MISSING",folder)
  for file in ["wp-config.php","wp-config-sample.php","wp-admin/install.php","wp-includes/version.php","install.php","wordpress-latest.zip"]:
   try: print("EXISTS",file,"bytes",s.stat(ROOT+"/"+file).st_size)
   except FileNotFoundError:print("MISSING",file)
 finally:s.close()
finally:tr.close()

# Public HTTP diagnostics: report status only, never cookies, headers or response bodies.
import urllib.request, urllib.error
for path in ["/", "/wp-login.php", "/wp-admin/install.php", "/wp-admin/setup-config.php"]:
 try:
  with urllib.request.urlopen("https://bahnhof-grenzau.odpc.de"+path, timeout=20) as response:
   print("HTTPS",path,"status",response.status,"final_url",response.url)
 except urllib.error.HTTPError as exc:
  print("HTTPS",path,"status",exc.code)
 except Exception as exc:
  print("HTTPS",path,"error_type",type(exc).__name__)
