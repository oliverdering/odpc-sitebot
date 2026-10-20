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
 return '';
}
add_action('wp_enqueue_scripts', function () {
 wp_enqueue_script('hm-guide-core', content_url('/mu-plugins/odpc-monkey-experience/guide-core.js'), [], '1.0.0', false);
 wp_enqueue_style('hm-experience', content_url('/mu-plugins/odpc-monkey-experience/experience.css'), [], '1.0.0');
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
