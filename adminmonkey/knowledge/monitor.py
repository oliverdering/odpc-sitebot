"""AdminMonkey daily Microsoft Learn evidence monitor.
Only Microsoft Learn metadata, no customer data, no autonomous model modification.
"""
import hashlib
import json
from datetime import datetime, timezone
from pathlib import Path
from urllib.request import Request, urlopen
from urllib.error import HTTPError, URLError

ROOT = Path(__file__).resolve().parent
DB = ROOT / "knowledge_index.json"
SOURCES = {
    "m365-admin": "https://learn.microsoft.com/en-us/microsoft-365/admin/",
    "graph": "https://learn.microsoft.com/en-us/graph/overview",
    "entra": "https://learn.microsoft.com/en-us/entra/fundamentals/whatis",
    "intune": "https://learn.microsoft.com/en-us/intune/intune-service/fundamentals/what-is-intune",
    "azure": "https://learn.microsoft.com/en-us/azure/azure-resource-manager/management/overview",
    "credentials": "https://learn.microsoft.com/en-us/credentials/",
}

def update_index(old, fetched, at):
    result = {"schema_version": 1, "sources": dict(old.get("sources", {})), "changes": list(old.get("changes", []))}
    for key, evidence in fetched.items():
        previous = result["sources"].get(key)
        if evidence.get("status") != "ok":
            # Network failures must not erase previously verified evidence.
            continue
        if previous is None or previous.get("sha256") != evidence["sha256"]:
            result["changes"].append({"id": key, "at": at, "sha256": evidence["sha256"],
                                      "review_status": "pending_human_review"})
        result["sources"][key] = evidence
    result["changes"] = result["changes"][-1000:]
    return result

def collect():
    records = {}
    for key, url in SOURCES.items():
        try:
            with urlopen(Request(url, headers={"User-Agent": "ODPC-AdminMonkey-Knowledge-Monitor/1.0"}), timeout=15) as response:
                final_url = response.geturl()
                if not final_url.startswith("https://learn.microsoft.com/"):
                    raise ValueError("untrusted redirect")
                body = response.read(1_000_000)
                records[key] = {"url": url, "final_url": final_url, "sha256": hashlib.sha256(body).hexdigest(),
                                "status": "ok", "bytes_sampled": len(body),
                                "review_status": "pending_human_review"}
        except (HTTPError, URLError, TimeoutError, OSError, ValueError) as exc:
            records[key] = {"url": url, "status": "unavailable", "error_type": type(exc).__name__}
    return records

def main():
    old = json.loads(DB.read_text(encoding="utf-8")) if DB.exists() else {}
    at = datetime.now(timezone.utc).isoformat()
    fetched = collect()
    index = update_index(old, fetched, at)
    DB.write_text(json.dumps(index, indent=2, ensure_ascii=False, sort_keys=True) + "\n", encoding="utf-8")
    print(json.dumps({"sources_checked": len(fetched), "reachable": sum(x["status"] == "ok" for x in fetched.values()),
                      "pending_review_events": len(index["changes"]), "persisted": str(DB)}))
if __name__ == "__main__":
    main()
