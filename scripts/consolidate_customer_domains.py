#!/usr/bin/env python3
"""Retire duplicate public HTML with host-scoped redirects; preserve every file.

Before writing, crawl production pages and their first-party dependencies. Any
loading dependency on a retired host blocks the release. Legacy navigation
references are reported and remain valid through checked redirects. Only two .htaccess files may be
changed, with encrypted prior-file backups and automatic rollback on QA failure.
"""
import argparse
import base64
import concurrent.futures
import hashlib
import json
import os
import re
import stat
import time
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser
from pathlib import Path
from xml.etree import ElementTree as ET

PAIRS = {
    'atrium191-rb.odpc.de': 'atrium191.de',
    'bahnhof-grenzau.odpc.de': 'bahnhof-grenzau.de',
}
OLD_HOSTS = tuple(PAIRS)
MARKER = '# BEGIN ODPC customer-domain-consolidation v1'
END = '# END ODPC customer-domain-consolidation v1'
REPORT = Path('customer-domain-consolidation-results.json')
ASSET_EXT = r'avif|css|gif|ico|jpe?g|js|mjs|png|svg|webp|woff2?|ttf|eot|otf|pdf|mp4|webm|mp3|wav|ogg'


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def fetch(url, follow=True):
    request = urllib.request.Request(url, headers={
        'User-Agent': 'ODPC-Customer-Domain-Consolidation/1.0',
        'Cache-Control': 'no-cache',
    })
    opener = urllib.request.build_opener() if follow else urllib.request.build_opener(NoRedirect)
    try:
        response = opener.open(request, timeout=35)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        data = response.read(10000001)
        if len(data) > 10000000:
            raise RuntimeError('Response exceeds safe inspection limit: ' + url)
        return {'url': url, 'final_url': response.url, 'status': response.status,
                'content_type': response.headers.get('Content-Type', ''),
                'location': response.headers.get('Location'), 'data': data}


def old_reference(data):
    text = data.decode('utf-8', 'replace').lower().replace('\\/', '/')
    return [host for host in OLD_HOSTS if host in text or urllib.parse.quote(host, safe='') in text]


def absolute(url, base):
    if not url or url.startswith(('data:', 'blob:', 'javascript:', 'mailto:', 'tel:', '#')):
        return None
    result = urllib.parse.urljoin(base, url)
    split = urllib.parse.urlsplit(result)
    return urllib.parse.urlunsplit(split._replace(fragment='')) if split.scheme in ('http', 'https') else None


class References(HTMLParser):
    def __init__(self, base):
        super().__init__(convert_charrefs=True)
        self.base, self.assets = base, set()
        self.in_style = False

    def add(self, value):
        value = absolute(value, self.base)
        if value:
            self.assets.add(value)

    def handle_starttag(self, tag, values):
        attrs = dict(values)
        if tag == 'style':
            self.in_style = True
        if tag in ('img', 'script', 'source', 'video', 'audio', 'iframe', 'embed'):
            self.add(attrs.get('src'))
        if tag == 'video':
            self.add(attrs.get('poster'))
        if tag == 'object':
            self.add(attrs.get('data'))
        if tag == 'link' and set((attrs.get('rel') or '').split()) & {'stylesheet', 'preload', 'icon', 'modulepreload'}:
            self.add(attrs.get('href'))
        if tag == 'a' and re.search(r'\.(?:pdf|zip)(?:[?#]|$)', attrs.get('href') or '', re.I):
            self.add(attrs.get('href'))
        if attrs.get('srcset') and not attrs['srcset'].startswith('data:'):
            for candidate in attrs['srcset'].split(','):
                self.add(candidate.strip().split()[0])
        self.css(attrs.get('style') or '')

    def handle_endtag(self, tag):
        if tag == 'style':
            self.in_style = False

    def handle_data(self, data):
        if self.in_style:
            self.css(data)

    def css(self, text):
        for match in re.finditer(r'url\(\s*[\"\x27]?([^\)\"\x27]+)', text, re.I):
            self.add(match[1].strip())
        for match in re.finditer(r'@import\s+[\"\x27]([^\"\x27]+)', text, re.I):
            self.add(match[1])


