<?php
define('ABSPATH',__DIR__);
$filters=[];$slug='struktur-portfolio';$front=false;
function add_action(...$args){}
function add_filter($name,$fn,...$rest){global $filters;$filters[$name]=$fn;}
function is_admin(){return false;}
function is_front_page(){global $front;return $front;}
function is_page($pages){global $slug;return in_array($slug,(array)$pages,true);}
function is_main_query(){return true;}
function in_the_loop(){return true;}
function get_post_field(...$args){global $slug;return $slug;}
function get_queried_object_id(){return 1;}
function esc_url($s){return htmlspecialchars($s,ENT_QUOTES);}
function esc_html($s){return htmlspecialchars($s,ENT_QUOTES);}
function esc_attr($s){return htmlspecialchars($s,ENT_QUOTES);}
require __DIR__.'/../../site-deploy/shared/odpc-monkey-offers.php';
function check($yes,$why){if(!$yes)throw new RuntimeException($why);}
$_SERVER['HTTP_HOST']='odpc.de';$original='<article id="original">Vorhandene Fachinhalte bleiben vollständig.</article>';
foreach(array_merge(array_keys(om_catalog()),['struktur-portfolio','prompt-engineering-digitale-mitarbeiter-ki-sinnvoll-nutzen','einsatz-von-ki-je-nach-branche']) as $slug){
 $html=$filters['the_content']($original);check(strpos($html,$original)!==false,'Original content changed');
 check(substr_count($html,'id="om-premium"')===1,'Missing or duplicate premium section');
 check(strpos($html,'Kostenpflichtige persönliche Leistung.')!==false,'Missing cost boundary');
 check($filters['the_content']($html)===$html,'Duplicate insertion');
 if(isset(om_catalog()[$slug]))check(substr_count($html,'data-product=')===1,'Unrelated catalog on specialist page');
}
$slug='impressum';check($filters['the_content']($original)===$original,'Legal page changed');
$_SERVER['HTTP_HOST']='ai.odpc.de';check($filters['the_content']($original)===$original,'Wrong site content injection');
echo "PASS offer map, specialist context, original content, idempotence and site boundaries\n";
