"""Deterministic, source-attributed chunks from captured Microsoft Learn text.

No database writes or LLM calls. This is a staging format for retrieval, not
proof of semantic competence. Avoid publishing article text to public GitHub.
"""
import hashlib
import json
import re
from pathlib import Path
from urllib.parse import urlparse

ROOT = Path(__file__).resolve().parent
MAX_WORDS = 320
OVERLAP_WORDS = 40


def trusted_source(doc):
    url = doc.get("url", "")
    digest = doc.get("sha256", "")
    parsed = urlparse(url)
    return (parsed.scheme == "https" and parsed.hostname == "learn.microsoft.com"
            and parsed.path.startswith("/en-us/")
            and isinstance(digest, str) and len(digest) == 64
            and all(ch in "0123456789abcdefABCDEF" for ch in digest))


def chunk_document(doc, max_words=MAX_WORDS, overlap_words=OVERLAP_WORDS):
    """Return reproducible chunks; require text to match the captured digest."""
    if not trusted_source(doc):
        return []
    if not 0 <= overlap_words < max_words:
        raise ValueError("overlap_words must be smaller than max_words")
    raw = doc.get("text")
    if not isinstance(raw, str) or not raw.strip():
        return []
    normalized = re.sub(r"\\s+", " ", raw).strip()
    if hashlib.sha256(normalized.encode("utf-8")).hexdigest().lower() != doc["sha256"].lower():
        return []
    words = normalized.split(" ")
    result = []
    step = max_words - overlap_words
    for start in range(0, len(words), step):
        segment = words[start:start + max_words]
        if not segment:
            break
        text = " ".join(segment)
        result.append({
            "source_url": doc["url"],
            "source_sha256": doc["sha256"].lower(),
            "captured_at": doc.get("captured_at"),
            "chunk_index": len(result),
            "chunk_sha256": hashlib.sha256(text.encode("utf-8")).hexdigest(),
            "text": text,
            "word_count": len(segment),
        })
        if start + max_words >= len(words):
            break
    return result


def build(state):
    documents = state.get("documents", {})
    chunks = []
    rejected = {"untrusted_or_missing_text": 0, "digest_mismatch": 0}
    for doc in documents.values():
        if not trusted_source(doc) or not isinstance(doc.get("text"), str):
            rejected["untrusted_or_missing_text"] += 1
            continue
        doc_chunks = chunk_document(doc)
        if not doc_chunks:
            rejected["digest_mismatch"] += 1
            continue
        chunks.extend(doc_chunks)
    return {"schema_version": 1, "source_documents": len(documents),
            "chunked_documents": len({c["source_url"] for c in chunks}),
            "chunks": chunks, "rejected": rejected,
            "note": "Staging data only; not independently verified retrieval or competence."}


if __name__ == "__main__":
    state_path = ROOT / "crawl_state.json"
    state = json.loads(state_path.read_text(encoding="utf-8"))
    result = build(state)
    output = ROOT / "retrieval_chunks.private.json"
    output.write_text(json.dumps(result, ensure_ascii=False) + "\n", encoding="utf-8")
    print(json.dumps({key: result[key] for key in ("source_documents", "chunked_documents", "rejected") }
                     | {"chunks_total": len(result["chunks"])}))
