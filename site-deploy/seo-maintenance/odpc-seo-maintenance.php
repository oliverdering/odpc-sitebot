<?php
/** Plugin Name: ODPC Scoped SEO Maintenance
 * Description: Search metadata and sitemap cleanup without homepage or layout changes.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;

function odpc_seom_host() {
    return strtolower(preg_replace('/:[0-9]+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
}
function odpc_seom_path() {
    $path = (string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return '/' . trim(preg_replace('~^/index\.php(?:/|$)~', '/', $path), '/');
}
function odpc_seom_policy($host, $path) {
    $path = '/' . trim($path, '/');
    if ($path === '/') return null; // Explicit homepage freeze, including query-string variants.
    if ($host === 'odpc.de') {
        if ($path === '/leck-im-system') return ['noindex' => true];
        $meta = [
            '/initiq' => ['initIQ – KI-Einführung für Unternehmen | ODPC Koblenz', 'KI im Unternehmen einführen: ODPC verbindet vorhandene IT, Konten und Arbeitsabläufe. Persönliche Analyse, Einrichtung und Einweisung in der Region Koblenz und remote.'],
            '/ki-gestuetzte-dienstleistungen-automatisierung' => ['KI-Automatisierung für Unternehmen | ODPC Koblenz', 'Wiederkehrende Büroarbeit mit KI vereinfachen: ODPC analysiert Abläufe, richtet passende Assistenten ein und prüft die Übergabe gemeinsam mit Ihrem Unternehmen.'],
        ];
        if (isset($meta[$path])) return ['title'=>$meta[$path][0], 'description'=>$meta[$path][1]];
    }
    if ($host === 'tante-lenchen.odpc.de' && (
        str_starts_with($path, '/tl_dish/') || $path === '/tl_dish' ||
        str_starts_with($path, '/author/') || str_starts_with($path, '/category/') ||
        $path === '/2026/10/17/hallo-welt'
    )) return ['noindex'=>true];
    if ($host === 'ai.odpc.de' && $path === '/connector-einrichten') {
        return ['canonical'=>'https://ai.odpc.de/'];
    }
    if (in_array($host, ['atrium191.de','atrium191.com'], true)) {
        $en = $host === 'atrium191.com';
        if ($path === '/sample-page') return ['noindex'=>true];
        if (preg_match('~^/(?:wohnungen|apartments)/we(0[1-9]|1[0-6])$~i', $path, $m)) {
            $n=(int)$m[1]; $unit='WE'.sprintf('%02d',$n);
            $deFloor=$n<=6?'im Erdgeschoss':($n<=13?'im 1. Obergeschoss':'auf der Penthouseebene');
            $enFloor=$n<=6?'on the ground floor':($n<=13?'on the first floor':'on the penthouse level');
            return $en
                ? ['title'=>$unit.' – Floor plan & location | ATRIUM 191', 'description'=>'Apartment '.$unit.' at ATRIUM 191 in Ransbach-Baumbach: explore its original floor plan and position '.$enFloor.'. Request current project documents and availability.']
                : ['title'=>$unit.' – Grundriss & Lage | ATRIUM 191', 'description'=>'Wohnung '.$unit.' im ATRIUM 191 in Ransbach-Baumbach: Originalgrundriss und Lage '.$deFloor.' ansehen. Aktuelle Projektunterlagen und Verfügbarkeit anfragen.'];
        }
        $catalog = [
            '/architektur'=>'Architektur und Planung von ATRIUM 191: die vorhandenen Gebäudeansichten, den Umbau und das Wohnkonzept in Ransbach-Baumbach kennenlernen.',
            '/lage'=>'Die Lage von ATRIUM 191 an der Rheinstraße in Ransbach-Baumbach entdecken: Umfeld, regionale Verbindungen und Informationen zum Standort.',
            '/geschichte'=>'Die Standortgeschichte von ATRIUM 191 in Ransbach-Baumbach: dokumentierte Hintergründe und die Entwicklung vom Gewerbegebäude zum Wohnprojekt.',
            '/kontakt'=>'Kontakt zum Wohnprojekt ATRIUM 191: aktuelle Informationen, Grundrisse und Projektunterlagen für Ransbach-Baumbach persönlich anfragen.',
            '/erleben'=>'Ansichten und Originalpläne von ATRIUM 191 entdecken: Architektur und Grundrisse des Wohnprojekts in Ransbach-Baumbach im Überblick.',
            '/architecture'=>'Discover the architecture and planning of ATRIUM 191: original building views, the conversion and the residential concept in Ransbach-Baumbach.',
            '/location'=>'Explore the location of ATRIUM 191 on Rheinstrasse in Ransbach-Baumbach: surroundings, regional connections and information about the site.',
            '/history'=>'Discover the documented history of the ATRIUM 191 site in Ransbach-Baumbach and its development from commercial premises to a residential project.',
            '/contact'=>'Contact the ATRIUM 191 project team to request current information, original floor plans and project documents for Ransbach-Baumbach.',
            '/views-and-plans'=>'Explore original architectural views and plans for ATRIUM 191: a closer look at the building and apartment layouts in Ransbach-Baumbach.',
        ];
        if (isset($catalog[$path])) return ['description'=>$catalog[$path], 'dedupe'=>$en];
        if ($en) return ['dedupe'=>true];
    }
    return null;
}

function odpc_seom_replace_meta($head, $attribute, $name, $value) {
    $pattern='~<meta\b(?=[^>]*\b'.preg_quote($attribute,'~').'\s*=\s*([\x22\x27])'.preg_quote($name,'~').'\1)[^>]*>\s*~is';
    $head=preg_replace($pattern, '', $head);
    $tag='<meta '.$attribute.'="'.esc_attr($name).'" content="'.esc_attr($value).'">'."\n";
    return preg_replace_callback('~</head\s*>~i', static function()use($tag){return $tag.'</head>';}, $head, 1);
}
function odpc_seom_transform($html, $host, $path) {
    $policy=odpc_seom_policy($host,$path);
    if (!$policy || !preg_match('~<head\b[^>]*>.*?</head\s*>~is',$html,$m,PREG_OFFSET_CAPTURE)) return $html;
    $head=$m[0][0];
    if (isset($policy['title'])) {
        $head=preg_replace('~<title\b[^>]*>.*?</title\s*>\s*~is','',$head);
        $title='<title>'.esc_html($policy['title']).'</title>'."\n";
        $head=preg_replace_callback('~</head\s*>~i',static function()use($title){return $title.'</head>';},$head,1);
        foreach (['og:title'=>'property','twitter:title'=>'name'] as $key=>$attr) $head=odpc_seom_replace_meta($head,$attr,$key,$policy['title']);
    }
    if (isset($policy['description'])) {
        $head=odpc_seom_replace_meta($head,'name','description',$policy['description']);
        foreach (['og:description'=>'property','twitter:description'=>'name'] as $key=>$attr) $head=odpc_seom_replace_meta($head,$attr,$key,$policy['description']);
    } elseif (!empty($policy['dedupe'])) {
        $seen=[];
        $head=preg_replace_callback('~<meta\b(?=[^>]*\bname\s*=\s*([\x22\x27])(?:description|robots)\1)[^>]*>\s*~is', static function($tag)use(&$seen){
            preg_match('~\bname\s*=\s*([\x22\x27])([^\x22\x27]+)\1~i',$tag[0],$name);
            $key=strtolower($name[2]??''); if(isset($seen[$key]))return ''; $seen[$key]=true;return $tag[0];
        },$head);
    }
    if (isset($policy['canonical'])) {
        $head=preg_replace('~<link\b(?=[^>]*\brel\s*=\s*([\x22\x27])canonical\1)[^>]*>\s*~is','',$head);
        $tag='<link rel="canonical" href="'.esc_url($policy['canonical']).'">'."\n";
        $head=preg_replace_callback('~</head\s*>~i',static function()use($tag){return $tag.'</head>';},$head,1);
        $head=odpc_seom_replace_meta($head,'property','og:url',$policy['canonical']);
    }
    if (!empty($policy['noindex'])) {
        $head=preg_replace('~<meta\b(?=[^>]*\bname\s*=\s*([\x22\x27])(?:robots|googlebot)\1)[^>]*>\s*~is','',$head);
        $head=odpc_seom_replace_meta($head,'name','robots','noindex, follow');
    }
    return substr_replace($html,$head,$m[0][1],strlen($m[0][0]));
}

function odpc_seom_excluded_ids() {
    $host=odpc_seom_host();$ids=[];
    if($host==='odpc.de'){$p=get_page_by_path('leck-im-system');if($p)$ids[]=(int)$p->ID;}
    if($host==='tante-lenchen.odpc.de'){
        $ids=get_posts(['post_type'=>'post','post_status'=>'publish','name'=>'hallo-welt','fields'=>'ids','posts_per_page'=>5]);
    }
    if(in_array($host,['atrium191.de','atrium191.com'],true)){$p=get_page_by_path('sample-page');if($p)$ids[]=(int)$p->ID;}
    return array_map('intval',$ids);
}
add_filter('wp_sitemaps_post_types',function($types){if(odpc_seom_host()==='tante-lenchen.odpc.de')unset($types['tl_dish']);return $types;},99);
add_filter('wp_sitemaps_add_provider',function($provider,$name){
    return odpc_seom_host()==='tante-lenchen.odpc.de'&&in_array($name,['users','taxonomies'],true)?false:$provider;
},99,2);
add_filter('wp_sitemaps_posts_query_args',function($args,$type){
    $ids=odpc_seom_excluded_ids();if($ids)$args['post__not_in']=array_values(array_unique(array_merge($args['post__not_in']??[],$ids)));return $args;
},99,2);
add_filter('rank_math/sitemap/entry',function($entry,$type,$object){
    return $type==='post'&&isset($object->ID)&&in_array((int)$object->ID,odpc_seom_excluded_ids(),true)?false:$entry;
},99,3);
add_filter('rank_math/sitemap/enable_caching',function($enabled){return odpc_seom_host()==='odpc.de'?false:$enabled;},99);
add_filter('wp_robots',function($robots){
    $p=odpc_seom_policy(odpc_seom_host(),odpc_seom_path());
    if(!empty($p['noindex'])){unset($robots['index'],$robots['nofollow']);$robots['noindex']=true;$robots['follow']=true;}
    return $robots;
},999);
add_filter('rank_math/frontend/robots',function($robots){
    $p=odpc_seom_policy(odpc_seom_host(),odpc_seom_path());
    if(!empty($p['noindex'])){$robots['index']='noindex';$robots['follow']='follow';unset($robots['nofollow']);}return $robots;
},999);
add_action('template_redirect',function(){
    if(is_admin()||is_feed()||is_search()||is_preview()||is_embed()||wp_doing_ajax()||(defined('REST_REQUEST')&&REST_REQUEST))return;
    $host=odpc_seom_host();$path=odpc_seom_path();$policy=odpc_seom_policy($host,$path);
    if(!$policy||$path==='/'||is_front_page())return;
    if(!empty($policy['noindex']))header('X-Robots-Tag: noindex, follow');
    // Earlier than existing metadata buffers: this finalizes HEAD after they run.
    ob_start(static function($html)use($host,$path){return odpc_seom_transform($html,$host,$path);});
},-3000);
