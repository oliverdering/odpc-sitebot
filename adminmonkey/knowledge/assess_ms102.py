"""Evidence-gated MS-102 practice assessment. Never generates or fabricates agent answers."""
import json
import re
import sys
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent
BANK = ROOT.parent / "academy" / "competency_bank.json"
OUT = ROOT / "assessment_results.json"

def assess(submissions, bank):
    scenarios = {s["id"]: s for s in bank["scenarios"] if s["track"] == "MS-102"}
    results = []
    for item in submissions:
        sid = item.get("scenario_id")
        if sid not in scenarios:
            raise ValueError("Unknown MS-102 scenario: " + str(sid))
        scenario = scenarios[sid]
        answer = item.get("answer", "")
        sources = item.get("sources", [])
        if not isinstance(answer, str) or not isinstance(sources, list):
            raise ValueError("Invalid answer or sources")
        normalized = re.sub(r"\\s+", " ", answer.casefold())
        required = [phrase for phrase in scenario["required"] if phrase.casefold() in normalized]
        unsafe = [phrase for phrase in scenario["unsafe"] if phrase.casefold() in normalized]
        valid_sources = [s for s in sources if isinstance(s, str) and s.startswith("https://learn.microsoft.com/")]
        complete = len(required) == len(scenario["required"]) and not unsafe and bool(valid_sources)
        results.append({
            "scenario_id": sid, "track": "MS-102",
            "required_matched": required, "required_total": len(scenario["required"]),
            "unsafe_detected": unsafe, "source_urls": valid_sources,
            "screening_pass": complete,
            "independent_semantic_review": "pending",
            "verified_competence": False
        })
    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "assessment_type": "keyword-and-source screening of externally supplied agent answers",
        "agent_answers_evaluated": len(results),
        "screening_passes": sum(r["screening_pass"] for r in results),
        "verified_passes": 0,
        "competency_percent": None,
        "results": results,
        "notice": "Keyword matching does not prove correctness, source support, or competence. Independent semantic and safety review is required."
    }

def main():
    if len(sys.argv) != 2:
        raise SystemExit("Usage: python assess_ms102.py submissions.json")
    payload = json.loads(Path(sys.argv[1]).read_text(encoding="utf-8"))
    if not isinstance(payload, list):
        raise SystemExit("Expected JSON array of {scenario_id, answer, sources}")
    result = assess(payload, json.loads(BANK.read_text(encoding="utf-8")))
    OUT.write_text(json.dumps(result, indent=2, ensure_ascii=False) + "\\n", encoding="utf-8")
    print(json.dumps({"answers": result["agent_answers_evaluated"], "screening_passes": result["screening_passes"], "verified_passes": 0}))

if __name__ == "__main__":
    main()
