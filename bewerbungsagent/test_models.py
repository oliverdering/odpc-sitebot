import unittest
from models import Application, Status

class LifecycleTests(unittest.TestCase):
    def test_requires_approval_before_send(self):
        app = Application("job-1", "candidate-1")
        with self.assertRaises(ValueError):
            app.transition(Status.SENT, "attempt")
        app.transition(Status.APPROVED, "user approved")
        app.transition(Status.SENT, "sent by user")
        self.assertEqual(len(app.events), 2)

    def test_rejects_blank_reason(self):
        app = Application("job-1", "candidate-1")
        with self.assertRaises(ValueError):
            app.transition(Status.APPROVED, " ")
        self.assertEqual(app.status, Status.DRAFT)

    def test_terminal_rejection(self):
        app = Application("job-1", "candidate-1")
        for status in (Status.APPROVED, Status.SENT, Status.REJECTED):
            app.transition(status, "confirmed")
        with self.assertRaises(ValueError):
            app.transition(Status.INTERVIEW, "late message")

if __name__ == "__main__":
    unittest.main()
