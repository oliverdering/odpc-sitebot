"""Tests for disabled-by-default D.R.O.L. protocol registry."""
import unittest
from adapter_registry import OfflineAdapterRegistry

class AdapterRegistryTests(unittest.TestCase):
    def setUp(self):
        self.registry = OfflineAdapterRegistry()

    def test_profiles_are_not_hardware_enabled(self):
        profiles = self.registry.profiles()
        self.assertEqual(len(profiles), 2)
        self.assertTrue(all(not p["transport_enabled"] for p in profiles))
        self.assertTrue(all(not p["flight_verified"] for p in profiles))

    def test_flow_ufo_preview(self):
        result = self.registry.encode_neutral_preview("flow-ufo")
        self.assertEqual(result["length"], 21)
        self.assertTrue(result["hex"].startswith("036614"))
        self.assertFalse(result["transmitted"])

    def test_lsrc_preview(self):
        result = self.registry.encode_neutral_preview("lsrc-s1s")
        self.assertEqual(result["length"], 125)
        self.assertTrue(result["hex"].startswith("ef027c"))
        self.assertFalse(result["transmitted"])

    def test_unknown_adapter_rejected(self):
        with self.assertRaises(ValueError):
            self.registry.encode_neutral_preview("unknown")

if __name__ == "__main__":
    unittest.main()