def site_pages(host):
    base = 'https://' + host
    todo = [base + '/sitemap.xml']
    seen, pages = set(), {base + '/'}
    while todo:
        url = todo.pop(0)
        if url in seen:
            continue
        seen.add(url)
        response = fetch(url)
        if response['status'] != 200:
            raise RuntimeError('Production sitemap is unavailable: ' + url)
        root = ET.fromstring(response['data'])
        name = root.tag.rsplit('}', 1)[-1]
        if name not in ('sitemapindex', 'urlset'):
            raise RuntimeError('Unexpected production sitemap format')
        for element in root:
            child = next((c for c in element if c.tag.rsplit('}', 1)[-1] == 'loc'), None)
            if child is None or not child.text:
                continue
            loc = child.text.strip()
            if urllib.parse.urlsplit(loc).hostname != host:
                raise RuntimeError('Sitemap crosses a production-host boundary')
            if name == 'sitemapindex':
                todo.append(loc)
            else:
                pages.add(loc)
        if len(seen) > 20 or len(pages) > 100:
            raise RuntimeError('Unexpected crawl size')
    if host == 'bahnhof-grenzau.de':
        pages.update([base + '/?page_id=7', base + '/?page_id=8'])
    # Also verify every destination of the old ATRIUM public page routes.
    if host == 'atrium191.de':
        pages.update(base + '/' + slug + '/' for slug in (
            'wohnungen', 'erleben', 'geschichte', 'architektur', 'lage',
            'faq', 'kontakt', 'impressum', 'datenschutz', 'barrierefreiheit'))
    return sorted(pages)


def inspect_production():
    pages = sorted({url for host in PAIRS.values() for url in site_pages(host)})
    resources, checked_assets, snapshots = set(), set(), {}
    failures, legacy_references = [], []
    with concurrent.futures.ThreadPoolExecutor(max_workers=6) as executor:
        responses = list(executor.map(fetch, pages))
    for response in responses:
        url = response['url']
        if response['status'] != 200 or 'html' not in response['content_type']:
            failures.append('Production HTML unavailable: ' + url)
            continue
        if urllib.parse.urlsplit(response['final_url']).hostname not in PAIRS.values():
            failures.append('Production page redirects off the intended domains: ' + url)
        if old_reference(response['data']):
            legacy_references.append(url)
        parser = References(response['final_url'])
        text = response['data'].decode('utf-8', 'replace')
        parser.feed(text)
        resources.update(parser.assets)
        stable = re.sub(r'(\bnonce[\"\x27]?\s*[:=]\s*[\"\x27])[^\"\x27]+', r'\1NONCE', text, flags=re.I)
        snapshots[url] = hashlib.sha256(stable.encode()).hexdigest()
        if 'page_id=7' in url and not re.search(r'Impressum', text, re.I):
            failures.append('Missing destination legal content: ' + url)
        if 'page_id=8' in url and not re.search(r'Datenschutz', text, re.I):
            failures.append('Missing destination privacy content: ' + url)
    # Follow first-party stylesheets recursively, checking fonts/images/imports.
    for depth in range(4):
        todo = sorted(url for url in resources - checked_assets
                      if urllib.parse.urlsplit(url).hostname in set(PAIRS.values()) | set(OLD_HOSTS))
        if not todo:
            break
        if len(todo) + len(checked_assets) > 300:
            raise RuntimeError('Unexpected dependency crawl size')
        with concurrent.futures.ThreadPoolExecutor(max_workers=6) as executor:
            responses = list(executor.map(fetch, todo))
        for response in responses:
            url = response['url']
            checked_assets.add(url)
            if urllib.parse.urlsplit(url).hostname in OLD_HOSTS:
                failures.append('Production resource depends on retired host: ' + url)
            if response['status'] != 200:
                failures.append('Production resource unavailable: ' + url)
                continue
            if 'html' in response['content_type']:
                failures.append('Asset unexpectedly returns HTML: ' + url)
            if any(word in response['content_type'] for word in ('css', 'javascript', 'json', 'svg')):
                if old_reference(response['data']):
                    failures.append('Production code refers to retired host: ' + url)
                if 'css' in response['content_type']:
                    parser = References(response['final_url'])
                    parser.css(response['data'].decode('utf-8', 'replace'))
                    resources.update(parser.assets)
    result = {'page_count': len(pages), 'asset_count': len(checked_assets),
              'failures': failures, 'legacy_text_or_navigation_references': legacy_references,
              'pages': pages, 'assets': sorted(checked_assets)}
    if failures:
        REPORT.write_text(json.dumps({'status': 'preflight_blocked', 'production': result}, indent=2))
        raise RuntimeError('Production dependencies must be resolved before redirects: ' + '; '.join(failures[:5]))
    return result, snapshots


