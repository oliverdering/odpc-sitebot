<?php
/** Caller holds the common importer lock, commits or rolls back the transaction. */
function importFirmware(PDO $pdo, array $documents): array
{
    if (!$pdo->inTransaction()) {
        throw new RuntimeException('Firmware import requires a transaction');
    }
    $count = fn() => (int)$pdo->query("SELECT COUNT(*) FROM drol_firmware_releases")->fetchColumn();
    $before = $count();
    $inserted = 0;
    $existing = 0;
    $corroborated = 0;
    $hardware = $pdo->prepare("SELECT h.id FROM drol_hardware h JOIN drol_manufacturers m ON m.id=h.manufacturer_id
        WHERE m.name=? AND h.model IN (?,?)");
    $sources = $pdo->prepare("SELECT id FROM drol_sources WHERE url=? ORDER BY id");
    $newSource = $pdo->prepare("INSERT INTO drol_sources(title,url,source_type,checked_on) VALUES (?,?,'manufacturer_firmware',?)");
    $find = $pdo->prepare("SELECT id,released_on,source_id FROM drol_firmware_releases
        WHERE hardware_id=? AND version=? AND region='' AND channel='aircraft' FOR UPDATE");
    $add = $pdo->prepare("INSERT INTO drol_firmware_releases(hardware_id,version,released_on,region,channel,source_id)
        VALUES (?,?,?,'','aircraft',?)");
    $findProof = $pdo->prepare("SELECT id FROM drol_change_log WHERE object_type='firmware_release'
        AND object_id=? AND action='corroborate' AND JSON_UNQUOTE(JSON_EXTRACT(after_json,'$.source'))=? LIMIT 1");
    $audit = $pdo->prepare("INSERT INTO drol_change_log(actor,action,object_type,object_id,after_json)
        VALUES ('ODPC source review',?,'firmware_release',?,?)");

    foreach ($documents as $doc) {
        if ($doc['kind'] !== 'aircraft_firmware' || $doc['evidence'] !== 'E1') {
            throw new RuntimeException('Unsupported firmware evidence');
        }
        $hardware->execute([$doc['manufacturer'], $doc['model'], $doc['manufacturer'].' '.$doc['model']]);
        $ids = $hardware->fetchAll(PDO::FETCH_COLUMN);
        if (count($ids) !== 1) {
            throw new RuntimeException('Firmware model mapping ambiguous or missing: '.$doc['model']);
        }
        $url = $doc['source_url'];
        $sources->execute([$url]);
        $sourceIds = $sources->fetchAll(PDO::FETCH_COLUMN);
        if (count($sourceIds) > 1) {
            throw new RuntimeException('Duplicate firmware source mapping');
        }
        if (!$sourceIds) {
            $newSource->execute([$doc['manufacturer'].' '.$doc['model'].' aircraft firmware release notes', $url, $doc['checked_on']]);
        }
        $sourceId = (int)($sourceIds[0] ?? $pdo->lastInsertId());
        foreach ($doc['releases'] as $row) {
            $version = $row['version'];
            $date = $row['released_on'];
            $find->execute([$ids[0], $version]);
            $old = $find->fetch(PDO::FETCH_ASSOC);
            if ($old) {
                if ($old['released_on'] !== $date) {
                    throw new RuntimeException('Firmware date conflict: '.$doc['model'].' '.$version);
                }
                $existing++;
                if ((int)$old['source_id'] === $sourceId) {
                    continue;
                }
                $id = (int)$old['id'];
                $findProof->execute([$id, $url]);
                if ($findProof->fetchColumn() !== false) {
                    continue;
                }
                $action = 'corroborate';
                $corroborated++;
            } else {
                $add->execute([$ids[0], $version, $date, $sourceId]);
                $id = (int)$pdo->lastInsertId();
                $action = 'insert';
                $inserted++;
            }
            $audit->execute([$action, $id, json_encode([
                'version'=>$version, 'released_on'=>$date, 'source'=>$url, 'page'=>$row['page'],
                'source_sha256'=>$doc['source_sha256'], 'field'=>'Aircraft Firmware', 'evidence'=>'E1', 'checked_on'=>$doc['checked_on']
            ], JSON_THROW_ON_ERROR)]);
        }
    }
    $after = $count();
    if ($after - $before !== $inserted) {
        throw new RuntimeException('Firmware inventory delta differs from insert count');
    }
    return ['before'=>$before, 'after'=>$after, 'inserted'=>$inserted, 'existing'=>$existing, 'corroborated'=>$corroborated];
}
