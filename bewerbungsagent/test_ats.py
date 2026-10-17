import unittest
from ats import Criterion, analyze

class ATSTests(unittest.TestCase):
    def test_matching_and_mandatory_gap(self):
        result = analyze("Erfahrung mit Python und VMware.", [
            Criterion("Python", True), Criterion("Kubernetes", True), Criterion("VMware")])
        self.assertEqual(result["score"], 66.7)
        self.assertEqual(result["missing_required"], ["Kubernetes"])

    def test_no_false_substring(self):
        self.assertFalse(analyze("JavaScript", [Criterion("Java")])["criteria"][0]["matched"])

    def test_empty(self):
        self.assertIsNone(analyze("", [])["score"])

    def test_invalid_weight(self):
        with self.assertRaises(ValueError):
            analyze("Python", [Criterion("Python", weight=0)])

if __name__ == "__main__":
    unittest.main()
