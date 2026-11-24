import unittest
from drol_metadata_db_plan import build_plan, INSERT_SQL

class ImportPlanTest(unittest.TestCase):
    def test_review_and_dedup(self):
        url = "https://example.org/"
        source = {"url": url, "status": "monitor_only",
                  "collection_policy": "metadata_and_original_analysis_only"}
        registry = {"sources": [source]}
        record = {"source_url": url, "title": "Example"}
        self.assertEqual(build_plan(registry, [record])["blocked"], 1)
        source.update(civil_relevance="civil_verified", license_type="documented",
                      metadata_review={"decision": "approved_metadata_only",
                                       "reviewer": "fixture", "reviewed_at": "2026-11-24",
                                       "evidence_url": url})
        result = build_plan(registry, [record, record])
        self.assertEqual(len(result["rows"]), 1)
        self.assertEqual(result["blocked"], 0)
        self.assertFalse(result["production_import_allowed"])
        self.assertEqual(result["rows"], build_plan(registry, [record])["rows"])
        self.assertEqual(INSERT_SQL.count("%s"), 4)
        source["status"] = "excluded"
        self.assertEqual(build_plan(registry, [record])["rejected"], 1)

if __name__ == "__main__":
    unittest.main()
