<?php
declare(strict_types=1);
$token='__INSTALL_TOKEN__';
if (!isset($_GET['token']) || !hash_equals($token,(string)$_GET['token'])) { http_response_code(404); exit('Not found'); }
header('Cache-Control: no-store');
$root=__DIR__;
if (is_file($root.'/wp-config.php') || is_dir($root.'/wp-admin')) { http_response_code(409); exit('WordPress already present. Refusing overwrite.'); }
$zipPath=$root.'/wordpress-latest.zip';
if (!is_file($zipPath)) { http_response_code(404); exit('Archive missing'); }
if (!class_exists('ZipArchive')) { http_response_code(500); exit('PHP ZipArchive extension unavailable'); }
$zip=new ZipArchive();
if ($zip->open($zipPath)!==true) { http_response_code(500); exit('Unable to open archive'); }
for ($i=0;$i<$zip->numFiles;$i++) {
 $name=$zip->getNameIndex($i);
 if (!str_starts_with($name,'wordpress/') || str_contains($name,'../') || str_contains($name,'\\')) { $zip->close(); http_response_code(400); exit('Unexpected ZIP entry'); }
}
if ($_SERVER['REQUEST_METHOD']!=='POST') {
 $zip->close();
 echo '<!doctype html><html lang="de"><meta charset="utf-8"><title>Bahnhof Grenzau – WordPress</title><h1>WordPress installieren</h1><p>WordPress wird im Hauptverzeichnis installiert. Vorhandene Installationen werden nicht überschrieben.</p><form method="post"><button>ZIP entpacken</button></form></html>';
 exit;
}
for ($i=0;$i<$zip->numFiles;$i++) {
 $name=$zip->getNameIndex($i);
 $relative=substr($name,10);
 if ($relative==='') continue;
 $dest=$root.'/'.$relative;
 if (str_ends_with($name,'/')) { if (!is_dir($dest) && !mkdir($dest,0755,true)) {throw new RuntimeException('Directory creation failed');} continue; }
 $dir=dirname($dest);
 if (!is_dir($dir) && !mkdir($dir,0755,true)) throw new RuntimeException('Directory creation failed');
 if (file_exists($dest)) throw new RuntimeException('Refusing overwrite');
 $source=$zip->getStream($name);
 if (!$source) throw new RuntimeException('ZIP read error');
 $target=fopen($dest,'xb');
 if (!$target) throw new RuntimeException('File create error');
 stream_copy_to_stream($source,$target);
 fclose($source); fclose($target);
}
$zip->close();
@unlink($zipPath);
@unlink(__FILE__);
header('Location: /wp-admin/install.php',true,303);
exit;
