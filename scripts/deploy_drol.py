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

def with_drol_seo(payload: bytes, filename: str) -> bytes:
    """Inject canonical and social metadata without changing approved design."""
    import html
    import re
    if filename == "index.html":
        canonical = "https://drol.odpc.de/"
    else:
        canonical = "https://drol.odpc.de/" + filename
    head_end = payload.lower().find(b"</head>")
    if head_end < 0:
        raise RuntimeError("D.R.O.L. SEO: missing head: " + filename)
    source = payload.decode("utf-8")
    head = source[:source.lower().find("</head>")]
    title_match = re.search(r"<title[^>]*>(.*?)</title>", head, re.I | re.S)
    title = html.unescape(re.sub(r"<[^>]+>", "", title_match.group(1))).strip() if title_match else "D.R.O.L."
    description_match = re.search(r'<meta\s+name=[\x22\x27]description[\x22\x27]\s+content=[\x22\x27]([^"\']+)', head, re.I)
    description = html.unescape(description_match.group(1)) if description_match else "D.R.O.L. – technische Wissensplattform für Drohnen, Sensorik, Firmware, KI und Datenverarbeitung."
    additions = []
    if not re.search(r'<link[^>]+rel=[\x22\x27]canonical[\x22\x27]', head, re.I):
        additions.append('<link rel="canonical" href="' + html.escape(canonical, quote=True) + '">')
    if not re.search(r'<meta[^>]+property=[\x22\x27]og:title[\x22\x27]', head, re.I):
        additions.append('<meta property="og:type" content="website"><meta property="og:title" content="' + html.escape(title, quote=True) + '"><meta property="og:description" content="' + html.escape(description, quote=True) + '"><meta property="og:url" content="' + html.escape(canonical, quote=True) + '">')
    if not re.search(r'<meta[^>]+name=[\x22\x27]twitter:card[\x22\x27]', head, re.I):
        additions.append('<meta name="twitter:card" content="summary">')
    if not additions:
        return payload
    return source.replace("</head>", "\n".join(additions) + "</head>", 1).encode("utf-8")

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

DROL_SHARED_APPEARANCE = """<style id="drol-shared-appearance-css">
/* Preserve the homepage's original two controls; never add a second switch. */
.drol-design-controls{display:flex;gap:6px;align-items:center}
.drol-design-controls button{font:700 13px system-ui;min-height:44px;border:1px solid var(--line);border-radius:9px;padding:8px 11px;background:var(--surface2);color:var(--ink);cursor:pointer}
html[data-drol-style="army"]{--bg:#1b2518;--surface:#24311f;--surface2:#34432a;--ink:#f4f4e9;--muted:#d2dbc7;--line:#788869;--accent:#d7df95;--accent-ink:#17220f;--section:#202d1b;--ui-panel:#24311f;--ui-panel2:#34432a;--ui-text:#f4f4e9;--ui-muted:#d2dbc7;--ui-line:#788869;--ui-accent:#d7df95}
html[data-drol-style="army"][data-drol-tone="light"]{color-scheme:light;--bg:#e2d4aa;--surface:#f5edda;--surface2:#dfd1ad;--ink:#29281d;--muted:#504c38;--line:#9c906c;--accent:#455c2a;--accent-ink:#fff;--section:#e7dbbc;--ui-panel:#f5edda;--ui-panel2:#dfd1ad;--ui-text:#29281d;--ui-muted:#504c38;--ui-line:#9c906c;--ui-accent:#455c2a}
@media(max-width:700px){.drol-design-controls{gap:3px}.drol-design-controls button{font-size:11px;padding:7px 6px}}
</style><script id="drol-shared-appearance-js">(function(){
var root=document.documentElement;
function read(k,def){try{return localStorage.getItem(k)||def}catch(e){return def}}
if(!root.dataset.drolStyle)root.dataset.drolStyle=read('drol-style','army');
if(!root.dataset.drolTone)root.dataset.drolTone=read('drol-tone','dark');
function mount(){
/* Homepage already owns both controls and their event handlers. */
if(document.querySelector('.drol-design-controls'))return;
var h=document.querySelector('.header .actions,.drol-unified-header .actions,.header-row .actions,.drol-unified-header,.header-row,.header');if(!h)return;
var wrap=document.createElement('div');wrap.className='drol-design-controls';wrap.setAttribute('role','group');wrap.setAttribute('aria-label','Darstellung wählen');
var a=document.createElement('button');a.type='button';a.id='drol-style-choice';
var b=document.createElement('button');b.type='button';b.id='drol-tone-choice';
function sync(){var army=root.dataset.drolStyle==='army',light=root.dataset.drolTone==='light';root.dataset.theme=light?'light':'dark';a.textContent=army?'Army':'Technisch';b.textContent=light?'Hell':'Dunkel';a.setAttribute('aria-label','Design wechseln');b.setAttribute('aria-label','Helligkeit wechseln')}
a.addEventListener('click',function(){root.dataset.drolStyle=root.dataset.drolStyle==='army'?'tech':'army';try{localStorage.setItem('drol-style',root.dataset.drolStyle)}catch(e){}sync()});
b.addEventListener('click',function(){root.dataset.drolTone=root.dataset.drolTone==='light'?'dark':'light';try{localStorage.setItem('drol-tone',root.dataset.drolTone)}catch(e){}sync()});
wrap.appendChild(a);wrap.appendChild(b);h.appendChild(wrap);sync()
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',mount);else mount();
})();</script>"""


