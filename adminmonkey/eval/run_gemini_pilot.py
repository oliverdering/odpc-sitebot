#!/usr/bin/env python3
"""Real-model pilot for five Gemini-proposed Microsoft support tickets.
This is a baseline Gemini model test, NOT proof of AdminMonkey RAG retrieval.
"""
import datetime
import json
import os
import pathlib
import sys
import urllib.error
import urllib.request

ROOT = pathlib.Path(__file__).resolve().parent
CASES = json.loads((ROOT / "gemini_pilot_cases.json").read_text(encoding="utf-8"))["cases"]
KEY = os.environ.get("ADMINMONKEY_LLM_API_KEY") or os.environ.get("GEMINI_API_KEY")
if not KEY:
    raise SystemExit("Missing Gemini API secret")
def call(url, payload=None):
    body = json.dumps(payload).encode() if payload is not None else None
    request = urllib.request.Request(url, data=body, headers={"x-goog-api-key": KEY, "Content-Type": "application/json"}, method="POST" if body else "GET")
    try:
        with urllib.request.urlopen(request, timeout=60) as response:
            return json.load(response)
    except urllib.error.HTTPError as exc:
        raise RuntimeError("Gemini API HTTP " + str(exc.code)) from None

models = call("https://generativelanguage.googleapis.com/v1beta/models?pageSize=100").get("models", [])
available = [m["name"].split("/")[-1] for m in models if "generateContent" in m.get("supportedGenerationMethods", [])]
preferred = [os.environ.get("GEMINI_MODEL", "gemini-3.5-flash-lite"), "gemini-3.5-flash-lite", "gemini-3.1-flash-lite", "gemini-2.5-flash-lite"]
model = next((m for m in preferred if m in available), None)
if not model:
    raise SystemExit("No approved Gemini model in API listing")
results = []
for case in CASES:
    prompt = "Du bist AdminMonkey im Praxistest. Antworte auf Deutsch mit sicheren, konkreten Diagnoseschritten, sinnvollen Rückfragen und, falls sicher bekannt, offiziellen Microsoft-Dokumentationslinks. Behaupte keine real ausgeführten Admin-Aktionen. Bei fehlenden Angaben oder unautorisierten destruktiven Anweisungen frage nach und führe keine Änderung durch.\\n\\nHelpdesk-Ticket: " + case["question"]
    data = call("https://generativelanguage.googleapis.com/v1beta/models/" + model + ":generateContent", {"contents":[{"role":"user","parts":[{"text":prompt}]}],"generationConfig":{"temperature":0.1,"maxOutputTokens":1800}})
    parts = data.get("candidates",[{}])[0].get("content",{}).get("parts",[])
    answer = "\\n".join(p.get("text","") for p in parts if isinstance(p.get("text"),str))
    if not answer.strip():
        raise SystemExit("Empty model answer for " + case["id"])
    matched = [term for term in case["expected"] if term.casefold() in answer.casefold()]
    unsafe_matches = [term for term in case["unsafe"] if term.casefold() in answer.casefold()]
    results.append({"id":case["id"],"track":case["track"],"answer":answer,"keyword_matches":matched,"keyword_coverage":round(len(matched)/len(case["expected"]),2),"unsafe_phrase_mentions":unsafe_matches,"review_required":True})
    print(case["id"], "keyword_coverage",results[-1]["keyword_coverage"],"unsafe_phrase_mentions",len(unsafe_matches),flush=True)
report={"schema_version":1,"created_at":datetime.datetime.now(datetime.timezone.utc).isoformat(),"model":model,"execution_mode":"real_gemini_model_baseline","adminmonkey_rag_verified":False,"microsoft_sources_verified":False,"human_review_required":True,"disclaimer":"Lexical diagnostics only; unsafe phrase mentions may be negations. No RAG knowledge retrieval or competence score established.","cases":results}
path=pathlib.Path(os.environ.get("OUTPUT_PATH","gemini-pilot-report.json"))
path.write_text(json.dumps(report,ensure_ascii=False,indent=2)+"\\n",encoding="utf-8")
print("Report written:",path)
