import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from drol_metric_history import assemble


def sample(time, facts=4):
    return {"measured_at": time, "candidate_facts_total": 1, "metadata_inbox": 0,
            "model_facts_total": facts, "source_records": 3,
            "evidence": {"E0": 0, "E1": facts, "E2": 0, "E3": 0}}


class MetricHistoryTest(unittest.TestCase):
    def test_calendar_retention_and_first_legacy_sample(self):
        old = {"measured_at": "2026-11-23T12:00:00Z", "model_facts_total": 2}
        data = assemble(sample("2026-11-24T11:00:00Z"), old, {})
        data = assemble(sample("2026-11-24T12:00:00Z", 5), data, {})
        self.assertEqual([r["model_facts_total"] for r in data["history"]], [2, 5])
        self.assertEqual(data["next_inventory_at"], "2026-11-24T12:41:00+00:00")
        data = assemble(sample("2027-03-01T22:00:00Z"), data, {})
        self.assertEqual(len(data["history"]), 1)

    def test_import_history(self):
        item = {"at": "2026-11-23T11:00:00Z", "run_id": 321}
        data = assemble(sample("2026-11-24T10:00:00Z"), None, {"failure": item})
        result = assemble(sample("2026-11-24T11:00:00Z"), data, {"success": item})
        self.assertEqual(result["imports"], {"failure": item, "success": item})

    def test_reject_older_and_unapproved_values(self):
        data = sample("2026-11-24T10:00:00Z")
        with self.assertRaises(ValueError):
            assemble(sample("2026-11-23T10:00:00Z"), data, {})
        for extension in ({"raw_sql": "private"}, {"source_records": False}):
            with self.assertRaises(ValueError):
                assemble({**data, **extension}, None, {})
