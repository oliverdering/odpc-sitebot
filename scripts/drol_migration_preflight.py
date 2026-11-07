#!/usr/bin/env python3
"""Offline-only D.R.O.L. schema preflight. Never opens a DB connection."""
from pathlib import Path
import re
import sys

root = Path("site-deploy/drol.odpc.de/database")
migrations = sorted(root.glob("[0-9][0-9][0-9]_*.sql"))
expected = ["001_catalog.sql", "002_evidence_model.sql", "003_knowledge_core.sql"]
errors = []
if [p.name for p in migrations] != expected:
    errors.append("Unexpected migration sequence: " + ", ".join(p.name for p in migrations))
seen_tables = set()
for file in migrations:
    sql = file.read_text(encoding="utf-8")
    if re.search(r"\b(?:DROP\s+(?:DATABASE|TABLE)|TRUNCATE|DELETE\s+FROM|GRANT\s+ALL)\b", sql, re.I):
        errors.append(f"Destructive or privileged statement in {file.name}")
    tables = re.findall(r"CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+(\w+)", sql, re.I)
    for table in tables:
        if table in seen_tables:
            errors.append(f"Duplicate CREATE TABLE across migrations: {table}")
        seen_tables.add(table)
    references = re.findall(r"REFERENCES\s+(\w+)\s*\(", sql, re.I)
    for target in references:
        if target not in seen_tables:
            errors.append(f"{file.name}: FK references a not-yet-created table: {target}")
    print(f"{file.name}: {len(tables)} tables; {len(references)} FK declarations")
print("Tables:", ", ".join(sorted(seen_tables)))
if errors:
    for e in errors:
        print("ERROR:", e, file=sys.stderr)
    sys.exit(1)
print("Offline migration preflight OK. This is NOT a live MariaDB compatibility or backup test.")
