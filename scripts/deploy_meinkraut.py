"""Deploy only the new, explicitly selected /MeinKraut webspace."""
import base64,hashlib,hmac,json,os,posixpath,stat,time,sys,zipfile
from pathlib import Path
from urllib.request import Request,urlopen
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN
ROOT='/MeinKraut';ODPC_ROOT='/clickandbuilds/Dering';SOURCE=Path('site-deploy/meinkraut');BACKUP=Path('backup-meinkraut')
def mkdirs(sf,path):
    parts=path.strip('/').split('/');current=''
    for part in parts:
        current+='/'+part
        try:
            if not stat.S_ISDIR(sf.stat(current).st_mode):raise RuntimeError('Expected directory')
        except FileNotFoundError:sf.mkdir(current)
def get(url):
    with urlopen(Request(url,headers={'Cache-Control':'no-cache','User-Agent':'MeinKraut-ReleaseCheck/2'}),timeout=35) as r:return r.status,r.read(),r.headers
def main():
    catalog=json.loads((SOURCE/'content/shop-catalog.json').read_text())
    if catalog.get('checkoutEnabled') is not False or catalog.get('mode')!='test':raise RuntimeError('Only test commerce is approved')
    tr=paramiko.Transport((HOST,22));tr.start_client(timeout=25)
    try:
        actual='SHA256:'+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual,PIN):raise RuntimeError('Host key mismatch')
        tr.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD']);sf=paramiko.SFTPClient.from_transport(tr)
        try:
            if not stat.S_ISDIR(sf.lstat(ROOT).st_mode):raise RuntimeError('User-created MeinKraut webspace is missing')
            fresh=True
            try:
                with sf.open(ROOT+'/index.php') as f:previous=f.read().decode()
                fresh=False
                if 'Mein Kraut' not in previous:raise RuntimeError('Refusing to overwrite unrelated existing website')
            except FileNotFoundError:pass
            sf.stat(ODPC_ROOT+'/wp-content/plugins/weltgewuerz-atlas/weltgewuerz-atlas.php')
            with zipfile.ZipFile(SOURCE/'assets-bundle.zip') as z:
                if any(Path(x).is_absolute() or '..' in Path(x).parts for x in z.namelist()):raise RuntimeError('Unsafe asset archive')
                z.extractall(SOURCE)
            required=['index.php','data.php','health.php','meinkraut.css','meinkraut.js','.htaccess','robots.txt','sitemap.xml','shop.css','shop.js','shop-api.php','server/public.php','server/commerce.php','content/shop-catalog.json','vendor/qrcode.js','vendor/qrcode-LICENSE.txt','server/database.php','server/install.php','server/.htaccess','content/atlas-base.json','content/meinkraut-content.json']
            files=[SOURCE/x for x in required]+[p for p in (SOURCE/'assets').rglob('*') if p.is_file() and p.suffix=='.webp']
            files.sort(key=lambda p:p.name=='index.php')
            BACKUP.mkdir(exist_ok=True);changed=[]
            try:
                for source in files:
                    relative=source.relative_to(SOURCE).as_posix();target=ROOT+'/'+relative;mkdirs(sf,posixpath.dirname(target))
                    old=BACKUP/relative;old.parent.mkdir(parents=True,exist_ok=True);existed=False
                    try:
                        st=sf.lstat(target)
                        if not stat.S_ISREG(st.st_mode):raise RuntimeError('Unsafe existing file')
                        if st.st_size==source.stat().st_size:
                            with sf.open(target,'rb') as remote:
                                if hashlib.sha256(remote.read()).digest()==hashlib.sha256(source.read_bytes()).digest():continue
                        sf.get(target,str(old));existed=True
                    except FileNotFoundError:pass
                    temp=target+'.uploading';sf.put(str(source),temp,confirm=True);sf.chmod(temp,0o644);sf.posix_rename(temp,target);changed.append((target,old,existed))
                setup=json.loads(Path('deployment/meinkraut-bootstrap.json').read_text());hash_value=setup['token_sha256']
                if len(hash_value)!=64 or any(c not in '0123456789abcdef' for c in hash_value):raise RuntimeError('Invalid setup verifier')
                wrapper=ODPC_ROOT+'/.mk-setup-'+hash_value[:20]+'.php'
                code="<?php\ndefine('MK_SETUP_HASH', '"+hash_value+"');\ndefine('MK_SETUP_WRAPPER', __FILE__);\nrequire dirname(__DIR__,2).'"+ROOT+"/server/install.php';\n"
                if fresh:
                    with sf.open(wrapper+'.uploading','w') as f:f.write(code)
                    sf.chmod(wrapper+'.uploading',0o644);sf.posix_rename(wrapper+'.uploading',wrapper)
                # Verify rewritten routes before accepting the release. IONOS needs an explicit URL base.
                for smoke_path in ['/','/gewuerz/schwarzer-pfeffer/','/mischung/mk-steakpfeffer/','/glaeser-etiketten/','/gewuerzshop/','/premium/','/nachfuellen/','/etiketten/','/warenkorb/','/konto/']:
                    try:smoke_status,smoke_body,_=get('https://meinkraut.odpc.de'+smoke_path)
                    except Exception as exc:raise RuntimeError('Public route request failed: '+smoke_path) from exc
                    if smoke_status!=200 or b'<h1' not in smoke_body:raise RuntimeError('Public route verification failed: '+smoke_path)
                status,body,_=get('https://odpc.de/.mk-setup-'+hash_value[:20]+'.php')
                # An unauthenticated GET must stay inaccessible (normally HTTPError 404).
                if status==200:raise RuntimeError('Setup must not accept unauthenticated GET')
            except Exception as exc:
                from urllib.error import HTTPError
                if isinstance(exc,HTTPError) and exc.code==404:
                    print('PASS: Files deployed; one-time setup endpoint denies unauthenticated GET')
                else:
                    for target,old,existed in reversed(changed):
                        if existed:sf.put(str(old),target+'.restore');sf.posix_rename(target+'.restore',target)
                        else:
                            try:sf.remove(target)
                            except OSError:pass
                    raise
            print(json.dumps({'status':'deployed','webspace':ROOT,'files':len(files),'url':'https://meinkraut.odpc.de/'}))
            try:
                _,body,_=get('https://meinkraut.odpc.de/health.php?release='+str(int(time.time())));h=json.loads(body)
                expected=json.loads((SOURCE/'content/atlas-base.json').read_text());extra=json.loads((SOURCE/'content/meinkraut-content.json').read_text())
                if h.get('recipes')!=len(expected['recipes'])+len(extra['mixes']):print('Catalog database activation pending; file deployment alone does not replace it')
                print('PUBLIC HEALTH:',json.dumps(h))
            except Exception as e:print('Public domain verification pending:',type(e).__name__)
        finally:sf.close()
    finally:tr.close()
if __name__=='__main__':main()
