"""Offline tests of scenario execution, API validation and report semantics."""
import importlib.util
import json
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

PATH = Path(__file__).with_name("run_competency_engine.py")
spec = importlib.util.spec_from_file_location("run_competency_engine", PATH)
import sys
sys.path.insert(0, str(PATH.parent))
engine = importlib.util.module_from_spec(spec)
spec.loader.exec_module(engine)


class EngineTests(unittest.TestCase):
    def setUp(self):
        self.bank = {"scenarios": [
            {"id": "A", "track": "AZ-104", "prompt": "Secure?", "required": ["least privilege"], "unsafe": ["allow all"]},
            {"id": "B", "track": "SC-300", "prompt": "Audit?", "required": ["audit"], "unsafe": []},
        ]}

    def test_runs_and_preserves_answers(self):
        report = engine.run(self.bank, lambda prompt: "least privilege" if prompt == "Secure?" else "audit", model="fake")
        self.assertEqual(report["total"], 2)
        self.assertEqual(report["passed"], 2)
        self.assertIsNone(report["competency_percent"])
        self.assertFalse(report["independent_agent_assessment"])
        self.assertEqual(len(report["answers"]), 2)

    def test_partial_failures_are_recorded(self):
        def responder(prompt):
            if prompt == "Audit?":
                raise ValueError("provider unavailable")
            return "least privilege"
        report = engine.run(self.bank, responder, model="fake")
        self.assertEqual(report["answered"], 1)
        self.assertIn("B", report["errors"])
        self.assertEqual(report["passed"], 1)

    def test_limit(self):
        report = engine.run(self.bank, lambda _: "least privilege", model="fake", limit=1)
        self.assertEqual(report["total"], 1)

    def test_rejects_http_and_missing_credentials(self):
        with self.assertRaises(ValueError):
            engine.generate("test", endpoint="http://example.org", api_key="x", model="x")
        with self.assertRaises(ValueError):
            engine.generate("test", endpoint="https://example.org", api_key="", model="x")

    def test_model_response_parsing(self):
        class Response:
            def __enter__(self): return self
            def __exit__(self, *args): pass
            def read(self, *args): return b'{"choices":[{"message":{"content":"least privilege"}}]}'
        with patch.object(engine.urllib.request, "urlopen", return_value=Response()) as mock:
            result = engine.generate("test", endpoint="https://example.org/v1/chat/completions", api_key="secret", model="fake")
            self.assertEqual(result, "least privilege")
            request = mock.call_args.args[0]
            self.assertEqual(request.get_header("Authorization"), "Bearer secret")
            self.assertEqual(json.loads(request.data)["model"], "fake")


if __name__ == "__main__":
    unittest.main()
