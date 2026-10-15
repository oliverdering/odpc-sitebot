<?php
/**
 * Plugin Name: ODPC Helper Monkey V4 Bridge
 * Description: Links ODPC services to the multilingual Helper Monkey start guide and preserves the existing WhatsApp Business button.
 * Version: 4.0.1
 */
if(!defined('ABSPATH'))exit;
add_action('wp_head',function(){
 if(is_admin())return;
 echo '<style id="odpc-hm-v4-style">
 .odpc-hm-v4-top{display:flex;align-items:center;justify-content:space-between;gap:11px;flex-wrap:wrap;padding:9px clamp(14px,3vw,29px);background:#222b36;color:#eee5d9;font:700 14px/1.45 system-ui,sans-serif;border-bottom:1px solid #ad9177}
 .odpc-hm-v4-top a{color:#f5e7d8!important;text-decoration:none!important}.odpc-hm-v4-top .odpc-hm-v4-flags{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
 .odpc-hm-v4-flags a{display:inline-flex;align-items:center;justify-content:center;border:1px solid #927865;border-radius:8px;min-height:39px;min-width:36px;font-size:20px}
 .odpc-hm-v4-wa{position:fixed;z-index:9997;right:16px;bottom:16px;display:flex;align-items:center;gap:8px;min-height:47px;background:#1c754d;color:white!important;text-decoration:none!important;border:2px solid #fff;border-radius:999px;padding:11px 16px;box-shadow:0 6px 23px #142d3350;font:800 14px system-ui,sans-serif}
 .odpc-hm-v4-helpbtn{position:fixed;z-index:9997;right:16px;bottom:76px;border:2px solid #a9917e;border-radius:999px;background:#202e39;color:white;min-height:42px;padding:9px 14px;font:700 13px system-ui,sans-serif;cursor:pointer}
 .odpc-hm-v4-help{display:none;position:fixed;z-index:9997;right:16px;bottom:127px;width:min(390px,calc(100vw - 32px));max-height:65vh;overflow:auto;border-radius:13px;background:white;padding:17px;border:1px solid #bb9984;box-shadow:0 6px 28px #17233050;color:#27313b;font:15px/1.5 system-ui,sans-serif}
 .odpc-hm-v4-help.is-open{display:block}.odpc-hm-v4-help h2{font-size:20px;margin:0 0 8px;color:#243647}.odpc-hm-v4-help a{display:block;color:#504239!important;text-decoration:none;border:1px solid #e1d5cd;border-radius:9px;background:#f9f8f6;margin:7px 0;padding:9px;font-weight:760}
 .odpc-hm-v4-help button{border:0;border-radius:8px;min-height:40px;padding:8px 14px;background:#26313f;color:white;font:inherit;cursor:pointer}
 .odpc-hm-v4-top a:focus-visible,.odpc-hm-v4-wa:focus-visible,.odpc-hm-v4-helpbtn:focus-visible{outline:3px solid #d4ac81;outline-offset:3px}
 @media(max-width:580px){.odpc-hm-v4-wa{right:9px;bottom:9px;font-size:13px}.odpc-hm-v4-helpbtn{right:9px;bottom:65px}.odpc-hm-v4-help{right:9px;bottom:117px}}
 </style>';
},97);
add_action('wp_footer',function(){
 if(is_admin())return;
 echo '<button id="odpc-hm-v4-helpbtn" class="odpc-hm-v4-helpbtn" type="button" aria-expanded="false" aria-controls="odpc-hm-v4-help">❔ Fragen &amp; Hilfe</button>';
 echo '<section id="odpc-hm-v4-help" class="odpc-hm-v4-help" aria-label="ODPC Selbsthilfe"><h2>ODPC hilft dir weiter</h2><p>Du musst nicht wissen, wie KI funktioniert. Beginne mit einem kurzen Interview oder wähle dein Thema.</p><a href="https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/">🎙 Einfach starten – Interview mit sechs Sprachen</a><a href="https://ai.odpc.de/start/">🐵 Kostenlose und professionelle Agenten</a><a href="https://odpc.de/initiq">🏢 KI für Firmen – initIQ</a><a href="https://odpc.de/l-i-f-e-mpu-selbstlernkurs-neun-lernmodule/">🌱 L.I.F.E. – MPU-Selbstlernbereich</a><a href="https://odpc.de/contact-me">✉ Kontakt zu ODPC</a><p>Über WhatsApp Business kannst du persönlich um Hilfe bitten.</p><button id="odpc-hm-v4-close" type="button">Schließen</button></section>';
 echo '<a id="odpc-hm-v4-wa" class="odpc-hm-v4-wa" href="'.esc_url('https://wa.me/492654882183?text='.rawurlencode('Hallo Oliver, ich brauche Unterstützung auf odpc.de.')).'" target="_blank" rel="noopener noreferrer" aria-label="ODPC WhatsApp Business kontaktieren">✆ WhatsApp Business</a>';
 echo '<script id="odpc-hm-v4-js">(function(){var b=document.getElementById("odpc-hm-v4-helpbtn"),p=document.getElementById("odpc-hm-v4-help");if(!b||!p)return;function open(v){p.classList.toggle("is-open",v);b.setAttribute("aria-expanded",String(v))}b.addEventListener("click",function(){open(!p.classList.contains("is-open"))});document.getElementById("odpc-hm-v4-close").addEventListener("click",function(){open(false);b.focus()});document.addEventListener("keydown",function(e){if(e.key==="Escape")open(false)});if(document.querySelector(".odpc-wa-sticky")){document.getElementById("odpc-hm-v4-wa").remove()}})();</script>';
},35);
