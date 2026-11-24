"""Public aggregate allowlist, measured calendar-day history and workflow observations."""
import json
import os
from datetime import datetime, timedelta, timezone
from pathlib import Path
from urllib.request import Request, urlopen

CORE = {"candidate_facts_total", "metadata_inbox", "model_facts_total", "source_records"}
COUNTS = CORE | set("""hardware_models manufacturers hardware_source_links firmware_releases
source_snapshots attribute_definitions knowledge_entities knowledge_relations knowledge_sources
capability_evidence aliases release_batches change_log source_watches table_count
sources_with_check_date sources_with_snapshot""".split())
TREND = {"model_facts_total", "firmware_releases", "knowledge_entities", "knowledge_relations"}
STEPS = {
    "D.R.O.L. / Autonomous knowledge discovery": "Import newly discovered facts into MariaDB as pending E0 knowledge",
    "D.R.O.L. / Reviewed knowledge import": "Import verified knowledge",
}


def instant(value):
    dt = datetime.fromisoformat(value.replace("Z", "+00:00"))
    if dt.tzinfo is None:
        raise ValueError("Missing timezone")
    return dt.astimezone(timezone.utc)


def counts(record, allowed):
    if not record.keys() <= allowed or any(type(value) is not int or value < 0 for value in record.values()):
        raise ValueError("Invalid aggregate")


def measurement(data, fields):
    instant(data["measured_at"])
    return {"measured_at": data["measured_at"], **{key: data[key] for key in fields if key in data}}


def import_observation():
    if os.getenv("GITHUB_EVENT_NAME") != "workflow_run":
        return {}
    run = json.loads(Path(os.environ["GITHUB_EVENT_PATH"]).read_text())["workflow_run"]
    step = STEPS.get(run["name"])
    if not step or run["head_branch"] != "main":
        return {}
    run_id = int(run["id"])
    attempt = int(run["run_attempt"])
    repo = os.environ["GITHUB_REPOSITORY"]
    url = f"https://api.github.com/repos/{repo}/actions/runs/{run_id}/attempts/{attempt}/jobs?per_page=100"
    req = Request(url, headers={"Authorization": "Bearer " + os.environ["GH_TOKEN"]})
    with urlopen(req, timeout=15) as response:
        jobs = json.load(response)["jobs"]
    for job in jobs:
        for item in job["steps"]:
            outcome = item["conclusion"]
            if item["name"] == step and outcome in {"success", "failure"}:
                return {outcome: {"at": item["completed_at"], "run_id": run_id}}
    return {}


def assemble(data, old, observation):
    current = dict(data)
    levels = current.pop("evidence")
    if set(levels) != {"E0", "E1", "E2", "E3"}:
        raise ValueError("Invalid evidence distribution")
    counts(levels, set(levels))
    current.pop("measured_at")
    counts(current, COUNTS)
    if not CORE <= current.keys():
        raise ValueError("Missing aggregates")
    now = instant(data["measured_at"])
    days = {}
    if old:
        if now < instant(old["measured_at"]):
            raise ValueError("Older measurement cannot replace a newer snapshot")
        for sample in [*old.get("history", []), measurement(old, TREND)]:
            stamp = instant(sample["measured_at"])
            selected = measurement(sample, TREND)
            counts({k: v for k, v in selected.items() if k != "measured_at"}, TREND)
            if now - timedelta(days=92) <= stamp <= now:
                day = stamp.date()
                if day not in days or instant(days[day]["measured_at"]) < stamp:
                    days[day] = selected
    days[now.date()] = measurement(data, TREND)
    history = [days[d] for d in sorted(days)]
    imports = {}
    for entry in ((old or {}).get("imports", {}), observation):
        for state in ("success", "failure"):
            if state not in entry:
                continue
            item = entry[state]
            t = instant(item["at"])
            if type(item["run_id"]) is not int or item["run_id"] <= 0:
                raise ValueError("Invalid import identifier")
            if state not in imports or t > instant(imports[state]["at"]):
                imports[state] = {"at": item["at"], "run_id": item["run_id"]}
    # drol-db-inventory.yml UTC schedule: minute 41 every third hour.
    scheduled = now.replace(hour=(now.hour // 3) * 3, minute=41, second=0, microsecond=0)
    if scheduled <= now:
        scheduled += timedelta(hours=3)
    return {**data, "history": history, "imports": imports, "next_inventory_at": scheduled.isoformat()}
