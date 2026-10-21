<?php
declare(strict_types=1);
if (!defined('MK_SETUP_HASH') || !defined('MK_SETUP_WRAPPER') || ($_SERVER['REQUEST_METHOD']??'')!=='POST') {http_response_code(404);exit;}
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
$token=(string)($_SERVER['HTTP_X_MK_SETUP_TOKEN']??'');
if (!hash_equals(MK_SETUP_HASH,hash('sha256',$token))) {http_response_code(404);exit;}
define('MK_INTERNAL',true);require __DIR__.'/database.php';
if (is_file(mk_config_path())) {http_response_code(409);echo '{"status":"already_configured"}';exit;}
try {
    $c=json_decode(file_get_contents('php://input'),true,16,JSON_THROW_ON_ERROR);
    if (($c['host']??'')!=='db5021707616.hosting-data.io'||($c['database']??'')!=='dbs16253574'||($c['user']??'')!=='dbu1088334'||!is_string($c['password']??null)||strlen($c['password'])<8) throw new RuntimeException('invalid_input');
    $pdo=new PDO('mysql:host='.$c['host'].';port=3306;dbname='.$c['database'].';charset=utf8mb4',$c['user'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>10]);
    $pdo->exec('CREATE TABLE IF NOT EXISTS mk_releases (release_key CHAR(64) PRIMARY KEY, version VARCHAR(40) NOT NULL, active TINYINT NOT NULL DEFAULT 0, atlas_json LONGTEXT NOT NULL, extra_json LONGTEXT NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY mk_active (active)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $pdo->exec('CREATE TABLE IF NOT EXISTS mk_catalog_records (release_key CHAR(64) NOT NULL, collection VARCHAR(24) NOT NULL, record_id VARCHAR(160) NOT NULL, payload LONGTEXT NOT NULL, PRIMARY KEY (release_key,collection,record_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $atlas=json_decode(file_get_contents(dirname(__DIR__).'/content/atlas-base.json'),true,512,JSON_THROW_ON_ERROR);
    $extra=json_decode(file_get_contents(dirname(__DIR__).'/content/meinkraut-content.json'),true,512,JSON_THROW_ON_ERROR);
    $flags=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR;
    $aj=json_encode($atlas,$flags);$ej=json_encode($extra,$flags);$key=hash('sha256',$aj.$ej);
    $pdo->beginTransaction();
    $st=$pdo->prepare('INSERT INTO mk_releases(release_key,version,active,atlas_json,extra_json) VALUES(?,?,0,?,?) ON DUPLICATE KEY UPDATE version=VALUES(version)');$st->execute([$key,$extra['version'],$aj,$ej]);
    $st=$pdo->prepare('INSERT INTO mk_catalog_records(release_key,collection,record_id,payload) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE payload=VALUES(payload)');
    foreach(['ingredients'=>'id','recipes'=>'id','countries'=>'code','regions'=>'id'] as $collection=>$idField)foreach($atlas[$collection] as $record)$st->execute([$key,$collection,$record[$idField],json_encode($record,$flags)]);
    foreach($extra['mixes'] as $record)$st->execute([$key,'recipes',$record['id'],json_encode($record,$flags)]);
    foreach(['countries'=>'country_profiles','ingredients'=>'spice_profiles'] as $field=>$collection)foreach($extra[$field] as $id=>$record)$st->execute([$key,$collection,$id,json_encode($record,$flags)]);
    $pdo->exec('UPDATE mk_releases SET active=0 WHERE active=1');$st=$pdo->prepare('UPDATE mk_releases SET active=1 WHERE release_key=?');$st->execute([$key]);$pdo->commit();
    $path=mk_config_path();$directory=dirname($path);
    if (!is_dir($directory)&&!mkdir($directory,0700,true))throw new RuntimeException('private_directory');
    chmod($directory,0700);
    file_put_contents($directory.'/.htaccess',"Require all denied\n",LOCK_EX);
    $temp=$path.'.tmp';
    if(file_put_contents($temp,"<?php\nreturn ".var_export(['host'=>$c['host'],'database'=>$c['database'],'user'=>$c['user'],'password'=>$c['password']],true).";\n",LOCK_EX)===false)throw new RuntimeException('private_config');
    chmod($temp,0600);if(!rename($temp,$path))throw new RuntimeException('private_config_rename');
    $check=mk_database();$count=(int)$check->query('SELECT COUNT(*) FROM mk_catalog_records')->fetchColumn();
    @unlink(MK_SETUP_WRAPPER);
    echo json_encode(['status'=>'configured','version'=>$extra['version'],'catalogRecords'=>$count,'recipes'=>count($atlas['recipes'])+count($extra['mixes']),'setupRemoved'=>!is_file(MK_SETUP_WRAPPER)]);
} catch(Throwable $e) {
    if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
    error_log('Mein Kraut: database setup failed');http_response_code(500);echo '{"status":"setup_failed"}';
}
