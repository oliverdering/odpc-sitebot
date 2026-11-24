<?php
declare(strict_types=1);

/** MariaDB DDL commits implicitly. Validate *all* statements before the first
 * CREATE. The three additive statements can be retried after an interruption. */
function drol_prepare_graph(PDO $pdo, array $statements): void
{
    $expected = [
        'c628a85d6ef570567af52260001f8d53bd100e9c6695b26c0ff72fce92b0fdb1',
        'f35151943ec38dc5e1895dbbde6c5bf44531d3902bb4337eab1889c5179006a7',
        '54ba573099a6b5543956cf62053bbd160240c3028d8f788a231b64a24f6eb96f'
    ];
    if ($pdo->inTransaction() || array_map(static fn($s) => hash('sha256', $s), $statements) !== $expected) {
        throw new RuntimeException('Unexpected graph migration or open transaction');
    }
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
}

/** Seed only the registry identities. Existing reviewed records and edges remain intact. */
function drol_import_graph(PDO $pdo, array $entities, bool $apply): array
{
    if ($pdo->inTransaction() || count($entities) === 0 || count($entities) > 500) {
        throw new RuntimeException('Invalid graph import state');
    }
    $types = ['drone','controller','flight_controller','firmware','flight_stack','protocol','sdk',
              'sensor','camera','companion_computer','software','accessory','use_case'];
    $seen = [];
    foreach ($entities as $e) {
        $key = $e['key'] ?? '';
        if (!is_string($key) || !preg_match('/^[a-z0-9][a-z0-9-]{0,189}$/D', $key) || isset($seen[$key])
            || !in_array($e['type'] ?? null, $types, true) || ($e['review_status'] ?? null) !== 'seed'
            || !is_string($e['name'] ?? null) || trim($e['name']) === '' || strlen($e['name']) > 250
            || !array_key_exists('manufacturer', $e)
            || ($e['manufacturer'] !== null && (!is_string($e['manufacturer']) || trim($e['manufacturer']) === '' || strlen($e['manufacturer']) > 190))) {
            throw new RuntimeException('Invalid or duplicate E0 graph seed');
        }
        $seen[$key] = true;
    }
    if ((int)$pdo->query("SELECT GET_LOCK('drol_e0_graph_seed', 10)")->fetchColumn() !== 1) {
        throw new RuntimeException('Graph import lock unavailable');
    }
    try {
        $pdo->beginTransaction();
        $count = static fn() => (int)$pdo->query("SELECT COUNT(*) FROM drol_knowledge_entities")->fetchColumn();
        $before = $count();
        $lookup = $pdo->prepare('SELECT entity_type,manufacturer,display_name FROM drol_knowledge_entities WHERE entity_key=?');
        $insert = $pdo->prepare("INSERT INTO drol_knowledge_entities(entity_key,entity_type,manufacturer,display_name,review_status) VALUES (?,?,?,?,'seed')");
        $audit = $pdo->prepare("INSERT INTO drol_change_log(actor,action,object_type,object_id,after_json)
            VALUES ('ODPC graph import','insert','knowledge_entity',?,?)");
        $missing = 0;
        foreach ($entities as $e) {
            $lookup->execute([$e['key']]);
            $old = $lookup->fetch(PDO::FETCH_NUM);
            $identity = [$e['type'], $e['manufacturer'], $e['name']];
            if ($old !== false) {
                if ($old !== $identity) {
                    throw new RuntimeException('Existing graph identity conflict: ' . $e['key']);
                }
                continue;
            }
            $missing++;
            if ($apply) {
                $insert->execute(array_merge([$e['key']], $identity));
                $audit->execute([$pdo->lastInsertId(), json_encode(['key'=>$e['key'], 'review_status'=>'seed', 'evidence'=>'E0'], JSON_THROW_ON_ERROR)]);
            }
        }
        $after = $count();
        $created = $apply ? $missing : 0;
        if ($after - $before !== $created) {
            throw new RuntimeException('Graph count mismatch');
        }
        $pdo->commit();
        return ['before'=>$before, 'after'=>$after, 'created'=>$created, 'existing'=>count($entities)-$missing, 'planned'=>$missing];
    } finally {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $pdo->query("SELECT RELEASE_LOCK('drol_e0_graph_seed')")->closeCursor();
    }
}
