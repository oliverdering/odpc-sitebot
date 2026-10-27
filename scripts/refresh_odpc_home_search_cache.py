#!/usr/bin/env python3
"""Expire only stale HTML for the approved ODPC home and webdesign updates."""
import base64
import gzip
import hashlib
import hmac
import os
from pathlib import Path
import posixpath
import re
import stat
import time
from urllib.request import Request, urlopen
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

ROOT = '/clickandbuilds/Dering'
EXPECTED = 'object-position:50% 0%!important'
TARGETS = {
    'https://odpc.de': EXPECTED,
    'https://odpc.de/webdesign-live-projekte-48h-sichtbarkeit': EXPECTED,
}

def fetch(url):
    with urlopen(Request(url, headers={'Cache-Control':'no-cache'}), timeout=30) as response:
        if response.status != 200:
            raise RuntimeError('Homepage unavailable')
        return response.read(1000000).decode('utf-8')

def main():
    for target_url, expected in TARGETS.items():
        separator = '&' if '?' in target_url else '?'
        if expected not in fetch(target_url+separator+'odpc_cache_preflight='+str(time.time_ns())):
            raise RuntimeError('Updated WordPress content not present; cache unchanged: '+target_url)
    password = os.environ.get('ODPC_SFTP_PASSWORD')
    if not password:
        raise RuntimeError('Missing protected SFTP credential')
    transport = paramiko.Transport((HOST,22))
    transport.start_client(timeout=25)
    actual = 'SHA256:'+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
    if not hmac.compare_digest(actual, PIN):
        transport.close()
        raise RuntimeError('SFTP fingerprint mismatch')
    try:
        transport.auth_password(USER,password)
        with paramiko.SFTPClient.from_transport(transport) as sftp:
            for path in [ROOT+'/wp-config.php', ROOT+'/wp-content/themes/astra/style.css']:
                if not stat.S_ISREG(sftp.stat(path).st_mode):
                    raise RuntimeError('Unexpected WordPress installation')
            cache_root = ROOT+'/wp-content/cache'
            bases = []
            for name in ['all', 'wpfc-mobile-cache', 'wpo-cache']:
                candidate = cache_root+'/'+name
                try:
                    if stat.S_ISDIR(sftp.lstat(candidate).st_mode):
                        bases.append(candidate)
                except FileNotFoundError:
                    pass
            if not bases:
                raise RuntimeError('WordPress HTML cache directories not found')
            queue = [(folder,0) for folder in bases]
            inspected = 0
            removed = 0
            while queue and inspected < 400:
                folder,depth = queue.pop(0)
                for item in sftp.listdir_attr(folder):
                    inspected += 1
                    if inspected > 400:
                        break
                    path = posixpath.join(folder,item.filename)
                    if item.filename in {'.','..'} or '/' in item.filename:
                        continue
                    if stat.S_ISDIR(item.st_mode) and depth < 3:
                        relative = next(posixpath.relpath(path, base) for base in bases if path.startswith(base+'/'))
                        relative = relative.removeprefix('odpc.de/').rstrip('/')
                        slugs = [url.removeprefix('https://odpc.de/').strip('/') for url in TARGETS if url != 'https://odpc.de']
                        if relative == 'odpc.de' or any(relative == slug or slug.startswith(relative+'/') for slug in slugs):
                            queue.append((path,depth+1))
                    if not stat.S_ISREG(item.st_mode) or item.st_size > 2000000:
                        continue
                    if not re.search(r'\.html(?:[._-](?:gz|gzip))?$',item.filename):
                        continue
                    with sftp.open(path,'rb') as cached:
                        raw = cached.read(2000001)
                    if len(raw) > 2000000:
                        continue
                    try:
                        decoded = gzip.decompress(raw) if raw.startswith(b'\x1f\x8b') else raw
                        source = decoded.decode('utf-8')
                    except (OSError,UnicodeError):
                        continue
                    canonicals = re.findall(r'<link\b[^>]*rel=[\"\x27]canonical[\"\x27][^>]*href=[\"\x27]([^\"\x27]+)',source,re.I)
                    if not canonicals:
                        canonicals = re.findall(r'<link\b[^>]*href=[\"\x27]([^\"\x27]+)[\"\x27][^>]*rel=[\"\x27]canonical[\"\x27]',source,re.I)
                    if len(canonicals) != 1:
                        continue
                    expected = TARGETS.get(canonicals[0].rstrip('/'))
                    if expected is None or expected in source:
                        continue
                    digest = hashlib.sha256(raw).hexdigest()
                    backup = Path('backup-odpc-home-cache')/(digest+'.bin')
                    backup.parent.mkdir(exist_ok=True)
                    backup.write_bytes(raw)
                    sftp.remove(path)
                    removed += 1
                    print('EXPIRED: verified stale SEO cache',posixpath.relpath(path,cache_root))
            print('Homepage cache variants expired:',removed)
    finally:
        transport.close()
    for url, expected in TARGETS.items():
        if expected not in fetch(url+'/'):
            raise RuntimeError('Public page still has stale SEO metadata: '+url)
        print('PASS: normal public URL serves updated SEO metadata:',url)

if __name__ == '__main__':
    main()
