"""Idempotent AdminMonkey MariaDB migration and Microsoft Learn document import.

Credentials ONLY via GitHub Actions secrets or local environment variables.
Never writes to WordPress tables; never promotes unreviewed content.
Requires: pip install PyMySQL
"""
import argparse
import json
import os
from pathlib import Path
from urllib.parse import urlparse

import pymysql

BASE = Path(__file__).resolve().parents[1]
SCHEMA = Path(__file__).with_name("001_knowledge_schema.sql")
STATE = BASE / "knowledge" / "crawl_state.json"
DB_PROGRESS = BASE / "knowledge" / "database_progress.json"

def connect():
    keys = ("AMK_DB_HOST", "AMK_DB_NAME", "AMK_DB_USER", "AMK_DB_PASSWORD")
    missing = [key for key in keys if not os.environ.get(key)]
    if missing:
        raise RuntimeError("Missing secure DB environment variables: " + ", ".join(missing))
    return pymysql.connect(
        host=os.environ["AMK_DB_HOST"], port=int(os.environ.get("AMK_DB_PORT", "3306")),
        user=os.environ["AMK_DB_USER"], password=os.environ["AMK_DB_PASSWORD"],
        database=os.environ["AMK_DB_NAME"], charset="utf8mb4",
        connect_timeout=8, read_timeout=25, write_timeout=25, autocommit=False)

def migrate(conn):
    statements = [part.strip() for part in SCHEMA.read_text(encoding="utf-8").split(";")]
    with conn.cursor() as cur:
        for statement in statements:
            lines = [line for line in statement.splitlines() if not line.lstrip().startswith("--")]
            sql = "\n".join(lines).strip()
            if sql:
                if not sql.upper().startswith("CREATE TABLE IF NOT EXISTS AMK_"):
                    raise ValueError("Unexpected schema operation blocked")
                cur.execute(sql)
    conn.commit()

def import_documents(conn, state):
    docs = state.get("documents", {})
    imported = 0
    with conn.cursor() as cur:
        for item in docs.values():
            url = item.get("url", "")
            if (urlparse(url).scheme != "https" or urlparse(url).hostname != "learn.microsoft.com"
                    or not urlparse(url).path.startswith("/en-us/")):
                continue
            digest = item.get("sha256", "")
            body = item.get("text", "")
            if len(digest) != 64 or len(body) < 100:
                continue
            area = urlparse(url).path.split("/")[2] if len(urlparse(url).path.split("/")) > 2 else "general"
            cur.execute(
                "INSERT INTO amk_sources (canonical_url, product_area, last_checked) VALUES (%s,%s,UTC_TIMESTAMP()) "
                "ON DUPLICATE KEY UPDATE last_checked=UTC_TIMESTAMP()",
                (url, area[:80]))
            cur.execute("SELECT id FROM amk_sources WHERE canonical_url=%s LIMIT 1", (url,))
            row = cur.fetchone()
            if row is None:
                raise RuntimeError("Source not found after upsert")
            cur.execute(
                "INSERT IGNORE INTO amk_documents "
                "(source_id,content_sha256,title,content_text,review_status) "
                "VALUES (%s,%s,%s,%s,'unreviewed')",
                (row[0], digest, urlparse(url).path.rsplit("/", 1)[-1][:512] or url, body))
            imported += cur.rowcount
    conn.commit()
    return imported

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--apply", action="store_true", help="Explicitly allow schema and document writes")
    args = parser.parse_args()
    state = json.loads(STATE.read_text(encoding="utf-8")) if STATE.exists() else {}
    print(json.dumps({"mode": "apply" if args.apply else "dry-run",
                      "documents_available": len(state.get("documents", {})),
                      "schema": str(SCHEMA.name)}))
    if not args.apply:
        return
    with connect() as conn:
        migrate(conn)
        imported = import_documents(conn, state)
        with conn.cursor() as cur:
            cur.execute("SELECT COUNT(*) FROM amk_documents")
            total = cur.fetchone()[0]
        with conn.cursor() as cur:
            cur.execute("SELECT COUNT(*) FROM amk_sources")
            sources_total = cur.fetchone()[0]
            cur.execute("SELECT COUNT(*) FROM amk_documents WHERE review_status = %s", ("approved",))
            approved_total = cur.fetchone()[0]
        snapshot = {"database_connected": True, "new_document_versions": imported,
                    "document_versions_total": total, "sources_total": sources_total,
                    "approved_document_versions": approved_total,
                    "official_microsoft_certification": False}
        DB_PROGRESS.write_text(json.dumps(snapshot, indent=2) + "\n", encoding="utf-8")
        print(json.dumps(snapshot))

if __name__ == "__main__":
    main()
