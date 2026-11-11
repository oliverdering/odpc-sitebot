"""Regression tests for screening-only AdminMonkey rubric."""
import importlib.util
import unittest
from pathlib import Path

SCRIPT = Path(__file__).with_name("evaluate_responses.py")
spec = importlib.util.spec_from_file_location("amk_evaluator", SCRIPT)
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)


class EvaluatorSafetyTests(unittest.TestCase):
    def test_no_response_cannot_pass(self):
        bank = {"scenarios": [{"id": "x", "track": "AZ-104", "required": ["NSG"], "unsafe": []}]}
        result = module.evaluate(bank, {})
        self.assertEqual(result["passed"], 0)
        self.assertFalse(result["independent_agent_assessment"])

    def test_empty_rubric_cannot_pass_or_divide_by_zero(self):
        bank = {"scenarios": [{"id": "x", "track": "AZ-104", "required": [], "unsafe": []}]}
        result = module.evaluate(bank, {"x": "anything"})
        self.assertEqual(result["passed"], 0)

    def test_unsafe_match_blocks_keyword_pass(self):
        bank = {"scenarios": [{"id": "x", "track": "AZ-104", "required": ["NSG"], "unsafe": ["allow all inbound traffic"]}]}
        result = module.evaluate(bank, {"x": "NSG; allow all inbound traffic"})
        self.assertEqual(result["passed"], 0)

    def test_invalid_answers_rejected(self):
        with self.assertRaises(ValueError):
            module.evaluate({"scenarios": []}, ["not", "an", "object"])


if __name__ == "__main__":
    unittest.main()
