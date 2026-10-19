#!/usr/bin/env python3
"""Back up and publish a narrowly scoped 301 plus WordPress sitemap integration."""
import base64
import hashlib
import hmac
import os
import re
import stat
import time
from pathlib import Path
from urllib.error import HTTPError
from urllib.request import HTTPRedirectHandler, Request, build_opener

import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

ROOT = '/clickandbuilds/Dering'
DEST = 'https://tante-lenchen.odpc.de/'
SOURCE = Path('site-deploy/odpc.de/odpc-lenchen-migration.php')
BACKUP = Path('backup-lenchen-migration')
BLOCK = b'''# BEGIN ODPC Tante Lenchen migration
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{HTTP_HOST} ^(www\\.)?odpc\\.de$ [NC]
RewriteRule ^tante-lenchen/?$ https://tante-lenchen.odpc.de/ [R=301,L,NE]
</IfModule>
# END ODPC Tante Lenchen migration
'''


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def fetch(url):
    request = Request(url, headers={'User-Agent': 'ODPC-Lenchen-Migration-QA/1', 'Cache-Control': 'no-cache'})
    try:
        response = build_opener(NoRedirect).open(request, timeout=40)
    except HTTPError as error:
        if error.code not in (301, 302, 307, 308): raise
        response = error
    with response:
        return response.code, dict(response.headers), response.read().decode('utf-8', 'replace')


def verify():
    for suffix in ['', '/', '?utm_source=migration-check']:
        url = 'https://odpc.de/tante-lenchen' + suffix
        code, headers, _ = fetch(url)
        expected = DEST + ('?utm_source=migration-check' if suffix.startswith('?') else '')
        location = next((value for key, value in headers.items() if key.lower() == 'location'), '')
        if code != 301 or location != expected:
            raise RuntimeError(f'301 verification failed: {url}: {code}, {location}')
        print('PASS: direct HTTP 301', url, '->', location)
    code, headers, html = fetch(DEST)
    if code != 200 or DEST not in re.findall(r'<link[^>]+rel=[\'\"]canonical[\'\"][^>]+href=[\'\"]([^\'\"]+)', html):
        raise RuntimeError('Destination must return 200 with its own canonical')
    if re.search(r'<meta[^>]+name=[\'\"]robots[\'\"][^>]+content=[\'\"][^\'\"]*noindex', html, re.I):
        raise RuntimeError('Destination is noindex')
    if any(key.lower() == 'x-robots-tag' and 'noindex' in value.lower() for key, value in headers.items()):
        raise RuntimeError('Destination has noindex HTTP header')
    print('PASS: destination HTTP 200, self canonical, no noindex')
    for url in ['https://odpc.de/', 'https://odpc.de/kunden-referenzen', DEST + 'robots.txt', DEST + 'wp-sitemap.xml', DEST + 'wp-sitemap-posts-page-1.xml']:
        code, _, body = fetch(url)
        if code != 200: raise RuntimeError('Unrelated page or sitemap unavailable: ' + url)
        if url.endswith('robots.txt') and re.search(r'^Disallow:\s*/\s*$', body, re.M):
            raise RuntimeError('Destination blocks crawling')
        if url.endswith('wp-sitemap-posts-page-1.xml') and '<loc>' + DEST + '</loc>' not in body:
            raise RuntimeError('Destination homepage absent from page sitemap')
        print('PASS: HTTP 200', url)
    code, _, sitemap = fetch('https://odpc.de/page-sitemap.xml?lenchen_migration=' + str(int(time.time())))
    if code != 200 or re.search(r'<loc>https://(?:www\.)?odpc\.de/tante-lenchen/?</loc>', sitemap):
        raise RuntimeError('Old URL still in ODPC page sitemap')
    print('PASS: old page excluded from ODPC sitemap')


def main():
    password = os.environ.get('ODPC_SFTP_PASSWORD')
    if not password: raise RuntimeError('Missing protected SFTP secret')
    if fetch(DEST)[0] != 200: raise RuntimeError('Destination unavailable before deployment')
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=25)
    try:
        actual = 'SHA256:' + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual, PIN): raise RuntimeError('Host fingerprint mismatch')
        transport.auth_password(USER, password)
        with paramiko.SFTPClient.from_transport(transport) as sftp:
            for path in [ROOT + '/wp-config.php', ROOT + '/wp-content/themes/astra/style.css']:
                if not stat.S_ISREG(sftp.lstat(path).st_mode): raise RuntimeError('Wrong WordPress root')
            directory = ROOT + '/wp-content/mu-plugins'
            try:
                if not stat.S_ISDIR(sftp.lstat(directory).st_mode): raise RuntimeError('Unsafe plugin directory')
            except FileNotFoundError:
                sftp.mkdir(directory)
            BACKUP.mkdir(exist_ok=True)
            targets = [ROOT + '/.htaccess', directory + '/' + SOURCE.name]
            previous = {}
            for index, target in enumerate(targets):
                try:
                    info = sftp.lstat(target)
                    if not stat.S_ISREG(info.st_mode) or info.st_size > 200000: raise RuntimeError('Unsafe target')
                    with sftp.open(target, 'rb') as remote: previous[target] = remote.read()
                    (BACKUP / f'previous-{index}.txt').write_bytes(previous[target])
                except FileNotFoundError:
                    previous[target] = None
            old = previous[targets[0]] or b''
            pattern = rb'# BEGIN ODPC Tante Lenchen migration\r?\n.*?# END ODPC Tante Lenchen migration\r?\n?'
            new_htaccess = BLOCK + re.sub(pattern, b'', old, flags=re.S)
            payloads = [new_htaccess, SOURCE.read_bytes()]
            written = []
            try:
                # Plugin first: server redirect becomes effective after the sitemap integration.
                for target, data in reversed(list(zip(targets, payloads))):
                    with sftp.open(target + '.uploading', 'wb') as remote: remote.write(data)
                    sftp.posix_rename(target + '.uploading', target)
                    written.append(target)
                    with sftp.open(target, 'rb') as remote:
                        if remote.read() != data: raise RuntimeError('Upload byte mismatch')
                print('Published exact-path 301 and sitemap integration; existing page content retained')
                verify()
            except Exception:
                for target in reversed(written):
                    if previous[target] is None:
                        sftp.remove(target)
                    else:
                        with sftp.open(target + '.rollback', 'wb') as remote: remote.write(previous[target])
                        sftp.posix_rename(target + '.rollback', target)
                print('ROLLBACK: previous server and plugin state restored')
                raise
    finally:
        transport.close()


if __name__ == '__main__':
    main()
