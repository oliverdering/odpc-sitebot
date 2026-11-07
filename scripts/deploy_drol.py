"""Deploy only D.R.O.L. to its existing /drol IONOS directory; never touch ODPC."""
import base64,hashlib,hmac,os,posixpath,stat
from pathlib import Path
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN
# Shared ODPC attribution on every published D.R.O.L. HTML page.
# Keep the approved page design; the credit is appended immediately before </body>.
ODPC_CREDIT = """<style id="drol-odpc-credit-style">
#drol-odpc-credit{box-sizing:border-box;display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;max-width:1100px;margin:24px auto 96px;padding:20px 24px;border-top:1px solid rgba(135,176,191,.35);color:inherit;font:inherit}
#drol-odpc-credit a{color:inherit;text-decoration:none}#drol-odpc-credit a:hover{text-decoration:underline}
#drol-odpc-credit img{display:block;width:64px;height:64px;object-fit:cover;object-position:50% 12%;border-radius:50%;border:2px solid rgba(255,255,255,.55)}
#drol-odpc-credit .odpc-credit-label{display:block;font-size:.8rem;opacity:.8}
#drol-odpc-credit .odpc-credit-link{display:inline-block;font-weight:750}
@media(max-width:640px){#drol-odpc-credit{margin-bottom:100px;padding:18px}}
</style><aside id="drol-odpc-credit" aria-label="Website erstellt von ODPC"><a href="https://odpc.de/" target="_blank" rel="noopener noreferrer" aria-label="ODPC – Oliver Dering"><img src="https://odpc.de/wp-content/uploads/2026/08/1786371119667.png.webp" alt="Oliver Dering" width="64" height="64" loading="lazy" decoding="async"></a><div><span class="odpc-credit-label">Webdesign, Technik &amp; KI-Integration</span><a class="odpc-credit-link" href="https://odpc.de/" target="_blank" rel="noopener noreferrer">Powered by odpc.de · Oliver Dering</a></div></aside>"""

DROL_CONTRAST_PATCH = """<style id="drol-global-contrast-v1">
body main .catalog-audit,body main .audit,body main .info-box,body main .notice,body main .note,body main .callout,body main .filters,body main .card,body main article.card,body main .panel,body main .box,body main .method,body main .usecase,body main .report-sheet,body main .faq-jump,body main .toc{background:var(--ui-panel,var(--surface,#0b2031))!important;color:var(--ui-text,var(--ink,#f4f9fd))!important;border-color:var(--ui-line,var(--line,#34576d))!important}
body main .catalog-audit h2,body main .catalog-audit h3,body main .card h2,body main .card h3,body main .panel h2{color:var(--ui-text,var(--ink,#f4f9fd))!important}
body main .catalog-audit p,body main .catalog-audit li,body main .card p,body main .panel p,body main .note p,body main .notice p,body main .meta,body main .filter-hint{color:var(--ui-muted,var(--muted,#bdd0dc))!important}
body main .meta b,body main .catalog-audit strong,body main .card strong{color:var(--ui-text,var(--ink,#f4f9fd))!important}
body main input,body main select,body main textarea{background:var(--ui-panel2,var(--surface2,#163447))!important;color:var(--ui-text,var(--ink,#f4f9fd))!important;border-color:var(--ui-line,var(--line,#34576d))!important}
body main input::placeholder,body main textarea::placeholder{color:var(--ui-muted,var(--muted,#bdd0dc))!important;opacity:1}
body main select option{background:var(--ui-panel,var(--surface,#0b2031))!important;color:var(--ui-text,var(--ink,#f4f9fd))!important}
body main a{color:var(--ui-accent,var(--accent,#53e8f3))!important}
</style>"""

