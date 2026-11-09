"""Regression checks for source provenance and honest Academy scoring."""
import json
import unittest
from pathlib import Path
from build_progress import manufacturer_verified, mapped_tracks

ROOT = Path(__file__).resolve().parent

class KnowledgeIntegrityTests(unittest.TestCase):
    def test_official_source_requires_digest(self):
        good = {"url": "https://learn.microsoft.com/en-us/entra/identity/", "sha256": "a" * 64}
        self.assertTrue(manufacturer_verified(good))
        self.assertFalse(manufacturer_verified({**good, "url": "https://learn.microsoft.com.evil.example/en-us/entra/"}))
        self.assertFalse(manufacturer_verified({**good, "sha256": "invalid"}))
        self.assertFalse(manufacturer_verified({**good, "url": "http://learn.microsoft.com/en-us/entra/"}))

    def test_track_mapping(self):
        self.assertIn("SC-300", mapped_tracks({"url": "https://learn.microsoft.com/en-us/entra/identity/"}))
        self.assertIn("AZ-104", mapped_tracks({"url": "https://learn.microsoft.com/en-us/azure/virtual-machines/"}))

    def test_published_snapshots_are_consistent(self):
        progress = json.loads((ROOT / "academy_progress.json").read_text(encoding="utf-8"))
        manifest = json.loads((ROOT / "document_manifest.json").read_text(encoding="utf-8"))
        docs = manifest["documents"]
        self.assertEqual(progress["knowledge"]["documents_collected"], len(docs))
        self.assertLessEqual(progress["knowledge"]["manufacturer_verified"], len(docs))
        self.assertTrue(all(t["verified_passes"] == 0 and t["badge_status"] == "not_earned"
                            for t in progress["tracks"]), "Unverified competence must not earn badges")
        urls = [d["url"] for d in docs]
        self.assertEqual(len(urls), len(set(urls)), "Duplicate document URLs in public manifest")

if __name__ == "__main__":
    unittest.main()
