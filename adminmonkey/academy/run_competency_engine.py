"""Run AdminMonkey practice scenarios through an OpenAI-compatible chat endpoint.

Never equate keyword screening with independent competence assessment.
"""
import argparse
import datetime as dt
import hashlib
import json
import os
import sys
import time
import urllib.error
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent
from evaluate_responses import evaluate


def generate(prompt, *, endpoint, api_key, model, timeout=45):
    if not endpoint.startswith("https://"):
        raise ValueError("API endpoint must use HTTPS")
    if not api_key or not model:
        raise ValueError("API key and model are required")
    payload = {"model": model, "temperature": 0, "messages": [
        {"role": "system", "content": "You are AdminMonkey, a cautious Microsoft enterprise administrator. Answer with safe, actionable diagnostic steps, verification, rollback and least privilege. Never claim to have executed actions."},
        {"role": "user", "content": prompt}]}
    req = urllib.request.Request(endpoint, data=json.dumps(payload).encode(),
        headers={"Authorization": "Bearer " + api_key, "Content-Type": "application/json"}, method="POST")
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            data = json.load(resp)
    except urllib.error.HTTPError as exc:
        # Provider error bodies explain 404 (unknown model vs route). Never echo raw body or credentials.
        try:
            detail = json.loads(exc.read(8192).decode('utf-8', 'replace'))
            error = detail.get('error', {})
            code = error.get('status', '') if isinstance(error, dict) else ''
            message = error.get('message', '') if isinstance(error, dict) else ''
            safe = ''.join(ch for ch in str(message) if ch.isalnum() or ch in ' .,;:()_-')[:160]
            # Provider text can include user-supplied input; avoid exposing anything beyond known error codes.
            safe_status = str(code) if str(code) in ('NOT_FOUND', 'INVALID_ARGUMENT', 'PERMISSION_DENIED', 'RESOURCE_EXHAUSTED', 'UNAUTHENTICATED', 'FAILED_PRECONDITION') else 'UNSPECIFIED'
            raise ValueError(f'Gemini HTTP {exc.code}; provider_status={safe_status}; provider_message={safe}') from None
        except (UnicodeError, json.JSONDecodeError, AttributeError, TypeError):
            raise ValueError(f'Gemini HTTP {exc.code}; provider returned non-JSON error') from None
    answer = data["choices"][0]["message"]["content"]
    if not isinstance(answer, str) or not answer.strip():
        raise ValueError("Model returned empty or non-text response")
    return answer


def run(bank, responder, *, model, limit=0):
    scenarios = bank["scenarios"][:limit or None]
    answers, errors = {}, {}
    for item in scenarios:
        try:
            answers[item["id"]] = responder(item["prompt"])
        except (ValueError, KeyError, IndexError, TimeoutError, urllib.error.URLError, OSError) as exc:
            errors[item["id"]] = type(exc).__name__ + ": " + str(exc)[:240]
    report = evaluate({"scenarios": scenarios}, answers)
    report.update({
        "schema_version": 1,
        "created_at": dt.datetime.now(dt.timezone.utc).isoformat(),
        "model": model,
        "bank_sha256": hashlib.sha256(json.dumps(scenarios, sort_keys=True).encode()).hexdigest(),
        "errors": errors,
        "answers": answers,
        "evaluation_disclaimer": "Lexical screening only; NOT independent agent assessment or Microsoft certification.",
        "competency_percent": None,
        "execution_mode": "model" if model != "offline-smoke-test-no-model" else "offline_smoke",
        "model_invoked": model != "offline-smoke-test-no-model",
    })
    return report


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--output", default="adminmonkey-competency-report.json")
    parser.add_argument("--limit", type=int, default=0)
    parser.add_argument("--dry-run", action="store_true", help="Offline transport smoke test; never generates real AI answers")
    args = parser.parse_args()
    if args.limit < 0:
        parser.error("--limit must be nonnegative")
    bank = json.loads((ROOT / "competency_bank.json").read_text(encoding="utf-8"))
    if args.dry_run:
        model = "offline-smoke-test-no-model"
        responder = lambda prompt: "DRY RUN: no AI model invoked; no competence evidence."
    else:
        endpoint = os.environ.get("ADMINMONKEY_LLM_ENDPOINT", "")
        key = os.environ.get("ADMINMONKEY_LLM_API_KEY", "")
        model = os.environ.get("ADMINMONKEY_LLM_MODEL", "")
        if not endpoint.startswith("https://") or not key or not model:
            parser.error("Set ADMINMONKEY_LLM_ENDPOINT (HTTPS), ADMINMONKEY_LLM_API_KEY and ADMINMONKEY_LLM_MODEL")
        responder = lambda prompt: generate(prompt, endpoint=endpoint, api_key=key, model=model)
    report = run(bank, responder, model=model, limit=args.limit)
    if args.dry_run:
        report["answered"] = 0
        report["passed"] = 0
        report["answers"] = {}
        report["results"] = []
        report["smoke_scenarios_checked"] = len(bank["scenarios"][:args.limit or None])
    Path(args.output).write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps({k: report[k] for k in ("model", "answered", "total", "passed", "errors", "competency_percent")}, ensure_ascii=False))
    if report["errors"]:
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(main())
