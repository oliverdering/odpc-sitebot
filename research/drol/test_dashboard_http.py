"""HTTP integration tests for D.R.O.L. localhost-only dashboard."""
import http.client
import json
import threading
import unittest
from http.server import ThreadingHTTPServer

import local_dashboard as dashboard
from bridge_core import OfflineBridge

class DashboardHTTPTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.server = ThreadingHTTPServer(("127.0.0.1", 0), dashboard.Handler)
        cls.port = cls.server.server_address[1]
        cls.thread = threading.Thread(target=cls.server.serve_forever, daemon=True)
        cls.thread.start()

    @classmethod
    def tearDownClass(cls):
        cls.server.shutdown()
        cls.server.server_close()
        cls.thread.join(timeout=3)

    def setUp(self):
        with dashboard.LOCK:
            dashboard.bridge = OfflineBridge(dashboard.FakeAdapter(), dashboard.time.monotonic, timeout_seconds=30)

    def request(self, method, path, payload=None, headers=None):
        conn = http.client.HTTPConnection("127.0.0.1", self.port, timeout=3)
        body = None if payload is None else json.dumps(payload)
        h = {"Content-Type": "application/json"} if body is not None else {}
        h.update(headers or {})
        conn.request(method, path, body=body, headers=h)
        response = conn.getresponse()
        data = response.read()
        result = (response.status, data, dict(response.getheaders()))
        conn.close()
        return result

    def test_html_is_local_dashboard(self):
        code, body, _ = self.request("GET", "/")
        self.assertEqual(code, 200)
        self.assertIn(b"OFFLINE-SIMULATION", body)

    def test_status_profiles(self):
        code, body, _ = self.request("GET", "/api/status")
        data = json.loads(body)
        self.assertEqual(code, 200)
        self.assertTrue(data["simulation_only"])
        self.assertEqual(data["state"], "disconnected")
        self.assertEqual(len(data["profiles"]), 4)
        self.assertEqual(sum(1 for p in data["profiles"] if p["control"]), 1)

    def test_valid_state_sequence(self):
        for action in ("connect", "arm", "takeoff", "land", "disconnect"):
            code, body, _ = self.request("POST", "/api/action", {"action": action})
            self.assertEqual(code, 200, (action, body))
        code, body, _ = self.request("GET", "/api/status")
        self.assertEqual(json.loads(body)["state"], "disconnected")

    def test_reject_unarmed_takeoff(self):
        code, _, _ = self.request("POST", "/api/action", {"action": "takeoff"})
        self.assertEqual(code, 400)

    def test_reject_unknown_action(self):
        code, _, _ = self.request("POST", "/api/action", {"action": "enable_hardware"})
        self.assertEqual(code, 400)

    def test_missing_json_content_type(self):
        code, _, _ = self.request("POST", "/api/action", {"action": "connect"},
                                  {"Content-Type": "text/plain"})
        self.assertEqual(code, 415)

    def test_unknown_route(self):
        code, _, _ = self.request("GET", "/api/hardware")
        self.assertEqual(code, 404)

if __name__ == "__main__":
    unittest.main()
