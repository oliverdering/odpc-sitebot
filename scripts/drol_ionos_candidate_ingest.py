"""Ingest discovered upstream releases into MariaDB as E0 candidate facts.

Uses an ephemeral authenticated PHP bridge because GitHub runners cannot connect
directly to the IONOS database. Never publishes or approves candidate facts.
"""
import json
import os
from pathlib import Path

PHP=r"""<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD']??'')!=='POST' ||
    !hash_equals('__TOKEN_HASH__',hash('sha256',$_SERVER['HTTP_X_DROL_PROBE']??''))) {
    http_response_code(404);exit;
}
try {
    $raw=file_get_contents('php://input',false,null,0,65537);
    if ($raw===false || strlen($raw)>65536) throw new RuntimeException('size');
    $data=json_decode($raw,true,32,JSON_THROW_ON_ERROR);
    foreach(['host','database','username','password'] as $k)
        if (empty($data[$k]) || !is_string($data[$k])) throw new RuntimeException('config');
    $items=$data['candidates']??null;
    if (!is_array($items)||count($items)<1||count($items)>30) throw new RuntimeException('items');
    $pdo=new PDO(sprintf('mysql:host=%s;port=3306;dbname=%s;charset=utf8mb4',
        $data['host'],$data['database']),$data['username'],$data['password'],[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
    $pdo->beginTransaction();
    $pdo->exec("INSERT INTO drol_attributes(code,datatype,filterable) VALUES ('upstream_software_release','text',0)
        ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
    $attributeId=(int)$pdo->lastInsertId();
    $sourceFind=$pdo->prepare('SELECT id FROM drol_sources WHERE url=? LIMIT 1');
    $sourceAdd=$pdo->prepare("INSERT INTO drol_sources(title,url,source_type) VALUES (?,?,'upstream_release')");
    $snapFind=$pdo->prepare('SELECT id FROM drol_source_snapshots WHERE source_id=? AND sha256=? LIMIT 1');
    $snapAdd=$pdo->prepare('INSERT INTO drol_source_snapshots(source_id,retrieved_at,sha256,http_status,snapshot_ref) VALUES (?,UTC_TIMESTAMP(),?,200,?)');
    $candidateFind=$pdo->prepare('SELECT id FROM drol_candidate_facts WHERE snapshot_id=? AND attribute_id=? LIMIT 1');
    $candidateAdd=$pdo->prepare("INSERT INTO drol_candidate_facts(run_id,entity_hint,attribute_id,value_raw,value_normalized,snapshot_id,source_quote,source_locator,diff_kind,status)
        VALUES (?,?,?,?,?,?,?,?,'new','pending')");
    $created=0;$known=0;
    foreach($items as $item) {
        if (!is_array($item)) throw new RuntimeException('item');
        foreach(['product','repository','tag','url'] as $k)
            if (empty($item[$k])||!is_string($item[$k])||strlen($item[$k])>190)
                throw new RuntimeException('field');
        $url=$item['url'];
        if (!filter_var($url,FILTER_VALIDATE_URL)||parse_url($url,PHP_URL_SCHEME)!=='https'||
            parse_url($url,PHP_URL_HOST)!=='github.com')throw new RuntimeException('url');
        if (!preg_match('~^https://github[.]com/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+/releases/tag/~',$url))
            throw new RuntimeException('release_url');
        $sourceFind->execute([$url]);$sourceId=$sourceFind->fetchColumn();
        if (!$sourceId){$sourceAdd->execute([$item['product'].' '.$item['tag'],$url]);$sourceId=$pdo->lastInsertId();}
        $hash=hash('sha256',$item['repository']."\n".$item['tag']."\n".$url);
        $snapFind->execute([(int)$sourceId,$hash]);$snapshotId=$snapFind->fetchColumn();
        if (!$snapshotId) {
            $snapAdd->execute([(int)$sourceId,$hash,substr($item['repository'].'@'.$item['tag'],0,500)]);
            $snapshotId=$pdo->lastInsertId();
        }
        $candidateFind->execute([(int)$snapshotId,$attributeId]);
        if ($candidateFind->fetchColumn()){$known++;continue;}
        // Preserve upstream provenance and machine-extracted topics as unreviewed metadata.
        // Never promote release-note keywords into verified model capabilities.
        $notes=$item['release_notes']??[];
        if (!is_array($notes)) $notes=[];
        $topics=array_values(array_filter($notes['topics']??[],
            fn($topic)=>is_string($topic) && in_array($topic,
                ['security','api_change','firmware','sensor','navigation'],true)));
        $topics=array_slice(array_unique($topics),0,5);
        $excerpt=is_string($notes['excerpt']??null)?substr($notes['excerpt'],0,1200):'';
        $digest=$notes['sha256']??null;
        if (!is_string($digest)||!preg_match('/^[a-f0-9]{64}$/D',$digest)) $digest=null;
        $value=json_encode(['repository'=>$item['repository'],'tag'=>$item['tag'],
            'published_at'=>$item['published_at']??null,'evidence'=>'E0',
            'compatibility'=>'UNKNOWN','review_status'=>'UNREVIEWED',
            'software_class'=>$item['software_class']??'unclassified',
            'topics_unverified'=>$topics,'release_notes_sha256'=>$digest,
            'release_notes_excerpt'=>$excerpt],JSON_THROW_ON_ERROR);
        $candidateAdd->execute([substr($data['run_id']??'drol-auto',0,80),
            $item['product'],$attributeId,$item['tag'],$value,(int)$snapshotId,
            substr($excerpt!==''?$excerpt:($item['product'].' release '.$item['tag']),0,500),$url]);
        $created++;
    }
    $pdo->commit();
    $total=(int)$pdo->query("SELECT COUNT(*) FROM drol_candidate_facts WHERE status='pending'")->fetchColumn();
    echo json_encode(['status'=>'ok','created'=>$created,'existing'=>$known,'pending_total'=>$total],JSON_THROW_ON_ERROR);
} catch(Throwable $e) {
    if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
    http_response_code(503);echo json_encode(['status'=>'unavailable']);
}
"""
def main():
    report=json.loads(Path("drol-release-candidates.json").read_text(encoding="utf-8"))
    items=report.get("candidates")
    if not isinstance(items,list) or not 1<=len(items)<=30:raise RuntimeError("No candidates")
    from drol_db_bridge import invoke
    result = invoke(PHP, {"run_id": os.environ.get("GITHUB_RUN_ID", "drol-auto"), "candidates": items})
    if result.get("status") != "ok" or result["created"] + result["existing"] != len(items):
        raise RuntimeError("Candidate import not confirmed")
    print("New E0 candidates:", result["created"], "existing:", result["existing"],
          "total pending:", result["pending_total"])
if __name__=="__main__":main()
