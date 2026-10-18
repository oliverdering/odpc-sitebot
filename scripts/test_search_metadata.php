<?php
define('ABSPATH', __DIR__);
function add_action(...$args) {}
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_attr($value); }
function wp_json_encode($value, $flags) { return json_encode($value, $flags); }
require __DIR__.'/../site-deploy/ai.odpc.de/odpc-search-metadata.php';
function check($value, $message) { if (!$value) throw new RuntimeException($message); }
$data = ['title'=>'KI & Tools $1', 'description'=>'Kostenlos <testen> & prüfen.', 'url'=>'https://ai.odpc.de/', 'schema'=>['@context'=>'https://schema.org', '@graph'=>[['@type'=>'WebPage', 'name'=>'</script><script>alert(1)</script>']]]];
$body = '<body><h1>Unverändert</h1><script>let voice="male";</script><a href="/start/#downloads">Downloads</a></body></html>';
$input = '<!doctype html><html><head><title>Alt</title><meta content="alt" name="description"><meta name=\'description\' content=\'doppelt\'><meta name="robots" content="noindex, follow"><meta property="og:title" content="Alt"><link href="https://ai.odpc.de/start/" rel="canonical"><style>h1{color:green}</style></head>'.$body;
$out = odpc_ai_search_rewrite($input, $data);
check(substr($out, strpos($out, '<body>')) === $body, 'Body or voice controls changed');
check(substr_count($out, '<title>') === 1, 'Duplicate title');
check(strpos($out, 'KI &amp; Tools $1') !== false, 'Title escaping or dollar preservation failed');
check(substr_count($out, 'name="description"') === 1, 'Duplicate description');
check(substr_count($out, 'rel="canonical"') === 1, 'Duplicate canonical');
check(strpos($out, '<meta name="robots" content="noindex, follow">') !== false, 'Existing noindex removed');
check(strpos($out, '<style>h1{color:green}</style>') !== false, 'Layout changed');
preg_match('~<script type="application/ld\+json" id="odpc-search-metadata-1">(.*?)</script>~s', $out, $json);
check(json_decode($json[1], true, 512, JSON_THROW_ON_ERROR) === $data['schema'], 'Schema JSON mismatch');
check(strpos($out, '</script><script>alert') === false, 'JSON HTML escaping failed');
check(odpc_ai_search_rewrite('not html', $data) === 'not html', 'Non-HTML response changed');
foreach (odpc_ai_search_catalog() as $slug=>$entry) {
    check(strlen($entry[0]) < 100, 'Oversized title: '.$slug);
    check(strlen($entry[1]) < 220, 'Oversized description: '.$slug);
}
echo "PASS: metadata uniqueness, canonical, escaping, JSON, robots and exact body preservation\n";
