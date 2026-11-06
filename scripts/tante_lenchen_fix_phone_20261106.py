import os, base64, hashlib, hmac, pathlib, subprocess, tempfile, zipfile
import paramiko

HOST='home119567219.1and1-data.host'
USER='acc1461323326'
PIN='SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q'
THEME='/Lenchen/wp-content/themes/tante-lenchen'
tr=paramiko.Transport((HOST,22)); tr.start_client(timeout=30)
try:
    fp='SHA256:'+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
    if not hmac.compare_digest(fp,PIN): raise RuntimeError('Server fingerprint mismatch')
    tr.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD'])
    s=paramiko.SFTPClient.from_transport(tr)
    try:
        paths=[x.filename for x in s.listdir_attr(THEME) if x.filename.endswith(('.php','.css'))]
        before={}
        for name in paths:
            with s.open(THEME+'/'+name,'rb') as f: before[name]=f.read()
        edits={}
        for name,data in before.items():
            text=data.decode('utf-8')
            new=text.replace('+4926219862644','+4926216298644').replace('02621 9862644','02621 6298644').replace('02621/9862644','02621/6298644')
            if text!=new:
                if name.endswith('.php'):
                    with tempfile.NamedTemporaryFile(suffix='.php') as f:
                        f.write(new.encode()); f.flush(); subprocess.run(['php','-l',f.name],check=True)
                edits[name]=new.encode()
        if not edits: raise RuntimeError('No incorrect phone number found; nothing changed')
        with zipfile.ZipFile('phone-before.zip','w',zipfile.ZIP_DEFLATED) as z:
            for name in edits: z.writestr(name,before[name])
        for name,data in edits.items():
            temp=THEME+'/'+name+'.phone-new'
            with s.open(temp,'wb') as f: f.write(data)
            s.posix_rename(temp,THEME+'/'+name)
            with s.open(THEME+'/'+name,'rb') as f:
                if f.read()!=data: raise RuntimeError('Readback differs: '+name)
        print('Updated theme files:', ', '.join(sorted(edits)))
    finally: s.close()
finally: tr.close()
