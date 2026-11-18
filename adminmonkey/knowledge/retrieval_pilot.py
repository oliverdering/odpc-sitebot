"""Read-only local retrieval pilot. No model calls, writes, or live endpoint.

Pass a private crawl-state JSON exported by the existing workflow. Returned
extracts are evidence, not independently evaluated answers or security advice.
"""
import argparse
import json
import math
import re
from collections import Counter
from pathlib import Path
from chunk_documents import build

TOKEN = re.compile(r"[a-z0-9][a-z0-9_-]{2,}", re.I)
STOP = frozenset("the and for with from this that are how what when which where does can die der das und mit für ist wie welche einer sind".split())


def terms(text):
    return [t for t in TOKEN.findall(text.casefold()) if t not in STOP]


def retrieve(question, chunks, limit=5):
    if not isinstance(question, str) or not question.strip() or len(question) > 1000:
        raise ValueError("question must contain 1 to 1000 characters")
    if not 1 <= limit <= 10:
        raise ValueError("limit must be between 1 and 10")
    query = set(terms(question))
    if len(query) < 2:
        return []
    docs = [(chunk, Counter(terms(chunk.get("text", "")))) for chunk in chunks]
    freq = Counter(term for _, counts in docs for term in query if term in counts)
    hits = []
    for chunk, counts in docs:
        matched = query.intersection(counts)
        if len(matched) < 2:
            continue
        score = sum((1 + math.log(counts[t])) * (1 + math.log((len(docs) + 1) / (freq[t] + 1))) for t in matched)
        hits.append((score, chunk))
    hits.sort(key=lambda item: (-item[0], item[1]["source_url"], item[1]["chunk_index"]))
    return [{"source_url": c["source_url"], "source_sha256": c["source_sha256"],
             "chunk_sha256": c["chunk_sha256"], "chunk_index": c["chunk_index"],
             "captured_at": c.get("captured_at"), "text": c["text"],
             "matched_terms": sorted(query.intersection(terms(c["text"]))) }
            for _, c in hits[:limit]]


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--state", required=True, help="private crawler state JSON; never commit")
    parser.add_argument("--question", required=True)
    parser.add_argument("--limit", type=int, default=5)
    args = parser.parse_args()
    state = json.loads(Path(args.state).read_text(encoding="utf-8"))
    staged = build(state)
    hits = retrieve(args.question, staged["chunks"], args.limit)
    print(json.dumps({"mode": "extractive_pilot_not_a_model_answer", "question": args.question,
        "source_documents": staged["source_documents"], "chunked_documents": staged["chunked_documents"],
        "rejected": staged["rejected"], "matches": hits,
        "notice": "Unreviewed search results; a match does not prove an answer."
                  if hits else "No evidence found; do not infer an answer."}, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
