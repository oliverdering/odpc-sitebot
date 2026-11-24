"""One-shot IONOS-local MariaDB backup. No credentials written to server or Git."""
import base64, hashlib, hmac, json, os, secrets, sys
from urllib.request import Request, build_opener, HTTPRedirectHandler
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
    $db = $cfg['database'];
    $privileges = [];
    foreach ($pdo->query("SHOW GRANTS")->fetchAll(PDO::FETCH_COLUMN) as $grant) {
        if (preg_match('/^GRANT (.+?) ON (.+?) TO /i', $grant, $matches)
            && in_array(str_replace('\\_', '_', $matches[2]), ['*.*', '`'.$db.'`.*'], true))
            $privileges = array_merge($privileges, explode(', ', strtoupper($matches[1])));
    }
    if (!in_array('ALL PRIVILEGES', $privileges, true) &&
        array_diff(['SELECT','SHOW VIEW','TRIGGER','EVENT','ALTER ROUTINE'], $privileges))
        throw new RuntimeException('cannot_attest_object_visibility');
    $pdo->exec("SET SESSION time_zone='+00:00'");
    $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
    $pdo->exec("START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY");
    $inventory = static function () use ($pdo): array {
        foreach (['TRIGGERS'=>'TRIGGER_SCHEMA','ROUTINES'=>'ROUTINE_SCHEMA','EVENTS'=>'EVENT_SCHEMA'] as $name=>$column) {
            if ((int)$pdo->query("SELECT COUNT(*) FROM information_schema.$name WHERE $column=DATABASE()")->fetchColumn())
                throw new RuntimeException('unsupported_database_object');
        }
        $items = $pdo->query("SELECT TABLE_NAME, TABLE_TYPE, ENGINE FROM information_schema.TABLES
            WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME")->fetchAll();
        foreach ($items as [$name,$type,$engine]) {
            if ($type !== 'BASE TABLE' || $engine !== 'InnoDB' || !preg_match('/^[a-zA-Z0-9_]+$/D',$name))
                throw new RuntimeException('unsupported_table');
        }
        return $items;
    };
    $tables = $inventory();
    // Metadata locks survive until commit and fence concurrent ALTER or DROP operations.
    foreach ($tables as [$name]) $pdo->query("SELECT * FROM `$name` LIMIT 0")->closeCursor();
    $backup = [];
    foreach ($tables as [$name]) {
        $ddl = $pdo->query("SHOW CREATE TABLE `$name`")->fetch()[1];
        $cols = $pdo->query("SHOW FULL COLUMNS FROM `$name`")->fetchAll(PDO::FETCH_ASSOC);
        $columns = [];
        $projection = [];
        foreach ($cols as $col) {
            $c = $col['Field'];
            if (!preg_match('/^[a-zA-Z0-9_]+$/D',$c) || stripos($col['Extra'],'GENERATED') !== false ||
                stripos($col['Extra'],'INVISIBLE') !== false)
                throw new RuntimeException('unsupported_column');
            $columns[] = $c;
            $projection[] = "HEX(CAST(`$c` AS BINARY))";
        }
        $rows = $pdo->query("SELECT ".implode(',', $projection)." FROM `$name`")->fetchAll();
        $backup[] = ['name'=>$name,'ddl'=>$ddl,'columns'=>$columns,'rows'=>$rows];
    }
    if ($inventory() !== $tables) throw new RuntimeException('database_object_set_changed');
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    $schema = $pdo->query("SHOW CREATE DATABASE `".$db."`")->fetch()[1];
    $pdo->exec("COMMIT");
    echo json_encode(['status'=>'connected','version'=>$version,'database'=>$db,'database_ddl'=>$schema,
        'backup'=>$backup], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(503);
    $known = ['cannot_attest_object_visibility','unsupported_database_object','unsupported_table','unsupported_column','database_object_set_changed'];
    echo json_encode(['status'=>'unavailable','reason'=>in_array($e->getMessage(), $known, true) ? $e->getMessage() : 'connection_or_query_failed']);
}
"""

class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, *args):
        return None


def main():
    urlopen = build_opener(NoRedirect()).open
    password = os.environ.get("DROL_DB_PASSWORD")
    sftp_password = os.environ.get("ODPC_SFTP_PASSWORD")
    if not password or not sftp_password:
        raise RuntimeError("Required GitHub Actions secrets are missing")
    token = secrets.token_urlsafe(48)
    name = "drol-backup-" + secrets.token_hex(20) + ".php"
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
                with urlopen(req, timeout=120) as response:
                    result = json.load(response)
            except HTTPError as exc:
                reason = json.loads(exc.read(1024)).get("reason") if exc.code == 503 else None
                raise RuntimeError(f"Snapshot rejected (HTTP {exc.code}): {reason if reason in ('cannot_attest_object_visibility', 'unsupported_database_object', 'unsupported_table', 'unsupported_column', 'database_object_set_changed') else 'connection_or_query_failed'}") from None
            if result.get("status") != "connected":
                raise RuntimeError("IONOS-local database probe did not connect")
            from drol_backup_restore import protect_and_verify
            protect_and_verify(sf, result, password, sftp_password)
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
