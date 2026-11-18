import hashlib
import unittest
from chunk_documents import build
from retrieval_pilot import retrieve


def doc(text, url="https://learn.microsoft.com/en-us/entra/id"):
    return {"url": url, "text": text, "sha256": hashlib.sha256(text.encode()).hexdigest()}


class RetrievalPilotTests(unittest.TestCase):
    def test_real_query_has_traceable_unchanged_source(self):
        text = "Conditional Access policy administrators configure identity protections."
        result = build({"documents": {"a": doc(text)}})
        hits = retrieve("Conditional Access policy", result["chunks"])
        self.assertEqual(len(hits), 1)
        self.assertEqual(hits[0]["text"], text)
        self.assertEqual(hits[0]["chunk_sha256"], hashlib.sha256(text.encode()).hexdigest())
        self.assertTrue(hits[0]["source_url"].startswith("https://learn.microsoft.com/"))

    def test_unknown_query_has_no_evidence(self):
        chunks = build({"documents": {"a": doc("Conditional Access policy identity protection")}})["chunks"]
        self.assertEqual(retrieve("printer toner cartridge", chunks), [])

    def test_invalid_documents_cannot_become_evidence(self):
        forged = doc("Conditional Access policy", "https://learn.microsoft.com.attacker.invalid/en-us/x")
        tampered = {**doc("Conditional Access original"), "text": "Conditional Access policy"}
        chunks = build({"documents": {"forged": forged, "modified": tampered}})["chunks"]
        self.assertEqual(retrieve("Conditional Access", chunks), [])

    def test_input_limits(self):
        for q in ("", "x" * 1001):
            with self.assertRaises(ValueError): retrieve(q, [])
        with self.assertRaises(ValueError): retrieve("conditional access", [], 11)

    def test_stable_order_and_limit(self):
        a = doc("Conditional access policies for authentication", "https://learn.microsoft.com/en-us/z")
        b = doc("Conditional access policies for authentication", "https://learn.microsoft.com/en-us/a")
        chunks = build({"documents": {"z": a, "a": b}})["chunks"]
        self.assertEqual(retrieve("conditional access", chunks, 1)[0]["source_url"], b["url"])


if __name__ == "__main__": unittest.main()
