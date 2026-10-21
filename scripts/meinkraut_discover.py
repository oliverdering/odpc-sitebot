"""Read-only discovery of the newly created Mein Kraut webspace."""
import base64,hashlib,hmac,os,posixpath,stat,json
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN

def main():
    tr=paramiko.Transport((HOST,22));tr.start_client(timeout=25)
    try:
        actual='SHA256:'+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual,PIN):raise RuntimeError('Host key mismatch')
        tr.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD'])
        sf=paramiko.SFTPClient.from_transport(tr)
        try:
            starts=list(dict.fromkeys([sf.normalize('.'),'/clickandbuilds']))
            queue=[(x,0) for x in starts];seen=set();results=[]
            while queue and len(seen)<100:
                path,depth=queue.pop(0)
                if path in seen:continue
                seen.add(path)
                try: entries=sf.listdir_attr(path)
                except OSError:continue
                for e in entries:
                    if not stat.S_ISDIR(e.st_mode) or e.filename.startswith('.'):continue
                    child=posixpath.join(path,e.filename)
                    if depth<1:results.append({'directory':child})
                    if 'kraut' in e.filename.lower():
                        results.append({'candidate':child,'entries':[{'name':x.filename,'bytes':x.st_size,'directory':stat.S_ISDIR(x.st_mode)} for x in sf.listdir_attr(child) if not x.filename.startswith('.')][:30]})
                    if depth<3 and e.filename not in {'wp-content','vendor','node_modules','logs','cache','uploads','backups','tmp'}:queue.append((child,depth+1))
            print(json.dumps({'status':'read_only','paths':results},ensure_ascii=False))
        finally:sf.close()
    finally:tr.close()
if __name__=='__main__':main()
