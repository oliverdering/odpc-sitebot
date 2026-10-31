"""D.R.O.L. local-only simulator dashboard (Python standard library).

Run: python research/drol/local_dashboard.py
Opens on 127.0.0.1:8765; no drone sockets, no external dependencies.
"""
import json
import os
import sys
import threading
import time
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import urlsplit

sys.path.insert(0, str(Path(__file__).resolve().parent))
from bridge_core import OfflineBridge

HOST = "127.0.0.1"
PORT = int(os.environ.get("DROL_DASHBOARD_PORT", "8765"))
LOCK = threading.RLock()

class FakeAdapter:
    def capabilities(self):
        return {"model": "D.R.O.L. Simulator", "transport": "offline"}

bridge = OfflineBridge(FakeAdapter(), time.monotonic, timeout_seconds=30.0)
PROFILES = [
    {"id": "simulator", "name": "D.R.O.L. Simulator", "status": "offline simulation", "control": True},
    {"id": "flow-ufo", "name": "FLOW-UFO / KY", "status": "protocol researched; hardware unverified", "control": False},
    {"id": "lsrc-s1s", "name": "LSRC-S1S", "status": "protocol researched; hardware unverified", "control": False},
    {"id": "potensic-atom", "name": "Potensic Atom", "status": "flight log parsing only", "control": False},
]

HTML = """<!doctype html><html lang="de"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>D.R.O.L. · Local Control Lab</title>
<style>
:root{color-scheme:dark;font-family:system-ui,Arial,sans-serif;background:#09131d;color:#e8f1f8}
body{max-width:1050px;margin:auto;padding:22px}h1{font-size:2rem;margin-bottom:0}
small,.muted{color:#a9becf}.tag{color:#69e4bf}main{display:grid;grid-template-columns:1fr 1fr;gap:16px}
section{background:#142535;border:1px solid #30495b;border-radius:16px;padding:20px}
button{border:1px solid #5082a2;border-radius:9px;background:#204661;color:#fff;padding:11px;margin:4px;cursor:pointer}
button:hover{background:#2b5b7c}button:disabled{opacity:.4;cursor:not-allowed}
pre{white-space:pre-wrap;overflow-wrap:anywhere;max-height:270px;overflow:auto;background:#09131d;padding:12px;border-radius:9px}
li{margin:10px 0}.warning{color:#ffcb88}
@media(max-width:680px){main{grid-template-columns:1fr}}
</style></head><body>
<header><div class="tag">ODPC · D.R.O.L. LAB</div><h1>Drone Control Center</h1>
<p class="warning">OFFLINE-SIMULATION · Keine Drohne verbunden · Keine echten Flugbefehle</p></header>
<main><section><h2>Simulationsstatus</h2><p>Aktueller Zustand: <strong id="state">…</strong></p>
<p class="muted">Alle Aktionen verändern ausschließlich den lokalen Simulator.</p>
<div id="controls">
<button data-action="connect">Verbinden</button>
<button data-action="arm">Simuliert scharf schalten</button>
<button data-action="takeoff">Start simulieren</button>
<button data-action="land">Landung simulieren</button>
<button data-action="heartbeat">Heartbeat</button>
<button data-action="disconnect">Trennen</button>
</div><p id="message" aria-live="polite"></p></section>
<section><h2>Geräteprofile</h2><ul id="profiles"></ul>
<p class="muted">Nur das Simulatorprofil ist aktiv. Hardwaresteuerung bleibt gesperrt.</p></section>
<section style="grid-column:1/-1"><h2>Ereignisprotokoll</h2><pre id="events">Lade…</pre>
<small>Nur im Arbeitsspeicher; beim Neustart zurückgesetzt.</small></section></main>
<script>
const stateEl=document.querySelector('#state'), msg=document.querySelector('#message');
async function refresh(){
 try{
 const r=await fetch('/api/status',{cache:'no-store'}),data=await r.json();
 stateEl.textContent=data.state;
 document.querySelector('#events').textContent=JSON.stringify(data.events.slice(-25),null,2);
 document.querySelector('#profiles').replaceChildren(...data.profiles.map(p=>{
 const li=document.createElement('li');
 const name=document.createElement('strong');name.textContent=p.name;
 const detail=document.createElement('small');detail.textContent=' — '+p.status;
 li.append(name,detail);return li;
 }));
 const allowed={disconnected:['connect'],ready:['arm','heartbeat','disconnect'],
 armed:['takeoff','heartbeat','disconnect'],simulated_flight:['land','heartbeat','disconnect'],
 link_lost:['disconnect']};
 document.querySelectorAll('[data-action]').forEach(b=>b.disabled=!(allowed[data.state]||[]).includes(b.dataset.action));
 }catch(e){msg.textContent='Dashboard-Verbindung unterbrochen: '+e.message}
}
document.querySelector('#controls').addEventListener('click',async e=>{
 const b=e.target.closest('[data-action]');if(!b)return;
 b.disabled=true;
 try{const r=await fetch('/api/action',{method:'POST',headers:{'Content-Type':'application/json'},
 body:JSON.stringify({action:b.dataset.action})});
 const data=await r.json();msg.textContent=data.error||('Simulation: '+data.event.event);
 }catch(err){msg.textContent=err.message}
 await refresh();
});
refresh();setInterval(refresh,2000);
</script></body></html>"""

