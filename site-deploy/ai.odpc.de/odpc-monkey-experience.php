<?php
/**
 * Plugin Name: Helper Monkey Guided Experience
 * Description: Conversation-first entry and focused agent overview using the existing brand.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;
function hm_experience_route() {
 if (is_admin() || is_feed() || is_preview()) return '';
 if (is_front_page()) return 'home';
 if (is_page('start')) return 'agents';
 if (is_page(['connector-einrichten','helper-monkey-einfach-starten-sprachassistent'])) return 'connector';
 if(is_page()) return 'content';
 return '';
}
add_action('wp_enqueue_scripts', function () {
 wp_enqueue_script('hm-guide-core', content_url('/mu-plugins/odpc-monkey-experience/guide-core.js'), [], (string)filemtime(__DIR__.'/odpc-monkey-experience/guide-core.js'), false);
 wp_enqueue_style('hm-experience', content_url('/mu-plugins/odpc-monkey-experience/experience.css'), [], (string)filemtime(__DIR__.'/odpc-monkey-experience/experience.css'));
}, 20);
add_filter('template_include', function ($template) {
 return hm_experience_route() ? __DIR__.'/odpc-monkey-experience/page.php' : $template;
}, 99);
add_filter('pre_get_document_title', function($title){
 $r=hm_experience_route();
 return $r==='home'?'helper · monkey © – Deine KI. Dein Alltag. Einfach verbunden.':($r==='agents'?'Die Monkeys – Aufgaben und Entwicklungsstand | helper · monkey':($r==='connector'?'KI einrichten mit Monkey – Gespräch, Connector und persönliche Hilfe':$title));
},99);
add_action('wp_footer', function(){ ?>
<script>
(function(){
 document.querySelectorAll('[data-monkey-open]').forEach(function(b){b.addEventListener('click',function(){var v=document.getElementById('odpc-voice-open');if(v)v.click();else location.href='/helper-monkey-einfach-starten-sprachassistent/';});});
})();
</script>
<?php },99);

add_filter('the_content', function($content){
 if(!hm_experience_route() || !class_exists('DOMDocument')) return $content;
 $content=preg_replace('/individuelle ODPC-Beratung(?: bzw\. eingerichtete Lösungen auf Anfrage)?/iu','vorhandene Anleitungen und einzeln geprüfte Arbeitsabläufe',$content);
 $dom=new DOMDocument();$prior=libxml_use_internal_errors(true);
 $dom->loadHTML('<?xml encoding="UTF-8"><div id="hm-preserved-content">'.$content.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
 libxml_clear_errors();libxml_use_internal_errors($prior);
 $remove=[];
 foreach($dom->getElementsByTagName('a') as $link){
  $url=$link->getAttribute('href');$text=$link->textContent;
  if(preg_match('~odpc\.de/(?:init|webdesign|ki-agenten-persoenlich|ki-dienstleistungen)~i',$url) || preg_match('/initIQ|Webdesign und Leistungen|Einrichtung gegen Honorar|IT, Unternehmen und Beratung|Webdesign und Live-Referenzen|Remote-Unterstützung direkt von ODPC/i',$text)) $remove[]=$link;
 }
 foreach($remove as $link)if($link->parentNode)$link->parentNode->removeChild($link);
 $root=$dom->getElementById('hm-preserved-content');if(!$root)return $content;
 $result='';foreach($root->childNodes as $node)$result.=$dom->saveHTML($node);
 return $result;
},100);
