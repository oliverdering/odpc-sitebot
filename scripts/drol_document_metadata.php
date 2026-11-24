<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header('Content-Type: application/json');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !hash_equals('__TOKEN_HASH__', hash('sha256', $_SERVER['HTTP_X_DROL_PROBE'] ?? ''))) {
    http_response_code(404);
    exit;
}
try {
    $raw = file_get_contents('php://input', false, null, 0, 262145);
    if ($raw === false || strlen($raw) > 262144) throw new RuntimeException('size');
    $data = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
    $indexes = $data['indexes'];
    if (!is_array($indexes) || !count($indexes) || count($indexes) > 30) throw new RuntimeException('indexes');
    $pdo = new PDO('mysql:host='.$data['host'].';dbname='.$data['database'].';charset=utf8mb4',
        $data['username'], $data['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false]);
    if ((int)$pdo->query("SELECT GET_LOCK('drol-document-discovery',10)")->fetchColumn() !== 1)
        throw new RuntimeException('busy');
    $pdo->beginTransaction();
    $count = fn() => (int)$pdo->query('SELECT COUNT(*) FROM drol_metadata_inbox')->fetchColumn();
    $before = $count();
    $find = $pdo->prepare('SELECT id FROM drol_sources WHERE url=?');
    $source = $pdo->prepare("INSERT INTO drol_sources(title,url,source_type,checked_on) VALUES (?,?,'documentation_index',UTC_DATE())");
    $watch = $pdo->prepare("INSERT INTO drol_source_watch(source_id,watch_kind,interval_hours,enabled,last_checked_at,last_content_sha256)
        VALUES (?,'html',24,1,UTC_TIMESTAMP(),?) ON DUPLICATE KEY UPDATE last_checked_at=UTC_TIMESTAMP(),last_content_sha256=VALUES(last_content_sha256)");
    $checkWatch = $pdo->prepare("SELECT enabled FROM drol_source_watch WHERE source_id=? AND watch_kind='html'");
    $insert = $pdo->prepare("INSERT INTO drol_metadata_inbox(candidate_key,source_url,title,metadata_json) VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE candidate_key=candidate_key");
    $created = 0;
    $known = 0;
    $disabled = 0;
    foreach ($indexes as $index) {
        $base = parse_url($index['index']);
        if (($base['scheme'] ?? '') !== 'https' || empty($base['host']) ||
            !preg_match('/^[a-f0-9]{64}$/D', $index['digest']) || count($index['urls']) > 150)
            throw new RuntimeException('invalid index');
        $find->execute([$index['index']]);
        $ids = $find->fetchAll(PDO::FETCH_COLUMN);
        if (count($ids) > 1) throw new RuntimeException('ambiguous index');
        if (!$ids) {
            $source->execute([$index['name'], $index['index']]);
            $id = (int)$pdo->lastInsertId();
        } else {
            $id = (int)$ids[0];
        }
        $checkWatch->execute([$id]);
        $enabled = $checkWatch->fetchColumn();
        if ($enabled !== false && (int)$enabled === 0) { $disabled++; continue; }
        $watch->execute([$id, $index['digest']]);
        foreach ($index['urls'] as $url) {
            $target = parse_url($url);
            if (($target['scheme'] ?? '') !== 'https' || ($target['host'] ?? '') !== $base['host'] ||
                strlen($url) > 1000) throw new RuntimeException('invalid document URL');
            $title = $index['name'].' / '.basename($target['path'], '.html');
            $metadata = ['status'=>'pending', 'evidence'=>'E0', 'type'=>'document_reference',
                'found_on'=>$index['index'], 'license_reference'=>$index['license_url'], 'topic'=>$index['topic']];
            $insert->execute([hash('sha256',"document-reference\n".$url),$url,$title,json_encode($metadata,JSON_THROW_ON_ERROR)]);
            if ($insert->rowCount() === 1) $created++; else $known++;
        }
    }
    $after = $count();
    if ($after - $before !== $created) throw new RuntimeException('unexpected delta');
    $pdo->commit();
    echo json_encode(['status'=>'ok','inbox_before'=>$before,'inbox_after'=>$after,'created'=>$created,'existing'=>$known,'disabled_indexes'=>$disabled]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(503);
    echo json_encode(['status'=>'unavailable']);
}
