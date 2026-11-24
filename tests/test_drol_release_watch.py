"""Regression checks for evidence extraction; no network calls."""
import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from drol_release_watch import no_notifications, release_markers, summarize_release_notes, request


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


class NoNotificationsTests(unittest.TestCase):
    def test_no_at_sign_left(self):
        # every way upstream text can write a mention: plain, quoted, in code, in an unbalanced
        # code span, team, after punctuation, as HTML entity, e-mail address
        texts = ["by @dewagter in paparazzi/paparazzi#3273", "'@user' \"@user\" (@user) cc:@user @user.",
                 "`@user`", "see `foo @user` and ` @user", "@org/team", "&#64;user &#x40;user &commat;user &#064;user",
                 "@@user", "dev@example.com"]
        for text in texts:
            out = no_notifications(text)
            self.assertNotIn("@", out, text)
            self.assertNotRegex(out.lower(), r"&(#0*64|#x0*40|commat)", text)

    def test_references(self):
        self.assertEqual(no_notifications("paparazzi/paparazzi#3273, #12, # 7, ## What's Changed"),
                         "paparazzi/paparazzi\uff033273, \uff0312, \uff03 7, ## What's Changed")

    def test_readable_and_urls_untouched(self):
        self.assertEqual(no_notifications("parachute by @dewagter"), "parachute by \uff20dewagter")
        url = "https://github.com/paparazzi/paparazzi/pull/3273"
        self.assertEqual(no_notifications(url), url)


class ReadOnlyTests(unittest.TestCase):
    def test_reject_write_methods_without_network(self):
        for method in ("POST", "PATCH", "PUT", "DELETE"):
            with self.subTest(method=method), self.assertRaises(ValueError):
                request("https://example.invalid", method, {})

if __name__ == "__main__":
    unittest.main()
