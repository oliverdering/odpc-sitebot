"""Single-page release; fail closed on drift, back up, upload, verify, restore on error."""
import os, posixpath, stat, hashlib, base64, hmac, time
from pathlib import Path
from urllib.request import Request, urlopen
import paramiko
from scripts.deploy_helpermonkey_v4 import HOST, USER, PIN, find_theme
BASE='917cefd114edb8ee2add8ca04e25f1101d277beda63d30b3ba20e6a88b42ee4d'
new=Path('mission-control-release-v2/index.html').read_bytes()
picture=Path('mission-control-release-v2/oliver-claire-klavier.webp').read_bytes()
pw=os.environ.get('ODPC_SFTP_PASSWORD','')
if not pw:raise RuntimeError('Missing SFTP credential')
t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
if not hmac.compare_digest(actual,PIN):t.close();raise RuntimeError('Host key mismatch')
t.auth_password(USER,pw)
if not t.is_authenticated():t.close();raise RuntimeError('SFTP auth failed')
s=paramiko.SFTPClient.from_transport(t);root=find_theme(s).split('/wp-content/themes/')[0];folder=posixpath.join(root,'ki-orchester');dest=folder+'/index.html';pic=folder+'/oliver-claire-klavier.webp';changed=False;created=False
try:
 if not stat.S_ISDIR(s.lstat(folder).st_mode) or not stat.S_ISREG(s.lstat(dest).st_mode):raise RuntimeError('Unsafe destination')
 try:s.lstat(pic)
 except FileNotFoundError:pass
 else:raise RuntimeError('Piano asset already exists; refusing overwrite')
 with s.open(dest,'rb') as f:old=f.read()
 if hashlib.sha256(old).hexdigest()!=BASE:raise RuntimeError('Live page differs from V2; refusing overwrite')
 Path('master-backup').mkdir(exist_ok=True);Path('master-backup/index.html').write_bytes(old)
 def upload(data,path):
  tmp=path+'.master-uploading'
  try:s.lstat(tmp)
  except FileNotFoundError:pass
  else:raise RuntimeError('Temporary target exists')
  with s.open(tmp,'wb') as f:f.write(data)
  s.posix_rename(tmp,path)
 upload(picture,pic);created=True
 upload(new,dest);changed=True
 for url,terms in [('https://ai.odpc.de/ki-orchester/',[b'odpc-unified-dock-js',b'odpc-reader-js',b'setup-device',b'oliver-claire-klavier.webp']),('https://ai.odpc.de/ki-orchester/oliver-claire-klavier.webp',[b'RIFF',b'WEBP'])]:
  for _ in range(5):
   try:
    with urlopen(Request(url+'?master='+str(int(time.time())),headers={'User-Agent':'ODPC-Orchester-Master/1','Cache-Control':'no-cache'}),timeout=25) as f:body=f.read(1000000);ok=f.status==200 and all(term in body for term in terms)
    if ok:break
   except Exception:pass
   time.sleep(4)
  else:raise RuntimeError('Public verification failed: '+url)
 print('PASS: page, toolbar components and piano image publicly verified')
except Exception:
 if changed:upload(old,dest)
 if created:
  try:s.remove(pic)
  except OSError:pass
 raise
finally:s.close();t.close()