DROL_SITE_SHELL = """<style id="drol-site-shell-v1">
html[data-drol-style="army"]{--ui-bg:var(--bg);--ui-panel:var(--surface);--ui-panel2:var(--surface2);--ui-text:var(--ink);--ui-muted:var(--muted);--ui-line:var(--line);--ui-accent:var(--accent);--ui-btn:var(--surface2);--ui-btn-text:var(--ink)}
html[data-drol-style="tech"]{--bg:#031322;--surface:#0b2031;--surface2:#163447;--ink:#f4f9fd;--muted:#bdd0dc;--line:#34576d;--accent:#53e8f3;--ui-bg:var(--bg);--ui-panel:var(--surface);--ui-panel2:var(--surface2);--ui-text:var(--ink);--ui-muted:var(--muted);--ui-line:var(--line);--ui-accent:var(--accent);--ui-btn:var(--surface2);--ui-btn-text:var(--ink)}
html[data-drol-style="tech"][data-drol-tone="light"]{--bg:#f4f8fb;--surface:#fff;--surface2:#e7f1f7;--ink:#09283b;--muted:#405d6c;--line:#b8d3de;--accent:#006e86;--ui-bg:var(--bg);--ui-panel:var(--surface);--ui-panel2:var(--surface2);--ui-text:var(--ink);--ui-muted:var(--muted);--ui-line:var(--line);--ui-accent:var(--accent);--ui-btn:var(--surface2);--ui-btn-text:var(--ink)}
html[data-drol-style="army"] body{background-color:var(--bg)!important;color:var(--ink)!important;background-size:420px 320px!important;background-repeat:repeat!important;background-attachment:fixed}
body>header,body>nav.drol-core-nav,.drol-unified-header,.drol-floating,.quick,.floating-bar,#drol-global-quickbar{display:none!important}
#drol-global-header{display:flex!important;position:relative;z-index:2000;align-items:center;flex-wrap:wrap;gap:14px;padding:14px max(18px,calc((100% - 1320px)/2));background:var(--bg);color:var(--ink);border-bottom:1px solid var(--line);font:600 14px system-ui,sans-serif}
#drol-global-header .drol-logo{display:flex;align-items:center;gap:12px;margin-right:auto;text-decoration:none;color:var(--ink);font-weight:850;letter-spacing:.08em}
#drol-global-header .drol-logo img{width:54px;height:54px;object-fit:cover;border-radius:50%}
#drol-global-header nav{display:flex;flex-wrap:wrap;gap:7px}
#drol-global-header nav a,#drol-global-header button{border:1px solid var(--line)!important;border-radius:9px;padding:10px 12px;min-height:44px;background:var(--surface2)!important;color:var(--ink)!important;text-decoration:none;box-shadow:none!important;font:700 13px system-ui;cursor:pointer}
#drol-global-header .drol-controls{display:flex;gap:6px}#drol-global-header details{display:none}#drol-global-header summary{cursor:pointer;min-height:44px;padding:12px;border:1px solid var(--line);border-radius:9px;list-style:none;background:var(--surface2);color:var(--ink)}
#drol-global-dock{position:fixed;z-index:99990;bottom:max(10px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);width:calc(100% - 16px);height:58px;border-radius:999px;border:1px solid var(--line);background:color-mix(in srgb,var(--surface) 85%,transparent);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:space-around;gap:4px;padding:5px 8px;box-sizing:border-box}
#drol-global-dock button,#drol-global-dock a,#drol-global-dock select{background:var(--surface2);color:var(--ink);border:1px solid var(--line);border-radius:50px;min-height:42px;min-width:42px;padding:5px 8px;display:grid;place-items:center;text-decoration:none;font:700 14px system-ui;cursor:pointer}
#drol-global-dock select{max-width:105px;min-width:78px}
body{padding-bottom:85px!important}
@media(max-width:760px){#drol-global-header{padding:12px 14px;gap:8px}#drol-global-header .drol-logo{flex:1 1 auto}#drol-global-header .drol-logo small{display:none!important}#drol-global-header>nav{display:none}#drol-global-header details{display:block;position:relative}#drol-global-header details nav{display:flex;position:absolute;right:0;top:100%;min-width:200px;padding:8px;flex-direction:column;background:var(--surface);border:1px solid var(--line);border-radius:9px}#drol-global-header details nav a{white-space:nowrap}#drol-global-header .drol-controls{margin-left:auto}}

</style><script id="drol-site-shell-js">(function(){
var d=document.documentElement;
function get(k,v){try{return localStorage.getItem(k)||v}catch(e){return v}}
d.dataset.drolStyle=get('drol-style','army');d.dataset.drolTone=get('drol-tone','dark');
function start(){
if(document.getElementById('drol-global-header'))return;
var h=document.createElement('header');h.id='drol-global-header';
h.innerHTML='<a class="drol-logo" href="/"><img src="https://odpc.de/wp-content/uploads/2026/10/file_00000000c37c81f49cf793debb38a1c5.png" alt="D.R.O.L."><span>D.R.O.L.<small style="display:block;font-size:10px">DRONE RECON OPERATION LAB</small></span></a><nav aria-label="Hauptnavigation"><a href="/">Start</a><a href="/katalog.html">Drohnenkatalog</a><a href="/vorhandene-drohne.html">Meine Drohne</a><a href="/mission-control.html">Mission Control</a><a href="/wissen.html">Wissen</a><a href="/faq.html">FAQ</a></nav><div class="drol-controls"><button id="drol-global-style" type="button"></button><button id="drol-global-tone" type="button"></button></div>';
var menu=document.createElement('details');menu.innerHTML='<summary aria-label="Navigation öffnen">Menü ☰</summary>';menu.appendChild(h.querySelector('nav').cloneNode(true));h.appendChild(menu);document.body.insertBefore(h,document.body.firstChild);
var a=document.getElementById('drol-global-style'),b=document.getElementById('drol-global-tone');
function sync(){d.dataset.theme=d.dataset.drolTone==='light'?'light':'dark';a.textContent=d.dataset.drolStyle==='army'?'Army':'Technisch';b.textContent=d.dataset.drolTone==='light'?'Hell':'Dunkel';h.querySelector('.drol-logo img').src=d.dataset.drolStyle==='army'?'/drol-logo-army.png':'https://odpc.de/wp-content/uploads/2026/10/file_00000000c37c81f49cf793debb38a1c5.png'}
a.onclick=function(){d.dataset.drolStyle=d.dataset.drolStyle==='army'?'tech':'army';try{localStorage.setItem('drol-style',d.dataset.drolStyle)}catch(e){}sync()};
b.onclick=function(){d.dataset.drolTone=d.dataset.drolTone==='light'?'dark':'light';try{localStorage.setItem('drol-tone',d.dataset.drolTone)}catch(e){}sync()};sync();

}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
})();</script>"""

