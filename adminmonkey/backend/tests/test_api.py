from fastapi.testclient import TestClient
from app.main import app

client = TestClient(app)

def test_health_declares_demo():
    result = client.get("/api/health")
    assert result.status_code == 200
    assert result.json()["microsoft_connected"] is False

def test_audit_is_synthetic():
    result = client.post("/api/audits/odpc-demo")
    assert result.status_code == 201
    audit = result.json()
    assert audit["mode"] == "synthetic"
    assert all(not f["verified"] for f in audit["findings"])
    report = client.get(f'/api/audits/{audit["id"]}/report')
    assert report.json()["synthetic"] is True

def test_unknown_tenant_rejected():
    assert client.post("/api/audits/missing").status_code == 404

def test_create_tenant():
    result = client.post("/api/tenants", json={"name": "Test Customer"})
    assert result.status_code == 201
    assert result.json()["synthetic"] is True
