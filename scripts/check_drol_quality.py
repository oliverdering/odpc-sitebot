#!/usr/bin/env python3
"""Read-only D.R.O.L. editorial and structural quality gates (stdlib only)."""
import json
import re
from pathlib import Path

root = Path("site-deploy/drol.odpc.de")
pages = {p.name: p.read_text(encoding="utf-8") for p in root.glob("*.html")}
errors = []
registry = json.loads((root / "database/model-schema.json").read_text(encoding="utf-8"))
models = registry["models"]
ids = [m["id"] for m in models]
if len(ids) != len(set(ids)):
    errors.append("Duplicate canonical model IDs")
for model in models:
    if not all(model.get(k) for k in ("id", "manufacturer", "name", "category")):
        errors.append(f"Incomplete identity: {model!r}")
    for capability in model.get("capabilities", {}).values():
        if capability.get("assessment") not in {"YES", "PARTIAL", "NO", "UNKNOWN"}:
            errors.append(f"Invalid assessment: {model['id']}")
        if capability.get("assessment") in {"YES", "PARTIAL"} and not capability.get("source"):
            errors.append(f"Positive capability without exact evidence: {model['id']}")
for name, html in pages.items():
    if not re.search(r"<title>.+?</title>", html, re.I | re.S):
        errors.append(f"Missing page title: {name}")
    if not re.search(r'<html[^>]+lang=', html, re.I):
        errors.append(f"Missing html language: {name}")
    if html.count('class="drol-unified-header"') and len(re.findall(r'<nav[^>]+aria-label="Hauptnavigation"', html)) > 1:
        errors.append(f"Duplicate main navigation: {name}")
    for href in re.findall(r'href=["\']([^"\']+)["\']', html):
        if href.startswith(("https:", "http:", "mailto:", "tel:", "#", "javascript:", "data:")):
            continue
        dest = href.split("#", 1)[0].split("?", 1)[0]
        if not dest:
            continue
        if dest.startswith("/data/"):
            if dest != "/data/models.json":
                errors.append(f"Unknown published data endpoint: {name} -> {dest}")
            continue
        if dest.startswith("/"):
            dest = dest[1:]
        if not dest:
            dest = "index.html"
        if dest.endswith("/"):
            dest += "index.html"
        if dest.endswith(".html") and dest not in pages and dest != "lab/index.html":
            errors.append(f"Missing internal page: {name} -> {dest}")
print(f"D.R.O.L. QA: {len(pages)} HTML pages, {len(models)} canonical registry records, {len(errors)} issues")
for error in errors:
    print("ERROR:", error)
if errors:
    raise SystemExit(1)
