<?php
declare(strict_types=1);
// D.R.O.L. read-only catalog endpoint. Deployment and server credentials are not configured yet.
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}
$host = getenv('DROL_DB_HOST');
$name = getenv('DROL_DB_NAME');
$user = getenv('DROL_DB_USER');
$password = getenv('DROL_DB_PASSWORD');
if (!$host || !$name || !$user || $password === false) {
    http_response_code(503);
    echo json_encode(['error' => 'catalog_unavailable']);
    exit;
}
try {
    $dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4';
    $db = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $records = $db->query(
        'SELECT h.id, m.name AS manufacturer, h.model, h.category, h.hardware_revision, h.description, h.verification_status, h.last_verified
         FROM drol_hardware h JOIN drol_manufacturers m ON m.id=h.manufacturer_id
         ORDER BY m.name, h.model LIMIT 100'
    )->fetchAll();
    $sources = $db->query(
        'SELECT hs.hardware_id, s.title, s.url, s.source_type, s.checked_on
         FROM drol_hardware_sources hs JOIN drol_sources s ON s.id=hs.source_id
         ORDER BY hs.hardware_id, s.id'
    )->fetchAll();
    $byId = [];
    foreach ($records as $i => $row) {
        $records[$i]['id'] = (int) $row['id'];
        $records[$i]['sources'] = [];
        $byId[(int) $row['id']] = $i;
    }
    foreach ($sources as $source) {
        $id = (int) $source['hardware_id'];
        unset($source['hardware_id']);
        if (isset($byId[$id])) {
            $records[$byId[$id]]['sources'][] = $source;
        }
    }
    echo json_encode(['items' => $records, 'limit' => 100], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['error' => 'catalog_unavailable']);
}
