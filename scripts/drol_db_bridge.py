"""Call a short-lived first-party database adapter over the pinned IONOS SFTP path."""
import base64
import hashlib
import hmac
import json
import os
import secrets
from urllib.error import HTTPError
from urllib.request import HTTPRedirectHandler, Request, build_opener

import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, *args):
        return None


def invoke(php, payload):
    token = secrets.token_urlsafe(48)
    name = "drol-bridge-" + secrets.token_hex(20) + ".php"
    path = "/drol/" + name
    endpoint = "https://drol.odpc.de/" + name
    script = php.replace("__TOKEN_HASH__", hashlib.sha256(token.encode()).hexdigest())
    body = dict(payload, host="db5021788879.hosting-data.io", database="dbs16292022",
                username="dbu1397392", password=os.environ["DROL_DB_PASSWORD"])
    opener = build_opener(NoRedirect())
    with paramiko.Transport((HOST, 22)) as transport:
        transport.start_client(timeout=25)
        key = transport.get_remote_server_key().asbytes()
        fingerprint = "SHA256:" + base64.b64encode(hashlib.sha256(key).digest()).decode().rstrip("=")
        if not hmac.compare_digest(fingerprint, PIN):
            raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER, os.environ["ODPC_SFTP_PASSWORD"])
        with paramiko.SFTPClient.from_transport(transport) as sftp:
            uploaded = False
            try:
                with sftp.open(path, "wx") as remote:
                    uploaded = True
                    remote.write(script)
                sftp.chmod(path, 0o644)
                try:
                    with opener.open(Request(endpoint, data=b"{}"), timeout=20):
                        raise RuntimeError("Unauthenticated endpoint accepted request")
                except HTTPError as error:
                    if error.code != 404:
                        raise RuntimeError("Bridge access check failed") from None
                request = Request(endpoint, data=json.dumps(body).encode(),
                                  headers={"Content-Type": "application/json", "X-DROL-Probe": token})
                with opener.open(request, timeout=60) as response:
                    return json.load(response)
            finally:
                if uploaded:
                    sftp.remove(path)
