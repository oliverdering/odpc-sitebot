"""AdminMonkey Academy: deterministic, offline, synthetic competency checks.

Run: python -m unittest discover -s adminmonkey/academy -p 'test_*.py'
No Microsoft credentials, network access, or customer data required.
"""
from __future__ import annotations
import unittest

COMPETENCIES = {
    "MS-102": ("Microsoft 365", "Exchange", "Teams", "SharePoint"),
    "AZ-104": ("Azure", "RBAC", "networking", "monitoring"),
    "SC-300": ("Entra ID", "MFA", "Conditional Access", "PIM"),
    "MD-102": ("Intune", "Windows", "compliance", "enrollment"),
    "SC-200": ("Defender", "Sentinel", "incident", "KQL"),
    "AZ-305": ("architecture", "resilience", "governance", "cost"),
}
SCENARIOS = [
    {"id": "licensing", "request": "Report unused licenses", "read_only": True, "requires_approval": False, "data_source": "tenant"},
    {"id": "mfa-rollout", "request": "Enforce MFA for all users", "read_only": False, "requires_approval": True, "data_source": "tenant"},
    {"id": "delete-user", "request": "Delete departing employee", "read_only": False, "requires_approval": True, "data_source": "tenant"},
    {"id": "copilot-oversharing", "request": "Audit Copilot access exposure", "read_only": True, "requires_approval": False, "data_source": "tenant"},
    {"id": "disable-legacy-auth", "request": "Disable legacy authentication", "read_only": False, "requires_approval": True, "data_source": "tenant"},
    {"id": "device-check", "request": "Check unmanaged Android phone", "read_only": True, "requires_approval": False, "data_source": "device"},
]

def gate(scenario: dict, *, tenant_connected: bool, approved: bool, device_enrolled: bool = False) -> dict:
    if scenario["data_source"] == "tenant" and not tenant_connected:
        return {"allowed": False, "reason": "tenant_not_connected"}
    if scenario["data_source"] == "device" and not device_enrolled:
        return {"allowed": False, "reason": "device_not_enrolled"}
    if scenario["requires_approval"] and not approved:
        return {"allowed": False, "reason": "approval_required"}
    return {"allowed": True, "reason": "eligible_for_authorized_workflow"}

class AcademySafetyTests(unittest.TestCase):
    def test_six_exam_tracks(self):
        self.assertEqual(len(COMPETENCIES), 6)
        self.assertTrue(all(len(topics) >= 4 for topics in COMPETENCIES.values()))

    def test_no_tenant_no_claim(self):
        for scenario in SCENARIOS:
            if scenario["data_source"] == "tenant":
                self.assertEqual(gate(scenario, tenant_connected=False, approved=True)["reason"], "tenant_not_connected")

    def test_changes_require_approval(self):
        for scenario in SCENARIOS:
            if not scenario["read_only"]:
                self.assertEqual(gate(scenario, tenant_connected=True, approved=False)["reason"], "approval_required")

    def test_read_only_audit_allowed_with_tenant(self):
        audit = next(s for s in SCENARIOS if s["id"] == "licensing")
        self.assertTrue(gate(audit, tenant_connected=True, approved=False)["allowed"])

    def test_unenrolled_device_not_scannable(self):
        device = next(s for s in SCENARIOS if s["id"] == "device-check")
        self.assertEqual(gate(device, tenant_connected=True, approved=True)["reason"], "device_not_enrolled")

    def test_all_scenarios_unique(self):
        self.assertEqual(len({s["id"] for s in SCENARIOS}), len(SCENARIOS))

if __name__ == "__main__":
    unittest.main()
