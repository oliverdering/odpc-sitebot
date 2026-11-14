import os, posixpath, stat, hashlib, base64, hmac, time
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
from scripts.deploy_helpermonkey_v4 import HOST,USER,PIN,find_theme
src=Path('connector-faq-release/odpc-connector-faq-expansion.php')
password=os.environ.get('ODPC_SFTP_PASSWORD','')
if not password or not src.is_file(): raise RuntimeError('Missing input')
t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
if not hmac.compare_digest(actual,PIN):t.close();raise RuntimeError('Host key mismatch')
t.auth_password(USER,password)
if not t.is_authenticated():t.close();raise RuntimeError('SFTP auth failed')
s=paramiko.SFTPClient.from_transport(t);root=find_theme(s).split('/wp-content/themes/')[0]
folder=posixpath.join(root,'wp-content','mu-plugins');dest=folder+'/odpc-connector-faq-expansion-20261114.php';tmp=dest+'.uploading';changed=False
try:
 if not stat.S_ISDIR(s.lstat(folder).st_mode):raise RuntimeError('MU plugin directory missing')
 for path in [dest,tmp]:
  try:s.lstat(path)
  except FileNotFoundError:pass
  else:raise RuntimeError('Target exists; refusing to overwrite')
 s.put(str(src),tmp,confirm=True);s.posix_rename(tmp,dest);changed=True
 for i in range(8):
  try:
   url='https://ai.odpc.de/connector-einrichten/?faqrelease='+str(int(time.time()))
   with urlopen(Request(url,headers={'User-Agent':'ODPC-FAQ-Release/1','Cache-Control':'no-cache'}),timeout=25) as f:
    body=f.read(2000000);ok=f.status==200 and b'odpc-faq-library' in body and b'connector-start' in body and b'odpc-utility-dock' in body
   if ok:break
  except Exception:pass
  time.sleep(5)
 else:raise RuntimeError('Public connector verification failed')
 print('PASS: connector, dock and FAQ markers publicly returned')
except Exception:
 if changed:s.remove(dest)
 else:
  try:s.remove(tmp)
  except OSError:pass
 raise
finally:s.close();t.close()
