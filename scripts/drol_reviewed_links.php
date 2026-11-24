<?php
/** Import individually reviewed cross-references in a transaction owned by the caller. */
function drol_reviewed_links(PDO $pdo, array $items, string $revision): int
{
    if (!$pdo->inTransaction() || !preg_match('/^[a-f0-9]{40}$/D', $revision)) {
        throw new RuntimeException('Invalid review transaction');
    }
    $one = static function ($sql, $args) use ($pdo) {
        $st = $pdo->prepare($sql);
        $st->execute($args);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows)>1) throw new RuntimeException('Ambiguous reviewed identity');
        return $rows[0] ?? null;
    };
    $write = static function ($sql, $args) use ($pdo) {
        $st = $pdo->prepare($sql);
        $st->execute($args);
        return (int)$pdo->lastInsertId();
    };
    $changes = [];
    foreach ($items as $r) {
        $url = $r['url'];
        $kind = $r['kind'];
        $day = $r['checked_on'];
        $subject = $r['subject'];
        if ($kind === 'relation') {
            $s = $one('SELECT id FROM drol_knowledge_entities WHERE entity_key=?', [$subject]);
            $o = $one('SELECT id FROM drol_knowledge_entities WHERE entity_key=?', [$r['object']]);
            if (!$s || !$o) throw new RuntimeException('Missing graph entity');
            $old = $one("SELECT source_url,evidence_level,assessment FROM drol_knowledge_relations WHERE subject_id=? AND object_id=? AND predicate='compatible_with' FOR UPDATE", [$s['id'],$o['id']]);
            if ($old && $old !== ['source_url'=>$url,'evidence_level'=>'E1','assessment'=>'claimed']) throw new RuntimeException('Relation conflict');
            if (!$old) $changes[] = ['knowledge_relation',$write("INSERT INTO drol_knowledge_relations(subject_id,object_id,predicate,assessment,evidence_level,source_url,reviewed_at) VALUES (?,?,'compatible_with','claimed','E1',?,?)", [$s['id'],$o['id'],$url,$day]),$r];
            foreach ([$s,$o] as $entity) {
                $old = $one('SELECT evidence_level FROM drol_knowledge_sources WHERE entity_id=? AND source_url=? FOR UPDATE', [$entity['id'],$url]);
                if ($old && $old['evidence_level'] !== 'E1') throw new RuntimeException('Graph source conflict');
                if (!$old) $changes[] = ['knowledge_source',$write("INSERT INTO drol_knowledge_sources(entity_id,source_url,source_kind,evidence_level,checked_at) VALUES (?,?,'manufacturer','E1',?)", [$entity['id'],$url,$day]),$r];
            }
            continue;
        }
        $model = $one('SELECT h.id FROM drol_hardware h JOIN drol_manufacturers m ON m.id=h.manufacturer_id WHERE m.name=? AND h.model IN (?,?)', [$r['manufacturer'],$subject,$r['manufacturer'].' '.$subject]);
        if (!$model) throw new RuntimeException('Missing hardware');
        $source = $one('SELECT id FROM drol_sources WHERE BINARY url=? FOR UPDATE', [$url]);
        $sid = $source ? $source['id'] : $write("INSERT INTO drol_sources(title,url,source_type,checked_on) VALUES (?,?,'manufacturer',?)", [$r['title'],$url,$day]);
        $write('INSERT INTO drol_hardware_sources(hardware_id,source_id) VALUES (?,?) ON DUPLICATE KEY UPDATE source_id=VALUES(source_id)', [$model['id'],$sid]);
        if ($kind === 'alias') {
            $old = $one("SELECT hardware_id FROM drol_entity_aliases WHERE alias=? AND alias_type='model_code' FOR UPDATE", [$r['code']]);
            if ($old && (int)$old['hardware_id'] !== (int)$model['id']) throw new RuntimeException('Alias conflict');
            if (!$old) $changes[] = ['entity_alias',$write("INSERT INTO drol_entity_aliases(hardware_id,alias,alias_type) VALUES (?,?,'model_code')", [$model['id'],$r['code']]),$r];
        } elseif ($kind === 'capability') {
            $old = $one("SELECT evidence_level,assessment,source_id FROM drol_capability_evidence WHERE hardware_id=? AND capability='SDK_API' AND function_name=? FOR UPDATE", [$model['id'],$r['function']]);
            if ($old && ($old['evidence_level']!=='E1' || $old['assessment']!=='YES' || (int)$old['source_id']!==(int)$sid)) throw new RuntimeException('Capability conflict');
            if (!$old) $changes[] = ['capability_evidence',$write("INSERT INTO drol_capability_evidence(hardware_id,capability,assessment,sdk_line,function_name,evidence_level,source_id,source_locator,checked_on,checked_by,notes) VALUES (?,'SDK_API','YES','Drone REST API',?,'E1',?,?,?,'ODPC source review',?)", [$model['id'],$r['function'],$sid,$r['locator'],$day,$r['notes']]),$r];
        } else throw new RuntimeException('Unknown review kind');
    }
    if ($changes) {
        $batch = $write("INSERT INTO drol_release_batches(git_sha,fact_count,notes) VALUES (?,?,?)", [$revision,0,'Reviewed model codes, API capabilities, graph relations and source links. No model facts in this package.']);
        foreach ($changes as [$kind,$id,$record]) {
            $write("INSERT INTO drol_change_log(actor,action,object_type,object_id,after_json,release_batch_id) VALUES ('ODPC source review','insert',?,?,?,?)", [$kind,$id,json_encode($record,JSON_THROW_ON_ERROR),$batch]);
        }
    }
    return count($changes);
}
