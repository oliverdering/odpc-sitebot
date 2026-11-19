"""Regression checks for evidence extraction; no network calls."""
import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from drol_release_watch import quote_references, release_markers, summarize_release_notes


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


class DedupTests(unittest.TestCase):
    def test_marker_with_letter_s_in_tag(self):
        # tags such as v7.0.0_stable contain an "s": the whole tag must be the marker
        body = "Version: v7.0.0_stable\\n\\n<!-- drol-release:paparazzi/paparazzi:v7.0.0_stable -->"
        self.assertEqual(release_markers([{"body": body}]), {"drol-release:paparazzi/paparazzi:v7.0.0_stable"})

    def test_marker_new_body_and_missing_body(self):
        body = "Text\n\n<!-- drol-release:iNavFlight/inav:9.1.0 -->"
        self.assertEqual(release_markers([{"body": body}, {"body": None}, {}]), {"drol-release:iNavFlight/inav:9.1.0"})


class QuoteReferencesTests(unittest.TestCase):
    def test_mentions_and_references(self):
        text = "* [module] parachute by @dewagter in paparazzi/paparazzi#3273, see #12 and @org/team"
        self.assertEqual(quote_references(text),
                         "* [module] parachute by `@dewagter` in `paparazzi/paparazzi#3273`, see `#12` and `@org/team`")

    def test_email_and_urls_untouched(self):
        text = "mail dev@example.com or https://github.com/paparazzi/paparazzi/pull/3273"
        self.assertEqual(quote_references(text), text)


if __name__ == "__main__":
    unittest.main()
