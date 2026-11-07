"""Publish a strictly allowlisted, read-only D.R.O.L. MariaDB catalog snapshot."""
import base64, hashlib, hmac, json, os, secrets
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

ROOT="/drol"
URL="https://drol.odpc.de"
PHP=r"""<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !hash_equals('__TOKEN_HASH__',hash('sha256',$_SERVER['HTTP_X_DROL_PROBE'] ?? ''))) {
    http_response_code(404); exit;
}
try {
    $raw=file_get_contents('php://input',false,null,0,8193);
    if ($raw===false || strlen($raw)>8192) throw new RuntimeException('invalid');
    $cfg=json_decode($raw,true,16,JSON_THROW_ON_ERROR);
    foreach (['host','database','username','password'] as $key)
        if (empty($cfg[$key]) || !is_string($cfg[$key])) throw new RuntimeException('invalid');
    $pdo=new PDO(sprintf('mysql:host=%s;port=3306;dbname=%s;charset=utf8mb4',$cfg['host'],$cfg['database']),
        $cfg['username'],$cfg['password'],[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false]);
    $rows=$pdo->query("SELECT h.id, m.name AS manufacturer, h.model AS name, h.category, h.verification_status
        FROM drol_hardware h INNER JOIN drol_manufacturers m ON m.id=h.manufacturer_id
        ORDER BY m.name,h.model")->fetchAll();
    $sourceQuery=$pdo->prepare("SELECT s.url FROM drol_hardware_sources hs
        INNER JOIN drol_sources s ON s.id=hs.source_id WHERE hs.hardware_id=? ORDER BY s.url");
    $models=[];
    foreach ($rows as $row) {
        $sourceQuery->execute([$row['id']]);
        $urls=array_values(array_filter($sourceQuery->fetchAll(PDO::FETCH_COLUMN),
            fn($url)=>is_string($url) && str_starts_with($url,'https://')));
        $models[]=['manufacturer'=>$row['manufacturer'],'name'=>$row['name'],
            'category'=>$row['category'],'verification_status'=>$row['verification_status'],
            'sources'=>$urls];
    }
    echo json_encode(['schema_version'=>'1.1','source'=>'D.R.O.L. production MariaDB',
        'generated_utc'=>gmdate('c'),'model_count'=>count($models),'models'=>$models],
        JSON_THROW_ON_ERROR|JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['status'=>'unavailable']);
}
"""

def main():
    token=secrets.token_urlsafe(48)
    name="drol-export-"+secrets.token_hex(20)+".php"
    script=PHP.replace("__TOKEN_HASH__",hashlib.sha256(token.encode()).hexdigest())
    transport=paramiko.Transport((HOST,22))
    transport.start_client(timeout=25)
    sf=None
    uploaded=False
    try:
        actual="SHA256:"+base64.b64encode(hashlib.sha256(transport.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
        if not hmac.compare_digest(actual,PIN):raise RuntimeError("SFTP host key mismatch")
        transport.auth_password(USER,os.environ["ODPC_SFTP_PASSWORD"])
        sf=paramiko.SFTPClient.from_transport(transport)
        endpoint=ROOT+"/"+name
        with sf.open(endpoint,"wx") as f:
            uploaded=True
            f.write(script)
        sf.chmod(endpoint,0o644)
        try:
            try:
                with urlopen(Request(URL+"/"+name,data=b"{}",method="POST"),timeout=20):
                    raise RuntimeError("Unauthenticated endpoint accepted")
            except HTTPError as exc:
                if exc.code!=404:raise RuntimeError("Endpoint access control failed") from None
            payload={"host":"db5021788879.hosting-data.io","database":"dbs16292022",
                "username":"dbu1397392","password":os.environ["DROL_DB_PASSWORD"]}
            req=Request(URL+"/"+name,data=json.dumps(payload).encode(),method="POST",
                headers={"Content-Type":"application/json","X-DROL-Probe":token})
            try:
                with urlopen(req,timeout=30) as response:
                    snapshot=json.load(response)
            except HTTPError as exc:
                raise RuntimeError("Private database export failed HTTP "+str(exc.code)) from None
            models=snapshot.get("models")
            if snapshot.get("schema_version")!="1.1" or not isinstance(models,list) or len(models)<5:
                raise RuntimeError("Incomplete public export; refusing overwrite")
            for model in models:
                if not isinstance(model,dict) or set(model)!={"manufacturer","name","category","verification_status","sources"}:
                    raise RuntimeError("Unexpected public export fields")
            data=json.dumps(snapshot,ensure_ascii=False,indent=2).encode("utf-8")
            if len(data)>1000000:raise RuntimeError("Public export unexpectedly large")
            directory=ROOT+"/data"
            sf.stat(directory)
            tmp=directory+"/models-db.json.uploading"
            final=directory+"/models-db.json"
            with sf.open(tmp,"wb") as f:f.write(data)
            sf.chmod(tmp,0o644)
            sf.posix_rename(tmp,final)
            with sf.open(final,"rb") as f:
                if hashlib.sha256(f.read()).digest()!=hashlib.sha256(data).digest():
                    raise RuntimeError("Published snapshot checksum mismatch")
            print("PASS: read-only MariaDB catalog snapshot published; models:",len(models))
            print("Public snapshot: https://drol.odpc.de/data/models-db.json")
        finally:
            pass
    finally:
        if sf is not None:
            try:
                if uploaded:
                    sf.remove(ROOT+"/"+name)
                    print("Temporary export endpoint removed")
            finally:sf.close()
        transport.close()

if __name__=="__main__":
    main()
