#!/usr/bin/env python3
"""Scoped, reversible SFTP release of the Bahnhof Grenzau holding page."""
import base64
import hashlib
import hmac
import json
import os
from pathlib import Path
import secrets
import re
import stat
import time
import urllib.request
import urllib.error
import paramiko

HOST = "home119567219.1and1-data.host"
USER = "acc1461323326"
ROOT = "/Bahnhof-Grenzau"
URL = "https://bahnhof-grenzau.odpc.de"
PIN = "SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
MARKER = b'id="bahnhof-grenzau-start"'
GUARD = b'''# ODPC Bahnhof Grenzau: public holding page, unfinished PHP stays private
DirectoryIndex index.html index.php
Options -Indexes
<FilesMatch "(^\\.|\\.php$|\\.(?:zip|json|md|py|sh|bak|sql|log)$)">
  Require all denied
</FilesMatch>
'''

def connect():
    password = os.environ.get("ODPC_SFTP_PASSWORD")
    if not password:
        raise RuntimeError("ODPC_SFTP_PASSWORD is required; no server changes made")
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=30)
    actual = "SHA256:" + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
    if not hmac.compare_digest(actual, PIN):
        transport.close()
        raise RuntimeError("SFTP host fingerprint mismatch")
    transport.auth_password(USER, password)
    if not transport.is_authenticated():
        transport.close()
        raise RuntimeError("SFTP authentication failed")
    return transport, paramiko.SFTPClient.from_transport(transport)

def read(sftp, path):
    try:
        info = sftp.lstat(path)
    except FileNotFoundError:
        return None
    if not stat.S_ISREG(info.st_mode):
        raise RuntimeError("Expected regular file: " + path)
    with sftp.open(path, "rb") as source:
        return source.read()

def atomic_write(sftp, path, data, mode=0o644):
    temporary = path + ".odpc-" + secrets.token_hex(8)
    try:
        with sftp.open(temporary, "wx") as output:
            output.write(data)
        sftp.chmod(temporary, mode)
        sftp.posix_rename(temporary, path)
    finally:
        try:
            sftp.remove(temporary)
        except FileNotFoundError:
            pass

def find_landing():
    for path in [Path(__file__).resolve().parent / "index.html", Path("site-deploy/bahnhof-grenzau/index.html")]:
        if path.is_file():
            data = path.read_bytes()
            if MARKER not in data:
                raise RuntimeError("Unexpected landing page")
            return data
    raise RuntimeError("Packaged index.html missing")

def find_media():
    for base in [Path(__file__).resolve().parent, Path('site-deploy/bahnhof-grenzau')]:
        manifest = base / 'media.json'
        if not manifest.is_file():
            continue
        result = []
        for entry in json.loads(manifest.read_text()):
            name = entry['file']
            if not re.fullmatch(r'grenzau-[a-z0-9-]+\.webp', name):
                raise RuntimeError('Invalid media filename')
            data = (base / 'assets' / name).read_bytes()
            if len(data) > 2000000 or not data.startswith(b'RIFF') or data[8:12] != b'WEBP' or hashlib.sha256(data).hexdigest() != entry['sha256']:
                raise RuntimeError('Media checksum or format mismatch')
            result.append((name, data))
        return result
    return []

def find_pages():
    base = Path('site-deploy/bahnhof-grenzau')
    result = []
    for name in ['impressum.html', 'datenschutz.html', 'sitemap.xml']:
        path = base / name
        data = path.read_bytes()
        if len(data) > 200000:
            raise RuntimeError('Static page exceeds size limit')
        if name.endswith('.html'):
            marker = ('data-legal-page="' + name[:-5] + '"').encode()
            if marker not in data or b'Cengiz G' not in data:
                raise RuntimeError('Unexpected legal page')
        elif b'<urlset' not in data or b'https://bahnhof-grenzau.odpc.de/' not in data:
            raise RuntimeError('Unexpected sitemap')
        result.append((name, data))
    return result

def upload_media(sftp, media):
    if not media:
        return
    folder = ROOT + '/assets'
    try:
        sftp.mkdir(folder, 0o755)
    except IOError:
        if not stat.S_ISDIR(sftp.lstat(folder).st_mode):
            raise
    for name, data in media:
        existing = read(sftp, folder + '/' + name)
        if existing is not None:
            if existing != data:
                raise RuntimeError('Immutable media path contains unexpected data')
        else:
            atomic_write(sftp, folder + '/' + name, data)