def with_shared_appearance(payload: bytes, filename: str = "") -> bytes:
    """Keep the approved homepage untouched; use one shell on every subpage."""
    if filename == "index.html":
        return payload
    lower = payload.lower()
    pos = lower.rfind(b"</body>")
    if pos < 0:
        raise RuntimeError("Cannot add shared appearance: missing </body>")
    # Remove previously injected controls/shell to make redeploy idempotent.
    import re
    source = payload.decode("utf-8")
    for marker in ("drol-shared-appearance-css", "drol-shared-appearance-js",
                   "drol-site-shell-v1", "drol-site-shell-js"):
        source = re.sub(r'<(?:style|script)\\b[^>]*\\bid=.' + re.escape(marker) +
                        r'.[^>]*>.*?</(?:style|script)>', '', source,
                        flags=re.I | re.S)
    pos = source.lower().rfind("</body>")
    # Use the actual homepage component, including its speech and language logic.
    master = (SOURCE / "index.html").read_text(encoding="utf-8")
    def extract(pattern):
        found = re.search(pattern, master, re.I | re.S)
        if not found:
            raise RuntimeError("Homepage component missing: " + pattern)
        return found.group(0)
    dock_style = extract(r'<style id="drol-odpc-reference-dock-style">.*?</style>')
    dock_style = dock_style.replace('</style>', '#odpc-utility-dock #drol-dock-language{min-width:0}html[data-drol-style="army"] #odpc-utility-dock{background:color-mix(in srgb,var(--surface) 85%,transparent);border-color:var(--line)}html[data-drol-style="army"] #odpc-utility-dock button,html[data-drol-style="army"] #odpc-utility-dock select{background:var(--surface2);color:var(--ink);border-color:var(--line)}</style>')
    dock_nav = extract(r'<nav id="odpc-utility-dock".*?</nav>')
    dock_script = extract(r'<script id="drol-odpc-reference-dock-script">.*?</script>')
    camo = extract(r'<style>html\[data-drol-style="army"\]\[data-drol-tone="dark"\] body\{background-image:url\(.*?</style>')
    # Strip old copies before appending a single authoritative component.
    for pattern in (r'<style id="drol-odpc-reference-dock-style">.*?</style>',
                    r'<nav id="odpc-utility-dock".*?</nav>',
                    r'<script id="drol-odpc-reference-dock-script">.*?</script>'):
        source = re.sub(pattern, '', source, flags=re.I | re.S)
    if 'id="drol-i18n"' not in source:
        i18n = extract(r'<script id="drol-i18n">.*?</script>')
        source = source.replace('</body>', '<div id="drol-google-element" aria-hidden="true"></div>' + i18n + '</body>', 1)
    # The master language handler uses these buttons; add them only if absent.
    if 'data-drol-language=' not in source:
        languages = ('de','en','nl','fr','it','es','pt','tr','ru','zh-CN','ar','ja','ko','hi','pl','uk')
        buttons = ''.join('<button data-drol-language="' + lang + '"></button>' for lang in languages)
        source = source.replace('</body>', '<div hidden>' + buttons + '</div></body>', 1)
    pos = source.lower().rfind('</body>')
    return (source[:pos] + DROL_SHARED_APPEARANCE + camo + DROL_SITE_SHELL + dock_style + dock_nav + dock_script + source[pos:]).encode('utf-8')

