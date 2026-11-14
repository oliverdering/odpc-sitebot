import os,posixpath,stat,hashlib,base64,hmac,time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
from scripts.deploy_helpermonkey_v4 import HOST,USER,PIN,find_theme
BASE='9f4414d86e90ec8df3bc4755d76e885efcff7d2378f97e815a369306f097d094'
new=Path('mission-control-navigation/index.html').read_bytes();pw=os.environ.get('ODPC_SFTP_PASSWORD','')
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
 Path('orchestra-nav-backup').mkdir(exist_ok=True);Path('orchestra-nav-backup/index.html').write_bytes(old)
 def upload(data):
  tmp=dest+'.nav-uploading'
  try:s.lstat(tmp)
  except FileNotFoundError:pass
  else:raise RuntimeError('Temporary target already exists')
  with s.open(tmp,'wb') as f:f.write(data)
  s.posix_rename(tmp,dest)
 upload(new);changed=True
 for _ in range(5):
  try:
   with urlopen(Request('https://ai.odpc.de/ki-orchester/?navrelease='+str(int(time.time())),headers={'User-Agent':'ODPC-Orchestra-Navigation/1','Cache-Control':'no-cache'}),timeout=25) as f:body=f.read(1000000);ok=f.status==200 and all(x in body for x in [b'odpc-page-menu',b'odpc-team-portraits',b'data-apple-device',b'odpc-unified-dock-js'])
   if ok:break
  except Exception:pass
  time.sleep(4)
 else:raise RuntimeError('Public verification failed')
 print('PASS: navigation, team, Apple routes and dock markers public')
except Exception:
 if changed:upload(old)
 raise
finally:s.close();t.close()
