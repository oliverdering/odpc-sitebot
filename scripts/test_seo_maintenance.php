<?php
define('ABSPATH',__DIR__);
$GLOBALS['filters']=[];
function add_filter($name,$callback,$priority=10,$args=1){$GLOBALS['filters'][$name]=$callback;}
function add_action($name,$callback,$priority=10,$args=1){}
function esc_attr($s){return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function esc_html($s){return esc_attr($s);}
function esc_url($s){return esc_attr($s);}
function wp_parse_url($u,$component=-1){return parse_url($u,$component);}
function get_page_by_path($s){return (object)['ID'=>str_contains($s,'leck')?17:18];}
function get_posts($a){return [1];}
require __DIR__.'/../site-deploy/seo-maintenance/odpc-seo-maintenance.php';
function check($condition,$message){if(!$condition)throw new RuntimeException($message);}
$body='<body><header id="original">Unverändertes Layout</header><main><h1>Bestehende Texte</h1><img src="original.webp"><form><input name="x"></form></main></body>';
$source='<html><head><title>Alt</title><meta name="description" content="Alt"><meta name="description" content="Alt"><meta name="robots" content="index,follow"><link rel="canonical" href="https://example.test/a"><link rel="alternate" hreflang="de" href="https://atrium191.de/wohnungen/we01/"></head>'.$body.'</html>';
foreach(['odpc.de','ai.odpc.de','tante-lenchen.odpc.de','atrium191.de','atrium191.com','ra-justen.de','bahnhof-grenzau.de','guenana-bsv.de']as$host){check(odpc_seom_transform($source,$host,'/')===$source,'Homepage changed: '.$host);}
foreach([['odpc.de','/leck-im-system'],['tante-lenchen.odpc.de','/tl_dish/champignonschnitzel'],['tante-lenchen.odpc.de','/author/lenchen_webmaster'],['odpc.de','/initiq'],['ai.odpc.de','/connector-einrichten'],['atrium191.com','/apartments/we01']]as[$host,$path]){
 $out=odpc_seom_transform($source,$host,$path);check(str_contains($out,$body),'Visible body changed: '.$path);check(substr_count($out,'<head>')===1,'Broken head');
}
$out=odpc_seom_transform($source,'tante-lenchen.odpc.de','/tl_dish/champignonschnitzel');check(str_contains($out,'content="noindex, follow"'),'Dish indexable');
check(odpc_seom_policy('tante-lenchen.odpc.de','/speisekarte')===null,'Menu modified');
check(odpc_seom_policy('odpc.de','/kunden-referenzen')===null,'Unrelated page modified');
check(odpc_seom_policy('another.example','/leck-im-system')===null,'Host boundary crossed');
$out=odpc_seom_transform($source,'ai.odpc.de','/connector-einrichten');check(substr_count($out,'rel="canonical"')===1&&str_contains($out,'href="https://ai.odpc.de/"'),'Connector canonical');
$out=odpc_seom_transform($source,'atrium191.com','/apartments/we01');check(substr_count($out,'name="description"')===1,'Duplicate English description');check(str_contains($out,'hreflang="de"'),'Hreflang changed');check(str_contains($out,'Apartment WE01'),'Missing unit-specific description');
$_SERVER['HTTP_HOST']='tante-lenchen.odpc.de';$types=($GLOBALS['filters']['wp_sitemaps_post_types'])(['tl_dish'=>(object)[],'page'=>(object)[]]);check(!isset($types['tl_dish'])&&isset($types['page']),'Dish-only sitemap exclusion');
$args=($GLOBALS['filters']['wp_sitemaps_posts_query_args'])(['post__not_in'=>[22]],'post');check($args['post__not_in']===[22,1],'Sitemap retains earlier exclusions');
check(($GLOBALS['filters']['wp_sitemaps_add_provider'])('original','users')===false,'Users sitemap retained');
$_SERVER['HTTP_HOST']='odpc.de';check(($GLOBALS['filters']['wp_sitemaps_add_provider'])('original','users')==='original','Unrelated sitemap provider modified');
echo "PASS: homepage freeze, visible-body preservation, host boundaries, canonical, unit metadata and sitemap exclusions\n";