def with_odpc_credit(payload: bytes) -> bytes:
    # Preserve approved geometry; append only color overrides and ODPC credit.
    has_credit = b'id="drol-odpc-credit"' in payload
    has_contrast = b'id="drol-global-contrast-v1"' in payload
    lower = payload.lower()
    pos = lower.rfind(b"</body>")
    if pos < 0:
        raise RuntimeError("Cannot append D.R.O.L. enhancements: missing </body>")
    additions = (b"" if has_contrast else DROL_CONTRAST_PATCH.encode("utf-8"))
    additions += (b"" if has_credit else ODPC_CREDIT.encode("utf-8"))
    return payload[:pos] + additions + payload[pos:]

ROOT="/drol"
SOURCE=Path("site-deploy/drol.odpc.de")
def main():
    if not (SOURCE/"index.html").is_file():raise RuntimeError("Missing D.R.O.L. homepage")
    payload=(SOURCE/"index.html").read_bytes()
    if b"Wir machen Ihre Drohnendaten nutzbar." not in payload:raise RuntimeError("Invalid release")
    transport=paramiko.Transport((HOST,22));transport.start_client(timeout=25)
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual,PIN):raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
        sf=paramiko.SFTPClient.from_transport(transport)
        try:
            if not stat.S_ISDIR(sf.lstat(ROOT).st_mode):raise RuntimeError("User-created /drol directory missing")
            # Deploy the Lab demo as one explicitly scoped nested page.
            lab_source=SOURCE/"lab"/"index.html"
            if not lab_source.is_file():raise RuntimeError("Missing D.R.O.L. Lab page")
            lab_dir=ROOT+"/lab"
            try:
                lab_stat=sf.lstat(lab_dir)
            except FileNotFoundError:
                sf.mkdir(lab_dir,0o755)
                lab_stat=sf.lstat(lab_dir)
            if not stat.S_ISDIR(lab_stat.st_mode):raise RuntimeError("D.R.O.L. Lab path is not a directory")
            files=[(source,ROOT+"/"+source.name) for source in sorted(SOURCE.glob("*.html"))]
            files.append((lab_source,lab_dir+"/index.html"))
            # Publish the canonical, non-sensitive editorial registry as a public JSON resource.
            # Do not deploy SQL migrations or private database configuration.
            registry_source=SOURCE/"database"/"model-schema.json"
            if registry_source.is_file():
                import json
                registry=json.loads(registry_source.read_text(encoding="utf-8"))
                if registry.get("schema_version")!="1.0" or not isinstance(registry.get("models"),list):
                    raise RuntimeError("Invalid D.R.O.L. model registry")
                data_dir=ROOT+"/data"
                try:
                    data_stat=sf.lstat(data_dir)
                except FileNotFoundError:
                    sf.mkdir(data_dir,0o755)
                    data_stat=sf.lstat(data_dir)
                if not stat.S_ISDIR(data_stat.st_mode):
                    raise RuntimeError("D.R.O.L. data path is not a directory")
                files.append((registry_source,data_dir+"/models.json"))
            for source,target in files:
                payload=with_odpc_credit(source.read_bytes()) if source.suffix==".html" else source.read_bytes()
                try:
                    with sf.open(target,"rb") as f:old=f.read()
                    if source.suffix==".html" and b"D.R.O.L." not in old:raise RuntimeError("Refusing to overwrite unrelated content: "+target)
                except FileNotFoundError:old=None
                temp=target+".uploading"
                with sf.open(temp,"wb") as f:f.write(payload)
                sf.chmod(temp,0o644)
                sf.posix_rename(temp,target)
                with sf.open(target,"rb") as f:verified=f.read()
                if hashlib.sha256(verified).digest()!=hashlib.sha256(payload).digest():
                    if old is not None:
                        with sf.open(temp,"wb") as f:f.write(old)
                        sf.posix_rename(temp,target)
                    else:sf.remove(target)
                    raise RuntimeError("Remote checksum mismatch: "+target)
                print("D.R.O.L. uploaded and checksum verified:",target)
        finally:sf.close()
    finally:transport.close()
if __name__=="__main__":main()
