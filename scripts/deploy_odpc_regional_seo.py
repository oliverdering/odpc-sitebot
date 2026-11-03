#!/usr/bin/env python3
"""Deploy only ODPC regional SEO mu-plugin with backup and rollback."""
import base64, hashlib, hmac, os, posixpath, stat, time
from pathlib import Path
from urllib.request import Request, urlopen
import paramiko

HOST='home119567219.1and1-data.host'
USER='acc1461323326'
HOSTKEY='SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q'
ROOT='/clickandbuilds/Dering'
SOURCE=Path('site-deploy/odpc.de/odpc-regional-seo.php')
TARGET=posixpath.join(ROOT,'wp-content/mu-plugins/odpc-regional-seo.php')
BACKUP=Path('odpc-regional-seo-backup')
URL='https://odpc.de/it-dienstleistungen-ki-integration/'
def fetch():
    req=Request(URL+'?odpc_seo_check='+str(int(time.time())),headers={'User-Agent':'ODPC-Regional-SEO-QA','Cache-Control':'no-cache'})
    with urlopen(req,timeout=35) as response:
        if response.status!=200:raise RuntimeError('Service page HTTP error')
        return response.read().decode('utf-8','replace')
def main():
    pw=os.environ.get('ODPC_SFTP_PASSWORD')
    if not pw:raise RuntimeError('ODPC_SFTP_PASSWORD missing')
    if not SOURCE.is_file():raise RuntimeError('Source missing')
    # Verify the target exists before any mutation.
    baseline=fetch()
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=20)
    actual='SHA256:'+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
    if not hmac.compare_digest(actual,HOSTKEY):
        transport.close();raise RuntimeError('Host key mismatch')
    transport.auth_password(USER,pw)
    if not transport.is_authenticated():
        transport.close();raise RuntimeError('Authentication failed')
    sftp=paramiko.SFTPClient.from_transport(transport)
    previous=None
    try:
        sftp.stat(ROOT+'/wp-content/themes/astra/style.css')
        try:
            entry=sftp.lstat(TARGET)
            if not stat.S_ISREG(entry.st_mode) or entry.st_size>20000:raise RuntimeError('Unexpected target')
            with sftp.open(TARGET,'rb') as f:previous=f.read()
        except FileNotFoundError:pass
        BACKUP.mkdir(exist_ok=True)
        if previous is not None:(BACKUP/'previous.php').write_bytes(previous)
        temp=TARGET+'.deploying'
        sftp.put(str(SOURCE),temp,confirm=True)
        sftp.posix_rename(temp,TARGET)
        try:
            for attempt in range(3):
                time.sleep(3)
                page=fetch()
                if 'id="odpc-regional-service-schema"' in page and 'Koblenz' in page:break
            else:raise RuntimeError('Structured data missing on live service page')
            # Ensure no visible page markup was intentionally replaced.
            if '<body' not in page.lower():raise RuntimeError('Body missing')
            print('PASS: regional service schema live, page available, no theme or page content written')
        except Exception:
            rollback=TARGET+'.rollback'
            if previous is None:sftp.remove(TARGET)
            else:
                (BACKUP/'previous.php').write_bytes(previous)
                sftp.put(str(BACKUP/'previous.php'),rollback,confirm=True)
                sftp.posix_rename(rollback,TARGET)
            print('ROLLBACK completed')
            raise
    finally:
        sftp.close();transport.close()
if __name__=='__main__':main()
