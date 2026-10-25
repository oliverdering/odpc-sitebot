"""Publish scoped head-only corrections, crawl files and protected direct-Google support."""
import base64, concurrent.futures, gzip, hashlib, hmac, json, os, posixpath, re, stat, subprocess, time, urllib.request, urllib.error
import paramiko
from pathlib import Path
from html import escape
from xml.etree import ElementTree as ET
from deploy_bahnhof_grenzau_bootstrap import connect, read as optional_read, atomic_write
from deploy_shared_footer import find_roots

BASE=Path('site-deploy/search-completion')
BACKUP=Path('backup-search-completion')
INDEX_RULE='<meta name="robots" content="index, follow, max-image-preview:large">'
RESULTS=[]
def fetch(url):
    with urllib.request.urlopen(urllib.request.Request(url,headers={'User-Agent':'ODPC-Search-Release-QA/1','Cache-Control':'no-cache'}),timeout=40) as r:
        if r.status!=200:raise RuntimeError('HTTP failure: '+url)
        return r.read(1800000).decode('utf-8')
def content(source):
    m=re.search(r'<body\b[^>]*>.*?</body>',source,re.I|re.S)
    if not m:raise RuntimeError('Missing body')
    return re.sub(r'(name="(?:_wpnonce|tl_booking_nonce)" value=")[^"]+',r'\1NONCE',m[0])
def validate(url,before,release=False):
    after=fetch(url)
    head=after.split('</head>',1)[0]
    if content(before)!=content(after):raise RuntimeError('Visible body changed: '+url)
    if len(re.findall(r'<meta\b[^>]*name=[\"\x27]description[\"\x27]',head,re.I))!=1:raise RuntimeError('Description missing or duplicated: '+url)
    if len(re.findall(r'<link\b[^>]*rel=[\"\x27]canonical[\"\x27]',head,re.I))!=1:raise RuntimeError('Canonical missing or duplicated: '+url)
    if release and 'noindex'in head.lower():raise RuntimeError('Public page still noindex: '+url)
    return after

def write_files(sf,name,files,verify):
    changed=[]
    try:
        for path,new in files:
            old=optional_read(sf,path)
            if old==new:continue
            target=BACKUP/name/path.lstrip('/');target.parent.mkdir(parents=True,exist_ok=True)
            if old is not None:target.write_bytes(old)
            atomic_write(sf,path,new)
            changed.append((path,old))
        verify()
        RESULTS.append({'site':name,'status':'verified','changed':len(changed)})
    except Exception as e:
        for path,old in reversed(changed):
            if old is None:sf.remove(path)
            else:atomic_write(sf,path,old)
        RESULTS.append({'site':name,'status':'rolled_back','reason':str(e)})
    print(json.dumps(RESULTS[-1]),flush=True)

def sitemap(base,paths):
    return ('<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'+''.join('<url><loc>'+escape(base+p)+'</loc></url>'for p in paths)+'</urlset>\n').encode()
def robots(existing,base):
    text=existing.decode()if existing else 'User-agent: *\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\n'
    if re.search(r'^Disallow:\s*/\s*$',text,re.M|re.I):raise RuntimeError('Unexpected global crawl restriction')
    line='Sitemap: '+base+'/sitemap.xml'
    if line not in text:text=text.rstrip()+'\n\n'+line+'\n'
    return text.encode()
def check_maps(base):
    body=fetch(base+'/sitemap.xml');tree=ET.fromstring(body)
    if not tree.tag.endswith('urlset'):raise RuntimeError('Missing URL sitemap')
    if 'Sitemap: '+base+'/sitemap.xml'not in fetch(base+'/robots.txt'):raise RuntimeError('Sitemap declaration missing')

def expire_target_cache(sf,root,urls):
    targets={u.rstrip('/')for u in urls};queue=[];seen=0
    for part in ['all','wpfc-mobile-cache','wpo-cache','supercache']:
        base=root+'/wp-content/cache/'+part
        try:
            if stat.S_ISDIR(sf.lstat(base).st_mode):queue.append((base,0))
        except FileNotFoundError:pass
    while queue and seen<2000:
        folder,depth=queue.pop(0)
        try: entries=sf.listdir_attr(folder)
        except FileNotFoundError: continue
        for item in entries:
            seen+=1
            if seen>2000:break
            path=posixpath.join(folder,item.filename)
            if stat.S_ISDIR(item.st_mode) and depth<6:queue.append((path,depth+1));continue
            if not stat.S_ISREG(item.st_mode) or item.st_size>2000000 or not re.search(r'\.html(?:[._-](?:gz|gzip))?$',item.filename):continue
            try:
                with sf.open(path,'rb')as handle:raw=handle.read(2000001)
            except FileNotFoundError:continue
            try:text=(gzip.decompress(raw)if raw.startswith(b'\x1f\x8b')else raw).decode('utf-8')
            except (OSError,UnicodeError):continue
            links=re.findall(r'<link\b[^>]*>',text,re.I)
            canonical=[]
            for tag in links:
                if re.search(r'rel=[\"\x27]canonical[\"\x27]',tag,re.I):canonical+=re.findall(r'href=[\"\x27]([^\"\x27]+)',tag,re.I)
            if len(canonical)==1 and canonical[0].rstrip('/')in targets:
                saved=BACKUP/'public-html-cache'/hashlib.sha256(raw).hexdigest();saved.parent.mkdir(parents=True,exist_ok=True);saved.write_bytes(raw)
                try:sf.remove(path)
                except FileNotFoundError:pass

