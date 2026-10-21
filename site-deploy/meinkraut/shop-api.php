<?php
declare(strict_types=1);
define('MK_INTERNAL',true);require __DIR__.'/server/commerce.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');header('X-Robots-Tag: noindex');
try{
 $action=(string)($_GET['action']??'session');$method=$_SERVER['REQUEST_METHOD']??'GET';
 if(in_array($action,['checkout','pay','order','purchase'])){http_response_code(403);echo mk_json(['error'=>'Echte Zahlung und Bestellung sind gesperrt.','checkoutEnabled'=>false]);exit;}
 if(($action==='session'&&$method!=='GET')||($action!=='session'&&$method!=='POST')){http_response_code(405);header('Allow: '.($action==='session'?'GET':'POST'));echo mk_json(['error'=>'Methode nicht erlaubt.']);exit;}
 $db=mk_commerce_db();$session=mk_commerce_session($db);$body=[];
 if($method==='POST'){
  if(($_SERVER['HTTP_ORIGIN']??'https://meinkraut.odpc.de')!=='https://meinkraut.odpc.de')throw new DomainException('Anfragequelle nicht erlaubt.');
  if(!hash_equals($session['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??''))throw new DomainException('Sicherheitskennung fehlt. Seite erneut laden.');
  $raw=file_get_contents('php://input',false,null,0,1000001);if(strlen($raw)>1000000)throw new InvalidArgumentException('Datensatz zu groß.');$body=json_decode($raw,true,512,JSON_THROW_ON_ERROR);if(!is_array($body))throw new InvalidArgumentException('Ungültige Daten.');
 }
 echo mk_json(mk_commerce_dispatch($db,$session,$action,$body));
}catch(DomainException $e){http_response_code(403);echo mk_json(['error'=>$e->getMessage()]);}catch(InvalidArgumentException|JsonException $e){http_response_code(400);echo mk_json(['error'=>$e->getMessage()]);}catch(Throwable $e){http_response_code(503);error_log('MeinKraut test account service unavailable');echo mk_json(['error'=>'Testkonten sind vorübergehend nicht verfügbar. Dein lokales Regal bleibt nutzbar.']);}
