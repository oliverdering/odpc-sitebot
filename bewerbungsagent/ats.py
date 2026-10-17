"""Deterministic, explainable baseline for job/CV matching. No external dependencies."""
import re
from dataclasses import dataclass, asdict

def tokens(text):
    return set(re.findall(r"[\w+#.-]+", text.casefold(), flags=re.UNICODE))

@dataclass
class Criterion:
    name: str
    required: bool = False
    weight: float = 1.0

def analyze(cv_text, criteria):
    if not isinstance(cv_text, str):
        raise TypeError("cv_text must be a string")
    normalized = cv_text.casefold()
    outcomes = []
    total = 0.0
    matched = 0.0
    for criterion in criteria:
        if not isinstance(criterion, Criterion) or criterion.weight <= 0:
            raise ValueError("Invalid criterion")
        term = criterion.name.strip().casefold()
        if not term:
            raise ValueError("Empty criterion")
        # Phrase matching with token boundaries; do not infer synonyms or qualifications.
        found = bool(re.search(r"(?<!\w)" + re.escape(term) + r"(?!\w)", normalized))
        total += criterion.weight
        matched += criterion.weight if found else 0
        outcomes.append({"criterion": criterion.name, "required": criterion.required,
                         "matched": found, "weight": criterion.weight})
    return {"score": round(100 * matched / total, 1) if total else None,
            "criteria": outcomes,
            "missing_required": [o["criterion"] for o in outcomes if o["required"] and not o["matched"]],
            "disclaimer": "Keyword evidence only; not an ATS acceptance probability."}
