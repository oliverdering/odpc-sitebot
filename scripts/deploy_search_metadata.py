#!/usr/bin/env python3
"""Publish AI search metadata with pinned SFTP, backup, byte checks and rollback."""
import base64
import hashlib
import hmac
import os
from pathlib import Path
import stat
import time
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN, find_theme
from verify_search_metadata import fetch, validate

SOURCE = Path('site-deploy/ai.odpc.de/odpc-search-metadata.php')
URLS = ['https://ai.odpc.de/', 'https://ai.odpc.de/start/', 'https://ai.odpc.de/werkstatt/', 'https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/']

def main():
    password = os.environ.get('ODPC_SFTP_PASSWORD')
    if not password:
        raise RuntimeError('Missing protected ODPC_SFTP_PASSWORD')
    before = {url: fetch(url) for url in URLS}
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=25)
    actual = 'SHA256:' + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
    if not hmac.compare_digest(actual, PIN):
        transport.close()
        raise RuntimeError('SFTP host fingerprint mismatch')
    try:
        transport.auth_password(USER, password)
        with paramiko.SFTPClient.from_transport(transport) as sftp:
            root = find_theme(sftp).split('/wp-content/themes/')[0]
            sftp.stat(root+'/wp-config.php')
            folder = root+'/wp-content/mu-plugins'
            if not stat.S_ISDIR(sftp.stat(folder).st_mode):
                raise RuntimeError('Unsafe plugin directory')
            target = folder+'/odpc-search-metadata.php'
            backup = Path('backup-search-metadata/previous.php')
            backup.parent.mkdir(exist_ok=True)
            existed = False
            try:
                existing = sftp.lstat(target)
                if not stat.S_ISREG(existing.st_mode) or existing.st_size > 40000:
                    raise RuntimeError('Unsafe prior module')
                sftp.get(target, str(backup))
                existed = True
            except FileNotFoundError:
                pass
            try:
                sftp.put(str(SOURCE), target+'.uploading', confirm=True)
                sftp.posix_rename(target+'.uploading', target)
                with sftp.open(target, 'rb') as remote:
                    if remote.read() != SOURCE.read_bytes():
                        raise RuntimeError('Uploaded checksum mismatch')
                for url in URLS:
                    last_error = None
                    for attempt in range(4):
                        try:
                            validate(url, fetch(url), before[url])
                            last_error = None
                            break
                        except Exception as error:
                            last_error = error
                            time.sleep(3)
                    if last_error:
                        raise last_error
                    print('PASS: metadata, schema and unchanged body:', url)
            except Exception:
                if existed:
                    sftp.put(str(backup), target+'.rollback', confirm=True)
                    sftp.posix_rename(target+'.rollback', target)
                else:
                    sftp.remove(target)
                print('ROLLBACK: previous metadata state restored')
                raise
            finally:
                try:
                    sftp.remove(target+'.uploading')
                except FileNotFoundError:
                    pass
    finally:
        transport.close()

if __name__ == '__main__':
    main()
