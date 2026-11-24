<?php
/** Import source-reviewed documents only. The caller authenticates and creates PDO. */
function drol_reviewed_facts(PDO $pdo, array $facts, bool $apply): array {
    $lock = 'drol.reviewed_model_facts';
    $acquire = $pdo->prepare('SELECT GET_LOCK(?, 5)');
    $acquire->execute([$lock]);
    if ((int)$acquire->fetchColumn() !== 1) {
        throw new RuntimeException('model_facts_busy');
    }
    try {
        $pdo->beginTransaction();
        $count = static function () use ($pdo): int {
            return (int)$pdo->query('SELECT COUNT(*) FROM drol_model_facts')->fetchColumn();
        };
        $before = $count();
        $model = $pdo->prepare('SELECT h.id FROM drol_hardware h
            JOIN drol_manufacturers m ON m.id=h.manufacturer_id
            WHERE m.name=? AND h.model IN (?,?) FOR UPDATE');
        $source = $pdo->prepare('SELECT id FROM drol_sources WHERE BINARY url=? FOR UPDATE');
        $addSource = $pdo->prepare("INSERT INTO drol_sources(title,url,source_type,checked_on)
            VALUES (?,?,'manufacturer',?)");
        $link = $pdo->prepare('INSERT INTO drol_hardware_sources(hardware_id,source_id) VALUES (?,?)
            ON DUPLICATE KEY UPDATE source_id=VALUES(source_id)');
        $existing = $pdo->prepare('SELECT f.value_text,f.unit,s.url,f.evidence_level,f.notes,f.source_locator
            FROM drol_model_facts f JOIN drol_sources s ON s.id=f.source_id
            WHERE f.hardware_id=? AND f.fact_key=? AND f.valid_for_firmware IS NULL FOR UPDATE');
        $insert = $pdo->prepare("INSERT INTO drol_model_facts(hardware_id,fact_key,value_text,unit,
            source_id,source_locator,evidence_level,checked_on,checked_by,notes)
            VALUES (?,?,?,?,?,?,'E1',?,?,?)");
        $audit = $pdo->prepare("INSERT INTO drol_change_log(actor,action,object_type,object_id,after_json)
            VALUES (?,'insert','model_fact',?,?)");
        $planned = $known = $created = 0;
        $seen = [];
        foreach ($facts as $row) {
            if ($row['evidence_level'] !== 'E1') {
                throw new RuntimeException('unsupported_model_fact_evidence');
            }
            $model->execute([$row['manufacturer'],$row['name'],$row['manufacturer'].' '.$row['name']]);
            $ids = $model->fetchAll(PDO::FETCH_COLUMN);
            if (count($ids) !== 1) {
                throw new RuntimeException('ambiguous_or_absent_hardware: '.$row['model']);
            }
            $id = (int)$ids[0];
            $identity = $id.':'.$row['fact_key'];
            if (isset($seen[$identity])) {
                throw new RuntimeException('duplicate_batch_model_fact');
            }
            $seen[$identity] = true;
            $existing->execute([$id,$row['fact_key']]);
            $found = $existing->fetchAll(PDO::FETCH_NUM);
            $expected = [$row['value_text'],$row['unit'],$row['source_url'],'E1',$row['notes'],$row['source_locator']];
            if ($found && $found !== [$expected]) {
                throw new RuntimeException('model_fact_requires_review: '.$identity);
            }
            if ($found) {
                ++$known;
                continue;
            }
            $source->execute([$row['source_url']]);
            $sources = $source->fetchAll(PDO::FETCH_COLUMN);
            if (count($sources) > 1) {
                throw new RuntimeException('ambiguous_model_fact_source');
            }
            ++$planned;
            if (!$apply) {
                continue;
            }
            if (!$sources) {
                $addSource->execute([$row['manufacturer'].' '.$row['name'].' specifications',
                    $row['source_url'],$row['checked_on']]);
                $sourceId = (int)$pdo->lastInsertId();
            } else {
                $sourceId = (int)$sources[0];
            }
            $link->execute([$id,$sourceId]);
            $insert->execute([$id,$row['fact_key'],$row['value_text'],$row['unit'],$sourceId,
                $row['source_locator'],$row['checked_on'],$row['checked_by'],$row['notes']]);
            $factId = (int)$pdo->lastInsertId();
            $audit->execute([$row['checked_by'],$factId,json_encode($row, JSON_THROW_ON_ERROR)]);
            ++$created;
        }
        $after = $count();
        if ($after !== $before + $created) {
            throw new RuntimeException('unexpected_model_fact_delta');
        }
        if ($apply) {
            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
        return ['mode'=>$apply?'apply':'dry-run','before'=>$before,'after'=>$after,
            'created'=>$created,'planned'=>$planned,'existing'=>$known];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    } finally {
        $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
        $release->execute([$lock]);
    }
}
