#!/usr/bin/env python3
"""Read-only ODPC workflow inventory. No GitHub API, secrets or deployments."""
from __future__ import annotations
import argparse
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
WF = ROOT / ".github" / "workflows"
KEYS = ("push", "pull_request", "workflow_dispatch", "schedule", "workflow_call", "repository_dispatch")
SENSITIVE = re.compile(r"\b(deploy|publish|release|sftp|ftp|ssh|rsync|migrat|import|upload|sync|database|db)\b", re.I)
SECRET = re.compile(r"secrets\.([A-Za-z_][A-Za-z_0-9]*)")
SCRIPT = re.compile(r"(?:python(?:3)?\s+|bash\s+|sh\s+)([A-Za-z0-9_./-]+)")
GROUP = re.compile(r"^\s*group:\s*(.+?)\s*$", re.M)

def inspect(path: Path) -> dict:
    raw = path.read_text(encoding="utf-8")
    lines = raw.splitlines()
    # Deliberately heuristic; never use this inventory to authorize a deployment.
    triggers = [key for key in KEYS if re.search(r"^\s{2,4}" + re.escape(key) + r"\s*:", raw, re.M)]
    groups = GROUP.findall(raw)
    scripts = sorted(set(SCRIPT.findall(raw)))
    secrets = sorted(set(SECRET.findall(raw)))
    deploy_like = bool(SENSITIVE.search(path.stem) or SENSITIVE.search(" ".join(scripts)))
    # Only report branch restrictions as unknown unless an explicit branches: stanza exists.
    branch_filter = bool(re.search(r"^\s+branches\s*:", raw, re.M))
    return {
        "workflow": path.relative_to(ROOT).as_posix(),
        "triggers_heuristic": triggers,
        "has_explicit_branches_stanza": branch_filter,
        "concurrency_groups_heuristic": groups,
        "references_secret_names": secrets,
        "shell_script_paths_heuristic": scripts,
        "deploy_like_heuristic": deploy_like,
        "requires_manual_target_review": True,
    }

def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--output", default="-", help="JSON output path, or - for stdout")
    args = parser.parse_args()
    if not WF.is_dir():
        parser.error("Missing .github/workflows directory")
    paths = sorted(list(WF.glob("*.yml")) + list(WF.glob("*.yaml")))
    data = {"schema_version": 1, "read_only": True,
            "warning": "Heuristic metadata only. No actual remote targets, runs or deploy safety verified.",
            "workflow_count": len(paths), "workflows": [inspect(p) for p in paths]}
    encoded = json.dumps(data, indent=2, ensure_ascii=False) + "\n"
    if args.output == "-":
        print(encoded, end="")
    else:
        target = Path(args.output).resolve()
        if ROOT not in target.parents:
            parser.error("Output must be inside the repository")
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(encoded, encoding="utf-8")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
