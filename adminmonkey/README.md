# ODPC AdminMonkey — mobile demo + API MVP 0.3

**Development-only proof of concept.** The mobile page uses synthetic data. The API uses in-memory data and has **no authentication, persistence, Microsoft Graph integration or tenant security isolation**. Never expose the API publicly or connect it to customer systems.

## Mobile preview
Open `mobile/index.html` in a browser. It performs no real Microsoft or Android checks.

## Local API development
```bash
cd adminmonkey/backend
python -m venv .venv
. .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --host 127.0.0.1 --port 8000
pytest -q
```
Visit http://127.0.0.1:8000/docs on the development machine.

## Next security gates
Implement database persistence, authenticated sessions, per-tenant authorization checks, explicit consent, encrypted token storage, and an allowlisted read-only Graph connector before any production deployment. Never place tokens in the mobile HTML or GitHub.
