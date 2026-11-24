import unittest
from drol_metadata_staging import stage, review_gate

class StagingTest(unittest.TestCase):
    def test_policy_and_dedup(self):
        url = "https://example.org/"
        registry = {"sources": [{"url": url, "status": "monitor_only",
                    "collection_policy": "metadata_and_original_analysis_only"},
                    {"url": "https://example.net/", "status": "excluded"}]}
        record = {"source_url": url, "title": "Example"}
        result = stage(registry, [record, record, {"source_url": "https://example.net/", "title": "Excluded"},
                                  {"source_url": "https://unknown.example/", "title": "Unknown"}])
        self.assertEqual(len(result["accepted"]), 1)
        self.assertEqual(len(result["rejected"]), 2)
        self.assertFalse(result["accepted"][0]["production_import_allowed"])
        self.assertEqual(result["accepted"][0]["evidence"], "E0")

    def test_review_gate(self):
        self.assertIn("review_missing", review_gate({}))
        source = {"civil_relevance": "civil_verified", "license_type": "documented",
                  "metadata_review": {"decision": "approved_metadata_only", "reviewer": "test",
                                      "reviewed_at": "2026-11-24", "evidence_url": "https://example.org/policy"}}
        self.assertEqual(review_gate(source), [])
        source["metadata_review"]["evidence_url"] = ""
        self.assertIn("invalid_evidence_url", review_gate(source))

if __name__ == "__main__":
    unittest.main()
