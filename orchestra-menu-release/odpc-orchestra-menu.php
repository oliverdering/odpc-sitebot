<?php
/** Plugin Name: ODPC KI-Orchester Menü */
if (!defined('ABSPATH')) { exit; }
add_action('wp_footer', function () {
 if (!is_front_page()) { return; }
 ?>
<script id="odpc-orchestra-menu-link">(function(){
 function mount(){
  var panel=document.getElementById('odpc-nav-panel');if(!panel)return;
  if(panel.querySelector('a[href="https://ai.odpc.de/ki-orchester/"]'))return;
  var existing=panel.querySelector('a[href*="prompt-engineering-digitale-mitarbeiter"]');
  if(!existing||!existing.parentElement)return;
  var a=document.createElement('a');a.href='https://ai.odpc.de/ki-orchester/';a.textContent='KI-Orchester & Systemkarte';existing.parentElement.appendChild(a);
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',mount);else mount();
 window.addEventListener('load',mount);
})();</script>
<?php
}, 150);