class Handler(BaseHTTPRequestHandler):
    def _reply(self, code, obj, content_type="application/json; charset=utf-8"):
        body = (obj if isinstance(obj, bytes) else json.dumps(obj).encode("utf-8"))
        self.send_response(code)
        self.send_header("Content-Type", content_type)
        self.send_header("Content-Length", str(len(body)))
        self.send_header("Cache-Control", "no-store")
        self.send_header("X-Content-Type-Options", "nosniff")
        self.send_header("Content-Security-Policy", "default-src 'self' 'unsafe-inline'")
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        path = urlsplit(self.path).path
        if path == "/":
            self._reply(200, HTML.encode("utf-8"), "text/html; charset=utf-8")
        elif path == "/api/status":
            with LOCK:
                bridge.tick()
                self._reply(200, {"state": bridge.state.value, "events": list(bridge.events),
                                  "profiles": PROFILES, "simulation_only": True})
        else:
            self._reply(404, {"error": "not found"})

    def do_POST(self):
        host = self.headers.get("Host", "")
        origin = self.headers.get("Origin")
        allowed_hosts = {f"127.0.0.1:{self.server.server_address[1]}", f"localhost:{self.server.server_address[1]}"}
        if host not in allowed_hosts or (origin is not None and origin not in {f"http://{h}" for h in allowed_hosts}):
            return self._reply(403, {"error": "non-local host or cross-origin request blocked"})
        if urlsplit(self.path).path != "/api/action":
            return self._reply(404, {"error": "not found"})
        try:
            length = int(self.headers.get("Content-Length", "0"))
            if length < 1 or length > 1024:
                return self._reply(400, {"error": "invalid payload length"})
            if self.headers.get("Content-Type", "").split(";")[0].strip() != "application/json":
                return self._reply(415, {"error": "JSON required"})
            payload = json.loads(self.rfile.read(length))
            action = payload.get("action")
            actions = {"connect": bridge.connect, "arm": bridge.arm, "takeoff": bridge.takeoff,
                       "land": bridge.land, "heartbeat": bridge.heartbeat,
                       "disconnect": bridge.disconnect}
            if action not in actions:
                return self._reply(400, {"error": "unknown action"})
            with LOCK:
                result = actions[action]()
            self._reply(200, {"event": result, "simulation_only": True})
        except (ValueError, RuntimeError, TypeError, AttributeError) as exc:
            self._reply(400, {"error": str(exc)})

    def log_message(self, fmt, *args):
        return

def serve(port=PORT):
    server = ThreadingHTTPServer((HOST, port), Handler)
    print(f"D.R.O.L. offline dashboard: http://{HOST}:{port}")
    try:
        server.serve_forever()
    finally:
        server.server_close()

if __name__ == "__main__":
    serve()