def deploy_wp(sf,root,host,paths,release=False):
    sf.stat(root+'/wp-config.php')
    directory=root+'/wp-content/mu-plugins'
    try:sf.mkdir(directory)
    except IOError:
        if not stat.S_ISDIR(sf.lstat(directory).st_mode):raise
    urls=['https://'+host+p for p in paths]
    with concurrent.futures.ThreadPoolExecutor(max_workers=5)as ex:before=dict(zip(urls,ex.map(fetch,urls)))
    files=[(directory+'/odpc-seo-completion.php',(BASE/'odpc-seo-completion.php').read_bytes())]
    if host=='odpc.de':files.append((directory+'/odpc-google-index-audit.php',(BASE/'odpc-google-index-audit.php').read_bytes()))
    if release:
        files.extend([(root+'/sitemap.xml',sitemap('https://'+host,paths)),(root+'/robots.txt',robots(optional_read(sf,root+'/robots.txt'),'https://'+host))])
    def verify():
        expire_target_cache(sf,root,urls)
        for url in urls:validate(url,before[url],release)
        if release:check_maps('https://'+host)
        if host=='odpc.de':
            try:
                urllib.request.urlopen('https://odpc.de/wp-json/odpc-search/v1/audit',timeout=20)
                raise RuntimeError('Private Google audit route allowed an anonymous read')
            except urllib.error.HTTPError as e:
                if e.code not in (401,403):raise
    write_files(sf,host,files,verify)

def deploy_station(sf):
    root='/Bahnhof-Grenzau';url='https://bahnhof-grenzau.odpc.de/'
    old=optional_read(sf,root+'/index.html')
    if old is None or b'id="bahnhof-grenzau-start"'not in old:raise RuntimeError('Unexpected station page')
    text=old.decode();new,n=re.subn(r'<meta name="robots" content="noindex,follow">',INDEX_RULE,text)
    if n!=1 and INDEX_RULE not in text:raise RuntimeError('Unexpected station robots declaration')
    files=[(root+'/index.html',new.encode()),(root+'/sitemap.xml',sitemap(url.rstrip('/'),['/'])),(root+'/robots.txt',robots(optional_read(sf,root+'/robots.txt'),url.rstrip('/')))]
    def verify():
        after=validate(url,text,True);check_maps(url.rstrip('/'))
        for p in ['wp-login.php','wp-admin/install.php']:
            try:urllib.request.urlopen(url+p,timeout=20);raise RuntimeError('Unfinished PHP exposed')
            except urllib.error.HTTPError as e:
                if e.code not in (403,404):raise
    write_files(sf,'bahnhof-grenzau.odpc.de',files,verify)

def deploy_justen():
    password=os.environ.get('STRATO_JUSTEN_SFTP_PASSWORD','');pin=os.environ.get('STRATO_JUSTEN_HOSTKEY_SHA256','')
    if not password or not pin.startswith('SHA256:'):
        RESULTS.append({'site':'ra-justen.de','status':'not_available','reason':'No protected STRATO SFTP connection configured'});print(json.dumps(RESULTS[-1]),flush=True);return
    tr=paramiko.Transport(('59432978.ssh.w1.strato.hosting',22))
    try:
        tr.start_client(timeout=25)
        actual='SHA256:'+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual,pin):raise RuntimeError('STRATO fingerprint mismatch')
        tr.auth_password('stu471359126',password)
        with paramiko.SFTPClient.from_transport(tr)as sf:
            home=sf.normalize('.')
            roots=[posixpath.normpath(home+'/STRATO-apps/wordpress_01/app'),'/STRATO-apps/wordpress_01/app']
            for root in dict.fromkeys(roots):
                try:sf.stat(root+'/wp-config.php');sf.stat(root+'/wp-content/themes/akj-kanzlei-v1/style.css');break
                except FileNotFoundError:continue
            else:raise RuntimeError('Documented STRATO installation not found')
            deploy_wp(sf,root,'ra-justen.de',['/','/impressum/','/datenschutz/'])
    finally:tr.close()

def main():
    transport,sf=connect()
    try:
        roots=find_roots(sf)
        jobs=[
            ('/clickandbuilds/Dering','odpc.de',['/schach','/wga']+['/category/'+p for p in ['infos-news','kernportfolio','news-info','nicht-kategorisiert-de','seo','team','tools-support','unkategorisiert-zh','wichtige-info-%e2%9a%a0%ef%b8%8f']],False),
            (roots['helpermonkey-business-v34-child'],'ai.odpc.de',['/','/author/odpc_master/','/category/praxis/'],False),
            (roots['tante-lenchen'],'tante-lenchen.odpc.de',['/','/tl_dish/bayerischer-wurstsalat/','/author/lenchen_webmaster/','/category/allgemein/','/2026/10/17/hallo-welt/'],False),
            (roots['atrium191-odpc'],'atrium191-rb.odpc.de',['/']+['/index.php/'+p+'/'for p in ['wohnungen','erleben','geschichte','architektur','lage','faq','kontakt','impressum','datenschutz','barrierefreiheit']],True)]
        for args in jobs:
            try:deploy_wp(sf,*args)
            except Exception as e:RESULTS.append({'site':args[1],'status':'unchanged','reason':str(e)});print(json.dumps(RESULTS[-1]),flush=True)
        try:deploy_station(sf)
        except Exception as e:RESULTS.append({'site':'bahnhof-grenzau.odpc.de','status':'unchanged','reason':str(e)});print(json.dumps(RESULTS[-1]),flush=True)
    finally:sf.close();transport.close()
    try:deploy_justen()
    except Exception as e:RESULTS.append({'site':'ra-justen.de','status':'unchanged','reason':str(e)});print(json.dumps(RESULTS[-1]),flush=True)
    Path('search-completion-results.json').write_text(json.dumps(RESULTS,indent=2))
    if any(r['status'] not in ('verified','not_available')for r in RESULTS):raise SystemExit('Some sites require follow-up; verified sites retained')
if __name__=='__main__':main()
