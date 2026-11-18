#!/usr/bin/env python3
"""ODPC free, read-only indexability audit. No credentials, writes or submissions."""
import sys
import urllib.request
import urllib.error
import xml.etree.ElementTree as ET
from urllib.parse import urlparse, urljoin
from html.parser import HTMLParser

HOSTS = ["https://odpc.de", "https://ai.odpc.de", "https://drol.odpc.de"]
PATHS = ["/", "/robots.txt", "/sitemap_index.xml", "/wp-sitemap.xml"]

class PageParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.canonical = []
        self.robots = []
        self.titles = []
        self.in_title = False
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == "title": self.in_title = True
        if tag == "link" and "canonical" in a.get("rel", "").lower().split():
            self.canonical.append(a.get("href", ""))
        if tag == "meta" and a.get("name", "").lower() in ("robots", "googlebot", "bingbot"):
            self.robots.append((a.get("name"), a.get("content")))
    def handle_endtag(self, tag):
        if tag == "title": self.in_title = False
    def handle_data(self, data):
        if self.in_title: self.titles.append(data)

def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": "ODPC-IndexAudit/1.0 (+https://odpc.de/)"})
    try:
        with urllib.request.urlopen(req, timeout=12) as r:
            data = r.read(1024 * 1024)
            return r.status, r.geturl(), dict(r.headers), data
    except urllib.error.HTTPError as e:
        return e.code, e.geturl(), dict(e.headers), b""
    except Exception as e:
        return "ERROR", url, {}, str(e).encode()

def main():
    failures = 0
    for host in HOSTS:
        print("\nHOST", host)
        for path in PATHS:
            url = host + path
            status, final, headers, body = fetch(url)
            print(f"{path}: status={status} final={final}")
            if status == "ERROR":
                print("  error:", body.decode(errors="replace")[:160])
                failures += 1
                continue
            if path == "/":
                p = PageParser()
                p.feed(body.decode("utf-8", errors="replace"))
                print("  title:", " ".join(p.titles).strip()[:180])
                print("  canonical:", p.canonical)
                print("  robots meta:", p.robots)
                print("  x-robots-tag:", headers.get("X-Robots-Tag", ""))
            elif path == "/robots.txt" and status == 200:
                lines = body.decode("utf-8", errors="replace").splitlines()
                print("  sitemap directives:", [x for x in lines if x.lower().startswith("sitemap:")])
            elif "sitemap" in path and status == 200:
                try:
                    root = ET.fromstring(body)
                    print("  XML root:", root.tag, "entries:", len(root))
                except ET.ParseError:
                    print("  WARNING: invalid sitemap XML")
    print("\nThis is a read-only accessibility audit, not proof of search-engine indexing.")
    return 1 if failures else 0

if __name__ == "__main__":
    sys.exit(main())
