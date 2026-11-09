#!/usr/bin/env python3
"""Read-only IONOS-local MariaDB connectivity preflight; ephemeral endpoint removed."""
import base64, hashlib, hmac, json, os, secrets
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

PHP = """<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !hash_equals('__TOKEN_HASH__', hash('sha256', $_SERVER['HTTP_X_AMK_PROBE'] ?? ''))) {
    http_response_code(404); exit;
}
try {
    $raw = file_get_contents('php://input', false, null, 0, 8193);
    if ($raw === false || strlen($raw) > 8192) throw new RuntimeException('size');
    $p = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
    foreach (['host','name','user','password','port'] as $key) {
        if (!isset($p[$key]) || !is_string($p[$key]) || $p[$key] === '') throw new RuntimeException('config');
    }
    if (!ctype_digit($p['port'])) throw new RuntimeException('port');
    $pdo = new PDO('mysql:host='.$p['host'].';port='.$p['port'].';dbname='.$p['name'].';charset=utf8mb4',
        $p['user'], $p['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 8]);
    $ok = ((int)$pdo->query('SELECT 1')->fetchColumn() === 1);
    echo json_encode(['connected' => $ok, 'mode' => 'read_only_probe']);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['connected' => false, 'error_class' => get_class($e)]);
}
"""
def main():
    required = ['ODPC_SFTP_PASSWORD','AMK_DB_HOST','AMK_DB_NAME','AMK_DB_USER','AMK_DB_PASSWORD']
    missing = [k for k in required if not os.environ.get(k)]
    if missing: raise RuntimeError('Missing secret names: '+', '.join(missing))
    token = secrets.token_urlsafe(48)
    filename = 'amk-db-probe-'+secrets.token_hex(20)+'.php'
    source = PHP.replace('__TOKEN_HASH__', hashlib.sha256(token.encode()).hexdigest())
    transport = paramiko.Transport((HOST, 22))
    transport.start_client(timeout=25)
    sf = None
    uploaded = False
    try:
        actual = 'SHA256:'+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual, PIN): raise RuntimeError('SFTP host key mismatch')
        transport.auth_password(USER, os.environ['ODPC_SFTP_PASSWORD'])
        if not transport.is_authenticated(): raise RuntimeError('SFTP authentication refused')
        sf = paramiko.SFTPClient.from_transport(transport)
        # The existing D.R.O.L. webroot is proven by its established deployment pipeline.
        remote = '/drol/'+filename
        with sf.open(remote, 'wx') as handle:
            uploaded = True
            handle.write(source)
        sf.chmod(remote, 0o644)
        url = 'https://drol.odpc.de/'+filename
        try:
            with urlopen(Request(url, data=b'{}', method='POST'), timeout=15):
                raise RuntimeError('Unauthenticated probe was accepted')
        except HTTPError as exc:
            if exc.code != 404: raise RuntimeError('Probe access control failed') from None
        payload = {k:os.environ[v] for k,v in {
            'host':'AMK_DB_HOST','name':'AMK_DB_NAME','user':'AMK_DB_USER',
            'password':'AMK_DB_PASSWORD'}.items()}
        payload['port'] = os.environ.get('AMK_DB_PORT') or '3306'
        request = Request(url, data=json.dumps(payload).encode(), method='POST',
                          headers={'Content-Type':'application/json','X-AMK-Probe':token})
        try:
            with urlopen(request, timeout=25) as response: result = json.load(response)
        except HTTPError as exc:
            raise RuntimeError('IONOS-local MariaDB preflight failed, HTTP '+str(exc.code)) from None
        if result.get('connected') is not True: raise RuntimeError('IONOS-local MariaDB SELECT 1 not confirmed')
        print('PASS: IONOS-local AdminMonkey MariaDB SELECT 1 verified; no database writes performed')
    finally:
        if sf:
            try:
                if uploaded:
                    sf.remove('/drol/'+filename)
                    print('Ephemeral probe removed')
            finally: sf.close()
        transport.close()
if __name__ == '__main__': main()
