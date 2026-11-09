#!/usr/bin/env python3
"""Import verified-origin Microsoft Learn crawl records via ephemeral IONOS-local PHP endpoint."""
import base64,hashlib,hmac,json,os,secrets
from pathlib import Path
from urllib.parse import urlparse
from urllib.request import Request,urlopen
from urllib.error import HTTPError
import paramiko
from deploy_helpermonkey_v4 import HOST,USER,PIN

STATE=Path("adminmonkey/knowledge/crawl_state.json")
PHP=r"""<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD']??'')!=='POST' ||
    !hash_equals('__TOKEN_HASH__',hash('sha256',$_SERVER['HTTP_X_AMK_IMPORT']??''))) {
    http_response_code(404);exit;
}
try {
    $raw=file_get_contents('php://input',false,null,0,1000001);
    if ($raw===false || strlen($raw)>1000000) throw new RuntimeException('size');
    $p=json_decode($raw,true,16,JSON_THROW_ON_ERROR);
    foreach(['host','name','user','password','port'] as $key)
        if (!isset($p[$key]) || !is_string($p[$key]) || $p[$key]==='') throw new RuntimeException('config');
    if (!ctype_digit($p['port'])) throw new RuntimeException('port');
    $docs=$p['documents']??null;
    if (!is_array($docs) || count($docs)<1 || count($docs)>20) throw new RuntimeException('batch');
    $pdo=new PDO('mysql:host='.$p['host'].';port='.$p['port'].';dbname='.$p['name'].';charset=utf8mb4',
        $p['user'],$p['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>10]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS amk_sources (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        canonical_url VARCHAR(768) NOT NULL,
        product_area VARCHAR(80) NOT NULL,
        first_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_checked DATETIME NULL,
        UNIQUE KEY uq_source_url (canonical_url(191))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS amk_documents (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        source_id BIGINT UNSIGNED NOT NULL,
        content_sha256 CHAR(64) NOT NULL,
        title VARCHAR(512) NOT NULL,
        content_text MEDIUMTEXT NOT NULL,
        review_status ENUM('unreviewed','approved','rejected') NOT NULL DEFAULT 'unreviewed',
        captured_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_source_version (source_id,content_sha256),
        KEY ix_review (review_status,captured_at),
        CONSTRAINT fk_amk_source FOREIGN KEY (source_id) REFERENCES amk_sources(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $source=$pdo->prepare("INSERT INTO amk_sources(canonical_url,product_area,last_checked)
        VALUES(?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE last_checked=UTC_TIMESTAMP()");
    $find=$pdo->prepare("SELECT id FROM amk_sources WHERE canonical_url=? LIMIT 1");
    $insert=$pdo->prepare("INSERT IGNORE INTO amk_documents
        (source_id,content_sha256,title,content_text,review_status)
        VALUES(?,?,?,?,'unreviewed')");
    $pdo->beginTransaction();
    $added=0;
    foreach($docs as $d) {
        if (!is_array($d)) throw new RuntimeException('document');
        $url=$d['url']??'';$body=$d['text']??'';$hash=$d['sha256']??'';
        if (!is_string($url)||!preg_match('~^https://learn[.]microsoft[.]com/en-us/[a-zA-Z0-9/_-]+$~D',$url)
            ||strlen($url)>768 || !is_string($body)||strlen($body)<100||strlen($body)>200000
            ||!is_string($hash)||!preg_match('/^[a-f0-9]{64}$/D',$hash)
            ||!hash_equals($hash,hash('sha256',$body))) throw new RuntimeException('validation');
        $area=explode('/',parse_url($url,PHP_URL_PATH))[2]??'general';
        $source->execute([$url,substr($area,0,80)]);
        $find->execute([$url]);$id=$find->fetchColumn();
        if (!$id) throw new RuntimeException('source');
        $title=substr(basename(parse_url($url,PHP_URL_PATH)),0,500);
        $insert->execute([(int)$id,$hash,$title,$body]);$added+=$insert->rowCount();
    }
    $pdo->commit();
    $total=(int)$pdo->query('SELECT COUNT(*) FROM amk_documents')->fetchColumn();
    $sources=(int)$pdo->query('SELECT COUNT(*) FROM amk_sources')->fetchColumn();
    echo json_encode(['status'=>'ok','inserted'=>$added,'document_versions_total'=>$total,
        'sources_total'=>$sources,'batch_count'=>count($docs)]);
} catch(Throwable $e) {
    if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
    http_response_code(503);echo json_encode(['status'=>'unavailable']);
}
"""
def main():
    needed=['ODPC_SFTP_PASSWORD','AMK_DB_HOST','AMK_DB_NAME','AMK_DB_USER','AMK_DB_PASSWORD']
    missing=[k for k in needed if not os.environ.get(k)]
    if missing:raise RuntimeError('Missing secrets: '+', '.join(missing))
    state=json.loads(STATE.read_text(encoding='utf-8'))
    docs=[]
    for item in state.get('documents',{}).values():
        url=item.get('url','');body=item.get('text','');digest=item.get('sha256','')
        parsed=urlparse(url)
        if parsed.scheme!='https' or parsed.hostname!='learn.microsoft.com' or not parsed.path.startswith('/en-us/'):continue
        if not isinstance(body,str) or not 100<=len(body.encode())<=200000:continue
        if hashlib.sha256(body.encode()).hexdigest()!=digest:continue
        docs.append({'url':url,'text':body,'sha256':digest})
    if not docs:raise RuntimeError('No hash-verified Microsoft Learn documents in restored cache')
    token=secrets.token_urlsafe(48);name='amk-ingest-'+secrets.token_hex(20)+'.php'
    php=PHP.replace('__TOKEN_HASH__',hashlib.sha256(token.encode()).hexdigest())
    transport=paramiko.Transport((HOST,22));transport.start_client(timeout=25)
    sf=None;uploaded=False
    try:
        actual='SHA256:'+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual,PIN):raise RuntimeError('Host key mismatch')
        transport.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD'])
        if not transport.is_authenticated():raise RuntimeError('SFTP auth failed')
        sf=paramiko.SFTPClient.from_transport(transport)
        remote='/drol/'+name
        with sf.open(remote,'wx') as f:uploaded=True;f.write(php)
        sf.chmod(remote,0o644)
        url='https://drol.odpc.de/'+name
        try:
            with urlopen(Request(url,data=b'{}',method='POST'),timeout=20):
                raise RuntimeError('Unauthenticated endpoint accepted')
        except HTTPError as exc:
            if exc.code!=404:raise RuntimeError('Endpoint authorization check failed') from None
        base={k:os.environ[v] for k,v in {'host':'AMK_DB_HOST','name':'AMK_DB_NAME',
            'user':'AMK_DB_USER','password':'AMK_DB_PASSWORD'}.items()}
        base['port']=os.environ.get('AMK_DB_PORT') or '3306'
        inserted=0;result=None
        for start in range(0,len(docs),10):
            payload=dict(base,documents=docs[start:start+10])
            req=Request(url,data=json.dumps(payload).encode(),method='POST',
                headers={'Content-Type':'application/json','X-AMK-Import':token})
            try:
                with urlopen(req,timeout=60) as response:result=json.load(response)
            except HTTPError as exc:raise RuntimeError('Database import HTTP '+str(exc.code)+' at batch '+str(start//10+1)) from None
            if result.get('status')!='ok' or result.get('batch_count')!=len(payload['documents']):
                raise RuntimeError('Import not confirmed')
            inserted+=result['inserted']
        report={'database_connected':True,'new_document_versions':inserted,
            'document_versions_total':result['document_versions_total'],
            'sources_total':result['sources_total'],'imported_from_verified_origin':len(docs),
            'official_microsoft_certification':False}
        Path('adminmonkey/knowledge/database_progress.json').write_text(json.dumps(report,indent=2)+'\n')
        print('PASS: AdminMonkey knowledge imported:',json.dumps(report))
    finally:
        if sf:
            try:
                if uploaded:
                    sf.remove('/drol/'+name);print('Ephemeral import endpoint removed')
            finally:sf.close()
        transport.close()
if __name__=='__main__':main()