def redirect_block(host):
    target = PAIRS[host]
    condition = 'RewriteCond %{HTTP_HOST} ^' + re.escape(host) + r'(?::[0-9]+)?$ [NC]'
    rules = [MARKER, '<IfModule mod_rewrite.c>', 'RewriteEngine On',
             '# Administrative and static-resource requests continue through the original rules.']
    routes = [('^(?:sitemap|wp-sitemap)\\.xml$', 'https://' + target + '/sitemap.xml')]
    if host == 'bahnhof-grenzau.odpc.de':
        routes += [('^impressum\\.html$', 'https://' + target + '/?page_id=7'),
                   ('^datenschutz\\.html$', 'https://' + target + '/?page_id=8'),
                   ('^index\\.html$', 'https://' + target + '/')]
    routes += [('^index\\.php(?:/(.*))?$', 'https://' + target + '/$1'),
               ('^(.*)$', 'https://' + target + '/$1')]
    for pattern, destination in routes:
        rules += [condition,
                  r'RewriteCond %{REQUEST_URI} !^/(?:wp-admin(?:/|$)|wp-login\.php$|wp-json(?:/|$)) [NC]',
                  r'RewriteCond %{REQUEST_URI} !\.(?:' + ASSET_EXT + r')$ [NC]',
                  'RewriteRule ' + pattern + ' ' + destination + ' [R=301,L,NE]']
    return ('\n'.join(rules + ['</IfModule>', END, '']) + '\n').encode()


def updated_config(original, host):
    if MARKER.encode() in original:
        pattern = re.escape(MARKER.encode()) + rb'.*?' + re.escape(END.encode()) + rb'\s*'
        original, count = re.subn(pattern, b'', original, flags=re.S)
        if count != 1:
            raise RuntimeError('Ambiguous prior consolidation block')
    return redirect_block(host) + original


def inspect_redirects():
    expected = []
    for old, new in PAIRS.items():
        expected += [('https://' + old + '/', 'https://' + new + '/'),
                     ('http://' + old + '/', 'https://' + new + '/'),
                     ('https://' + old + '/sitemap.xml', 'https://' + new + '/sitemap.xml')]
    for slug in ('wohnungen', 'erleben', 'geschichte', 'architektur', 'lage', 'faq', 'kontakt', 'impressum', 'datenschutz', 'barrierefreiheit'):
        expected.append(('https://atrium191-rb.odpc.de/index.php/' + slug + '/', 'https://atrium191.de/' + slug + '/'))
    expected += [('https://bahnhof-grenzau.odpc.de/impressum.html', 'https://bahnhof-grenzau.de/?page_id=7'),
                 ('https://bahnhof-grenzau.odpc.de/datenschutz.html', 'https://bahnhof-grenzau.de/?page_id=8'),
                 ('https://atrium191-rb.odpc.de/?utm_source=legacy', 'https://atrium191.de/?utm_source=legacy')]
    checks = []
    for source, destination in expected:
        response = fetch(source, follow=False)
        upgrade = None
        # Some hosting configurations upgrade HTTP before document-root rules.
        # Permit that exact same-host HTTPS hop, then require our 301 to production.
        if source.startswith('http://') and response['status'] in (301, 308):
            https_source = 'https://' + source[len('http://'):]
            if response['location'] == https_source:
                upgrade = https_source
                response = fetch(https_source, follow=False)
        if response['status'] != 301 or response['location'] != destination:
            raise RuntimeError('Unexpected redirect: ' + source + ' -> ' + str(response['status']) + ' ' + str(response['location']))
        checks.append({'source': source, 'status': 301, 'destination': destination, 'hosting_https_upgrade': upgrade})
    return checks


