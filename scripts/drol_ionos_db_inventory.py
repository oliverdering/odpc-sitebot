"""One-shot IONOS-local MariaDB schema inventory. No credentials written to server or Git."""
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
    $raw = file_get_contents('php://input', false, null, 0, 8193);
    if ($raw === false || strlen($raw) > 8192) throw new RuntimeException('request_invalid');
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
    $pdo->exec("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
    $pdo->exec("START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY");
    $measured = $pdo->query("SELECT DATE_FORMAT(UTC_TIMESTAMP(), '%Y-%m-%dT%H:%i:%sZ')")->fetchColumn();
    $tables = $pdo->query("SHOW TABLES LIKE 'drol_%'")->fetchAll(PDO::FETCH_COLUMN);
    $inventory = [];
    foreach ($tables as $table) {
        if (!preg_match('/^drol_[a-z0-9_]+$/', $table)) throw new RuntimeException('table_invalid');
        $escaped = str_replace('`', '``', $table);
        $count = (int)$pdo->query("SELECT COUNT(*) FROM `".$escaped."`")->fetchColumn();
        $ddl = $pdo->query("SHOW CREATE TABLE `".$escaped."`")->fetch(PDO::FETCH_NUM)[1];
        $inventory[] = ['name'=>$table, 'rows'=>$count, 'schema_sha256'=>hash('sha256',$ddl)];
    }
    $levels = array_fill_keys(['E0','E1','E2','E3'], 0);
    foreach (['drol_model_facts','drol_capability_evidence','drol_knowledge_relations'] as $name) {
        if (!in_array($name, $tables, true)) continue;
        foreach ($pdo->query("SELECT evidence_level, COUNT(*) FROM `$name` GROUP BY evidence_level") as [$level,$count]) {
            if (!array_key_exists($level, $levels)) throw new RuntimeException('invalid_level');
            $levels[$level] += (int)$count;
        }
    }
    $covered = (int)$pdo->query('SELECT COUNT(DISTINCT hardware_id) FROM drol_model_facts')->fetchColumn();
    $dated = (int)$pdo->query("SELECT COUNT(*) FROM drol_sources WHERE checked_on IS NOT NULL")->fetchColumn();
    $linked = (int)$pdo->query("SELECT COUNT(DISTINCT source_id) FROM drol_source_snapshots")->fetchColumn();
    $pdo->exec("COMMIT");
    echo json_encode(['measured_at'=>$measured,'evidence'=>$levels,'models_with_facts'=>$covered,'sources_with_check_date'=>$dated,'sources_with_snapshot'=>$linked,'status'=>'connected','table_count'=>count($inventory),
        'inventory'=>$inventory, 'server_version'=>(string)$pdo->query('SELECT VERSION()')->fetchColumn()], JSON_THROW_ON_ERROR);
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
    name = "drol-inventory-" + secrets.token_hex(20) + ".php"
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
            req = Request(URL + "/" + name,
                data=json.dumps(payload).encode("utf-8"), method="POST",
                headers={"Content-Type":"application/json", "X-DROL-Probe":token})
            try:
                with urlopen(req, timeout=30) as response:
                    result = json.load(response)
            except HTTPError as exc:
                raise RuntimeError("IONOS-local database probe rejected (HTTP %s)" % exc.code) from None
            if result.get("status") != "connected":
                raise RuntimeError("IONOS-local database probe did not connect")
            print("D.R.O.L. IONOS-local schema inventory: PASS")
            for item in result.get("inventory", []):
                print("TABLE", item["name"], "ROWS", item["rows"], "SCHEMA_SHA256", item["schema_sha256"])
            print("TOTAL TABLES", result.get("table_count"))
            print("DATABASE VERSION", result.get("server_version"))
            if not any(item["name"] == "drol_metadata_inbox" for item in result.get("inventory", [])):
                raise RuntimeError("Metadata inbox missing")
            from pathlib import Path
            counts = {item["name"]: item["rows"] for item in result["inventory"]}
            public = {"measured_at": result["measured_at"],
                      "metadata_inbox": counts["drol_metadata_inbox"],
                      "candidate_facts_total": counts["drol_candidate_facts"],
                      "model_facts_total": counts["drol_model_facts"],
                      "source_records": counts["drol_sources"],
                      "hardware_models": counts["drol_hardware"],
                      "manufacturers": counts["drol_manufacturers"],
                      "hardware_source_links": counts["drol_hardware_sources"],
                      "firmware_releases": counts["drol_firmware_releases"],
                      "source_snapshots": counts["drol_source_snapshots"],
                      "attribute_definitions": counts["drol_attributes"]}
            for metric, table in {
                "knowledge_entities": "drol_knowledge_entities",
                "knowledge_relations": "drol_knowledge_relations",
                "knowledge_sources": "drol_knowledge_sources",
                "capability_evidence": "drol_capability_evidence",
                "aliases": "drol_entity_aliases",
                "release_batches": "drol_release_batches",
                "change_log": "drol_change_log",
                "source_watches": "drol_source_watch",
            }.items():
                if table in counts:
                    public[metric] = counts[table]
            public.update(table_count=result["table_count"], evidence=result["evidence"],
                          models_with_facts=result["models_with_facts"],
                          sources_with_check_date=result["sources_with_check_date"],
                          sources_with_snapshot=result["sources_with_snapshot"])
            Path("drol-knowledge-metrics.json").write_text(json.dumps(public, indent=2)+"\n", encoding="utf-8")
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
