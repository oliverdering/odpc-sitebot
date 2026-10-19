#!/usr/bin/env python3
"""Deploy one head-only MU plugin with host pinning, snapshot and rollback."""
import base64
import hashlib
import hmac
import json
import os
import re
import stat
from html.parser import HTMLParser
from pathlib import Path
from urllib.request import Request, urlopen

import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

ROOT = '/Lenchen'
BASE = 'https://tante-lenchen.odpc.de/'
SOURCE = Path('site-deploy/tante-lenchen.odpc.de/odpc-local-search.php')
PAGES = ['', 'speisekarte/', 'kontakt/', 'anfahrt/', 'tisch-anfragen/', 'medien/', 'impressum/', 'datenschutz/']


def fetch(path):
    request = Request(BASE + path, headers={'User-Agent': 'ODPC-Local-Search-QA/1', 'Cache-Control': 'no-cache'})
    with urlopen(request, timeout=35) as response:
        if response.status != 200 or response.url != BASE + path:
            raise RuntimeError('Unexpected public response: ' + path)
        return response.read(1500000).decode('utf-8')


class Head(HTMLParser):
    def __init__(self, source):
        super().__init__()
        self.descriptions = []
        self.canonicals = []
        self.titles = 0
        self.feed(source.split('</head>', 1)[0])

    def handle_starttag(self, tag, attrs):
        data = dict(attrs)
        if tag == 'title': self.titles += 1
        if tag == 'meta' and data.get('name') == 'description': self.descriptions.append(data.get('content', ''))
        if tag == 'link' and data.get('rel') == 'canonical': self.canonicals.append(data.get('href', ''))


def body(source):
    result = re.search(r'<body\b[^>]*>(.*?)</body>', source, re.S | re.I)
    if not result:
        raise RuntimeError('Missing body')
    return re.sub(r'(name="(?:_wpnonce|tl_booking_nonce)" value=")[^"]+', r'\1NONCE', result[1])


def validate(path, current, previous):
    head = Head(current)
    if head.titles != 1 or len(head.descriptions) != 1 or not head.descriptions[0]:
        raise RuntimeError('Title/description count invalid: ' + path)
    if head.canonicals != [BASE + path]:
        raise RuntimeError('Canonical invalid: ' + path)
    match = re.search(r'<script type="application/ld\+json" id="odpc-local-search-schema">(.*?)</script>', current, re.S)
    schema = json.loads(match[1]) if match else {}
    graph = schema.get('@graph', [])
    if [node.get('@type') for node in graph] != ['CafeOrCoffeeShop', 'WebSite', 'WebPage']:
        raise RuntimeError('Missing local schema: ' + path)
    if graph[0]['address']['streetAddress'] != 'Auf Ahl 2' or graph[2]['url'] != BASE + path:
        raise RuntimeError('Wrong schema identity: ' + path)
    if body(current) != body(previous):
        raise RuntimeError('Visible page body changed: ' + path)
    print('PASS: title, description, canonical, local schema, unchanged body:', BASE + path)


def main():
    password = os.environ.get('ODPC_SFTP_PASSWORD')
    if not password:
        raise RuntimeError('Missing protected ODPC_SFTP_PASSWORD')
    before = {path: fetch(path) for path in PAGES}
    for path, source in before.items():
        if Head(source).descriptions and 'odpc-local-search-schema' not in source:
            raise RuntimeError('Existing metadata provider: refusing duplication at ' + path)
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=25)
    try:
        actual = 'SHA256:' + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual, PIN):
            raise RuntimeError('SFTP fingerprint mismatch')
        transport.auth_password(USER, password)
        with paramiko.SFTPClient.from_transport(transport) as sftp:
            for path in [ROOT + '/wp-config.php', ROOT + '/wp-content/themes/tante-lenchen/functions.php']:
                if not stat.S_ISREG(sftp.lstat(path).st_mode):
                    raise RuntimeError('Unexpected WordPress root')
            directory = ROOT + '/wp-content/mu-plugins'
            try:
                if not stat.S_ISDIR(sftp.lstat(directory).st_mode):
                    raise RuntimeError('Unsafe MU directory')
            except FileNotFoundError:
                sftp.mkdir(directory)
            target = directory + '/odpc-local-search.php'
            backup = Path('backup-lenchen-search/previous.php')
            backup.parent.mkdir(exist_ok=True)
            existed = False
            try:
                info = sftp.lstat(target)
                if not stat.S_ISREG(info.st_mode) or info.st_size > 40000:
                    raise RuntimeError('Unsafe existing module')
                sftp.get(target, str(backup))
                existed = True
            except FileNotFoundError:
                pass
            try:
                sftp.put(str(SOURCE), target + '.uploading', confirm=True)
                sftp.posix_rename(target + '.uploading', target)
                with sftp.open(target, 'rb') as remote:
                    if remote.read() != SOURCE.read_bytes():
                        raise RuntimeError('Upload checksum mismatch')
                for path in PAGES:
                    validate(path, fetch(path), before[path])
            except Exception:
                if existed:
                    sftp.put(str(backup), target + '.rollback', confirm=True)
                    sftp.posix_rename(target + '.rollback', target)
                else:
                    sftp.remove(target)
                print('ROLLBACK: previous metadata state restored')
                raise
            finally:
                try: sftp.remove(target + '.uploading')
                except FileNotFoundError: pass
    finally:
        transport.close()


if __name__ == '__main__':
    main()
