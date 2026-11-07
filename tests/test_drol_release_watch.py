"""Regression checks for evidence extraction; no network calls."""
import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from drol_release_watch import summarize_release_notes


class ReleaseNotesTests(unittest.TestCase):
    def test_topics_and_whitespace(self):
        notes = summarize_release_notes("Security fixes\n\nBreaking API change for GPS sensor and waypoint missions")
        self.assertEqual(set(notes["topics"]), {"security", "api_change", "sensor", "navigation"})
        self.assertNotIn("\n", notes["excerpt"])
        self.assertEqual(len(notes["sha256"]), 64)

    def test_empty_and_untrusted_text(self):
        self.assertEqual(summarize_release_notes(None)["topics"], [])
        self.assertEqual(summarize_release_notes("<p>Firmware update</p>")["topics"], ["firmware"])

    def test_bounded_excerpt(self):
        self.assertEqual(len(summarize_release_notes("x" * 5000)["excerpt"]), 1200)


if __name__ == "__main__":
    unittest.main()
