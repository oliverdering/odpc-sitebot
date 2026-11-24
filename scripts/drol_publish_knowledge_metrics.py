"""Publish only aggregate DROL metrics, atomically, via pinned SFTP."""
import base64, hashlib, hmac, json, os, secrets
from pathlib import Path
from datetime import datetime
from urllib.request import urlopen, Request
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

def main():
    data = json.loads(Path("drol-knowledge-metrics.json").read_text())
    keys = {"metadata_inbox", "candidate_facts_total", "model_facts_total", "source_records"}
    if set(data) != keys | {"measured_at"} or any(type(data[k]) is not int or data[k] < 0 for k in keys):
        raise ValueError("Invalid aggregate metrics")
    if datetime.fromisoformat(data["measured_at"]).tzinfo is None:
        raise ValueError("Missing timezone")
    raw = json.dumps(data, indent=2).encode()
    target = "/drol/data/knowledge-metrics.json"
    tmp = target + "." + secrets.token_hex(8) + ".uploading"
    transport = paramiko.Transport((HOST,22))
    try:
        transport.start_client(timeout=25)
        fp = "SHA256:" + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(fp,PIN):
            raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
        with paramiko.SFTPClient.from_transport(transport) as sf:
            try:
                with sf.open(tmp,"wx") as f:
                    f.write(raw)
                sf.chmod(tmp,0o644)
                sf.posix_rename(tmp,target)
            finally:
                try: sf.remove(tmp)
                except FileNotFoundError: pass
        with urlopen(Request("https://drol.odpc.de/data/knowledge-metrics.json?ts="+secrets.token_hex(8),headers={"Cache-Control":"no-cache"}),timeout=20) as response:
            if json.load(response) != data:
                raise RuntimeError("Public metrics mismatch")
        print("PASS public aggregate metrics verified",data)
    finally:
        transport.close()

if __name__ == "__main__":
    main()
