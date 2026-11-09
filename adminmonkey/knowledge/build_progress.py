"""Generate a transparent public-safe AdminMonkey Academy progress snapshot.

Never equate crawled documents, baseline or keyword screening with certified skill.
"""
import json
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent
ACADEMY = ROOT.parent / "academy"
TRACKS = ["MS-102", "AZ-104", "SC-300", "MD-102"]

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
        status = doc.get("review_status", "unreviewed")
        counts[status if status in counts else "unreviewed"] += 1
    scenarios = bank.get("scenarios", [])
    tracks = []
    for track in TRACKS:
        questions = sum(s.get("track") == track for s in scenarios)
        tracks.append({"track": track, "practice_scenarios_available": questions,
                       "agent_answers_evaluated": 0, "verified_passes": 0,
                       "badge_status": "not_earned", "competency_percent": None})
    public_docs = [{"url": d.get("url"), "sha256": d.get("sha256"), "captured_at": d.get("captured_at"), "depth": d.get("depth"), "review_status": d.get("review_status", "unreviewed")} for d in docs.values() if d.get("url", "").startswith("https://learn.microsoft.com/en-us/")]
    (ROOT / "document_manifest.json").write_text(json.dumps({"schema_version": 1, "documents": public_docs}, ensure_ascii=False, indent=2) + "\\n", encoding="utf-8")
    return {"generated_at": datetime.now(timezone.utc).isoformat(),
            "product": "AdminMonkey Academy", "certification_issuer": "ODPC",
            "official_microsoft_certification": False,
            "knowledge": {"documents_collected": len(docs), "review": counts,
                          "last_batch": state.get("last_batch", {})},
            "tracks": tracks,
            "notice": "Collection is not learning; practice question count is not a competence score. No Banana Badge until independent agent responses are assessed."}

if __name__ == "__main__":
    result = build()
    output = ROOT / "academy_progress.json"
    output.write_text(json.dumps(result, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps({"documents_collected": result["knowledge"]["documents_collected"],
                      "tracks": len(result["tracks"]), "badges_awarded": 0}))
