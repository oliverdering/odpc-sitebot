"""Bounded Microsoft Learn crawler: extracts text, links and provenance.

No customer data, no login, no external domains, no arbitrary execution.
One batch per invocation; persists crawl queue and text documents as JSON.
"""
import hashlib
import html
import json
import re
from collections import deque
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import urljoin, urldefrag, urlparse
from urllib.request import Request, urlopen
from urllib.error import HTTPError, URLError

ROOT = Path(__file__).resolve().parent
STATE = ROOT / "crawl_state.json"
MANIFEST = ROOT / "document_manifest.json"
SEEDS = [
    "https://learn.microsoft.com/en-us/microsoft-365/admin/",
    "https://learn.microsoft.com/en-us/graph/overview",
    "https://learn.microsoft.com/en-us/entra/fundamentals/whatis",
    "https://learn.microsoft.com/en-us/intune/intune-service/fundamentals/what-is-intune",
    "https://learn.microsoft.com/en-us/azure/azure-resource-manager/management/overview",
    "https://learn.microsoft.com/en-us/copilot/microsoft-365/",
    "https://learn.microsoft.com/en-us/microsoft-copilot-studio/",
    "https://learn.microsoft.com/en-us/powershell/microsoftgraph/overview",
    "https://learn.microsoft.com/en-us/exchange/exchange-online",
    "https://learn.microsoft.com/en-us/microsoftteams/teams-overview",
    "https://learn.microsoft.com/en-us/defender-xdr/microsoft-365-defender",
    "https://learn.microsoft.com/en-us/purview/",
]
MAX_PAGES = 12
MAX_DOCS = 1500
MAX_DEPTH = 3

class Extractor(HTMLParser):
    def __init__(self):
        super().__init__()
        self.parts, self.links = [], []
        self.skip = 0
        self.in_main = 0
    def handle_starttag(self, tag, attrs):
        props = dict(attrs)
        if tag in ("script", "style", "nav", "footer"):
            self.skip += 1
        if tag == "main":
            self.in_main += 1
        if tag == "a" and props.get("href"):
            self.links.append(props["href"])
    def handle_endtag(self, tag):
        if tag in ("script", "style", "nav", "footer"):
            self.skip = max(0, self.skip - 1)
        if tag == "main":
            self.in_main = max(0, self.in_main - 1)
    def handle_data(self, data):
        if not self.skip and self.in_main and data.strip():
            self.parts.append(data.strip())

def allowed(url):
    p = urlparse(url)
    return (p.scheme == "https" and p.netloc == "learn.microsoft.com"
            and p.path.startswith("/en-us/") and not re.search(r"\.(pdf|zip|png|jpg|svg)$", p.path, re.I))

def crawl(state, fetch):
    docs = dict(state.get("documents", {}))
    queue = deque(state.get("queue") or [{"url": url, "depth": 0} for url in SEEDS])
    seen = set(state.get("seen", []))
    queued = {item["url"] for item in queue}
    for seed in reversed(SEEDS):
        if seed not in seen and seed not in queued:
            queue.appendleft({"url": seed, "depth": 0})
    attempts = 0
    failures = []
    attempted_this_batch = set()
    retry_next_batch = []
    while queue and attempts < MAX_PAGES and len(docs) < MAX_DOCS:
        item = queue.popleft()
        url, depth = item["url"], item["depth"]
        if url in seen or not allowed(url) or url in attempted_this_batch:
            continue
        attempts += 1
        attempted_this_batch.add(url)
        try:
            final_url, raw = fetch(url)
            if not allowed(final_url):
                raise ValueError("untrusted redirect")
            parser = Extractor()
            parser.feed(raw.decode("utf-8", errors="replace"))
            content = re.sub(r"\s+", " ", html.unescape(" ".join(parser.parts))).strip()[:35000]
            if len(content) < 100:
                raise ValueError("insufficient article text")
            digest = hashlib.sha256(content.encode("utf-8")).hexdigest()
            seen.add(url)
            docs[url] = {"url": url, "sha256": digest, "text": content,
                         "depth": depth, "captured_at": datetime.now(timezone.utc).isoformat(),
                         "review_status": "unreviewed"}
            if depth < MAX_DEPTH:
                for href in parser.links:
                    link = urldefrag(urljoin(final_url, href))[0].split("?")[0]
                    if allowed(link) and link not in seen and len(queue) < 2500:
                        queue.append({"url": link, "depth": depth + 1})
        except (HTTPError, URLError, TimeoutError, OSError, ValueError) as exc:
            failures.append({"url": url, "error": type(exc).__name__})
            retry_next_batch.append({"url": url, "depth": depth})
    queue.extend(retry_next_batch)
    return {"schema_version": 1, "documents": docs, "queue": list(queue),
            "seen": sorted(seen), "last_batch": {"attempted": attempts,
            "documents_total": len(docs), "queued": len(queue), "failures": failures}}

def fetch(url):
    with urlopen(Request(url, headers={"User-Agent": "ODPC-AdminMonkey-Academy/1.0"}), timeout=12) as r:
        return r.geturl(), r.read(750_000)

if __name__ == "__main__":
    old = json.loads(STATE.read_text(encoding="utf-8")) if STATE.exists() else {}
    if MANIFEST.exists():
        persisted = json.loads(MANIFEST.read_text(encoding="utf-8"))
        prior_docs = {d["url"]: d for d in persisted.get("documents", []) if d.get("url") and d.get("sha256")}
        old.setdefault("documents", {}).update({url: doc for url, doc in prior_docs.items() if url not in old.get("documents", {})})
        old["seen"] = sorted(set(old.get("seen", [])) | set(persisted.get("seen", [])))
        if not old.get("queue") and persisted.get("queue"):
            old["queue"] = persisted["queue"]
    state = crawl(old, fetch)
    STATE.write_text(json.dumps(state, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(state["last_batch"]))
