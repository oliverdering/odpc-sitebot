"""Offline validation of cross-vendor E0 knowledge graph seeds."""
import json
from pathlib import Path
import re

PATH = Path(__file__).resolve().parents[1] / "site-deploy/drol.odpc.de/database/knowledge-graph-seed.json"
TYPES = {"drone", "controller", "flight_controller", "firmware", "flight_stack", "protocol", "sdk",
         "sensor", "camera", "companion_computer", "software", "accessory", "use_case"}


def validate(data):
    if (data.get("schema_version") != "1.0" or data.get("evidence") != "E0"
            or data.get("verified_compatibilities") != [] or data.get("relations") != []):
        raise ValueError("This importer accepts only an E0 entity seed with no relationships")
    entities = data["entities"]
    seen = set()
    for item in entities:
        key = item["key"]
        if not re.fullmatch(r"[a-z0-9][a-z0-9-]{0,189}", key) or key in seen:
            raise ValueError("Duplicate or invalid graph key")
        seen.add(key)
        if (item["type"] not in TYPES or item["review_status"] != "seed"
                or not isinstance(item["name"], str) or not item["name"].strip() or len(item["name"]) > 250):
            raise ValueError("Invalid graph entity")
        manufacturer = item["manufacturer"]
        if manufacturer is not None and (not isinstance(manufacturer, str)
                                         or not manufacturer.strip() or len(manufacturer) > 190):
            raise ValueError("Invalid manufacturer")
    manufacturers = {e["manufacturer"] for e in entities if e["type"] == "drone" and e["manufacturer"]}
    if len(entities) < 20 or len(manufacturers) < 5:
        raise ValueError("Seed coverage fell below the cross-manufacturer baseline")
    return len(entities), len(manufacturers)


if __name__ == "__main__":
    n, m = validate(json.loads(PATH.read_text(encoding="utf-8")))
    print(f"PASS: {n} E0 seed entities across {m} drone manufacturers; zero compatibility claims")
