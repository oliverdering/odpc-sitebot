"""Read-only regression gates for AdminMonkey's published evidence claims.

Run: python -m unittest discover -s adminmonkey/knowledge -p 'test_evidence_contract.py' -v
No network, secrets, billing, or database writes.
"""
import json
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parent
BANK = ROOT.parent / "academy" / "competency_bank.json"
PROGRESS = ROOT / "academy_progress.json"


class EvidenceContractTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.bank = json.loads(BANK.read_text(encoding="utf-8"))
        cls.progress = json.loads(PROGRESS.read_text(encoding="utf-8"))

    def test_scenarios_have_unique_ids_and_nonempty_rubrics(self):
        scenarios = self.bank["scenarios"]
        ids = [s["id"] for s in scenarios]
        self.assertEqual(len(ids), len(set(ids)), "duplicate scenario ID")
        for scenario in scenarios:
            with self.subTest(scenario=scenario["id"]):
                self.assertTrue(scenario.get("track"))
                self.assertTrue(scenario.get("prompt", "").strip())
                self.assertTrue(scenario.get("required"))
                self.assertTrue(scenario.get("unsafe"))

    def test_published_tracks_match_actual_scenario_bank(self):
        for track in self.progress["tracks"]:
            with self.subTest(track=track["track"]):
                count = sum(s["track"] == track["track"] for s in self.bank["scenarios"])
                self.assertEqual(track["practice_scenarios_available"], count)

    def test_no_unverified_competency_claims(self):
        self.assertIs(self.progress["official_microsoft_certification"], False)
        self.assertFalse(self.progress["retrieval_staging"]["retrieval_quality_verified"])
        self.assertEqual(self.progress["retrieval_staging"]["agent_answers_verified"], 0)
        for track in self.progress["tracks"]:
            with self.subTest(track=track["track"]):
                if track["agent_answers_evaluated"] == 0:
                    self.assertEqual(track["verified_passes"], 0)
                    self.assertIsNone(track["competency_percent"])
                    self.assertEqual(track["badge_status"], "not_earned")


if __name__ == "__main__":
    unittest.main()