def with_translation_ui(payload: bytes) -> bytes:
    """Keep the site's language selector while suppressing translation banners."""
    style = b"""<style id="drol-translation-ui">
body > .skiptranslate,body > .VIpgJd-ZVi9od-ORHb-OEVmcd,iframe.goog-te-banner-frame,iframe.VIpgJd-ZVi9od-ORHb-OEVmcd{display:none!important}
body{top:0!important}
</style>"""
    pos = payload.lower().find(b"</head>")
    if pos < 0:
        raise RuntimeError("Cannot add translation controls: missing </head>")
    # Keep meaningful errors, but do not show the permanent success notice.
    payload = payload.replace("status('Automatic translation is loading…')".encode("utf-8"), b"status('')")
    payload = payload.replace(b"status('Automatic translation by Google; technical content is not professionally verified.')", b"status('')")
    pos = payload.lower().find(b"</head>")
    return payload[:pos] + style + payload[pos:]

def with_asset_version(payload: bytes) -> bytes:
    """Cache-bust the shared header stylesheet using its deployed content hash."""
    import re
    stylesheet = Path("site-deploy/drol.odpc.de/brand-header.css")
    if not stylesheet.is_file():
        return payload
    version = hashlib.sha256(stylesheet.read_bytes()).hexdigest()[:12].encode("ascii")
    return re.sub(rb'(brand-header\.css)(?:\?v=[A-Za-z0-9_-]+)?', lambda m: m.group(1) + b'?v=' + version, payload)

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
            # Publish shared stylesheets referenced by the HTML pages.
            files.extend((source,ROOT+"/"+source.name) for source in sorted(SOURCE.glob("*.css")))
            files.insert(0, (SOURCE / "drol-logo-army.png", ROOT + "/drol-logo-army.png"))
            files.append((lab_source,lab_dir+"/index.html"))
            for seo_name in ("robots.txt","sitemap.xml"):
                seo_file=SOURCE/seo_name
                if seo_file.is_file():files.append((seo_file,ROOT+"/"+seo_name))
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
            # Publish only the explicitly approved public event feed. Never touch
            # models-db.json: it is owned by the independent MariaDB exporter.
            events_source=SOURCE/"data"/"events.json"
            if events_source.is_file():
                import json
                events=json.loads(events_source.read_text(encoding="utf-8"))
                if events.get("schema_version")!=1 or not isinstance(events.get("events"),list):
                    raise RuntimeError("Invalid public D.R.O.L. event feed")
                for event in events["events"]:
                    if not isinstance(event,dict) or not all(isinstance(event.get(k),str) and event[k].strip() for k in ("title","start","url")) or not event["url"].startswith("https://"):
                        raise RuntimeError("Unverified event entry; refusing publication")
                data_dir=ROOT+"/data"
                try:
                    data_stat=sf.lstat(data_dir)
                except FileNotFoundError:
                    sf.mkdir(data_dir,0o755)
                    data_stat=sf.lstat(data_dir)
                if not stat.S_ISDIR(data_stat.st_mode):
                    raise RuntimeError("D.R.O.L. data path is not a directory")
                files.append((events_source,data_dir+"/events.json"))
            for source,target in files:
                payload=with_translation_ui(with_shared_appearance(with_odpc_credit(with_drol_seo(with_asset_version(source.read_bytes()),source.relative_to(SOURCE).as_posix())),source.relative_to(SOURCE).as_posix())) if source.suffix==".html" else source.read_bytes()
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
