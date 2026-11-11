#!/usr/bin/env python3
"""Controlled GitHub Issue -> Gemini API -> GitHub Issue response bridge."""
import json
import os
import sys
import urllib.error
import urllib.request

REPO = os.environ["GITHUB_REPOSITORY"]
ISSUE = int(os.environ.get("TASK_ISSUE", "219"))
if ISSUE != 219:
    raise SystemExit("Pilot is restricted to approved Issue #219")
TOKEN = os.environ["GH_TOKEN"]
KEY = os.environ.get("GEMINI_API_KEY", "")
MODEL = os.environ.get("GEMINI_MODEL", "gemini-2.5-flash")
if not KEY:
    raise SystemExit("GEMINI_API_KEY secret is missing; no external request sent")
if not MODEL.replace("-", "").replace(".", "").isalnum():
    raise SystemExit("Invalid model ID")
API = "https://api.github.com/repos/" + REPO + "/issues/" + str(ISSUE)
HEADERS = {"Authorization": "Bearer " + TOKEN, "Accept": "application/vnd.github+json", "User-Agent": "ODPC-AdminMonkey-Gemini-Pilot"}
def request(url, data=None, headers=None, method=None):
    h = dict(headers or {})
    body = None if data is None else json.dumps(data).encode("utf-8")
    req = urllib.request.Request(url, data=body, headers=h, method=method or ("POST" if body else "GET"))
    with urllib.request.urlopen(req, timeout=55) as response:
        return json.load(response)
issue = request(API, headers=HEADERS)
body = issue.get("body", "")
if "ACAD-R04-GEMINI-REALWORLD-001" not in body:
    raise SystemExit("Expected task ID missing; refusing to run")
comments = request(API + "/comments?per_page=100", headers=HEADERS)
if any("[GEMINI-API-PILOT-ACK:ACAD-R04-GEMINI-REALWORLD-001]" in c.get("body", "") for c in comments):
    print("Already acknowledged; idempotent exit")
    sys.exit(0)
prompt = """Du bist die API-basierte Forschungsrolle Claire/Gemini im ODPC Helper Monkey Projekt. Dies ist NICHT die bisherige persönliche Gemini-Chat-Sitzung.
Erstelle zunächst genau 5 praxisnahe Microsoft-Admin-Tickets aus den Bereichen Entra, Intune, M365 und Azure. Schreibe auf Deutsch. Zu jedem Ticket: eindeutige ID, Symptom, benötigte Rückfragen, sichere Diagnose, Microsoft Learn URL, und 0-5 Bewertungsrubrik. URLs nur angeben, wenn du sie verlässlich kennst; sonst 'Quelle zu prüfen'. KEINE Behauptung, dass du Foren live recherchiert hast, sofern kein Recherchetool verfügbar ist. Keine personenbezogenen Daten, keine schädlichen oder destruktiven Schritte. Maximal 5000 Zeichen. TASK-ID: ACAD-R04-GEMINI-REALWORLD-001."""
payload = {"contents": [{"role": "user", "parts": [{"text": prompt + "\n\nAuftrag-Auszug:\n" + body[:6000]}]}], "generationConfig": {"maxOutputTokens": 1800, "temperature": 0.3}}
url = "https://generativelanguage.googleapis.com/v1beta/models/" + MODEL + ":generateContent"
try:
    response = request(url, payload, {"x-goog-api-key": KEY, "Content-Type": "application/json"}, "POST")
except urllib.error.HTTPError as exc:
    # Do not expose response body: it may include sensitive provider metadata.
    raise SystemExit("Gemini API request failed with HTTP " + str(exc.code))
parts = response.get("candidates", [{}])[0].get("content", {}).get("parts", [])
answer = "\n".join(p.get("text", "") for p in parts if isinstance(p, dict)).strip()
if len(answer) < 100:
    raise SystemExit("Gemini returned no substantive answer; no ACK posted")
comment = ("[GEMINI-API-PILOT-ACK:ACAD-R04-GEMINI-REALWORLD-001]\n"
           "**Sender:** Gemini API (" + MODEL + ") · **Empfänger:** Oxana · **Status:** API response received, human review required\n"
           "**Wichtig:** Kein Nachweis einer autonomen Claire-Chat-Sitzung, keine geprüfte Forenrecherche.\n\n"
           "### Erste fünf Praxisfälle (ungeprüfter Entwurf)\n\n" + answer[:7500] +
           "\n\n**Abnahme:** Quellen und Rubriken vor Import unabhängig prüfen.")
result = request(API + "/comments", {"body": comment}, HEADERS | {"Content-Type": "application/json"}, "POST")
print("Posted response comment:", result.get("html_url", "(URL unavailable)"))
