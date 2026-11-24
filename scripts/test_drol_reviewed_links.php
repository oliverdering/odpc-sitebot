<?php
require __DIR__.'/drol_reviewed_links.php';
require __DIR__.'/drol_graph_import.php';
$dsn = getenv('DROL_FACT_TEST_DSN');
if (!str_starts_with($dsn ?: '', 'mysql:host=127.0.0.1;') || !str_contains($dsn,';dbname=drol_test;')) throw new RuntimeException('Expected disposable database');
$pdo = new PDO($dsn, 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$graph = json_decode(stream_get_contents(STDIN), true, 64, JSON_THROW_ON_ERROR);
drol_prepare_graph($pdo,$graph['statements']);
drol_import_graph($pdo,$graph['entities'],true);
$links = json_decode(file_get_contents(__DIR__.'/../site-deploy/drol.odpc.de/database/reviewed-links.json'),true,64,JSON_THROW_ON_ERROR);
foreach ($links as $r) {
    if (!isset($r['manufacturer'])) continue;
    $q=$pdo->prepare('INSERT INTO drol_manufacturers(name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
    $q->execute([$r['manufacturer']]);
    $id=$pdo->lastInsertId();
    $q=$pdo->prepare("INSERT INTO drol_hardware(manufacturer_id,model,category) VALUES (?,?,'camera_drone') ON DUPLICATE KEY UPDATE id=id");
    $q->execute([$id,$r['subject']]);
}
foreach (['preview','apply','repeat'] as $mode) {
    $pdo->beginTransaction();
    $n=drol_reviewed_links($pdo,$links,str_repeat('1',40));
    if (($mode==='repeat') !== ($n===0)) throw new RuntimeException('Unexpected repeat or missing inserts');
    if ($mode==='preview') $pdo->rollBack(); else $pdo->commit();
}
echo "PASS reviewed sources, relations, capabilities, aliases and batch replay\n";
