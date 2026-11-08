"""Offline tests for bounded Microsoft Learn discovery."""
import unittest
from crawl_learn import allowed, crawl

class CrawlTests(unittest.TestCase):
    def test_domain_guard(self):
        self.assertTrue(allowed("https://learn.microsoft.com/en-us/graph/overview"))
        self.assertFalse(allowed("https://example.com/en-us/graph/overview"))
        self.assertFalse(allowed("http://learn.microsoft.com/en-us/graph/overview"))
    def test_no_external_link_crawl(self):
        html = b'<main><h1>Microsoft Graph</h1><p>' + b'Useful reference text. '*20 + b'</p><a href="https://evil.example/en-us/bad">bad</a></main>'
        result = crawl({"queue":[{"url":"https://learn.microsoft.com/en-us/graph/overview","depth":0}]},
                       lambda url:(url, html))
        self.assertEqual(len(result["documents"]), 1)
        self.assertEqual(result["last_batch"]["queued"], 0)
        self.assertEqual(next(iter(result["documents"].values()))["review_status"], "unreviewed")
if __name__ == "__main__":
    unittest.main()
