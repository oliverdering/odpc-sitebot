"""Deploy ODPC News & Signals as a standalone must-use WordPress plugin."""
import base64,hashlib,hmac,os,sys,time,stat
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
sys.path.insert(0,str(Path(__file__).resolve().parents[1]/'scripts'))
from deploy_odpc_remote import HOST,USER,PIN,ROOT
BASE=Path(__file__).resolve().parent
DEST=ROOT+'/wp-content/mu-plugins'
def read(s,p):
 try:
  with s.open(p,'rb') as f:return f.read()
 except IOError:return None
def write(s,p,data):
 tmp=p+'.pending'
 with s.open(tmp,'wb') as f:f.write(data)
 if read(s,tmp)!=data:raise RuntimeError('Staging verification failed')
 s.posix_rename(tmp,p)
def purge_news_cache(s):
 for base in ['all','wpfc-mobile-cache','wpo-cache/odpc.de']:
  folder=ROOT+'/wp-content/cache/'+base+'/news-signals/'
  try: entries=s.listdir_attr(folder)
  except IOError:continue
  for entry in entries:
   if stat.S_ISREG(entry.st_mode) and entry.filename in {'index.html','index.html.gz','index-https.html','index-https.html.gz','index.gzip.html'}:
    s.remove(folder+entry.filename)
def check():
 req=Request('https://odpc.de/news-signals/?odpc_news_check='+str(time.time_ns()),headers={'Cache-Control':'no-cache','User-Agent':'ODPC-News-QA'})
 with urlopen(req,timeout=30) as r:html=r.read(4000000).decode('utf8')
 if 'id="odpc-news-v2"' not in html or 'Zur KI-Mediathek' not in html:raise RuntimeError('News portal not visible on canonical URL')
 return True
with paramiko.Transport((HOST,22)) as t:
 t.start_client(timeout=25)
 actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
 if not hmac.compare_digest(actual,PIN):raise RuntimeError('Host key mismatch')
 t.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD'])
 with paramiko.SFTPClient.from_transport(t) as s:
  files={DEST+'/odpc-news-signals.php':(BASE/'news-signals.php').read_bytes(),DEST+'/news-signals.css':(BASE/'news-signals.css').read_bytes()}
  previous={p:read(s,p) for p in files}
  changed=[]
  try:
   for p,data in files.items():
    if previous[p]!=data:write(s,p,data);changed.append(p)
   purge_news_cache(s)
   check()
   print('News & Signals v2 deployed and verified.')
  except Exception:
   for p in reversed(changed):
    if previous[p] is None:s.remove(p)
    else:write(s,p,previous[p])
   purge_news_cache(s)
   raise
