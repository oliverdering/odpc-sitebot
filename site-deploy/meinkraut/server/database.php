<?php
declare(strict_types=1);
if (!defined('MK_INTERNAL')) { http_response_code(404); exit; }
function mk_config_path(): string {
    return dirname(__DIR__,2).'/.meinkraut-private-'.substr(hash('sha256',dirname(__DIR__)),0,12).'/config.php';
}
function mk_database(): ?PDO {
    $path=mk_config_path();
    if (!is_file($path)) return null;
    $c=require $path;
    if (!is_array($c)) return null;
    return new PDO('mysql:host='.$c['host'].';port=3306;dbname='.$c['database'].';charset=utf8mb4',$c['user'],$c['password'],[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES=>false,
        PDO::ATTR_TIMEOUT=>8,
    ]);
}
function mk_public_data(): array {
    try {
        $pdo=mk_database();
        if ($pdo) {
            $q=$pdo->query('SELECT atlas_json, extra_json FROM mk_releases WHERE active=1 ORDER BY created_at DESC LIMIT 1');
            $r=$q->fetch(PDO::FETCH_ASSOC);
            if ($r) return [json_decode($r['atlas_json'],true,512,JSON_THROW_ON_ERROR),json_decode($r['extra_json'],true,512,JSON_THROW_ON_ERROR),'database'];
        }
    } catch (Throwable $e) { error_log('Mein Kraut: public catalog database fallback'); }
    return [json_decode(file_get_contents(dirname(__DIR__).'/content/atlas-base.json'),true,512,JSON_THROW_ON_ERROR),json_decode(file_get_contents(dirname(__DIR__).'/content/meinkraut-content.json'),true,512,JSON_THROW_ON_ERROR),'bundle'];
}
