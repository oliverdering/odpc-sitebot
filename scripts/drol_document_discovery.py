"""Discover document references on approved civil documentation indexes (metadata only)."""
import argparse
import hashlib
import json
import re
import time
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urljoin, urlsplit
from urllib.request import HTTPRedirectHandler, Request, build_opener
from urllib.robotparser import RobotFileParser

from drol_metadata_staging import review_gate


REGISTRY = Path("site-deploy/drol.odpc.de/database/source-registry.json")
AGENT = "DROL-DocumentationMonitor"
USER_AGENT = AGENT + "/1.0 (+https://drol.odpc.de/)"

class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, *args):
        return None


class IndexLinks(HTMLParser):
    def __init__(self):
        super().__init__()
        self.links = set()

    def handle_starttag(self, tag, attrs):
        if tag == "a":
            href = dict(attrs).get("href")
            if href:
                self.links.add(href)


def index_urls(source, page):
    index = source["url"]
    pattern = re.compile(source["discovery_path_regex"])
    parser = IndexLinks()
    parser.feed(page)
    urls = set()
    for href in parser.links:
        target = urlsplit(urljoin(index, href))
        if (target.scheme == "https" and target.netloc == urlsplit(index).netloc and
                not target.query and pattern.fullmatch(target.path)):
            urls.add(target._replace(fragment="").geturl())
    return sorted(urls)


def read_url(opener, url, limit, content_types):
    with opener.open(Request(url, headers={"User-Agent": USER_AGENT}), timeout=12) as response:
        if response.headers.get_content_type() not in content_types:
            raise ValueError("unexpected content type")
        raw = response.read(limit + 1)
        if len(raw) > limit:
            raise ValueError("response exceeded bound")
        return raw.decode(response.headers.get_content_charset() or "utf-8")


def robot_rules(index, opener):
    robots_url = urljoin(index, "/robots.txt")
    try:
        robots_text = read_url(opener, robots_url, 512000, {"text/plain"})
    except HTTPError as error:
        if error.code != 404:
            raise
        robots_text = ""
    rules = RobotFileParser(robots_url)
    rules.parse(robots_text.splitlines())
    return rules


def fetch_index(source, opener):
    index = source["url"]
    rules = robot_rules(index, opener)
    if not rules.can_fetch(AGENT, index):
        raise ValueError("robots denied index")
    rate = rules.request_rate(AGENT)
    delay = max(1, rules.crawl_delay(AGENT) or 0, rate.seconds if rate else 0)
    if delay > 30:
        raise ValueError("crawl delay exceeds run budget")
    time.sleep(delay)
    page = read_url(opener, index, 2_000_000, {"text/html", "application/xhtml+xml"})
    urls = [url for url in index_urls(source, page) if rules.can_fetch(AGENT, url)]
    if not 1 <= len(urls) <= 150:
        raise ValueError("no eligible links or index exceeded review bound")
    return {"index": index, "name": source["name"], "topic": source["scope"],
            "license_url": source["metadata_review"]["evidence_url"],
            "digest": hashlib.sha256("\n".join(urls).encode()).hexdigest(), "urls": urls}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--apply", action="store_true")
    args = parser.parse_args()
    sources = json.loads(REGISTRY.read_text())["sources"]
    approved = [s for s in sources if s.get("feed_type") == "html_index" and
                s["status"] == "monitor_only" and s["collection_policy"] == "metadata_and_original_analysis_only"
                and not review_gate(s)]
    indexes, errors = [], []
    opener = build_opener(NoRedirect())
    for source in approved:
        try:
            indexes.append(fetch_index(source, opener))
        except (URLError, OSError, ValueError) as error:
            errors.append({"index": source["url"], "error": str(error)[:200]})
    report = {"indexes": indexes, "errors": errors, "mode": "apply" if args.apply else "dry-run"}
    if args.apply and indexes:
        from drol_db_bridge import invoke
        php = Path(__file__).with_name("drol_document_metadata.php").read_text()
        result = invoke(php, {"indexes": indexes})
        if result.get("status") != "ok":
            raise RuntimeError("Metadata transaction rejected")
        report["database"] = result
    Path("drol-document-discovery.json").write_text(json.dumps(report, indent=2) + "\n")
    print("Approved indexes:", len(approved), "read:", len(indexes),
          "distinct references:", len({url for index in indexes for url in index["urls"]}))
    print("Failed indexes:", errors)
    if "database" in report:
        print("Metadata import:", report["database"])
    if errors or not indexes:
        raise SystemExit(1)


if __name__ == "__main__":
    main()
