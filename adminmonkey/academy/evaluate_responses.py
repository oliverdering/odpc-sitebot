"""Evaluate supplied AdminMonkey responses against original scenario rubric.

Usage: python adminmonkey/academy/evaluate_responses.py responses.json
Input: {"answers":{"SC300-1":"...","AZ104-1":"..."}}
Missing answers score zero. Lexical rubric is only a screening gate, not expert grading.
"""
import json
import sys
from pathlib import Path

BANK = Path(__file__).with_name("competency_bank.json")

def evaluate(bank, answers):
    if not isinstance(answers, dict):
        raise ValueError("answers must be a JSON object")
    results = []
    for scenario in bank["scenarios"]:
        response = str(answers.get(scenario["id"], "")).casefold()
        matched = [term for term in scenario["required"] if term.casefold() in response]
        unsafe = [term for term in scenario["unsafe"] if term.casefold() in response]
        score = len(matched) / len(scenario["required"]) if scenario["required"] else 0.0
        results.append({"id": scenario["id"], "track": scenario["track"],
                        "coverage": round(score, 3), "missing": [x for x in scenario["required"] if x not in matched],
                        "unsafe_matches": unsafe, "pass": bool(response.strip()) and bool(scenario["required"]) and score >= .8 and not unsafe})
    return {"rubric": "lexical-screening-v2", "assessment_type": "keyword_screening_only",
            "independent_agent_assessment": False, "official_certification": False,
            "answered": sum(bool(str(answers.get(s["id"], "")).strip()) for s in bank["scenarios"]),
            "total": len(results), "passed": sum(x["pass"] for x in results), "results": results}

if __name__ == "__main__":
    bank = json.loads(BANK.read_text(encoding="utf-8"))
    answers = json.loads(Path(sys.argv[1]).read_text(encoding="utf-8")).get("answers", {}) if len(sys.argv) > 1 else {}
    print(json.dumps(evaluate(bank, answers), ensure_ascii=False, indent=2))
