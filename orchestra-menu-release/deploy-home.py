import os,posixpath,stat,hashlib,base64,hmac,time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
from scripts.deploy_helpermonkey_v4 import HOST,USER,PIN
src=Path('orchestra-menu-release/odpc-orchestra-menu.php');pw=os.environ.get('ODPC_SFTP_PASSWORD','')
if not pw or not src.is_file():raise RuntimeError('Missing input')
t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
if not hmac.compare_digest(actual,PIN):t.close();raise RuntimeError('Host key mismatch')
t.auth_password(USER,pw)
if not t.is_authenticated():t.close();raise RuntimeError('SFTP auth failed')
s=paramiko.SFTPClient.from_transport(t);root='/clickandbuilds/Dering';folder=root+'/wp-content/mu-plugins';dest=folder+'/odpc-orchestra-menu-20261114.php';tmp=dest+'.uploading';changed=False
try:
 if not stat.S_ISDIR(s.lstat(folder).st_mode):raise RuntimeError('Missing plugin directory')
 s.stat(root+'/wp-content/themes/astra/style.css')
 for p in [dest,tmp]:
  try:s.lstat(p)
  except FileNotFoundError:pass
  else:raise RuntimeError('Target exists; refusing overwrite')
 s.put(str(src),tmp,confirm=True);s.posix_rename(tmp,dest);changed=True
 for i in range(8):
  try:
   with urlopen(Request('https://odpc.de/?menurelease='+str(int(time.time())),headers={'User-Agent':'ODPC-Menu-Release/1','Cache-Control':'no-cache'}),timeout=25) as f:
    body=f.read(2000000);ok=f.status==200 and b'odpc-orchestra-menu-link' in body and b'odpc-nav-panel' in body
   if ok:break
  except Exception:pass
  time.sleep(5)
 else:raise RuntimeError('Public homepage check failed')
 print('PASS: homepage and menu link module returned')
except Exception:
 if changed:s.remove(dest)
 else:
  try:s.remove(tmp)
  except OSError:pass
 raise
finally:s.close();t.close()
