"""Build a deterministic MariaDB import plan; never connect or execute SQL."""
import json
from drol_metadata_staging import stage

def build_plan(registry, records):
    staged = stage(registry, records)
    rows = []
    for item in staged["accepted"]:
        if item["review_blockers"]:
            continue
        rows.append((item["key"], item["source_url"], item["title"],
                     json.dumps({"evidence": "E0", "status": "pending"})))
    return {"mode": "dry_run", "rows": rows,
            "blocked": len(staged["accepted"]) - len(rows),
            "rejected": len(staged["rejected"]), "production_import_allowed": False}

# Parameterized statement for a future explicitly authorized transaction.
INSERT_SQL = """INSERT INTO drol_metadata_inbox
(candidate_key, source_url, title, metadata_json)
VALUES (%s, %s, %s, %s)
ON DUPLICATE KEY UPDATE candidate_key=candidate_key"""
