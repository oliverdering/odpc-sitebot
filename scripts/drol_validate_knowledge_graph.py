"""Offline validation of cross-vendor E0 knowledge graph seeds."""
import json
from pathlib import Path

PATH = Path(__file__).resolve().parents[1] / "site-deploy/drol.odpc.de/database/knowledge-graph-seed.json"
TYPES = {"drone","controller","flight_controller","firmware","sdk","sensor","camera","companion_computer","software","accessory","use_case"}

def validate(data):
    assert data["evidence"] == "E0"
    assert data["verified_compatibilities"] == []
    entities = data["entities"]
    keys = [e["key"] for e in entities]
    assert len(keys) == len(set(keys)), "duplicate entity keys"
    assert len(entities) >= 20, "insufficient seed coverage"
    for entity in entities:
        assert entity["type"] in TYPES
        assert entity["review_status"] == "seed"
        assert entity["key"] and entity["name"]
    for relation in data["relations"]:
        assert relation["subject"] in keys and relation["object"] in keys
        assert relation.get("evidence") == "E0"
        assert relation.get("assessment", "unknown") == "unknown"
    manufacturers = {e["manufacturer"] for e in entities if e["type"] == "drone"}
    assert len(manufacturers) >= 5, "must cover multiple manufacturers"
    return len(entities), len(manufacturers)

if __name__ == "__main__":
    n, m = validate(json.loads(PATH.read_text(encoding="utf-8")))
    print(f"PASS: {n} seed entities across {m} drone manufacturers; zero invented compatibility claims")
