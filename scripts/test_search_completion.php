<?php
define('ABSPATH', __DIR__);
function add_action(...$args) {}
function esc_attr($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_url($s) { return esc_attr($s); }
require __DIR__.'/../site-deploy/search-completion/odpc-seo-completion.php';
function verify($v, $m) { if (!$v) throw new RuntimeException($m); }
$body='<body><h1>Original</h1><script>const voice="female";</script></body></html>';
$original='<html><head><title>Original</title><meta name="robots" content="noindex,nofollow"><style>a{color:red}</style></head>'.$body;
$out=odpc_search_completion_head($original, 'Test <tags> & $1', 'https://example.test/', true);
verify(substr($out,strpos($out,'<body>'))===$body,'Body changed');
verify(!str_contains($out,'noindex'),'Release kept noindex');
verify(str_contains($out,'index, follow'),'Release rules missing');
verify(str_contains($out,'Test &lt;tags&gt; &amp; $1'),'Description not escaped');
verify(substr_count($out,'name="description"')===1,'Duplicate description');
verify(substr_count($out,'rel="canonical"')===1,'Duplicate canonical');
verify(odpc_search_completion_head($out,'Replacement','https://elsewhere.test/')===$out,'Existing SEO changed');
verify(str_contains(odpc_search_completion_head($original,'Test','https://example.test/'),'noindex,nofollow'),'Unreleased noindex removed');
verify(odpc_search_completion_head('not HTML','x','y',true)==='not HTML','Non-HTML changed');
echo "PASS: escaping, existing metadata, body, release and private robots preservation\n";
