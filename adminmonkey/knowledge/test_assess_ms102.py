import unittest
from assess_ms102 import assess

BANK = {"scenarios": [{"id":"MS102-1","track":"MS-102","required":["sign-in logs","MFA"],"unsafe":["disable audit logging"]}]}

class AssessmentTests(unittest.TestCase):
    def test_empty_is_not_competence(self):
        self.assertIsNone(assess([], BANK)["competency_percent"])
    def test_keyword_pass_is_not_verified(self):
        r = assess([{"scenario_id":"MS102-1","answer":"Review sign-in logs and MFA","sources":["https://learn.microsoft.com/en-us/entra/"]}], BANK)
        self.assertTrue(r["results"][0]["screening_pass"])
        self.assertEqual(r["verified_passes"], 0)
    def test_unsafe_fails(self):
        r = assess([{"scenario_id":"MS102-1","answer":"sign-in logs MFA disable audit logging","sources":["https://learn.microsoft.com/en-us/entra/"]}], BANK)
        self.assertFalse(r["results"][0]["screening_pass"])

if __name__ == "__main__":
    unittest.main()
