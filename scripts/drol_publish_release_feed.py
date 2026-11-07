"""Publish upstream release metadata as explicitly unreviewed E0 notices."""
import base64,hashlib,hmac,json,os
from datetime import datetime,timezone
from pathlib import Path
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN
def main():
    report=json.loads(Path("drol-release-candidates.json").read_text())
    entries=[]
    for item in report["candidates"]:
        repo,tag,url=item["repository"],item["tag"],item["url"]
        if url!="https://github.com/"+repo+"/releases/tag/"+tag:continue
        entries.append({"product":item["product"],"version":tag,"url":url,
                        "published_at":item.get("published_at"),"status":"UNREVIEWED",
                        "evidence_level":"E0","compatibility":"UNKNOWN"})
    if not entries:raise RuntimeError("No validated releases")
    data=json.dumps({"schema_version":1,"generated_utc":datetime.now(timezone.utc).isoformat(),
                     "count":len(entries),"releases":entries},ensure_ascii=False).encode()
    transport=paramiko.Transport((HOST,22));transport.start_client(timeout=25)
    try:
        fp="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(fp,PIN):raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
        sf=paramiko.SFTPClient.from_transport(transport)
        try:
            root="/drol/data";sf.stat(root)
            tmp=root+"/releases.json.uploading";target=root+"/releases.json"
            with sf.open(tmp,"wb") as f:f.write(data)
            sf.chmod(tmp,0o644);sf.posix_rename(tmp,target)
            with sf.open(target,"rb") as f:
                if hashlib.sha256(f.read()).digest()!=hashlib.sha256(data).digest():raise RuntimeError("Checksum mismatch")
            print("PASS published",len(entries),"E0 releases")
        finally:sf.close()
    finally:transport.close()
if __name__=="__main__":main()
