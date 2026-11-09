#!/usr/bin/env python3
"""Fail closed when AdminMonkey DB evidence is stale or contradictory.

This does not claim to connect to MariaDB; a DB-side COUNT(*) probe is
required to certify persisted knowledge.
"""
import json
from datetime import datetime, timezone
from pathlib import Path

root = Path(__file__).resolve().parent
progress_path = root / "database_progress.json"
diag_path = root / "database_diagnostics.json"
progress = json.loads(progress_path.read_text()) if progress_path.exists() else {}
diagnostic = json.loads(diag_path.read_text()) if diag_path.exists() else {}
count = progress.get("document_versions_total")
direct_ok = diagnostic.get("database_connected") is True
reported_ok = progress.get("database_connected") is True
report = {
    "checked_at": datetime.now(timezone.utc).isoformat(),
    "reported_document_versions": count if isinstance(count, int) and count >= 0 else None,
    "progress_report_claims_connected": reported_ok,
    "runner_direct_db_connected": direct_ok,
    "runner_diagnostic_verdict": diagnostic.get("verdict", "UNKNOWN"),
    "verified_live_database_count": None,
    "knowledge_growth_verified": False,
    "verdict": "LIVE_DB_COUNT_REQUIRED",
    "next_action": "Run SELECT COUNT(*) on IONOS-side MariaDB with read-only credentials; record count, timestamp, run ID and source in a signed or authenticated report.",
}
(root / "database_evidence_audit.json").write_text(json.dumps(report, indent=2) + "\n")
print(json.dumps(report, indent=2))
