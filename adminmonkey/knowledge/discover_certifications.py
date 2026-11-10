"""Audit candidate Microsoft exam codes against official Microsoft Learn search results.

This produces discovery evidence, NOT verified certifications. No credentials or paid APIs.
"""
import json
import os
import time
import urllib.parse
import urllib.request
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
CANDIDATES = ROOT / "academy" / "certification_candidates_2026.json"
OUT = ROOT / "academy" / "certification_discovery_report.json"
API = "https://learn.microsoft.com/api/search"
HEADERS = {"User-Agent": "ODPC-AdminMonkey-Credential-Discovery/1.0", "Accept": "application/json"}


def search_exam(code):
    params = urllib.parse.urlencode({"search": '"' + code + '" certification', "locale": "en-us", "$top": 5})
    request = urllib.request.Request(API + "?" + params, headers=HEADERS)
    with urllib.request.urlopen(request, timeout=18) as response:
        data = json.load(response)
    results = []
    for item in data.get("results", [])[:5]:
        url = item.get("url", "")
        parsed = urllib.parse.urlparse(url)
        if parsed.scheme != "https" or parsed.hostname != "learn.microsoft.com":
            continue
        results.append({"title": str(item.get("title", ""))[:250], "url": url})
    return results


def main():
    candidates = json.loads(CANDIDATES.read_text(encoding="utf-8"))["exam_candidates"]
    assert len({x["exam_code"] for x in candidates}) == len(candidates)
    records = []
    for entry in candidates:
        code = entry["exam_code"]
        try:
            hits = search_exam(code)
            records.append({"exam_code": code, "status": "official_search_results_found" if hits else "no_search_results", "search_results": hits})
        except (OSError, ValueError, TimeoutError) as exc:
            records.append({"exam_code": code, "status": "search_error", "error": type(exc).__name__})
        time.sleep(0.25)
    output = {"generated_at": datetime.now(timezone.utc).isoformat(), "source": API,
              "verification_status": "discovery_only_not_verified",
              "warning": "Search results are leads only; exam code, current status, official title, skill outline and retirement require explicit verification on the official credential page.",
              "candidate_count": len(candidates), "records": records}
    OUT.write_text(json.dumps(output, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(json.dumps({"candidate_count": len(candidates), "search_results_found": sum(bool(x.get("search_results")) for x in records), "errors": sum(x["status"] == "search_error" for x in records)}))


if __name__ == "__main__":
    main()
