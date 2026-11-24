<?php
/** Run against a disposable local MariaDB after applying catalog/evidence/core migrations. */
require __DIR__.'/drol_reviewed_facts.php';

$dsn = getenv('DROL_FACT_TEST_DSN');
if (!is_string($dsn) || !preg_match('/^mysql:(?:unix_socket=[^;]+|host=127\.0\.0\.1(?:;port=[0-9]+)?);dbname=drol_test;charset=utf8mb4$/D', $dsn)) {
    throw new RuntimeException('Expected an isolated local drol_test database');
}
$options = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false];
$connect = static function () use ($dsn,$options): PDO {
    return new PDO($dsn, getenv('DROL_FACT_TEST_USER') ?: 'root', getenv('DROL_FACT_TEST_PASSWORD') ?: '', $options);
};
$pdo = $connect();
$facts = json_decode(stream_get_contents(STDIN), true, 32, JSON_THROW_ON_ERROR);
function check(bool $ok, string $test): void {
    if (!$ok) throw new RuntimeException($test);
}
$maker = $pdo->prepare("INSERT INTO drol_manufacturers(name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
$add = $pdo->prepare("INSERT INTO drol_hardware(manufacturer_id,model,category) VALUES (?,?,'camera_drone') ON DUPLICATE KEY UPDATE id=id");
foreach ($facts as $row) {
    $maker->execute([$row['manufacturer']]);
    $add->execute([(int)$pdo->lastInsertId(),$row['name']]);
}
$dry = drol_reviewed_facts($pdo,$facts,false);
check($dry['planned']===count($facts) && $dry['after']===0, 'dry run');
check((int)$pdo->query('SELECT COUNT(*) FROM drol_sources')->fetchColumn()===0, 'dry run sources');
$bad = $facts;
$bad[count($bad)-1]['name'] = 'absent model';
try {
    drol_reviewed_facts($pdo,$bad,true);
    throw new RuntimeException('accepted missing hardware');
} catch (RuntimeException $e) {
    check(str_starts_with($e->getMessage(),'ambiguous_or_absent_hardware'), 'missing hardware');
}
foreach (['drol_model_facts','drol_sources','drol_change_log','drol_hardware_sources'] as $table) {
    check((int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn()===0, 'rollback '.$table);
}
$other = $connect();
$other->query("SELECT GET_LOCK('drol.reviewed_model_facts',0)")->fetchColumn();
try {
    drol_reviewed_facts($pdo,$facts,true);
    throw new RuntimeException('accepted concurrent writer');
} catch (RuntimeException $e) {
    check($e->getMessage()==='model_facts_busy','concurrency');
} finally {
    $other->query("SELECT RELEASE_LOCK('drol.reviewed_model_facts')")->fetchColumn();
}
$first = drol_reviewed_facts($pdo,$facts,true);
$repeat = drol_reviewed_facts($pdo,$facts,true);
check($first['created']===count($facts) && $first['after']===count($facts),'apply');
check($repeat['created']===0 && $repeat['existing']===count($facts),'repeat');
check((int)$pdo->query('SELECT COUNT(*) FROM drol_change_log')->fetchColumn()===count($facts),'audit');
$bad = $facts;
$bad[0]['value_text'] = 'contradictory value';
try {
    drol_reviewed_facts($pdo,$bad,true);
    throw new RuntimeException('accepted conflicting fact');
} catch (RuntimeException $e) {
    check(str_starts_with($e->getMessage(),'model_fact_requires_review'), 'conflict');
}
echo "PASS: dry run, full rollback, lock contention, apply, audit, idempotency, conflict\n";
