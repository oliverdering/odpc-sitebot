"""Safely seed D.R.O.L. editorial model registry into IONOS MariaDB (idempotent)."""
import base64
import hashlib
import hmac
import json
import os
from pathlib import Path
import secrets
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

ROOT = "/drol"
URL = "https://drol.odpc.de"
REGISTRY = Path("site-deploy/drol.odpc.de/database/model-schema.json")
PHP = r"""<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !hash_equals('__TOKEN_HASH__', hash('sha256', $_SERVER['HTTP_X_DROL_PROBE'] ?? ''))) {
    http_response_code(404); exit;
}
try {
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if ($raw === false || strlen($raw) > 65536) throw new RuntimeException('invalid_request');
    $input = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    foreach (['host','database','username','password'] as $key) {
        if (empty($input[$key]) || !is_string($input[$key])) throw new RuntimeException('invalid_config');
    }
    $models = $input['models'] ?? null;
    if (!is_array($models) || count($models) < 1 || count($models) > 100)
        throw new RuntimeException('invalid_models');
    $pdo = new PDO(sprintf('mysql:host=%s;port=3306;dbname=%s;charset=utf8mb4',
        $input['host'], $input['database']), $input['username'], $input['password'],
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES=>false]);
    $pdo->beginTransaction();
    $maker = $pdo->prepare('INSERT INTO drol_manufacturers(name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
    $hw = $pdo->prepare("INSERT INTO drol_hardware(manufacturer_id,model,category,verification_status) VALUES (?,?,?,'UNVERIFIED') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
    $findSource = $pdo->prepare('SELECT id FROM drol_sources WHERE url=? LIMIT 1');
    $addSource = $pdo->prepare("INSERT INTO drol_sources(title,url,source_type) VALUES (?,?,'manufacturer')");
    $link = $pdo->prepare('INSERT IGNORE INTO drol_hardware_sources(hardware_id,source_id) VALUES (?,?)');
    $count = 0;
    foreach ($models as $m) {
        if (!is_array($m)) throw new RuntimeException('invalid_model');
        foreach (['manufacturer','name','category'] as $k) {
            if (!isset($m[$k]) || !is_string($m[$k]) || trim($m[$k]) === '' || strlen($m[$k]) > 180)
                throw new RuntimeException('invalid_model_field');
        }
        $maker->execute([$m['manufacturer']]);
        $makerId = (int)$pdo->lastInsertId();
        $hw->execute([$makerId,$m['name'],$m['category']]);
        $hwId = (int)$pdo->lastInsertId();
        foreach (($m['sources'] ?? []) as $source) {
            if (!is_string($source) || strlen($source)>950 || !filter_var($source,FILTER_VALIDATE_URL)
                || parse_url($source,PHP_URL_SCHEME)!=='https') throw new RuntimeException('invalid_source');
            $findSource->execute([$source]);
            $sourceId = $findSource->fetchColumn();
            if (!$sourceId) {
                $addSource->execute([$m['manufacturer'].' / '.$m['name'],$source]);
                $sourceId = $pdo->lastInsertId();
            }
            $link->execute([$hwId,(int)$sourceId]);
        }
        $count++;
    }
    $pdo->commit();
    $total = (int)$pdo->query('SELECT COUNT(*) FROM drol_hardware')->fetchColumn();
    echo json_encode(['status'=>'seeded','processed'=>$count,'hardware_total'=>$total],JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(503);
    echo json_encode(['status'=>'unavailable']);
}
"""

def main():
    password = os.environ["DROL_DB_PASSWORD"]
    sftp_password = os.environ["ODPC_SFTP_PASSWORD"]
    registry = json.loads(REGISTRY.read_text(encoding="utf-8"))
    if registry.get("schema_version") != "1.0" or not isinstance(registry.get("models"), list):
        raise RuntimeError("Invalid registry")
    models = registry["models"]
    if not 1 <= len(models) <= 100:
        raise RuntimeError("Unexpected model count")
    token = secrets.token_urlsafe(48)
    name = "drol-seed-" + secrets.token_hex(20) + ".php"
    script = PHP.replace("__TOKEN_HASH__", hashlib.sha256(token.encode()).hexdigest())
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=25)
    sf = None
    uploaded = False
    try:
        actual = "SHA256:" + base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual, PIN):
            raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER, sftp_password)
        sf = paramiko.SFTPClient.from_transport(transport)
        target = ROOT + "/" + name
        with sf.open(target, "wx") as f:
            uploaded = True
            f.write(script)
        sf.chmod(target, 0o644)
        try:
            try:
                with urlopen(Request(URL+"/"+name,data=b"{}",method="POST"),timeout=20):
                    raise RuntimeError("Unauthenticated endpoint accepted")
            except HTTPError as exc:
                if exc.code != 404:
                    raise RuntimeError("Endpoint access control failed") from None
            payload = {
                "host":"db5021788879.hosting-data.io",
                "database":"dbs16292022",
                "username":"dbu1397392",
                "password":password,
                "models":models
            }
            req = Request(URL+"/"+name,data=json.dumps(payload).encode("utf-8"),
                method="POST",headers={"Content-Type":"application/json","X-DROL-Probe":token})
            try:
                with urlopen(req,timeout=35) as response:
                    result = json.load(response)
            except HTTPError as exc:
                raise RuntimeError("IONOS seed rejected HTTP "+str(exc.code)) from None
            if result.get("status") != "seeded" or result.get("processed") != len(models):
                raise RuntimeError("Database seeding was not confirmed")
            print("PASS: D.R.O.L. registry models imported:",result["processed"])
            print("Live hardware total:",result["hardware_total"])
        finally:
            pass
    finally:
        if sf is not None:
            try:
                if uploaded:
                    sf.remove(ROOT+"/"+name)
                    print("Temporary seed endpoint removed")
            finally:
                sf.close()
        transport.close()

if __name__ == "__main__":
    main()
