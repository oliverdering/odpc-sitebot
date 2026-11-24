"""Load reviewed E1 catalogue facts for the guarded MariaDB importer."""
from datetime import date
import json
from pathlib import Path
import re
from urllib.parse import urlsplit

DATABASE = Path(__file__).resolve().parents[1] / "site-deploy/drol.odpc.de/database"
PRIMARY_HOSTS = {"DJI": {"www.dji.com", "enterprise.dji.com"}, "BETAFPV": {"betafpv.com"},
                 "Holybro": {"holybro.com"}, "Parrot": {"www.parrot.com"}}
LIMITS = {"fact_key": 100, "value_text": 500, "source_locator": 500,
          "source_url": 1000, "checked_by": 150, "notes": 2000}


def load_facts(database=DATABASE):
    models = {}
    for model in json.loads((database / "model-schema.json").read_text())["models"]:
        if model["id"] in models:
            raise ValueError(f"Ambiguous catalogue ID: {model['id']}")
        models[model["id"]] = model
    facts = []
    identities = set()
    for path in sorted(database.glob("reviewed-facts-batch-*.json")):
        batch = json.loads(path.read_text())
        if batch["schema_version"] != 1 or batch["status"] != "source_reviewed":
            raise ValueError(f"Unsupported or unreviewed batch: {path.name}")
        for row in batch["facts"]:
            model = models[row["model"]]
            for key, limit in LIMITS.items():
                value = row[key]
                if not isinstance(value, str) or not value.strip() or len(value) > limit:
                    raise ValueError(f"Invalid {key} in {path.name}")
            unit = row["unit"]
            if unit is not None and (not isinstance(unit, str) or not 1 <= len(unit) <= 40):
                raise ValueError("Invalid unit")
            if not re.fullmatch(r"[a-z][a-z0-9_]*", row["fact_key"]):
                raise ValueError("Invalid fact key")
            url = urlsplit(row["source_url"])
            if (row["source_url"] not in model["sources"] or url.scheme != "https"
                    or url.netloc not in PRIMARY_HOSTS.get(model["manufacturer"], set())
                    or not (url.path.rstrip("/").endswith(("/specs", "/technical-specifications"))
                            or re.fullmatch(r"/products/[a-z0-9-]+", url.path)) or url.query or url.fragment):
                raise ValueError(f"Unreviewed source for {model['id']}")
            checked = date.fromisoformat(row["checked_on"])
            if row["evidence_level"] != "E1" or checked > date.today():
                raise ValueError("Expected dated primary-document evidence")
            identity = (model["id"], row["fact_key"])
            if identity in identities:
                raise ValueError(f"Repeated model fact: {identity}")
            identities.add(identity)
            facts.append({**row, "manufacturer": model["manufacturer"], "name": model["name"]})
    if not facts:
        raise ValueError("No reviewed model facts")
    return facts


if __name__ == "__main__":
    rows = load_facts()
    print(f"Validated {len(rows)} E1 facts for {len({r['model'] for r in rows})} models")
