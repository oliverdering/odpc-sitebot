import os,posixpath,stat,hashlib,base64,hmac,time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
from scripts.deploy_helpermonkey_v4 import HOST,USER,PIN,find_theme
BASE='ae051f96ab5284b02cd9b0cc63ecd0fc75fdcbef3002ae48ff2e2693b25ffc66'
new=Path('orchestra-overlay-release/index.html').read_bytes();pw=os.environ.get('ODPC_SFTP_PASSWORD','')
if not pw:raise RuntimeError('Missing SFTP credential')
t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
if not hmac.compare_digest(actual,PIN):t.close();raise RuntimeError('Host key mismatch')
t.auth_password(USER,pw)
if not t.is_authenticated():t.close();raise RuntimeError('SFTP auth failed')
s=paramiko.SFTPClient.from_transport(t);root=find_theme(s).split('/wp-content/themes/')[0];folder=posixpath.join(root,'ki-orchester');dest=folder+'/index.html';changed=False
try:
 if not stat.S_ISDIR(s.lstat(folder).st_mode) or not stat.S_ISREG(s.lstat(dest).st_mode):raise RuntimeError('Unsafe destination')
 with s.open(dest,'rb') as f:old=f.read()
 if hashlib.sha256(old).hexdigest()!=BASE:raise RuntimeError('Live page differs from verified baseline; refusing overwrite')
 Path('orchestra-overlay-backup').mkdir(exist_ok=True);Path('orchestra-overlay-backup/index.html').write_bytes(old)
 def upload(data):
  tmp=dest+'.overlay-uploading'
  try:s.lstat(tmp)
  except FileNotFoundError:pass
  else:raise RuntimeError('Temporary target already exists')
  with s.open(tmp,'wb') as f:f.write(data)
  s.posix_rename(tmp,dest)
 upload(new);changed=True
 for _ in range(5):
  try:
   with urlopen(Request('https://ai.odpc.de/ki-orchester/?navrelease='+str(int(time.time())),headers={'User-Agent':'ODPC-Orchestra-Navigation/1','Cache-Control':'no-cache'}),timeout=25) as f:body=f.read(1000000);ok=f.status==200 and all(x in body for x in [b'odpc-translate-overlay-fix',b'odpc-service-strip',b'data-apple-device',b'odpc-unified-dock-js'])
   if ok:break
  except Exception:pass
  time.sleep(4)
 else:raise RuntimeError('Public verification failed')
 print('PASS: navigation, team, Apple routes and dock markers public')
except Exception:
 if changed:upload(old)
 raise
finally:s.close();t.close()
