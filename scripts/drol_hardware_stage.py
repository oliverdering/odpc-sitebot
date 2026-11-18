"""Offline, source-backed hardware candidate staging for D.R.O.L.

Input JSON: {"candidates": [{"manufacturer": "...", "model": "...",
"variant": "...", "source_url": "https://...", "source_excerpt": "...",
"retrieved_utc": "2026-11-08T12:00:00Z"}]}
Never publishes to MariaDB or the website. All candidates remain E0.
"""
import argparse
import hashlib
import json
import re
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urlsplit

def normalize(value):
    if not isinstance(value, str):
        raise ValueError("expected string")
    return " ".join(value.strip().split())

def key(row):
    return "|".join(normalize(row[field]).casefold() for field in ("manufacturer", "model", "variant"))

def stage(payload):
    if not isinstance(payload, dict) or not isinstance(payload.get("candidates"), list):
        raise ValueError("expected candidates array")
    if len(payload["candidates"]) > 500:
        raise ValueError("candidate limit exceeded")
    staged, duplicates = [], 0
    seen = set()
    for row in payload["candidates"]:
        if not isinstance(row, dict):
            raise ValueError("invalid candidate")
        required = {"manufacturer", "model", "variant", "source_url", "source_excerpt", "retrieved_utc"}
        if set(row) != required:
            raise ValueError("unexpected or missing candidate fields")
        for field in ("manufacturer", "model", "source_excerpt"):
            if not normalize(row[field]) or len(row[field]) > 1500:
                raise ValueError("missing or oversized " + field)
        identity = key(row)
        if not identity.replace("|", ""):
            raise ValueError("empty identity")
        url = urlsplit(row["source_url"])
        if url.scheme != "https" or not url.hostname or url.username or url.password or url.fragment:
            raise ValueError("invalid source URL")
        if url.hostname in {"localhost", "127.0.0.1", "::1"} or url.hostname.endswith((".local", ".internal")):
            raise ValueError("private source URL")
        try:
            date = datetime.fromisoformat(row["retrieved_utc"].replace("Z", "+00:00"))
            if date.tzinfo is None:
                raise ValueError("timezone required")
        except (TypeError, ValueError) as exc:
            raise ValueError("invalid retrieval timestamp") from exc
        if identity in seen:
            duplicates += 1
            continue
        seen.add(identity)
        staged.append({
            "candidate_id": hashlib.sha256(identity.encode()).hexdigest()[:24],
            "manufacturer": normalize(row["manufacturer"]),
            "model": normalize(row["model"]),
            "variant": normalize(row["variant"]),
            "source_url": row["source_url"],
            "source_excerpt": normalize(row["source_excerpt"]),
            "retrieved_utc": date.astimezone(timezone.utc).isoformat(),
            "evidence_level": "E0",
            "status": "UNREVIEWED",
            "model_compatibility": "UNKNOWN",
        })
    staged.sort(key=lambda x: x["candidate_id"])
    return {"schema_version": 1, "status": "STAGING_ONLY", "count": len(staged),
            "duplicates": duplicates, "candidates": staged}

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("input", type=Path)
    parser.add_argument("output", type=Path)
    args = parser.parse_args()
    result = stage(json.loads(args.input.read_text(encoding="utf-8")))
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(result, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print("Staged", result["count"], "E0 candidates;", result["duplicates"], "duplicates")

if __name__ == "__main__":
    main()
