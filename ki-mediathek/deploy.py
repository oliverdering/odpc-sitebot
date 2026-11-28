"""Publish a validated index over the established ODPC SFTP connection."""
import argparse
import base64
import hashlib
import hmac
import json
import os
from pathlib import Path
import stat
import sys
import tempfile
import time
from urllib.request import Request, urlopen
from datetime import datetime

from collect import collect, BERLIN, ROOT as SOURCES
sys.path.insert(0,str(SOURCES.parent/'scripts'))
from deploy_odpc_remote import HOST, USER, PIN, ROOT
import paramiko

MU=ROOT+'/wp-content/mu-plugins'
DIR=MU+'/odpc-ki-mediathek'
LOADER=MU+'/odpc-ki-mediathek.php'
LIVE='https://odpc.de/ki-mediathek'
def read_file(s,path):
    try:
        st=s.lstat(path)
        if not stat.S_ISREG(st.st_mode) or st.st_size>4000000:raise RuntimeError('Unexpected file type or size: '+path)
        with s.open(path,'rb') as f:return f.read()
    except FileNotFoundError:return None

def atomic(s,path,data):
    if data is None:
        if read_file(s,path) is not None:s.remove(path)
        return
    # A PHP temporary must retain its extension so its source is never served.
    tmp=(DIR+'/loader-pending.php') if path==LOADER else path+'.pending'
    with s.open(tmp,'wb') as f:f.write(data)
    if read_file(s,tmp)!=data:raise RuntimeError('Staging checksum mismatch')
    s.posix_rename(tmp,path)

def fetch(url):
    req=Request(url,headers={'Cache-Control':'no-cache','User-Agent':'ODPC-Mediathek-Abnahme/2.0'})
    with urlopen(req,timeout=35) as r:return r.read(3500000).decode('utf8')

def expire_pages(s):
    # These are derived HTML page caches, limited to homepage and portal.
    for variant in ['all','wpfc-mobile-cache','wpo-cache/odpc.de']:
        for slug in ['','ki-mediathek/']:
            folder=f'{ROOT}/wp-content/cache/{variant}/{slug}'
            try:entries=s.listdir_attr(folder)
            except FileNotFoundError:continue
            for entry in entries:
                if stat.S_ISREG(entry.st_mode) and entry.filename in {'index.html','index.html.gz','index-https.html','index-https.html.gz','index.gzip.html'}:
                    s.remove(folder+entry.filename)

def publish(s,mode):
    s.stat(ROOT+'/wp-content/themes/astra/style.css')
    try:s.mkdir(DIR)
    except OSError:
        if not stat.S_ISDIR(s.stat(DIR).st_mode):raise
    try:s.mkdir(DIR+'/logos')
    except OSError:
        if not stat.S_ISDIR(s.stat(DIR+'/logos').st_mode):raise
    if mode=='legacy':
        atomic(s,DIR+'/paused',b'Rollback requested')
        atomic(s,LOADER,None)
        atomic(s,DIR+'/verified',None)
        expire_pages(s)
        page=fetch(LIVE+'?odpc_legacy='+str(time.time_ns()))
        if 'id="odpc-ki-guide"' in page:raise RuntimeError('Rollback verification failed')
        print('Legacy shortcode restored; scheduled publisher paused.');return
    if read_file(s,DIR+'/paused') is not None and mode!='resume':
        print('Publisher paused after rollback.');return
    old=read_file(s,DIR+'/catalogue.json')
    state=json.loads(old) if old else {}
    now=datetime.now(BERLIN)
    # Code-only releases reuse today's verified catalogue. Explicit reruns refresh.
    reuse=(os.getenv('GITHUB_EVENT_NAME')=='push' and os.getenv('GITHUB_RUN_ATTEMPT')=='1'
           and state.get('complete') and datetime.fromtimestamp(state['updated'],BERLIN).date()==now.date())
    if reuse:
        data=state
        print('Reusing today’s complete catalogue for the software release.')
    else:
        data,_=collect(state,now)
    if not data['complete'] and not old:raise RuntimeError('Initial publication requires both sources.')
    if not data['complete'] and old:
        data['last_complete']=state.get('last_complete',state['updated'] if state.get('complete') else 0)
    else:data['last_complete']=data['updated']
    files={DIR+'/'+name:(SOURCES/name).read_bytes() for name in ['stations.tsv','portal.css','portal.js','logos.js']}
    for asset in sorted((SOURCES/'logos').iterdir()):
        if asset.suffix in ('.json','.png'):files[DIR+'/logos/'+asset.name]=asset.read_bytes()
    files[DIR+'/catalogue.json']=json.dumps(data,ensure_ascii=False,separators=(',',':')).encode()
    files[LOADER]=(SOURCES/'portal.php').read_bytes()
    backup={path:read_file(s,path) for path in [*files,DIR+'/verified',DIR+'/paused']}
    # A persistent recovery copy contains only public catalogue data.
    if old and not reuse:atomic(s,DIR+'/previous.json',old)
    modified=[]
    try:
        for path,content in files.items():
            if backup[path]==content:continue
            atomic(s,path,content);modified.append(path)
        stamp=str(data['updated'])
        html=fetch(LIVE+'?odpc_media_check='+stamp)
        markers=['id="odpc-ki-guide"','data-updated="'+stamp+'"','id="kg-banner"','class="odpc-media-track"','id="kg-sender"','class="kg-filters"']
        if not all(m in html for m in markers):raise RuntimeError('Portal verification failed')
        if html.count('class="odpc-media-track"')!=1:raise RuntimeError('Unexpected ticker count')
        if 'class="odpc-media-track"' not in fetch('https://odpc.de/?odpc_media_check='+stamp):raise RuntimeError('Homepage ticker missing')
        expire_pages(s)
        # Verify requested canonical URL prior to retiring the previous schedule.
        if 'data-updated="'+stamp+'"' not in fetch(LIVE):raise RuntimeError('Public page still has a stale cache')
        for path,content in [(DIR+'/verified',b'v2'),(DIR+'/paused',None)]:
            if backup[path]!=content:
                atomic(s,path,content);modified.append(path)
        fetch(LIVE+'?odpc_media_confirm='+stamp)
        print('Published',stamp,'media',len(data['items']),'broadcasts',len(data['upcoming']),'sources',data['sources'])
    except Exception:
        for path in reversed(modified):atomic(s,path,backup[path])
        expire_pages(s)
        raise

if __name__=='__main__':
    parser=argparse.ArgumentParser(); parser.add_argument('mode',choices=['refresh','legacy','resume'],default='refresh',nargs='?')
    mode=parser.parse_args().mode
    with paramiko.Transport((HOST,22)) as transport:
        transport.start_client(timeout=25)
        fingerprint='SHA256:'+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(fingerprint,PIN):raise RuntimeError('Host key mismatch')
        transport.set_keepalive(20)
        transport.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD'])
        with paramiko.SFTPClient.from_transport(transport) as sftp:publish(sftp,mode)
