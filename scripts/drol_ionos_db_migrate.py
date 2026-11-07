"""One-shot additive D.R.O.L. schema migration after successful backup. No credentials written to server or Git."""
import base64, hashlib, hmac, json, os, secrets, sys
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

ROOT = "/drol"
URL = "https://drol.odpc.de"
PHP = r"""<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !hash_equals('__TOKEN_HASH__', hash('sha256', $_SERVER['HTTP_X_DROL_PROBE'] ?? ''))) {
    http_response_code(404); exit;
}
try {
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if ($raw === false || strlen($raw) > 65536) throw new RuntimeException('request_invalid');
    $cfg = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    foreach (['host','database','username','password'] as $key)
        if (!isset($cfg[$key]) || !is_string($cfg[$key]) || $cfg[$key] === '')
            throw new RuntimeException('config_invalid');
    $port = (int)($cfg['port'] ?? 3306);
    if ($port < 1 || $port > 65535) throw new RuntimeException('port_invalid');
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $cfg['host'], $port, $cfg['database']);
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 8
    ]);
    $statements = $cfg['statements'] ?? null;
    if (!is_array($statements) || count($statements) !== 10) throw new RuntimeException('statement_count_invalid');
    $before = $pdo->query("SHOW TABLES LIKE 'drol_%'")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['drol_hardware','drol_hardware_sources','drol_manufacturers','drol_sources'];
    foreach ($required as $table) if (!in_array($table,$before,true)) throw new RuntimeException('baseline_missing');
    $expected = [
        'drol_model_facts','drol_capability_evidence','drol_attributes',
        'drol_entity_aliases','drol_source_snapshots','drol_candidate_facts',
        'drol_firmware_releases','drol_source_watch','drol_release_batches','drol_change_log'
    ];
    foreach ($statements as $i => $sql) {
        if (!is_string($sql) || !preg_match('/^CREATE\\s+TABLE\\s+IF\\s+NOT\\s+EXISTS\\s+(drol_[a-z0-9_]+)\\s*\\(/i',$sql,$match)
            || strtolower($match[1]) !== $expected[$i]) throw new RuntimeException('statement_not_allowlisted');
    }
    foreach ($statements as $sql) $pdo->exec($sql);
    $after = $pdo->query("SHOW TABLES LIKE 'drol_%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach (array_merge($required,$expected) as $table)
        if (!in_array($table,$after,true)) throw new RuntimeException('post_migration_missing_table');
    echo json_encode(['status'=>'migrated','table_count'=>count($after),
        'new_tables'=>$expected],JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['status'=>'unavailable','reason'=>'connection_or_query_failed']);
}
"""

def main():
    password = os.environ.get("DROL_DB_PASSWORD")
    sftp_password = os.environ.get("ODPC_SFTP_PASSWORD")
    if not password or not sftp_password:
        raise RuntimeError("Required GitHub Actions secrets are missing")
    token = secrets.token_urlsafe(48)
    name = "drol-migration-" + secrets.token_hex(20) + ".php"
    script = PHP.replace("__TOKEN_HASH__", hashlib.sha256(token.encode()).hexdigest())
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=25)
    uploaded = False
    sf = None
    try:
        fingerprint = "SHA256:" + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(fingerprint, PIN):
            raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER, sftp_password)
        sf = paramiko.SFTPClient.from_transport(transport)
        target = ROOT + "/" + name
        with sf.open(target, "wx") as f:
            uploaded = True
            f.write(script)
        sf.chmod(target, 0o644)
        try:
            req = Request(URL + "/" + name, data=b"{}", method="POST")
            try:
                with urlopen(req, timeout=20):
                    raise RuntimeError("Unauthenticated probe unexpectedly accepted")
            except HTTPError as exc:
                if exc.code != 404:
                    raise RuntimeError("Probe access control failed") from None
            payload = {
                "host": "db5021788879.hosting-data.io", "port": 3306,
                "database": "dbs16292022", "username": "dbu1397392",
                "password": password
            }
            from pathlib import Path
            import re
            statements = []
            for filename in ("002_evidence_model.sql", "003_knowledge_core.sql"):
                sql = (Path("site-deploy/drol.odpc.de/database") / filename).read_text(encoding="utf-8")
                sql = "\n".join(line for line in sql.splitlines() if not line.lstrip().startswith("--"))
                statements.extend(s.strip() for s in sql.split(";") if s.strip())
            if len(statements) != 10:
                raise RuntimeError("Unexpected migration statement count")
            payload["statements"] = statements
            req = Request(URL + "/" + name,
                data=json.dumps(payload).encode("utf-8"), method="POST",
                headers={"Content-Type":"application/json", "X-DROL-Probe":token})
            try:
                with urlopen(req, timeout=30) as response:
                    result = json.load(response)
            except HTTPError as exc:
                raise RuntimeError("IONOS-local database probe rejected (HTTP %s)" % exc.code) from None
            if result.get("status") != "migrated":
                raise RuntimeError("IONOS-local database probe did not connect")
            if result.get("status") != "migrated" or result.get("table_count") != 14:
                raise RuntimeError("Migration verification failed")
            print("PASS: additive D.R.O.L. migrations 002 and 003 applied; tables:", result["table_count"])
            print("Verified new tables:", ", ".join(result["new_tables"]))
        finally:
            # Deletion is attempted regardless of HTTP success.
            pass
    finally:
        if sf is not None:
            try:
                if uploaded:
                    sf.remove(ROOT + "/" + name)
                    print("Temporary probe endpoint removed")
            finally:
                sf.close()
        transport.close()

if __name__ == "__main__":
    main()
