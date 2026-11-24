import json
from pathlib import Path
import sys
import unittest
from unittest.mock import patch
from urllib.error import HTTPError

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
import drol_document_discovery as docs
from drol_source_monitor import eligible_urls
from drol_release_watch import REPOS, SOFTWARE_CLASSES


class DiscoveryTests(unittest.TestCase):
    def setUp(self):
        self.sources = json.loads(docs.REGISTRY.read_text())["sources"]
        self.pdal = next(s for s in self.sources if s.get("name") == "PDAL point cloud input documentation")

    def test_metadata_only_internal_curated_https_paths(self):
        page = "".join(f'<a href="{href}">' for href in (
            "readers.las.html", "readers.las.html#limits", "readers.las.html?tracking=x",
            "https://foreign.example/readers.las.html", "http://pdal.io/en/stable/stages/readers.las.html",
            "readers.unapproved.html", "../license.html"))
        self.assertEqual(docs.index_urls(self.pdal, page), ["https://pdal.io/en/stable/stages/readers.las.html"])

    def test_fail_closed_robots(self):
        with patch.object(docs, "read_url", return_value="User-agent: *\nDisallow: /") as read:
            with self.assertRaisesRegex(ValueError, "robots denied"):
                docs.fetch_index(self.pdal, None)
            self.assertEqual(read.call_count, 1)
        with patch.object(docs, "read_url", side_effect=HTTPError("", 429, "", None, None)) as read:
            with self.assertRaises(HTTPError):
                docs.fetch_index(self.pdal, None)
            self.assertEqual(read.call_count, 1)

    def test_excluded_sources_even_in_catalog_and_watched_sources_reviewed(self):
        references = [s["url"] for s in self.sources if "ANAFI UKR" in s["name"]]
        self.assertEqual(len(references), 2)
        watched = eligible_urls([{"sources": references}], self.sources)
        self.assertTrue(set(watched).isdisjoint(references))
        for source in self.sources:
            if source.get("feed_type") == "html_index":
                self.assertEqual(source["status"], "monitor_only")
                self.assertFalse(docs.review_gate(source))
                self.assertEqual(source["evidence"], "E0")
        self.assertEqual(REPOS.keys(), SOFTWARE_CLASSES.keys())
