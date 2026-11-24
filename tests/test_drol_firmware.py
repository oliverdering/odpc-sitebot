import copy
import hashlib
import json
from pathlib import Path
import sys
import tempfile
import unittest
from unittest.mock import patch

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
import drol_firmware as firmware


class FirmwareTest(unittest.TestCase):
    def setUp(self):
        self.documents = firmware.load_documents()
        self.document = copy.deepcopy(self.documents[0])
        self.row = self.document["releases"][0]

    def test_manifest_validation(self):
        for field, value in (("source_url", "https://example.org/fake.pdf"), ("evidence", "E2"), ("kind", "sdk")):
            with self.subTest(field=field), tempfile.TemporaryDirectory() as directory:
                bad = dict(self.document, **{field: value})
                path = Path(directory) / "manifest.json"
                path.write_text(json.dumps({"schema_version": 1, "documents": [bad]}))
                with self.assertRaises(ValueError):
                    firmware.load_documents(path)

    def test_match_aircraft_field_and_exclude_rc_and_app(self):
        page = ("DJI Mini 4 Pro Release Notes\nDate: 2025.11.05\nAircraft Firmware: v01.00.1100\n"
                "Remote Controller Firmware: v08.00.0000\nDJI Fly App: v1.19.0\n")
        with patch.object(firmware.subprocess, "run") as process:
            process.return_value.stdout = page.encode()
            self.assertEqual(firmware.extract_pages("Mini 4 Pro", "unused"), [self.row])
            with self.assertRaises(ValueError):
                firmware.extract_pages("Mini 3", "unused")

    def test_conflicting_manufacturer_dates_require_review(self):
        with tempfile.TemporaryDirectory() as directory:
            pdf = Path(directory) / "review.pdf"
            pdf.write_bytes(b"fixture")
            self.document.update(source_sha256=hashlib.sha256(pdf.read_bytes()).hexdigest(), releases=[self.row])
            rows = [self.row, dict(self.row, released_on="2025-11-06", page=2)]
            with patch.object(firmware, "extract_pages", return_value=rows), self.assertRaisesRegex(ValueError, "ambiguous"):
                firmware.verify_pdf(self.document, pdf)


if __name__ == "__main__":
    unittest.main()
