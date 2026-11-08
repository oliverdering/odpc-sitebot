import unittest
from scripts.drol_hardware_stage import stage

def candidate(**changes):
    row = {"manufacturer": "Holybro", "model": "X500 V2", "variant": "",
           "source_url": "https://holybro.com/products/x500-v2",
           "source_excerpt": "X500 V2 product information",
           "retrieved_utc": "2026-11-08T12:00:00Z"}
    row.update(changes)
    return row

class StageTests(unittest.TestCase):
    def test_staging_is_unreviewed(self):
        result = stage({"candidates": [candidate()]})
        self.assertEqual(result["count"], 1)
        self.assertEqual(result["candidates"][0]["status"], "UNREVIEWED")
        self.assertEqual(result["candidates"][0]["model_compatibility"], "UNKNOWN")

    def test_deduplication(self):
        result = stage({"candidates": [candidate(), candidate(manufacturer=" holybro ")]})
        self.assertEqual(result["count"], 1)
        self.assertEqual(result["duplicates"], 1)

    def test_deterministic(self):
        self.assertEqual(stage({"candidates": [candidate()]}), stage({"candidates": [candidate()]}))

    def test_no_http(self):
        with self.assertRaises(ValueError):
            stage({"candidates": [candidate(source_url="http://example.com")]})

    def test_missing_provenance(self):
        with self.assertRaises(ValueError):
            stage({"candidates": [candidate(source_excerpt="")]})

    def test_no_timezone(self):
        with self.assertRaises(ValueError):
            stage({"candidates": [candidate(retrieved_utc="2026-11-08T12:00:00")]})

    def test_reject_extra_fields(self):
        with self.assertRaises(ValueError):
            stage({"candidates": [candidate(password="not-allowed")]})

    def test_empty(self):
        self.assertEqual(stage({"candidates": []})["count"], 0)

if __name__ == "__main__":
    unittest.main()
