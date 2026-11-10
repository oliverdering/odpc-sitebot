"""Isolated retrieval staging tests; no network, database or secrets."""
import hashlib
import unittest
from chunk_documents import build, chunk_document


def document(text, url="https://learn.microsoft.com/en-us/entra/identity/"):
    return {"url": url, "text": text, "sha256": hashlib.sha256(text.encode()).hexdigest(),
            "captured_at": "2026-11-09T00:00:00+00:00"}


class ChunkDocumentsTests(unittest.TestCase):
    def test_chunk_overlap_and_provenance(self):
        doc = document(" ".join(f"word{i}" for i in range(12)))
        chunks = chunk_document(doc, max_words=6, overlap_words=2)
        self.assertEqual(len(chunks), 3)
        self.assertEqual(chunks[0]["text"].split()[-2:], chunks[1]["text"].split()[:2])
        self.assertEqual(chunks[1]["text"].split()[-2:], chunks[2]["text"].split()[:2])
        self.assertTrue(all(c["source_sha256"] == doc["sha256"] for c in chunks))
        self.assertEqual(chunks, chunk_document(doc, max_words=6, overlap_words=2))

    def test_reject_modified_or_untrusted_documents(self):
        good = document("valid source text")
        self.assertEqual(chunk_document({**good, "text": "tampered"}), [])
        self.assertEqual(chunk_document(document("text", "https://learn.microsoft.com.evil.test/en-us/")), [])
        self.assertEqual(chunk_document(document("text", "http://learn.microsoft.com/en-us/")), [])
        self.assertEqual(chunk_document({**good, "text": ""}), [])

    def test_missing_cached_text_is_not_claimed_as_chunked(self):
        good = document("some valid source text")
        missing = {k: v for k, v in good.items() if k != "text"}
        result = build({"documents": {"one": good, "two": missing}})
        self.assertEqual(result["source_documents"], 2)
        self.assertEqual(result["chunked_documents"], 1)
        self.assertEqual(result["rejected"]["untrusted_or_missing_text"], 1)

    def test_normalizes_whitespace_consistently_with_crawler_digest(self):
        canonical = "Microsoft Learn Entra ID role assignments"
        doc = document(canonical)
        doc["text"] = "Microsoft  Learn\nEntra ID\trole assignments"
        chunks = chunk_document(doc, max_words=20, overlap_words=2)
        self.assertEqual(len(chunks), 1)
        self.assertEqual(chunks[0]["text"], canonical)

    def test_invalid_window(self):
        with self.assertRaises(ValueError):
            chunk_document(document("words"), max_words=5, overlap_words=5)


if __name__ == "__main__":
    unittest.main()
