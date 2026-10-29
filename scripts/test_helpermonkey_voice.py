#!/usr/bin/env python3
"""Dependency-free regression checks for the WordPress voice module."""
from pathlib import Path
import re
import unittest

SOURCE = Path("site-deploy/ai.odpc.de/odpc-monkey-voice-interview.php")

class VoiceRegression(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.source = SOURCE.read_text(encoding="utf-8")

    def test_required_controls_exist(self):
        for control in ("odpc-voice-open", "odpc-voice-close", "odpc-voice-mic",
                        "odpc-voice-send", "odpc-voice-answer", "odpc-voice-stop",
                        "odpc-voice-reset", "odpc-voice-language"):
            with self.subTest(control=control):
                self.assertIn('id="'+control+'"', self.source)

    def test_accessible_text_fallback(self):
        for feature in ("SpeechRecognition", "tx('micUnsupported')",
                        "aria-live", "aria-labelledby", "Escape", "focus()"):
            with self.subTest(feature=feature):
                self.assertIn(feature, self.source)

    def test_copy_handoff_has_action_local_status(self):
        for feature in ("hm-guide-work-handoff", "aria-describedby",
                        "handoff.hidden=false", "handoff.setAttribute('role','status')"):
            with self.subTest(feature=feature):
                self.assertIn(feature, self.source)

    def test_privacy_and_safety(self):
        self.assertIn("Bitte nenne niemals Passwörter", self.source)
        self.assertIn("textContent", self.source)
        self.assertNotRegex(self.source, r"(?i)\b(?:eval|innerHTML|localStorage|sessionStorage)\s*[(=]")
        self.assertNotIn("fetch(", self.source)

    def test_stop_restart_and_permissions(self):
        for feature in ("recognition.abort()", "speechSynthesis.cancel()",
                        "odpc-voice-reset", "visibilitychange", "ticket!==session"):
            with self.subTest(feature=feature):
                self.assertIn(feature, self.source)

    def test_multilingual_release(self):
        for locale in ("de-DE", "en-US", "es-ES", "fr-FR", "it-IT", "pt-PT", "pl-PL", "tr-TR"):
            with self.subTest(locale=locale):
                self.assertIn('value="' + locale + '"', self.source)
                self.assertIn("'"+locale+"':{ui:", self.source)
        self.assertIn("const guide=window.MonkeyGuide", self.source)
        self.assertNotIn("questions.length", self.source)
        self.assertNotIn("questions[step]", self.source)

    def test_voice_selection_and_mobile_layout(self):
        for feature in ("getVoices()", "voiceschanged", "voiceCatalog",
                        "odpc-voice-gender", "odpc-voice-preview", "max-width:600px",
                        "odpc-voice-topclose", "el.replaceChildren(cb"):
            with self.subTest(feature=feature):
                self.assertIn(feature, self.source)

    def test_gender_filter_release(self):
        for feature in ("odpc-voice-gender", "voiceGender(v)", "refreshVoices()", "Männlich", "Weiblich"):
            with self.subTest(feature=feature):
                self.assertIn(feature, self.source)

    def test_wp_bootstrap(self):
        self.assertIn("if (!defined('ABSPATH')) exit;", self.source)
        self.assertIn("add_action('wp_footer'", self.source)

if __name__ == "__main__":
    unittest.main(verbosity=2)

