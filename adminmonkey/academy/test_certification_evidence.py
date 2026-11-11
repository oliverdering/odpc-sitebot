"""Independent competency evidence must not be confused with keyword screening."""
import importlib.util
import unittest
from pathlib import Path

PATH = Path(__file__).resolve().parents[1] / "knowledge" / "certification_competency.py"
spec = importlib.util.spec_from_file_location("certification_competency", PATH)
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)


def fixtures():
    registry = {"certifications": [{
        "id": "test-cert", "official_name": "Test credential", "exam_codes": ["AZ-104"],
        "exam_domains": [{"name": "Secure networking", "weight": "100%"}]
    }]}
    bank = {"scenarios": [{
        "id": "AZ104-TEST", "track": "AZ-104", "exam_domain": "Secure networking",
        "required": ["least privilege"], "unsafe": []
    }]}
    return registry, bank


class EvidenceGateTests(unittest.TestCase):
    def score(self, evidence):
        registry, bank = fixtures()
        return module.calculate(registry, bank, {"results": evidence})["certifications"][0]

    def test_no_evidence_is_not_scored(self):
        item = self.score([])
        self.assertEqual(item["status"], "insufficient_evidence")
        self.assertIsNone(item["competency_percent"])

    def test_keyword_screening_is_not_independent_assessment(self):
        item = self.score([{"scenario_id": "AZ104-TEST", "evaluation_type": "lexical-screening-v2",
                            "passed": True, "evidence_url": "https://example.org/test", "grader_id": "x"}])
        self.assertIsNone(item["competency_percent"])

    def test_missing_evidence_url_is_rejected(self):
        item = self.score([{"scenario_id": "AZ104-TEST", "evaluation_type": "independent_agent_assessment",
                            "passed": True, "grader_id": "independent-reviewer"}])
        self.assertIsNone(item["competency_percent"])

    def test_valid_structured_evidence_is_counted(self):
        item = self.score([{"scenario_id": "AZ104-TEST", "evaluation_type": "independent_agent_assessment",
                            "passed": True, "evidence_url": "https://example.org/test",
                            "grader_id": "independent-reviewer"}])
        self.assertEqual(item["competency_percent"], 100.0)
        self.assertEqual(item["status"], "assessed")

    def test_later_failed_retest_revokes_prior_pass(self):
        evidence = [{"scenario_id": "AZ104-TEST", "evaluation_type": "independent_agent_assessment",
                     "passed": value, "evidence_url": "https://example.org/test",
                     "grader_id": "independent-reviewer"} for value in (True, False)]
        self.assertEqual(self.score(evidence)["competency_percent"], 0.0)


if __name__ == "__main__":
    unittest.main()
