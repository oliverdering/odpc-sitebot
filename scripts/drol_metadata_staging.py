"""Offline D.R.O.L. metadata staging. No network, database or publication."""
import argparse
import hashlib
import json
from pathlib import Path

def review_gate(source):
    """Fail closed: a license label alone is not permission to collect."""
    reasons = []
    if source.get("civil_relevance") != "civil_verified":
        reasons.append("civil_scope_unverified")
    if source.get("license_type") in (None, "", "unverified"):
        reasons.append("license_unverified")
    review = source.get("metadata_review")
    if not isinstance(review, dict):
        reasons.append("review_missing")
    else:
        if review.get("decision") != "approved_metadata_only":
            reasons.append("metadata_permission_missing")
        for field in ("reviewer", "reviewed_at", "evidence_url"):
            if not isinstance(review.get(field), str) or not review[field].strip():
                reasons.append("missing_" + field)
        if not str(review.get("evidence_url", "")).startswith("https://"):
            reasons.append("invalid_evidence_url")
    return reasons

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
                         "review_blockers": review_gate(source),
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
