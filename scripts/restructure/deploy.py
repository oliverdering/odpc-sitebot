"""Publish a coordinated Helper Monkey release; no database or theme modifications."""
import base64,hashlib,hmac,json,os,posixpath,stat,sys,time
from pathlib import Path
from urllib.request import Request,urlopen
sys.path.insert(0,str(Path(__file__).resolve().parents[1]))
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN,find_theme
SOURCE=Path('site-deploy/ai.odpc.de')
FILES={
 'odpc-monkey-experience/dialog-copy.js':SOURCE/'experience/dialog-copy.js',
 'odpc-monkey-experience/guide-core.js':SOURCE/'experience/guide-core.js',
 'odpc-monkey-experience/experience.css':SOURCE/'experience/experience.css',
 'odpc-monkey-experience/page.php':SOURCE/'experience/page.php',
 'odpc-monkey-experience.php':SOURCE/'odpc-monkey-experience.php',
 'odpc-monkey-voice-interview.php':SOURCE/'odpc-monkey-voice-interview.php',
 'odpc-search-metadata.php':SOURCE/'odpc-search-metadata.php',
}
DOWNLOADS=['helpermonkey-connector-multilingual-v3.1.zip','helpermonkey-lebenslauf-multilingual-v3.1.zip','helpermonkey-bewerbung-multilingual-v3.1.zip','helpermonkey-postfach-multilingual-v3.1.zip','helpermonkey-gespraechsuebersetzer-multilingual-v1.0.zip','helpermonkey-gespraechsuebersetzer-v1.0.txt','helpermonkey-sprachmodul-v1.0.txt']
FILES.update({'odpc-monkey-experience/downloads/'+name:Path('downloads')/name for name in DOWNLOADS})
def get(url):
 with urlopen(Request(url,headers={'Cache-Control':'no-cache','User-Agent':'HelperMonkey-Release-QA/1'}),timeout=35) as f:return f.status,f.read()
def main():
 t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
 try:
  actual='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
  if not hmac.compare_digest(actual,PIN):raise RuntimeError('Host key mismatch')
  t.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD']);s=paramiko.SFTPClient.from_transport(t)
  known='/ai-odpc/wp-content/themes/helpermonkey-business-v34-child'
  try:
   s.stat(known+'/style.css');theme=known
  except FileNotFoundError:theme=find_theme(s)
  root=theme.split('/wp-content/themes/')[0];folder=root+'/wp-content/mu-plugins';s.stat(root+'/wp-config.php')
  if not stat.S_ISDIR(s.lstat(folder).st_mode):raise RuntimeError('Unexpected plugin directory')
  dest=folder+'/odpc-monkey-experience'
  try:
   if not stat.S_ISDIR(s.lstat(dest).st_mode):raise RuntimeError('Unsafe asset directory')
  except FileNotFoundError:s.mkdir(dest)
  downloads=dest+'/downloads'
  try:
   if not stat.S_ISDIR(s.lstat(downloads).st_mode):raise RuntimeError('Unsafe downloads directory')
  except FileNotFoundError:s.mkdir(downloads)
  previous={};written=[];report={'site':'https://ai.odpc.de','commit':os.environ.get('GITHUB_SHA'),'files':{},'checks':[],'full_backup':'User confirmed pre-existing full backup; additional full backup skipped'}
  rollback=Path('rollback-helpermonkey-experience');rollback.mkdir(exist_ok=True)
  for name,source in FILES.items():
   if not source.is_file() or source.stat().st_size>100000:raise RuntimeError('Invalid source '+name)
   target=folder+'/'+name
   try:
    info=s.lstat(target)
    if not stat.S_ISREG(info.st_mode) or info.st_size>100000:raise RuntimeError('Unexpected target '+name)
    with s.open(target,'rb') as f:previous[name]=f.read()
    local=rollback/name;local.parent.mkdir(exist_ok=True,parents=True);local.write_bytes(previous[name])
   except FileNotFoundError:previous[name]=None
  (rollback/'manifest.json').write_text(json.dumps({n:('restore' if b is not None else 'remove-new-file') for n,b in previous.items()},indent=2))
  try:
   for name,source in FILES.items():
    target=folder+'/'+name;s.put(str(source),target+'.uploading',confirm=True);s.posix_rename(target+'.uploading',target);written.append(name)
    with s.open(target,'rb') as f:remote=f.read()
    digest=hashlib.sha256(source.read_bytes()).hexdigest()
    if hashlib.sha256(remote).hexdigest()!=digest:raise RuntimeError('Uploaded checksum mismatch '+name)
    report['files'][name]=digest
   for path in ['/','/start/','/connector-einrichten/','/helper-monkey-einfach-starten-sprachassistent/']:
    good=False
    for attempt in range(3):
     code,body=get('https://ai.odpc.de'+path+'?hm_release=1.0.0-'+str(int(time.time())))
     html=body.decode('utf-8','replace')
     if code==200 and 'hm-experience-page' in html and 'data-version="0.14.5"' in html and 'hm-guide-help' in html:
      good=True;break
     time.sleep(2)
    if not good:raise RuntimeError('Live release absent '+path)
    if 'initIQ / ODPC' in html or 'Dein KI-Team. Ein Klick zum Start.' in html:raise RuntimeError('Legacy homepage still served')
    report['checks'].append({'path':path,'status':code,'bytes':len(body)})
   for path in ['https://odpc.de/impressum','https://odpc.de/datenschutzerklaerung','https://ai.odpc.de/werkstatt/','https://ai.odpc.de/webmaster-agent/']:
    code,body=get(path)
    if code!=200:raise RuntimeError('Preserved page failed '+path)
    report['checks'].append({'path':path,'status':code,'bytes':len(body)})
   for name in ['dialog-copy.js','guide-core.js','experience.css']:
    code,data=get('https://ai.odpc.de/wp-content/mu-plugins/odpc-monkey-experience/'+name+'?v=1.0.0')
    if data!=(SOURCE/'experience'/name).read_bytes():raise RuntimeError('Public asset mismatch '+name)
   for name in DOWNLOADS:
    code,data=get('https://ai.odpc.de/wp-content/mu-plugins/odpc-monkey-experience/downloads/'+name)
    if code!=200 or data!=(Path('downloads')/name).read_bytes():raise RuntimeError('Public download mismatch '+name)
    report['checks'].append({'download':name,'status':code,'bytes':len(data)})
   print('PASS: coordinated release, uploaded checksums, four entry pages and four retained pages verified')
   Path('helpermonkey-release-result.json').write_text(json.dumps(report,indent=2))
  except Exception:
   for name in reversed(written):
    target=folder+'/'+name
    if previous[name] is None:s.remove(target)
    else:
     with s.open(target+'.rollback','wb') as f:f.write(previous[name])
     s.posix_rename(target+'.rollback',target)
   print('ROLLBACK: previous files restored');raise
 finally:t.close()
if __name__=='__main__':main()
