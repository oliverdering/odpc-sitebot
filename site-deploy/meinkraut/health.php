<?php
declare(strict_types=1);
define('MK_INTERNAL',true);require __DIR__.'/server/database.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
try {[$atlas,$extra,$source]=mk_public_data();echo json_encode(['status'=>'ok','version'=>$extra['version'],'catalog'=>$source,'ingredients'=>count($atlas['ingredients']),'recipes'=>count($atlas['recipes'])+count($extra['mixes']),'countries'=>count($atlas['countries']),'countryProfiles'=>count($extra['countries']),'spiceProfiles'=>count($extra['ingredients'])]);}
catch(Throwable $e){http_response_code(503);echo '{"status":"unavailable"}';}
