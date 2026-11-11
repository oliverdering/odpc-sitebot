"""Evidence-gated Microsoft certification competency scoring.

A score is unavailable until every official domain has independently graded,
scenario-linked evidence. Source counts and screening results never count.
"""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
REGISTRY = ROOT / "academy" / "verified_certification_registry_2026.json"
BANK = ROOT / "academy" / "competency_bank.json"
RESULTS = ROOT / "knowledge" / "assessment_results.json"
OUTPUT = ROOT / "knowledge" / "certification_competency.json"


def calculate(registry, bank, results):
    scenarios = {s["id"]: s for s in bank.get("scenarios", []) if s.get("id")}
    entries = []
    for cert in registry["certifications"]:
        domains = []
        for domain in cert.get("exam_domains", []):
            name = domain["name"]
            matching = [s for s in scenarios.values()
                        if s.get("track") in cert["exam_codes"] and s.get("exam_domain") == name]
            grades = {}
            for result in results.get("results", []):
                sid = result.get("scenario_id")
                if (sid not in scenarios or scenarios[sid] not in matching
                    or result.get("evaluation_type") != "independent_agent_assessment"
                    or not isinstance(result.get("passed"), bool)
                    or not isinstance(result.get("evidence_url"), str)
                    or not result["evidence_url"].startswith("https://")
                    or not result.get("grader_id")):
                    continue
                # A later failed retest invalidates a prior pass.
                grades[sid] = result["passed"]
            assessed = len(grades)
            total = len(matching)
            complete = total > 0 and assessed == total
            domains.append({"name": name, "exam_weight": domain["weight"],
                            "scenarios": total, "independently_evaluated": assessed,
                            "passed": sum(grades.values()), "coverage_complete": complete,
                            "competency_percent": round(100 * sum(grades.values()) / total, 1)
                            if complete else None})
        # Exam weights are ranges; do not fabricate a precise weighted score.
        ready = bool(domains) and all(d["coverage_complete"] for d in domains)
        entries.append({"id": cert["id"], "exam_codes": cert["exam_codes"],
                        "official_name": cert["official_name"],
                        "status": "assessed" if ready else "insufficient_evidence",
                        "competency_percent": round(sum(d["competency_percent"] for d in domains) / len(domains), 1)
                        if ready else None,
                        "score_basis": "unweighted_domain_average_not_official_exam_score" if ready else "not_scored",
                        "domains": domains})
    return {"schema_version": 1, "assessment_policy": "independent_grader_with_https_evidence_and_scenario_domain_mapping",
            "not_microsoft_certification": True, "certifications": entries}


def main():
    registry = json.loads(REGISTRY.read_text())
    bank = json.loads(BANK.read_text())
    results = json.loads(RESULTS.read_text()) if RESULTS.exists() else {}
    output = calculate(registry, bank, results)
    OUTPUT.write_text(json.dumps(output, ensure_ascii=False, indent=2) + "\n")
    print(json.dumps({"certifications": len(output["certifications"]),
                      "assessed": sum(c["status"] == "assessed" for c in output["certifications"])}))


if __name__ == "__main__":
    main()
