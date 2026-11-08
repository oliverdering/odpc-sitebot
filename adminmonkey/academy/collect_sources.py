"""Build a reviewed, provenance-rich Microsoft knowledge snapshot.

No model self-training, no arbitrary code execution, no credentials.
External sources are allowlisted and only metadata is stored.
"""
import hashlib
import json
import os
from datetime import datetime, timezone
from pathlib import Path
from urllib.request import Request, urlopen
from urllib.error import URLError, HTTPError

ROOT = Path(__file__).resolve().parent
SOURCES = [
    {"id": "m365-admin", "url": "https://learn.microsoft.com/en-us/microsoft-365/admin/", "topic": "Microsoft 365"},
    {"id": "graph-overview", "url": "https://learn.microsoft.com/en-us/graph/overview", "topic": "Microsoft Graph"},
    {"id": "entra", "url": "https://learn.microsoft.com/en-us/entra/fundamentals/whatis", "topic": "Entra ID"},
    {"id": "intune", "url": "https://learn.microsoft.com/en-us/intune/intune-service/fundamentals/what-is-intune", "topic": "Intune"},
    {"id": "azure", "url": "https://learn.microsoft.com/en-us/azure/azure-resource-manager/management/overview", "topic": "Azure"},
    {"id": "credentials", "url": "https://learn.microsoft.com/en-us/credentials/", "topic": "Certifications"},
]
def collect():
    result = []
    for item in SOURCES:
        record = {**item, "review_status": "pending_human_review"}
        try:
            req = Request(item["url"], headers={"User-Agent": "ODPC-AdminMonkey-Academy/0.1 (metadata audit)"})
            with urlopen(req, timeout=12) as response:
                final_url = response.geturl()
                if not final_url.startswith("https://learn.microsoft.com/"):
                    raise ValueError("redirect outside Microsoft Learn")
                body = response.read(1_000_000)
                record.update({"reachable": response.status == 200, "final_url": final_url,
                               "sha256_first_1mb": hashlib.sha256(body).hexdigest(),
                               "bytes_sampled": len(body)})
        except (URLError, HTTPError, TimeoutError, ValueError, OSError) as exc:
            record.update({"reachable": False, "error": type(exc).__name__})
        result.append(record)
    return {"schema_version": 1, "collected_at": datetime.now(timezone.utc).isoformat(),
            "purpose": "source monitoring; not approved training data", "sources": result}
if __name__ == "__main__":
    output = ROOT / "knowledge_snapshot.json"
    output.write_text(json.dumps(collect(), ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Snapshot written to {output}")
