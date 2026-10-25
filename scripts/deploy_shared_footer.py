"""Apply only footer markup; back up each changed file and roll back failed sites."""
import base64
import hashlib
import hmac
import json
import os
import posixpath
from pathlib import Path
import stat
import subprocess
import tempfile
import time
import urllib.request
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN
from footer_credit import focus_existing, grenzau, meinkraut, PORTRAIT

BACKUP = Path('backup-shared-footer')
PORTRAIT_SHA256 = 'ea47cfd03e3302196b4e641444bbf2513eaa2d5675f1dcda3e37cecdb29fe3ec'


def original_portrait():
    with urllib.request.urlopen(PORTRAIT, timeout=35) as response:
        data = response.read(1000000)
    if hashlib.sha256(data).hexdigest() != PORTRAIT_SHA256:
        raise RuntimeError('Original portrait checksum differs; no replacement image accepted')
    return data


def read(sf, path):
    info = sf.lstat(path)
    if not stat.S_ISREG(info.st_mode) or info.st_size > 1000000:
        raise RuntimeError('Unsafe or oversized target: ' + path)
    with sf.open(path, 'rb') as handle:
        return handle.read(), stat.S_IMODE(info.st_mode)


def write(sf, path, data, mode):
    temporary = path + '.odpc-footer-upload'
    try:
        with sf.open(temporary, 'wb') as handle:
            handle.write(data)
        sf.chmod(temporary, mode)
        sf.posix_rename(temporary, path)
    finally:
        try:
            sf.remove(temporary)
        except FileNotFoundError:
            pass


def find_roots(sf):
    wanted = {'tante-lenchen', 'atrium191-odpc', 'helpermonkey-business-v34-child'}
    found = {}
    queue = [(sf.normalize('.'), 0)]
    seen = set()
    while queue and len(seen) < 220:
        root, depth = queue.pop(0)
        if root in seen:
            continue
        seen.add(root)
        for slug in wanted - found.keys():
            path = root + '/wp-content/themes/' + slug
            try:
                sf.stat(path + '/style.css')
                sf.stat(root + '/wp-config.php')
                found[slug] = root
            except FileNotFoundError:
                pass
        if wanted == found.keys():
            return found
        if depth >= 5:
            continue
        try:
            entries = sf.listdir_attr(root)
        except OSError:
            continue
        for item in entries:
            if stat.S_ISDIR(item.st_mode) and not item.filename.startswith('.') and item.filename not in {
                'wp-content', 'wp-admin', 'wp-includes', 'node_modules', 'vendor', 'logs', 'tmp',
                'cache', 'uploads', 'backup', 'backups', 'assets'}:
                queue.append((posixpath.join(root, item.filename), depth + 1))
    raise RuntimeError('Could not identify all three known WordPress installations')


def public(url):
    query = ('&' if '?' in url else '?') + 'odpc_footer_check=' + str(time.time_ns())
    request = urllib.request.Request(url + query, headers={'Cache-Control': 'no-cache', 'User-Agent': 'ODPC-Footer-QA/1'})
    with urllib.request.urlopen(request, timeout=35) as response:
        return response.read(800000).decode('utf-8')


def release(sf, name, files, urls):
    prepared = []
    for path, transform in files:
        old, mode = read(sf, path)
        new = transform(old.decode('utf-8')).encode('utf-8')
        if path.endswith(('.php', '.js')):
            with tempfile.NamedTemporaryFile(suffix=Path(path).suffix) as check:
                check.write(new)
                check.flush()
                command = ['php', '-l', check.name] if path.endswith('.php') else ['node', '--check', check.name]
                subprocess.run(command, check=True, capture_output=True)
        prepared.append((path, old, new, mode, True))
    if name == 'meinkraut':
        path = '/MeinKraut/assets/oliver-dering.webp'
        try:
            old, mode = read(sf, path)
            existed = True
        except FileNotFoundError:
            old, mode, existed = b'', 0o644, False
        prepared.insert(0, (path, old, original_portrait(), mode, existed))
    changed = []
    try:
        for path, old, new, mode, existed in prepared:
            if old == new:
                continue
            saved = BACKUP / name / path.lstrip('/')
            saved.parent.mkdir(parents=True, exist_ok=True)
            if existed:
                saved.write_bytes(old)
            write(sf, path, new, mode)
            changed.append((path, old, mode, existed))
        for url in urls:
            body = public(url)
            if 'object-position:50% 12%' not in body or 'Powered by odpc.de' not in body:
                raise RuntimeError('Footer verification failed: ' + url)
        print(json.dumps({'site': name, 'status': 'verified', 'files_changed': len(changed), 'urls': urls}), flush=True)
    except Exception:
        for path, old, mode, existed in reversed(changed):
            if existed:
                write(sf, path, old, mode)
            else:
                sf.remove(path)
        raise


def main():
    tr = paramiko.Transport((HOST, 22))
    tr.start_client(timeout=30)
    try:
        actual = 'SHA256:' + base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual, PIN):
            raise RuntimeError('SFTP host fingerprint mismatch')
        tr.auth_password(USER, os.environ['ODPC_SFTP_PASSWORD'])
        sf = paramiko.SFTPClient.from_transport(tr)
        try:
            roots = find_roots(sf)
            targets = [
                ('lenchen', [(roots['tante-lenchen'] + '/wp-content/themes/tante-lenchen/footer.php', focus_existing)], ['https://tante-lenchen.odpc.de/', 'https://tante-lenchen.odpc.de/impressum/']),
                ('atrium', [(roots['atrium191-odpc'] + '/wp-content/themes/atrium191-odpc/footer.php', focus_existing)], ['https://atrium191-rb.odpc.de/']),
                ('helper-monkey', [(roots['helpermonkey-business-v34-child'] + '/wp-content/mu-plugins/odpc-helpermonkey-v4.php', focus_existing)], ['https://ai.odpc.de/', 'https://ai.odpc.de/impressum/']),
                ('justen-preview', [('/Justen/index.html', focus_existing)], ['https://ra-justen.odpc.de/']),
                ('grenzau', [('/Bahnhof-Grenzau/index.html', grenzau)], ['https://bahnhof-grenzau.odpc.de/']),
                ('meinkraut', [('/MeinKraut/server/public.php', meinkraut), ('/MeinKraut/meinkraut.js', lambda value: meinkraut(value, True))], ['https://meinkraut.odpc.de/', 'https://meinkraut.odpc.de/gewuerze/'])]
            failed = []
            for name, files, urls in targets:
                try:
                    release(sf, name, files, urls)
                except Exception as error:
                    print(json.dumps({'site': name, 'status': 'rolled_back_or_unchanged', 'reason': str(error)}), flush=True)
                    failed.append(name)
            if failed:
                raise RuntimeError('Sites requiring follow-up: ' + ', '.join(failed))
        finally:
            sf.close()
    finally:
        tr.close()


if __name__ == '__main__':
    main()
