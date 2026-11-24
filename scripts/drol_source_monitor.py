"""Check civilian editorial source availability without collecting page contents."""
import datetime
import json
import time
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urlsplit
from urllib.request import Request, build_opener

from drol_document_discovery import AGENT, USER_AGENT, NoRedirect, robot_rules


def eligible_urls(models, sources):
    excluded = {s["url"] for s in sources if s.get("status") == "excluded"}
    urls = {u for m in models for u in m.get("sources", []) if isinstance(u, str)}
    urls.update(s["url"] for s in sources if s.get("status") == "monitor_only")
    return sorted(url for url in urls - excluded if url.startswith("https://") and urlsplit(url).hostname)


def main():
    directory = Path("site-deploy/drol.odpc.de/database")
    models = json.loads((directory / "model-schema.json").read_text())["models"]
    sources = json.loads((directory / "source-registry.json").read_text())["sources"]
    urls = eligible_urls(models, sources)
    opener = build_opener(NoRedirect())
    rules_by_host, next_allowed = {}, {}
    results = []
    # Spread the full roster over four days. A failed host cannot consume the entire job budget.
    day = datetime.datetime.now(datetime.timezone.utc).date().toordinal()
    for url in urls[day % 4::4]:
        host = urlsplit(url).netloc
        status, result = None, "network_unavailable"
        try:
            if host not in rules_by_host:
                rules_by_host[host] = None
                rules_by_host[host] = robot_rules(url, opener)
                next_allowed[host] = time.monotonic() + 1
            rules = rules_by_host[host]
            if rules is None or not rules.can_fetch(AGENT, url):
                result = "robots_unavailable_or_disallowed"
            else:
                rate = rules.request_rate(AGENT)
                delay = max(1, rules.crawl_delay(AGENT) or 0, rate.seconds if rate else 0)
                wait = next_allowed[host] - time.monotonic()
                if wait > 30 or delay > 30:
                    result = "deferred_host_limit"
                else:
                    # Include the initial robots request when enforcing host spacing.
                    if next_allowed[host] > time.monotonic():
                        time.sleep(max(wait, delay))
                    req = Request(url, headers={"User-Agent": USER_AGENT}, method="HEAD")
                    try:
                        with opener.open(req, timeout=8) as response:
                            status, result = response.status, "reachable"
                    finally:
                        next_allowed[host] = time.monotonic() + delay
        except HTTPError as exc:
            status = exc.code
            result = "redirect" if 300 <= status < 400 else "restricted_or_missing"
            if status in (401, 403, 429, 503):
                rules_by_host[host] = None
        except (URLError, OSError, ValueError):
            pass
        results.append({"url": url, "status": status, "result": result})
    report = {"checked_utc": datetime.datetime.now(datetime.timezone.utc).isoformat(),
              "eligible_source_count": len(urls), "source_count": len(results), "sources": results,
              "note": "Rotating health check; reachability does not verify a technical claim."}
    Path("drol-source-health.json").write_text(json.dumps(report, indent=2) + "\n")
    print("Source health checked:", len(results), "of", len(urls))


if __name__ == "__main__":
    main()
