<?php
declare(strict_types=1);
if(!defined('MK_INTERNAL')){http_response_code(404);exit;}
require_once __DIR__.'/public.php';
function mk_commerce_db(): PDO {
 static $pdo;if($pdo)return $pdo;
 $pdo=defined('MK_COMMERCE_TEST_DB')?new PDO('sqlite:'.MK_COMMERCE_TEST_DB):mk_database();
 if(!$pdo)throw new RuntimeException('Datenbank nicht verfügbar');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 $id=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite'?'INTEGER PRIMARY KEY AUTOINCREMENT':'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
 $pdo->exec("CREATE TABLE IF NOT EXISTS mk_shop_users (id $id,email VARCHAR(190) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,created_at BIGINT NOT NULL)");
 $pdo->exec('CREATE TABLE IF NOT EXISTS mk_shop_sessions (token_hash CHAR(64) PRIMARY KEY,user_id BIGINT NULL,csrf CHAR(64) NOT NULL,expires_at BIGINT NOT NULL)');
 $payload=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite'?'TEXT':'MEDIUMTEXT';
 $pdo->exec("CREATE TABLE IF NOT EXISTS mk_shop_state (user_id BIGINT PRIMARY KEY,payload $payload NOT NULL,updated_at BIGINT NOT NULL)");
 $pdo->exec('CREATE TABLE IF NOT EXISTS mk_shop_limits (bucket CHAR(64) PRIMARY KEY,attempts INTEGER NOT NULL,expires_at BIGINT NOT NULL)');return $pdo;
}
function mk_commerce_session(PDO $db): array {
 $token=$_COOKIE['mk_test_session']??'';$q=$db->prepare('SELECT * FROM mk_shop_sessions WHERE token_hash=? AND expires_at>?');$q->execute([hash('sha256',$token),time()]);$session=$q->fetch(PDO::FETCH_ASSOC);
 if(!$session){mk_commerce_limit($db,'session',60);$token=bin2hex(random_bytes(32));$session=['token_hash'=>hash('sha256',$token),'user_id'=>null,'csrf'=>bin2hex(random_bytes(32)),'expires_at'=>time()+7200];$db->prepare('INSERT INTO mk_shop_sessions(token_hash,user_id,csrf,expires_at) VALUES(?,?,?,?)')->execute(array_values($session));if(!defined('MK_COMMERCE_TEST_DB'))setcookie('mk_test_session',$token,['expires'=>$session['expires_at'],'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Strict']);}
 return $session;
}
function mk_commerce_limit(PDO $db,string $action,int $maximum=15): void {
 $bucket=hash('sha256',$action.'|'.($_SERVER['REMOTE_ADDR']??'local').'|'.(string)floor(time()/3600));
 $sqlite=$db->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite';
 $sql=$sqlite?'INSERT INTO mk_shop_limits(bucket,attempts,expires_at) VALUES(?,?,?) ON CONFLICT(bucket) DO UPDATE SET attempts=attempts+1':'INSERT INTO mk_shop_limits(bucket,attempts,expires_at) VALUES(?,?,?) ON DUPLICATE KEY UPDATE attempts=attempts+1';
 $db->prepare($sql)->execute([$bucket,1,time()+3600]);$q=$db->prepare('SELECT attempts FROM mk_shop_limits WHERE bucket=?');$q->execute([$bucket]);
 if((int)$q->fetchColumn()>$maximum)throw new DomainException('Zu viele Versuche. Bitte später erneut versuchen.');
 $db->prepare('DELETE FROM mk_shop_limits WHERE expires_at<?')->execute([time()]);$db->prepare('DELETE FROM mk_shop_sessions WHERE expires_at<?')->execute([time()]);
}
function mk_commerce_dispatch(PDO $db,array $session,string $action,array $body): array {
 $catalog=mk_shop_catalog();
 if(in_array($action,['checkout','pay','order','purchase']))throw new DomainException('Echte Bestellung und Zahlung sind serverseitig gesperrt.');
 if($action==='session')return ['mode'=>'test','accountMode'=>$catalog['accountMode'],'user'=>$session['user_id']?['id'=>(int)$session['user_id']]:null,'csrf'=>$session['csrf'],'checkoutEnabled'=>false];
 if(in_array($action,['register','login'])){
  mk_commerce_limit($db,$action);$email=strtolower(trim((string)($body['email']??'')));$password=(string)($body['password']??'');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>190||strlen($password)<12||strlen($password)>128)throw new InvalidArgumentException('Nutze eine gültige Testadresse und ein Passwort mit 12 bis 128 Zeichen.');
  if(($catalog['accountMode']??'demo')==='demo'&&!str_ends_with($email,'@example.invalid'))throw new InvalidArgumentException('Im Kontotest nur eine erfundene Adresse mit @example.invalid verwenden.');
  if($action==='register'){
   if(($body['acknowledgeTest']??false)!==true)throw new InvalidArgumentException('Bitte den Kontotest bestätigen.');
   try{$db->prepare('INSERT INTO mk_shop_users(email,password_hash,created_at) VALUES(?,?,?)')->execute([$email,password_hash($password,PASSWORD_DEFAULT),time()]);$userId=(int)$db->lastInsertId();}catch(PDOException $e){throw new InvalidArgumentException('Für diese Testadresse kann kein weiteres Konto angelegt werden.');}
  }else{$q=$db->prepare('SELECT id,password_hash FROM mk_shop_users WHERE email=?');$q->execute([$email]);$u=$q->fetch(PDO::FETCH_ASSOC);$dummy='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';if(!password_verify($password,$u['password_hash']??$dummy)||!$u)throw new InvalidArgumentException('Anmeldung nicht möglich. Prüfe Testadresse und Passwort.');$userId=(int)$u['id'];}
   $token=bin2hex(random_bytes(32));$new=['token_hash'=>hash('sha256',$token),'user_id'=>$userId,'csrf'=>bin2hex(random_bytes(32)),'expires_at'=>time()+7200];$db->prepare('INSERT INTO mk_shop_sessions(token_hash,user_id,csrf,expires_at) VALUES(?,?,?,?)')->execute(array_values($new));$db->prepare('DELETE FROM mk_shop_sessions WHERE token_hash=?')->execute([$session['token_hash']]);
   if(!defined('MK_COMMERCE_TEST_DB'))setcookie('mk_test_session',$token,['expires'=>$new['expires_at'],'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Strict']);return ['user'=>['id'=>$userId],'csrf'=>$new['csrf'],'mode'=>'test'];
 }
 if($action==='logout'){$db->prepare('DELETE FROM mk_shop_sessions WHERE token_hash=?')->execute([$session['token_hash']]);if(!defined('MK_COMMERCE_TEST_DB'))setcookie('mk_test_session','',['expires'=>1,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Strict']);return ['user'=>null,'mode'=>'test'];}
 if(!$session['user_id'])throw new DomainException('Für diese Aktion ist ein Testkonto erforderlich.');
 if($action==='load'){$q=$db->prepare('SELECT payload,updated_at FROM mk_shop_state WHERE user_id=?');$q->execute([$session['user_id']]);$r=$q->fetch(PDO::FETCH_ASSOC);return ['state'=>$r?json_decode($r['payload'],true):null,'updatedAt'=>$r?(int)$r['updated_at']:null];}
 if($action==='save'){
  $state=$body['state']??null;if(!is_array($state)||!in_array($state['schema']??null,[1,2])||!is_array($state['shop']??null))throw new InvalidArgumentException('Ungültiger Testregal-Datensatz.');$json=mk_json($state);if(strlen($json)>1000000)throw new InvalidArgumentException('Der Testdatensatz ist zu groß.');
  $db->beginTransaction();try{$db->prepare('DELETE FROM mk_shop_state WHERE user_id=?')->execute([$session['user_id']]);$db->prepare('INSERT INTO mk_shop_state(user_id,payload,updated_at) VALUES(?,?,?)')->execute([$session['user_id'],$json,time()]);$db->commit();}catch(Throwable $e){$db->rollBack();throw $e;}return ['saved'=>true,'mode'=>'test'];
 }
 if($action==='delete-account'){$db->beginTransaction();try{foreach(['mk_shop_state','mk_shop_sessions'] as $table)$db->prepare('DELETE FROM '.$table.' WHERE user_id=?')->execute([$session['user_id']]);$db->prepare('DELETE FROM mk_shop_users WHERE id=?')->execute([$session['user_id']]);$db->commit();}catch(Throwable $e){$db->rollBack();throw $e;}if(!defined('MK_COMMERCE_TEST_DB'))setcookie('mk_test_session','',['expires'=>1,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Strict']);return ['deleted'=>true,'mode'=>'test'];}
 throw new InvalidArgumentException('Unbekannte Aktion.');
}
