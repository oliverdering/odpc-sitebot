"""Offline unit tests for the read-only workflow inventory."""
import importlib.util
import tempfile
import unittest
from pathlib import Path

SCRIPT = Path(__file__).with_name("workflow_inventory.py")
spec = importlib.util.spec_from_file_location("workflow_inventory", SCRIPT)
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)


class WorkflowInventoryTests(unittest.TestCase):
    def test_extracts_metadata_without_running_workflow(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "sample.yml"
            path.write_text(
                "name: test\non:\n  push:\n    branches: [main]\n"
                "  workflow_dispatch:\nconcurrency:\n  group: odpc-production\n"
                "jobs:\n  deploy:\n    runs-on: ubuntu-latest\n"
                "    steps:\n      - run: python3 scripts/deploy_sample.py\n"
                "        env:\n          TOKEN: ${{ secrets.ODPC_SFTP_PASSWORD }}\n",
                encoding="utf-8",
            )
            # inspect() normally expects a file under ROOT
            original = module.ROOT
            try:
                module.ROOT = Path(directory)
                result = module.inspect(path)
            finally:
                module.ROOT = original
            self.assertIn("push", result["triggers_heuristic"])
            self.assertIn("workflow_dispatch", result["triggers_heuristic"])
            self.assertIn("odpc-production", result["concurrency_groups_heuristic"])
            self.assertIn("ODPC_SFTP_PASSWORD", result["references_secret_names"])
            self.assertTrue(result["deploy_like_heuristic"])
            self.assertTrue(result["requires_manual_target_review"])

    def test_no_secret_values_are_extracted(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "sample.yml"
            path.write_text("env:\n  PASSWORD: ${{ secrets.DATABASE_PASSWORD }}\n", encoding="utf-8")
            original = module.ROOT
            try:
                module.ROOT = Path(directory)
                result = module.inspect(path)
            finally:
                module.ROOT = original
            self.assertEqual(result["references_secret_names"], ["DATABASE_PASSWORD"])
            self.assertNotIn("${{", str(result))


if __name__ == "__main__":
    unittest.main()
