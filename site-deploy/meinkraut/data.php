<?php
declare(strict_types=1);
define('MK_INTERNAL',true);require __DIR__.'/server/database.php';
header('Content-Type: application/javascript; charset=utf-8');header('X-Content-Type-Options: nosniff');header('Cache-Control: public, max-age=300');
try {
    [$atlas,$extra,$source]=mk_public_data();
    $flags=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR;
    $payload='window.WGA_DATA='.json_encode($atlas,$flags).';window.MEINKRAUT_CONTENT='.json_encode($extra,$flags).';';
    $etag='"'.hash('sha256',$payload).'"';header('ETag: '.$etag);header('X-MeinKraut-Catalog: '.$source);
    if (($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);exit;}
    header('Vary: Accept-Encoding');
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING']??'', 'gzip')!==false && function_exists('gzencode') && !ini_get('zlib.output_compression')) {
        header('Content-Encoding: gzip');
        echo gzencode($payload,6);
    } else { echo $payload; }
} catch(Throwable $e) {http_response_code(503);header('Cache-Control: no-store');echo 'console.error("Der Gewürzkatalog ist vorübergehend nicht verfügbar.");';}
