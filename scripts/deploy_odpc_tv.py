from pathlib import Path
import os
import paramiko
from deploy_helpermonkey_v4 import HOST, USER

source = Path('site-deploy/odpc.de/odpc-tv-lab.php')
root = os.environ['ODPC_WORDPRESS_ROOT'].rstrip('/')
if not root or root == '/':
    raise RuntimeError('Invalid WordPress root')
transport = paramiko.Transport((HOST, 22))
transport.connect(username=USER, password=os.environ['ODPC_SFTP_PASSWORD'])
try:
    sftp = paramiko.SFTPClient.from_transport(transport)
    try:
        for name in ('wp-admin', 'wp-content', 'wp-includes'):
            sftp.stat(root + '/' + name)
        target_dir = root + '/wp-content/mu-plugins'
        try:
            sftp.stat(target_dir)
        except FileNotFoundError:
            sftp.mkdir(target_dir)
        target = target_dir + '/odpc-tv-lab.php'
        with sftp.open(target, 'wb') as output:
            output.write(source.read_bytes())
        print('ODPC TV installed:', target)
    finally:
        sftp.close()
finally:
    transport.close()
