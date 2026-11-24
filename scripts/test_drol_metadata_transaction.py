import unittest
from unittest.mock import Mock
from drol_metadata_transaction import import_reviewed

class TransactionTest(unittest.TestCase):
    def setUp(self):
        url = "https://example.org/"
        self.registry = {"sources": [{"url": url, "status": "monitor_only",
            "collection_policy": "metadata_and_original_analysis_only",
            "civil_relevance": "civil_verified", "license_type": "fixture",
            "metadata_review": {"decision": "approved_metadata_only", "reviewer": "fixture",
                "reviewed_at": "2026-11-24", "evidence_url": url}}]}
        self.records = [{"source_url": url, "title": "Fixture"}]
    def test_dry_run(self):
        db = Mock()
        self.assertFalse(import_reviewed(db, self.registry, self.records)["committed"])
        db.cursor.assert_not_called()
    def test_commit(self):
        db = Mock()
        self.assertTrue(import_reviewed(db, self.registry, self.records, execute=True)["committed"])
        db.commit.assert_called_once()
    def test_rollback(self):
        db = Mock()
        db.cursor.return_value.executemany.side_effect = RuntimeError("fixture")
        with self.assertRaises(RuntimeError):
            import_reviewed(db, self.registry, self.records, execute=True)
        db.rollback.assert_called_once()
        db.commit.assert_not_called()
    def test_blocked(self):
        db = Mock()
        self.registry["sources"][0]["license_type"] = "unverified"
        self.assertEqual(import_reviewed(db, self.registry, self.records, execute=True)["blocked"], 1)
        db.cursor.assert_not_called()