def release():
    landing = find_landing()
    media = find_media()
    pages = find_pages()
    previous_pages = {}
    transport, sftp = connect()
    changed = False
    backup = None
    old_html = old_guard = None
    try:
        if not stat.S_ISDIR(sftp.lstat(ROOT).st_mode):
            raise RuntimeError("Target is not a regular directory")
        if read(sftp, ROOT + "/wp-config.php") is not None:
            raise RuntimeError("Configured WordPress found; refusing holding-page takeover")
        old_guard = read(sftp, ROOT + "/.htaccess")
        old_html = read(sftp, ROOT + "/index.html")
        staging = read(sftp, ROOT + "/.odpc-staging")
        if old_html is not None and MARKER not in old_html:
            raise RuntimeError("Unrecognized existing index.html; refusing replacement")
        if old_guard != GUARD:
            if staging is None or old_guard is None or b"Require all denied" not in old_guard:
                raise RuntimeError("Expected unconfigured, protected staging installation not found")
            meaningful = [line.strip() for line in old_guard.decode("utf-8").splitlines() if line.strip() and not line.lstrip().startswith("#")]
            allowed = {"Options -Indexes", "Require all denied", "<IfModule mod_authz_core.c>", "</IfModule>", "DirectoryIndex index.php"}
            if any(line not in allowed for line in meaningful):
                raise RuntimeError("Unrecognized access rules; refusing automatic replacement")
        for name in ["index.php", "wp-admin/install.php", "wp-includes/version.php"]:
            if read(sftp, ROOT + "/" + name) is None:
                raise RuntimeError("Incomplete WordPress core; missing " + name)
        folder = ROOT + "/.odpc-backups"
        try:
            sftp.mkdir(folder, 0o700)
        except IOError:
            if not stat.S_ISDIR(sftp.lstat(folder).st_mode):
                raise
        atomic_write(sftp, folder + "/.htaccess", b"Require all denied\n", 0o600)
        backup = folder + "/" + time.strftime("%Y%m%dT%H%M%SZ", time.gmtime()) + "-" + secrets.token_hex(4)
        sftp.mkdir(backup, 0o700)
        for name, data in [("htaccess.previous", old_guard), ("index.previous", old_html)]:
            if data is not None:
                atomic_write(sftp, backup + "/" + name, data, 0o600)
        for name, data in pages:
            previous = read(sftp, ROOT + '/' + name)
            if previous is not None:
                expected = b'<urlset' if name == 'sitemap.xml' else ('data-legal-page="' + name[:-5] + '"').encode()
                if expected not in previous:
                    raise RuntimeError('Unrecognized existing static page: ' + name)
                atomic_write(sftp, backup + '/' + name + '.previous', previous, 0o600)
            previous_pages[name] = previous
        upload_media(sftp, media)
        # Publishing the access rules is the final mutation; PHP remains denied.
        changed = True
        for name, data in pages:
            atomic_write(sftp, ROOT + '/' + name, data)
        atomic_write(sftp, ROOT + "/index.html", landing)
        atomic_write(sftp, ROOT + "/.htaccess", GUARD)
        with urllib.request.urlopen(URL + "/?release=" + secrets.token_hex(6), timeout=30) as response:
            published = response.read(200000)
            if response.status != 200 or MARKER not in published or hashlib.sha256(published).digest() != hashlib.sha256(landing).digest():
                raise RuntimeError("Public start page verification failed")
        for name, data in pages:
            with urllib.request.urlopen(URL + '/' + name + '?release=' + secrets.token_hex(6), timeout=30) as response:
                if response.status != 200 or hashlib.sha256(response.read(200001)).digest() != hashlib.sha256(data).digest():
                    raise RuntimeError('Public static page verification failed: ' + name)
        for name, data in media:
            with urllib.request.urlopen(URL + '/assets/' + name, timeout=30) as response:
                if response.status != 200 or hashlib.sha256(response.read(2000001)).digest() != hashlib.sha256(data).digest():
                    raise RuntimeError('Public media verification failed')
        for path in ["/wp-login.php", "/wp-admin/install.php", "/wp-admin/setup-config.php"]:
            try:
                with urllib.request.urlopen(URL + path, timeout=20) as response:
                    raise RuntimeError("Unfinished PHP exposed: " + path)
            except urllib.error.HTTPError as error:
                if error.code not in (403, 404):
                    raise
        print("PUBLIC_START_PAGE_OK HTTP 200; unfinished PHP protected")
        if b'data-story-version=' in published:
            print('HISTORY_PAGE_OK; full HTML checksum verified; media files', len(media))
        print("BACKUP", backup)
        print("EXISTING_WORDPRESS_CORE_UNCHANGED; database configuration still required")
    except Exception:
        if changed:
            for name, previous in previous_pages.items():
                if previous is None:
                    try:
                        sftp.remove(ROOT + '/' + name)
                    except FileNotFoundError:
                        pass
                else:
                    atomic_write(sftp, ROOT + '/' + name, previous)
            if old_guard is not None:
                atomic_write(sftp, ROOT + "/.htaccess", old_guard)
            if old_html is None:
                try:
                    sftp.remove(ROOT + "/index.html")
                except FileNotFoundError:
                    pass
            else:
                atomic_write(sftp, ROOT + "/index.html", old_html)
            print("ROLLBACK_COMPLETED")
        raise
    finally:
        sftp.close()
        transport.close()

if __name__ == "__main__":
    release()

