"""AdminMonkey demo API. Synthetic data only; no Microsoft credentials."""
from datetime import datetime, timezone
from uuid import uuid4
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field

app = FastAPI(title="ODPC AdminMonkey", version="0.3.0", docs_url="/docs")
TENANT_ID = "odpc-demo"
TENANTS = {TENANT_ID: {"id": TENANT_ID, "name": "ODPC DEMO", "synthetic": True}}
AUDITS = {}

class TenantCreate(BaseModel):
    name: str = Field(min_length=1, max_length=100)

@app.get("/api/health")
def health():
    return {"status": "ok", "version": "0.3.0", "mode": "synthetic", "microsoft_connected": False}

@app.get("/api/tenants")
def tenants():
    return list(TENANTS.values())

@app.post("/api/tenants", status_code=201)
def add_tenant(data: TenantCreate):
    tid = str(uuid4())
    TENANTS[tid] = {"id": tid, "name": data.name, "synthetic": True}
    return TENANTS[tid]

@app.post("/api/audits/{tenant_id}", status_code=201)
def start_audit(tenant_id: str):
    if tenant_id not in TENANTS:
        raise HTTPException(404, "Tenant not found")
    audit_id = str(uuid4())
    AUDITS[audit_id] = {
        "id": audit_id, "tenant_id": tenant_id, "mode": "synthetic",
        "created_at": datetime.now(timezone.utc).isoformat(),
        "findings": [
            {"severity": "high", "title": "Example: MFA status requires verification", "verified": False},
            {"severity": "medium", "title": "Example: guest accounts require review", "verified": False},
            {"severity": "info", "title": "Example: unassigned licenses", "verified": False}
        ],
        "status": "complete"
    }
    return AUDITS[audit_id]

@app.get("/api/audits/{audit_id}")
def get_audit(audit_id: str):
    if audit_id not in AUDITS:
        raise HTTPException(404, "Audit not found")
    return AUDITS[audit_id]

@app.get("/api/audits/{audit_id}/report")
def report(audit_id: str):
    audit = get_audit(audit_id)
    return {"audit_id": audit_id, "synthetic": True, "title": "AdminMonkey Demo Audit",
            "summary": "No real tenant was inspected.", "findings": audit["findings"]}
