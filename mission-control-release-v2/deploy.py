import os, posixpath, stat, hashlib, base64, hmac, time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
from scripts.deploy_helpermonkey_v4 import HOST, USER, PIN, find_theme

base=Path('mission-control-release/odpc-ki-orchester.html').read_bytes()
new=Path('mission-control-release-v2/index.html').read_bytes()
img=Path('mission-control-release-v2/orchester.webp').read_bytes()
password=os.environ.get('ODPC_SFTP_PASSWORD','')
if not password:raise RuntimeError('SFTP credential missing')
t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
if not hmac.compare_digest(actual,PIN):t.close();raise RuntimeError('Host key mismatch')
t.auth_password(USER,password)
if not t.is_authenticated():t.close();raise RuntimeError('SFTP authentication failed')
s=paramiko.SFTPClient.from_transport(t);root=find_theme(s).split('/wp-content/themes/')[0];folder=posixpath.join(root,'ki-orchester');dest=folder+'/index.html';pic=folder+'/orchester.webp';changed=False;created=False
try:
 if not stat.S_ISDIR(s.lstat(folder).st_mode):raise RuntimeError('Unsafe target directory')
 if not stat.S_ISREG(s.lstat(dest).st_mode):raise RuntimeError('Unsafe target file')
 try:s.lstat(pic)
 except FileNotFoundError:pass
 else:raise RuntimeError('Image target already exists; refusing overwrite')
 with s.open(dest,'rb') as f:old=f.read()
 if old!=base:raise RuntimeError('Live page differs from baseline; refusing overwrite')
 Path('mission-control-backup-v2').mkdir(exist_ok=True);Path('mission-control-backup-v2/index.html').write_bytes(old)
 def upload(data,path):
  tmp=path+'.v2-uploading'
  try:s.lstat(tmp)
  except FileNotFoundError:pass
  else:raise RuntimeError('Temporary target already exists')
  with s.open(tmp,'wb') as f:f.write(data)
  s.posix_rename(tmp,path)
 upload(img,pic);created=True
 upload(new,dest);changed=True
 for url,terms in [('https://ai.odpc.de/ki-orchester/',[b'architektur-netzwerk',b'orchester.webp',b'connector-einrichten',b'unser-team']),('https://ai.odpc.de/ki-orchester/orchester.webp',[b'RIFF',b'WEBP'])]:
  for attempt in range(5):
   try:
    with urlopen(Request(url+'?release='+str(int(time.time())),headers={'User-Agent':'ODPC-Orchestra-V2/1','Cache-Control':'no-cache'}),timeout=25) as f:body=f.read(4_000_000);ok=f.status==200 and all(x in body for x in terms)
    if ok:break
   except Exception:pass
   time.sleep(4)
  else:raise RuntimeError('Live verification failed: '+url)
 print('PASS: public page, diagram, links and hero bytes verified')
except Exception:
 if changed:upload(old,dest)
 if created:
  try:s.remove(pic)
  except OSError:pass
 raise
finally:s.close();t.close()
