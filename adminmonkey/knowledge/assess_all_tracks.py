"""Screen real, externally supplied AdminMonkey answers across all four tracks.
This is evidence screening, not an independent competence certification.
Usage: python adminmonkey/knowledge/assess_all_tracks.py submissions.json
"""
import json
import re
import sys
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urlparse

ROOT = Path(__file__).resolve().parent
BANK = ROOT.parent / "academy" / "competency_bank.json"
OUTPUT = ROOT / "assessment_results.json"

def assess(submissions, bank):
    scenarios = {s["id"]: s for s in bank["scenarios"]}
    results = []
    for item in submissions:
        sid = item.get("scenario_id")
        if sid not in scenarios:
            raise ValueError("Unknown scenario: " + str(sid))
        scenario = scenarios[sid]
        answer, sources = item.get("answer"), item.get("sources")
        if not isinstance(answer, str) or not isinstance(sources, list):
            raise ValueError("Each submission requires answer text and source list")
        normalized = re.sub(r"\s+", " ", answer.casefold())
        required = [term for term in scenario["required"] if term.casefold() in normalized]
        unsafe = [term for term in scenario["unsafe"] if term.casefold() in normalized]
        official = [u for u in sources if isinstance(u, str) and
                    urlparse(u).scheme == "https" and
                    urlparse(u).hostname == "learn.microsoft.com"]
        passed = len(required) == len(scenario["required"]) and not unsafe and bool(official)
        results.append({
            "scenario_id": sid, "track": scenario["track"],
            "required_matched": required, "required_total": len(scenario["required"]),
            "unsafe_detected": unsafe, "source_urls": official,
            "screening_pass": passed, "independent_semantic_review": "pending",
            "verified_competence": False
        })
    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "assessment_type": "keyword-and-source screening of real supplied agent answers",
        "agent_answers_evaluated": len(results),
        "screening_passes": sum(x["screening_pass"] for x in results),
        "verified_passes": 0, "competency_percent": None,
        "results": results,
        "notice": "Screening is not a passed competence exam; independent semantic review required."
    }

def main():
    if len(sys.argv) != 2:
        raise SystemExit("Usage: python assess_all_tracks.py submissions.json")
    submissions = json.loads(Path(sys.argv[1]).read_text(encoding="utf-8"))
    if not isinstance(submissions, list):
        raise ValueError("Expected a list of actual agent answers")
    result = assess(submissions, json.loads(BANK.read_text(encoding="utf-8")))
    OUTPUT.write_text(json.dumps(result, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps({"answers": result["agent_answers_evaluated"],
                      "screening_passes": result["screening_passes"], "verified_passes": 0}))

if __name__ == "__main__":
    main()
