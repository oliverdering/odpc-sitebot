#!/usr/bin/env python3
"""Read-only Bahnhof Grenzau 403 diagnostics. Redact potential secrets."""
import os,base64,hashlib,hmac,stat,paramiko
HOST="home119567219.1and1-data.host"; USER="acc1461323326"; ROOT="/Bahnhof-Grenzau"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
t=paramiko.Transport((HOST,22));t.start_client(timeout=30)
try:
 fp="SHA256:"+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
 if not hmac.compare_digest(fp,PIN):raise RuntimeError("Unexpected SFTP host key")
 t.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
 s=paramiko.SFTPClient.from_transport(t)
 try:
  for path in [ROOT,ROOT+"/.htaccess",ROOT+"/index.php",ROOT+"/wp-admin",ROOT+"/wp-admin/setup-config.php",ROOT+"/wp-admin/install.php",ROOT+"/wp-login.php",ROOT+"/wp-config.php"]:
   try:
    a=s.stat(path);print("PATH",path,"mode",oct(stat.S_IMODE(a.st_mode)),"type","dir" if stat.S_ISDIR(a.st_mode) else "file","size",a.st_size)
   except FileNotFoundError:print("MISSING",path)
  for path in [ROOT+"/.htaccess"]:
   try:
    with s.open(path,"rb") as f: raw=f.read(4096).decode("utf-8","replace")
    for i,line in enumerate(raw.splitlines(),1):
     # Do not print arbitrary text: may contain passwords or tokens
     words=line.strip().split()
     directive=words[0] if words else "(blank)"
     if directive.startswith("#"):directive="(comment)"
     print("HTACCESS_LINE",i,"directive",directive,"characters",len(line))
   except FileNotFoundError:pass
 finally:s.close()
finally:t.close()
