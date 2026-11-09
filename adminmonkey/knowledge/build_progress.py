"""Generate a transparent public-safe AdminMonkey Academy progress snapshot.

Never equate crawled documents, baseline or keyword screening with certified skill.
"""
import json
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent
ACADEMY = ROOT.parent / "academy"
TRACKS = ["MS-102", "AZ-104", "SC-300", "MD-102"]
from urllib.parse import urlparse

TRACK_TOPICS = {
    "MS-102": ("microsoft-365", "exchange", "sharepoint", "microsoftteams", "purview", "copilot/microsoft-365"),
    "AZ-104": ("/azure/", "/powershell/azure/"),
    "SC-300": ("/entra/", "/identity/", "conditional-access"),
    "MD-102": ("/intune/", "/windows/client-management/", "/mem/"),
}

def manufacturer_verified(doc):
    """Official origin plus captured content digest; not an exam pass."""
    url = doc.get("url", "")
    parsed = urlparse(url)
    digest = doc.get("sha256", "")
    return (parsed.scheme == "https" and parsed.hostname == "learn.microsoft.com"
            and isinstance(digest, str) and len(digest) == 64
            and all(c in "0123456789abcdefABCDEF" for c in digest))

def mapped_tracks(doc):
    path = urlparse(doc.get("url", "")).path.lower()
    return [track for track, topics in TRACK_TOPICS.items() if any(topic in path for topic in topics)]


def read_json(path):
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except (FileNotFoundError, ValueError):
        return {}

def build():
    state = read_json(ROOT / "crawl_state.json")
    bank = read_json(ACADEMY / "competency_bank.json")
    docs = state.get("documents", {})
    counts = {"unreviewed": 0, "approved": 0, "rejected": 0}
    for doc in docs.values():
        if manufacturer_verified(doc):
            counts["approved"] += 1
        else:
            status = doc.get("review_status", "unreviewed")
            counts[status if status in counts else "unreviewed"] += 1
    scenarios = bank.get("scenarios", [])
    assessment = read_json(ROOT / "assessment_results.json")
    staging = read_json(ROOT / "retrieval_chunks.private.json")
    retrieval = {"chunked_documents": staging.get("chunked_documents", 0),
                 "chunks_prepared": len(staging.get("chunks", [])),
                 "retrieval_quality_verified": False,
                 "agent_answers_verified": 0,
                 "note": "Ephemeral, provenance-checked chunk preparation; no searchable production index yet."}
    tracks = []
    for track in TRACKS:
        questions = sum(s.get("track") == track for s in scenarios)
        screened_results = [x for x in assessment.get("results", []) if x.get("track") == track]
        verified_sources = [d for d in docs.values() if manufacturer_verified(d) and track in mapped_tracks(d)]
        tracks.append({"track": track, "manufacturer_verified_sources": len(verified_sources),
                       "knowledge_coverage": "source_count_only_not_learning_objective_coverage",
                       "practice_scenarios_available": questions,
                       "agent_answers_evaluated": len(screened_results), "verified_passes": 0,
                       "screening_passes": sum(x.get("screening_pass") is True for x in screened_results),
                       "badge_status": "not_earned", "competency_percent": None})
    public_docs = [{"url": d.get("url"), "sha256": d.get("sha256"), "captured_at": d.get("captured_at"), "depth": d.get("depth"), "review_status": "manufacturer_verified" if manufacturer_verified(d) else d.get("review_status", "unreviewed"), "mapped_tracks": mapped_tracks(d) if manufacturer_verified(d) else []} for d in docs.values() if d.get("url", "").startswith("https://learn.microsoft.com/en-us/")]
    (ROOT / "document_manifest.json").write_text(json.dumps({"schema_version": 2, "documents": public_docs, "seen": state.get("seen", []), "queue": state.get("queue", []), "last_batch": state.get("last_batch", {})}, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    return {"generated_at": datetime.now(timezone.utc).isoformat(),
            "product": "AdminMonkey Academy", "certification_issuer": "ODPC",
            "official_microsoft_certification": False,
            "knowledge": {"documents_collected": len(docs), "manufacturer_verified": counts["approved"], "review": counts,
                          "last_batch": state.get("last_batch", {})},
            "retrieval_staging": retrieval,
            "tracks": tracks,
            "assessment_status": "manufacturer_sources_mapped_competence_not_yet_verified",
            "notice": "Collection is not learning; practice question count is not a competence score. No Banana Badge until independent agent responses are assessed."}

if __name__ == "__main__":
    result = build()
    output = ROOT / "academy_progress.json"
    output.write_text(json.dumps(result, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps({"documents_collected": result["knowledge"]["documents_collected"],
                      "tracks": len(result["tracks"]), "badges_awarded": 0}))
