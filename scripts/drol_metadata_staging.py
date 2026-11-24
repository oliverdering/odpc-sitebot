"""Offline D.R.O.L. metadata staging. No network, database or publication."""
import argparse
import hashlib
import json
from pathlib import Path

def stage(registry, records):
    sources = {s["url"]: s for s in registry["sources"]}
    accepted, rejected, seen = [], [], set()
    for record in records:
        url = record.get("source_url")
        source = sources.get(url)
        if not source or source.get("status") != "monitor_only":
            rejected.append({"source_url": url, "reason": "source_not_eligible"})
            continue
        if source.get("collection_policy") != "metadata_and_original_analysis_only":
            rejected.append({"source_url": url, "reason": "policy_missing"})
            continue
        title = record.get("title")
        if not isinstance(title, str) or not title.strip() or len(title) > 250:
            rejected.append({"source_url": url, "reason": "invalid_title"})
            continue
        key = hashlib.sha256(json.dumps([url, title.strip()], ensure_ascii=False).encode()).hexdigest()
        if key in seen:
            continue
        seen.add(key)
        accepted.append({"source_url": url, "title": title.strip(), "key": key,
                         "evidence": "E0", "status": "pending",
                         "license_type": source.get("license_type", "unverified"),
                         "production_import_allowed": False})
    return {"mode": "offline_dry_run", "accepted": accepted, "rejected": rejected}

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--registry", required=True)
    parser.add_argument("--input", required=True)
    parser.add_argument("--output", required=True)
    args = parser.parse_args()
    load = lambda p: json.loads(Path(p).read_text(encoding="utf-8"))
    result = stage(load(args.registry), load(args.input))
    Path(args.output).write_text(json.dumps(result, indent=2, ensure_ascii=False)+"\n", encoding="utf-8")

if __name__ == "__main__":
    main()
