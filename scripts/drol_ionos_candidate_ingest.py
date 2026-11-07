"""Ingest discovered upstream releases into MariaDB as E0 candidate facts.

Uses an ephemeral authenticated PHP bridge because GitHub runners cannot connect
directly to the IONOS database. Never publishes or approves candidate facts.
"""
import base64,hashlib,hmac,json,os,secrets
from pathlib import Path
from urllib.request import Request,urlopen
from urllib.error import HTTPError
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN

ROOT="/drol"
URL="https://drol.odpc.de"
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
        $value=json_encode(['repository'=>$item['repository'],'tag'=>$item['tag'],
            'published_at'=>$item['published_at']??null,'evidence'=>'E0',
            'compatibility'=>'UNKNOWN'],JSON_THROW_ON_ERROR);
        $candidateAdd->execute([substr($data['run_id']??'drol-auto',0,80),
            $item['product'],$attributeId,$item['tag'],$value,(int)$snapshotId,
            substr($item['product'].' release '.$item['tag'],0,500),$url]);
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
    token=secrets.token_urlsafe(48)
    name="drol-candidates-"+secrets.token_hex(20)+".php"
    script=PHP.replace("__TOKEN_HASH__",hashlib.sha256(token.encode()).hexdigest())
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=25)
    sf=None;uploaded=False
    try:
        fingerprint="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(fingerprint,PIN):raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
        sf=paramiko.SFTPClient.from_transport(transport)
        with sf.open(ROOT+"/"+name,"wx") as f:
            uploaded=True;f.write(script)
        sf.chmod(ROOT+"/"+name,0o644)
        try:
            try:
                with urlopen(Request(URL+"/"+name,data=b"{}",method="POST"),timeout=20):
                    raise RuntimeError("Unauthenticated endpoint accepted")
            except HTTPError as exc:
                if exc.code!=404:raise RuntimeError("Access control failed") from None
            payload={"host":"db5021788879.hosting-data.io","database":"dbs16292022",
                "username":"dbu1397392","password":os.environ["DROL_DB_PASSWORD"],
                "run_id":os.environ.get("GITHUB_RUN_ID","drol-auto"),"candidates":items}
            req=Request(URL+"/"+name,data=json.dumps(payload).encode(),method="POST",
                headers={"Content-Type":"application/json","X-DROL-Probe":token})
            try:
                with urlopen(req,timeout=40) as response:result=json.load(response)
            except HTTPError as exc:
                raise RuntimeError("MariaDB candidate import rejected HTTP "+str(exc.code)) from None
            if result.get("status")!="ok" or result.get("created",0)+result.get("existing",0)!=len(items):
                raise RuntimeError("Candidate import not confirmed")
            print("PASS: MariaDB candidate facts created:",result["created"])
            print("Existing deduplicated:",result["existing"])
            print("Total pending candidates:",result["pending_total"])
        finally:pass
    finally:
        if sf:
            try:
                if uploaded:
                    sf.remove(ROOT+"/"+name)
                    print("Temporary candidate endpoint removed")
            finally:sf.close()
        transport.close()
if __name__=="__main__":main()
