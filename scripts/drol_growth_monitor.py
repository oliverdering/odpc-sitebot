"""D.R.O.L. public catalog growth telemetry; never infers verified facts."""
import datetime
import json
import os
import urllib.request
from pathlib import Path

URL = "https://drol.odpc.de/data/models-db.json"
HISTORY = Path("data/drol-growth-history.json")

def main():
    request = urllib.request.Request(URL, headers={"User-Agent": "DROL-Growth-Monitor/1.0", "Cache-Control": "no-cache"})
    with urllib.request.urlopen(request, timeout=30) as response:
        if response.status != 200:
            raise RuntimeError("Public catalog returned HTTP " + str(response.status))
        payload = json.load(response)
    models = payload.get("models")
    count = payload.get("model_count")
    if not isinstance(models, list) or type(count) is not int or count != len(models):
        raise RuntimeError("Catalog count mismatch; refusing to record unreliable sample")
    if count < 5 or payload.get("source") != "D.R.O.L. production MariaDB":
        raise RuntimeError("Catalog origin or inventory invalid")
    now = datetime.datetime.now(datetime.timezone.utc).isoformat()
    history = json.loads(HISTORY.read_text(encoding="utf-8")) if HISTORY.exists() else {"schema_version": 1, "samples": []}
    samples = history["samples"]
    previous = samples[-1] if samples else None
    delta = count - previous["model_count"] if previous else None
    elapsed = (datetime.datetime.fromisoformat(now) - datetime.datetime.fromisoformat(previous["checked_utc"])).total_seconds()/86400 if previous else None
    sample = {"checked_utc": now, "model_count": count, "delta": delta,
              "models_per_day": round(delta/elapsed, 3) if elapsed and elapsed > 0 else None,
              "catalog_generated_utc": payload.get("generated_utc"),
              "github_run_id": os.getenv("GITHUB_RUN_ID")}
    samples.append(sample)
    HISTORY.parent.mkdir(parents=True, exist_ok=True)
    HISTORY.write_text(json.dumps(history, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print("D.R.O.L. growth:", json.dumps(sample))
    if delta is not None and delta < 0:
        raise RuntimeError("Public model inventory decreased; investigate data loss")
    if previous and payload.get("generated_utc") == previous.get("catalog_generated_utc"):
        print("WARNING: Catalog export timestamp unchanged since previous sample")
    print("NOTE: Model count is not a count of verified technical facts.")

if __name__ == "__main__":
    main()
