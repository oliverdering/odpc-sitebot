"""Scoped ODPC release; refuse occupied target; backup and rollback on failed checks."""
import os, posixpath, stat, hashlib, base64, hmac, time
from pathlib import Path
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import paramiko
from scripts.deploy_helpermonkey_v4 import HOST, USER, PIN, find_theme

URL='https://ai.odpc.de/ki-orchester/'
CONNECTOR='https://ai.odpc.de/connector-einrichten/'
page=Path('mission-control-release/odpc-ki-orchester.html').read_bytes()
baseline=Path('site-deploy/ai.odpc.de/experience/page.php').read_bytes()
marker=b'<section class="hm-wrap hm-section hm-phone" id="smartphone">'
addition='''<section class="hm-wrap hm-section" id="ki-orchester"><p class="hm-eyebrow">DER NÄCHSTE SCHRITT</p><h2>Vom Connector zum KI-Orchester.</h2><p class="hm-intro">Entdecke, wie Menschen, KI und Dienste zusammenarbeiten – und richte deine Verbindungen Schritt für Schritt ein.</p><p><a class="hm-button hm-primary" href="/ki-orchester/">Das KI-Orchester entdecken →</a></p><p class="hm-fine">Der Connector begleitet die Einrichtung. Ein echter Zugriff benötigt die passende Freigabe und einen nachgewiesenen Test.</p></section>\n'''.encode()
if baseline.count(marker)!=1: raise RuntimeError('Connector source marker mismatch')
def fetch(url):
 try:
  with urlopen(Request(url,headers={'User-Agent':'ODPC-Orchester-Deploy/1','Cache-Control':'no-cache'}),timeout=25) as r:return r.status,r.geturl(),r.read(600000)
 except HTTPError as e:return e.code,e.geturl(),e.read(600000)
status,final,body=fetch(URL)
if not (status==404 or (status==200 and final==URL and b'Seite nicht gefunden' in body and b'<title>' in body)): raise RuntimeError('Target URL is not a verified not-found page; refusing to publish, HTTP '+str(status))
status,_,_=fetch(CONNECTOR)
if status!=200: raise RuntimeError('Connector HTTP check failed: '+str(status))
pw=os.environ.get('ODPC_SFTP_PASSWORD','')
if not pw:raise RuntimeError('Missing SFTP credential')
t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
if not hmac.compare_digest(actual,PIN):t.close();raise RuntimeError('SFTP host key mismatch')
t.auth_password(USER,pw)
if not t.is_authenticated():t.close();raise RuntimeError('SFTP authentication failed')
s=paramiko.SFTPClient.from_transport(t); root=find_theme(s).split('/wp-content/themes/')[0]; target=posixpath.join(root,'ki-orchester'); remote=posixpath.join(root,'wp-content/mu-plugins/odpc-monkey-experience/page.php'); modified=False; created=False
try:
 try:s.lstat(target)
 except FileNotFoundError:pass
 else:raise RuntimeError('Destination directory exists; refusing overwrite')
 st=s.lstat(remote)
 if not stat.S_ISREG(st.st_mode) or st.st_size>500000:raise RuntimeError('Unsafe connector file')
 with s.open(remote,'rb') as f: current=f.read()
 if current!=baseline:raise RuntimeError('Live connector differs from repository: refusing overwrite')
 Path('mission-control-backup').mkdir(exist_ok=True)
 Path('mission-control-backup/page.php').write_bytes(current)
 s.mkdir(target);created=True
 tmp=target+'/index.html.uploading'
 with s.open(tmp,'wb') as f:f.write(page)
 s.posix_rename(tmp,target+'/index.html')
 def verify(url,checks):
  for _ in range(4):
   time.sleep(3)
   try:
    code,final,body=fetch(url+'?odpc_release='+str(int(time.time())))
    if code==200 and all(c in body for c in checks):return
   except Exception: pass
  raise RuntimeError('Public verification failed: '+url)
 verify(URL,[b'KI-Orchester',b'connector-einrichten'])
 updated=current.replace(marker,addition+marker)
 with s.open(remote+'.odpc-uploading','wb') as f:f.write(updated)
 s.posix_rename(remote+'.odpc-uploading',remote);modified=True
 verify(CONNECTOR,[b'Vom Connector zum KI-Orchester',b'/ki-orchester/'])
 print('PASS: orchestra page and connector crosslink publicly verified')
except Exception:
 if modified:
  with s.open(remote+'.odpc-restore','wb') as f:f.write(current)
  s.posix_rename(remote+'.odpc-restore',remote)
 if created:
  for name in ('index.html.uploading','index.html'):
   try:s.remove(target+'/'+name)
   except OSError:pass
  try:s.rmdir(target)
  except OSError:pass
 raise
finally:s.close();t.close()