def deploy():
    from deploy_bahnhof_grenzau_bootstrap import connect, read, atomic_write
    from deploy_shared_footer import find_roots
    from cryptography.fernet import Fernet
    before, before_hashes = inspect_production()
    print(json.dumps({'phase': 'production_preflight_passed', 'pages': before['page_count'], 'assets': before['asset_count']}), flush=True)
    transport, sftp = connect()
    changed = []
    try:
        roots = find_roots(sftp)
        targets = {'atrium191-rb.odpc.de': roots['atrium191-odpc'],
                   'bahnhof-grenzau.odpc.de': '/Bahnhof-Grenzau'}
        if len(set(targets.values())) != 2:
            raise RuntimeError('Unexpected overlapping document roots')
        if b'Bahnhof' not in (read(sftp, '/Bahnhof-Grenzau/index.html') or b''):
            raise RuntimeError('Unexpected Bahnhof source root')
        prepared = []
        for host, root in targets.items():
            path = root + '/.htaccess'
            original = read(sftp, path)
            if original is None:
                raise RuntimeError('Expected existing site access configuration')
            mode = stat.S_IMODE(sftp.lstat(path).st_mode)
            prepared.append((host, path, original, mode, updated_config(original, host)))
        salt = os.urandom(16)
        key = base64.urlsafe_b64encode(hashlib.pbkdf2_hmac('sha256', os.environ['ODPC_SFTP_PASSWORD'].encode(), salt, 300000))
        archive = {'created_utc': time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime()),
                   'files': [{'host': h, 'path': p, 'mode': m, 'data_b64': base64.b64encode(o).decode()} for h, p, o, m, n in prepared]}
        backup = Path('backup-customer-domain-consolidation')
        backup.mkdir(exist_ok=True)
        encrypted = Fernet(key).encrypt(json.dumps(archive).encode())
        if json.loads(Fernet(key).decrypt(encrypted)) != archive:
            raise RuntimeError('Backup round-trip verification failed')
        (backup/'prior-configs.encrypted.json').write_text(json.dumps({
            'format': 'pbkdf2-sha256-300000-fernet-v1', 'salt': base64.b64encode(salt).decode(),
            'ciphertext': encrypted.decode()}))
        for host, path, original, mode, new in prepared:
            if original != new:
                atomic_write(sftp, path, new, mode)
                changed.append((path, original, mode))
        redirects = inspect_redirects()
        after, after_hashes = inspect_production()
        if before_hashes != after_hashes:
            raise RuntimeError('Production page content changed during the release; rolling back retirement rules')
        result = {'status': 'verified', 'files_changed': len(changed),
                  'files_deleted': 0, 'production_before': before,
                  'production_after': after, 'redirects': redirects,
                  'note': 'Existing media and administrative paths remain available; public duplicate HTML redirects to production.'}
        REPORT.write_text(json.dumps(result, indent=2))
        print(json.dumps({'status': result['status'], 'files_changed': len(changed), 'files_deleted': 0,
                          'pages_checked_after': after['page_count'], 'assets_checked_after': after['asset_count'],
                          'redirects_verified': len(redirects)}), flush=True)
    except BaseException:
        for path, original, mode in reversed(changed):
            atomic_write(sftp, path, original, mode)
        print(json.dumps({'status': 'rolled_back', 'restored_files': len(changed)}), flush=True)
        raise
    finally:
        sftp.close()
        transport.close()


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--deploy', action='store_true')
    args = parser.parse_args()
    if args.deploy:
        deploy()
    else:
        result, _ = inspect_production()
        REPORT.write_text(json.dumps({'status': 'preflight_passed', 'production': result}, indent=2))
        print(json.dumps({'status': 'preflight_passed', 'pages': result['page_count'], 'assets': result['asset_count']}), flush=True)
