import unittest
from bridge_core import OfflineBridge, State

class FakeAdapter:
    def capabilities(self):
        return {'model': 'simulator', 'transport': 'offline'}

class Clock:
    def __init__(self):
        self.now = 0.0
    def __call__(self):
        return self.now

class BridgeTests(unittest.TestCase):
    def setUp(self):
        self.clock = Clock()
        self.bridge = OfflineBridge(FakeAdapter(), self.clock)

    def test_normal_cycle(self):
        b = self.bridge
        b.connect(); b.arm(); b.takeoff(); b.land(); b.disconnect()
        self.assertEqual(b.state, State.DISCONNECTED)
        self.assertEqual([e['event'] for e in b.events],
                         ['connected', 'simulated_arm', 'simulated_takeoff',
                          'simulated_landing_complete', 'disconnected'])

    def test_takeoff_requires_arm(self):
        self.bridge.connect()
        with self.assertRaises(RuntimeError):
            self.bridge.takeoff()

    def test_timeout_blocks_further_commands(self):
        b = self.bridge
        b.connect(); b.arm(); b.takeoff()
        self.clock.now = 2.1
        event = b.tick()
        self.assertEqual(b.state, State.LINK_LOST)
        self.assertEqual(event['aircraft_action'], 'unknown')
        with self.assertRaises(RuntimeError):
            b.land()
        with self.assertRaises(RuntimeError):
            b.heartbeat()
        b.disconnect(); b.connect()
        self.assertEqual(b.state, State.READY)

    def test_heartbeat_extends_timeout(self):
        b = self.bridge
        b.connect(); self.clock.now = 1.9; b.heartbeat()
        self.clock.now = 3.0
        self.assertIsNone(b.tick())
        self.assertEqual(b.state, State.READY)

    def test_expired_heartbeat_cannot_revive_link(self):
        b = self.bridge
        b.connect()
        self.clock.now = 2.1
        with self.assertRaises(RuntimeError):
            b.heartbeat()
        self.assertEqual(b.state, State.LINK_LOST)
        self.assertEqual(b.events[-1]['event'], 'link_timeout')

    def test_no_telemetry_claim(self):
        self.assertFalse(self.bridge.capabilities().telemetry)
        self.assertFalse(self.bridge.capabilities().verified_on_hardware)
        self.assertTrue(self.bridge.capabilities().simulation_only)

if __name__ == '__main__':
    unittest.main()
