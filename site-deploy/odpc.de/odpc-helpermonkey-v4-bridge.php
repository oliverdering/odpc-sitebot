<?php
/**
 * Plugin Name: ODPC Helper Monkey V4 Bridge
 * Description: Links ODPC services to the multilingual Helper Monkey start guide and preserves the existing WhatsApp Business button.
 * Version: 4.0.3
 */
if(!defined('ABSPATH'))exit;
add_action('wp_head',function(){
 if(is_admin())return;
 echo '<style id="odpc-hm-v4-style">
 /* ODPC uses navy/sand branding; the Helper Monkey product site alone uses green. */
 #odpc-home aside[aria-label="Helper Monkey V4 kostenlos starten"]{background:#202e39!important;border-color:#a9917e!important;color:#f5e7d8!important}
 #odpc-home aside[aria-label="Helper Monkey V4 kostenlos starten"] strong{color:#f5e7d8!important}
 #odpc-home aside[aria-label="Helper Monkey V4 kostenlos starten"] p{color:#e7dfd6!important}
 #odpc-home aside[aria-label="Helper Monkey V4 kostenlos starten"] a{color:#24313e!important;background:#f5e7d8!important}
 .odpc-hm-v4-top{display:flex;align-items:center;justify-content:space-between;gap:11px;flex-wrap:wrap;padding:9px clamp(14px,3vw,29px);background:#222b36;color:#eee5d9;font:700 14px/1.45 system-ui,sans-serif;border-bottom:1px solid #ad9177}
 .odpc-hm-v4-top a{color:#f5e7d8!important;text-decoration:none!important}.odpc-hm-v4-top .odpc-hm-v4-flags{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
 .odpc-hm-v4-flags a{display:inline-flex;align-items:center;justify-content:center;border:1px solid #927865;border-radius:8px;min-height:39px;min-width:36px;font-size:20px}
 .odpc-hm-v4-wa{position:fixed;z-index:9997;right:16px;bottom:16px;display:flex;align-items:center;gap:8px;min-height:47px;background:#1c754d;color:white!important;text-decoration:none!important;border:2px solid #fff;border-radius:999px;padding:11px 16px;box-shadow:0 6px 23px #142d3350;font:800 14px system-ui,sans-serif}
 .odpc-hm-v4-helpbtn{position:fixed;z-index:9997;right:16px;bottom:76px;border:2px solid #a9917e;border-radius:999px;background:#202e39;color:white;min-height:42px;padding:9px 14px;font:700 13px system-ui,sans-serif;cursor:pointer}
 .odpc-hm-v4-help{display:none;position:fixed;z-index:9997;right:16px;bottom:127px;width:min(390px,calc(100vw - 32px));max-height:65vh;overflow:auto;border-radius:13px;background:white;padding:17px;border:1px solid #bb9984;box-shadow:0 6px 28px #17233050;color:#27313b;font:15px/1.5 system-ui,sans-serif}
 .odpc-hm-v4-help.is-open{display:block}.odpc-hm-v4-help h2{font-size:20px;margin:0 0 8px;color:#243647}.odpc-hm-v4-help a{display:block;color:#504239!important;text-decoration:none;border:1px solid #e1d5cd;border-radius:9px;background:#f9f8f6;margin:7px 0;padding:9px;font-weight:760}
 .odpc-hm-v4-help button{border:0;border-radius:8px;min-height:40px;padding:8px 14px;background:#26313f;color:white;font:inherit;cursor:pointer}
 .odpc-hm-v4-top a:focus-visible,.odpc-hm-v4-wa:focus-visible,.odpc-hm-v4-helpbtn:focus-visible{outline:3px solid #d4ac81;outline-offset:3px}
 @media(max-width:580px){.odpc-hm-v4-wa{right:10px;bottom:10px;font-size:12px;padding:8px 11px;min-height:38px}.odpc-hm-v4-helpbtn{right:10px;bottom:58px;font-size:12px;padding:7px 11px;min-height:36px}.odpc-hm-v4-help{right:10px;bottom:105px}}
 /* The homepage has its own contact journey: do not cover its hero, profile or headings. */
 body.home .odpc-hm-v4-wa,body.home .odpc-hm-v4-helpbtn,body.front-page .odpc-hm-v4-wa,body.front-page .odpc-hm-v4-helpbtn{display:none!important}
 </style>';
},97);
add_action('wp_footer',function(){
 if(is_admin())return;
 echo '<button id="odpc-hm-v4-helpbtn" class="odpc-hm-v4-helpbtn" type="button" aria-expanded="false" aria-controls="odpc-hm-v4-help">❔ Fragen &amp; Hilfe</button>';
 echo '<section id="odpc-hm-v4-help" class="odpc-hm-v4-help" aria-label="ODPC Selbsthilfe"><h2>ODPC hilft dir weiter</h2><p>Du musst nicht wissen, wie KI funktioniert. Beginne mit einem kurzen Interview oder wähle dein Thema.</p><a href="https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/">🎙 Einfach starten – Interview mit sechs Sprachen</a><a href="https://ai.odpc.de/start/">🐵 Kostenlose und professionelle Agenten</a><a href="https://odpc.de/initiq">🏢 KI für Firmen – initIQ</a><a href="https://odpc.de/l-i-f-e-mpu-selbstlernkurs-neun-lernmodule/">🌱 L.I.F.E. – MPU-Selbstlernbereich</a><a href="https://odpc.de/contact-me">✉ Kontakt zu ODPC</a><p>Über WhatsApp Business kannst du persönlich um Hilfe bitten.</p><button id="odpc-hm-v4-close" type="button">Schließen</button></section>';
 echo '<a id="odpc-hm-v4-wa" class="odpc-hm-v4-wa" href="'.esc_url('https://wa.me/492654882183?text='.rawurlencode('Hallo Oliver, ich brauche Unterstützung auf odpc.de.')).'" target="_blank" rel="noopener noreferrer" aria-label="ODPC WhatsApp Business kontaktieren">✆ WhatsApp Business</a>';
 echo '<script id="odpc-hm-v4-js">(function(){var b=document.getElementById("odpc-hm-v4-helpbtn"),p=document.getElementById("odpc-hm-v4-help");if(!b||!p)return;function open(v){p.classList.toggle("is-open",v);b.setAttribute("aria-expanded",String(v))}b.addEventListener("click",function(){open(!p.classList.contains("is-open"))});document.getElementById("odpc-hm-v4-close").addEventListener("click",function(){open(false);b.focus()});document.addEventListener("keydown",function(e){if(e.key==="Escape")open(false)});if(document.querySelector(".odpc-wa-sticky")){document.getElementById("odpc-hm-v4-wa").remove()}})();</script>';
},35);


// Accessible article listening controls; browser speech voices are best-effort.
add_action('wp_footer',function(){
 if(is_admin())return; ?>
 <style id="odpc-reader-css">
 #odpc-reader{position:fixed;left:12px;bottom:132px;z-index:9995;background:#182c39;color:#fff;border:1px solid #8ba6b4;border-radius:12px;padding:8px;box-shadow:0 4px 18px #0005;max-width:min(370px,calc(100vw - 24px));font:13px/1.4 system-ui,sans-serif}
 #odpc-reader button{background:#edf6f3;color:#142f29;border:0;border-radius:7px;padding:7px 9px;margin:2px;cursor:pointer;font:600 12px system-ui,sans-serif}
 #odpc-reader button:focus-visible{outline:2px solid #f0b854}
 #odpc-reader-status{font-size:11px;display:block;margin:3px}
 @media(max-width:600px){#odpc-reader{left:9px;bottom:125px;padding:5px}}
 </style>
 <div id="odpc-reader" role="group" aria-label="Artikel vorlesen"><button type="button" id="odpc-reader-play">▶ Vorlesen</button><button type="button" id="odpc-reader-pause">⏸ Pause</button><button type="button" id="odpc-reader-stop">■ Stopp</button><span id="odpc-reader-status" role="status" aria-live="polite"></span></div>
 <script id="odpc-reader-js">
 (function(){
 'use strict';
 var synth=window.speechSynthesis,play=document.getElementById('odpc-reader-play'),pause=document.getElementById('odpc-reader-pause'),stop=document.getElementById('odpc-reader-stop'),status=document.getElementById('odpc-reader-status'),queue=[],index=0,token=0,paused=false;
 function lang(){if(window.ODPCLanguage)return window.ODPCLanguage.get();var site=document.getElementById('odpc-ai-language');if(site)return site.value;try{var c=decodeURIComponent(document.cookie.match(/(?:^|; )googtrans=([^;]+)/)?.[1]||'');return c.split('/').pop()||'de'}catch(e){return 'de'}}
 function locale(){return ({de:'de-DE',en:'en-US',nl:'nl-NL',es:'es-ES',fr:'fr-FR',ru:'ru-RU',tr:'tr-TR'})[lang()]||'de-DE'}
 function text(){var root=document.querySelector('article .entry-content,main .entry-content,main article,article,main,#content');if(!root)return '';var clone=root.cloneNode(true);clone.querySelectorAll('script,style,nav,form,button,footer,aside,.screen-reader-text,#odpc-reader,.gtranslate_wrapper').forEach(function(e){e.remove()});return (clone.innerText||clone.textContent||'').replace(/\s+/g,' ').trim().slice(0,85000)}
 function split(s){var parts=s.match(/[^.!?。！？]+[.!?。！？]?/g)||[s],out=[],chunk='';parts.forEach(function(p){if((chunk+p).length>175){if(chunk)out.push(chunk);chunk='';while(p.length>175){out.push(p.slice(0,175));p=p.slice(175)}}chunk+=p}else chunk+=p});if(chunk)out.push(chunk);return out.filter(Boolean)}
 function cancel(){token++;queue=[];index=0;paused=false;if(synth)synth.cancel();status.textContent='Gestoppt'}
 function next(t){if(t!==token||index>=queue.length){if(t===token)status.textContent='Wiedergabe beendet';return}var u=new SpeechSynthesisUtterance(queue[index++]);u.lang=locale();u.rate=1;var v=synth.getVoices().filter(function(x){return x.lang.toLowerCase().split(/[-_]/)[0]===u.lang.split('-')[0]});if(v.length)u.voice=v[0];u.onend=function(){next(t)};u.onerror=function(){if(t===token){status.textContent='Stimme nicht verfügbar oder Wiedergabe unterbrochen';}};synth.speak(u);status.textContent='Lese Abschnitt '+index+' von '+queue.length+' ('+u.lang+')'}
 play.addEventListener('click',function(){if(!synth){status.textContent='Vorlesen in diesem Browser nicht unterstützt';return}if(paused){synth.resume();paused=false;status.textContent='Fortgesetzt';return}cancel();var s=text();if(!s){status.textContent='Kein Artikeltext gefunden';return}queue=split(s);index=0;var t=token;next(t)});
 pause.addEventListener('click',function(){if(!synth)return;if(synth.paused){synth.resume();paused=false;status.textContent='Fortgesetzt'}else if(synth.speaking){synth.pause();paused=true;status.textContent='Pausiert'}});
 stop.addEventListener('click',cancel);document.addEventListener('odpc:languagechange',cancel);window.addEventListener('pagehide',cancel);
 })();
 </script>
 <?php
},85);


/* ODPC shared first-party language preference; respects existing GTranslate UI. */
add_action('wp_footer',function(){if(is_admin())return; ?>
<script id="odpc-global-language-core">
(function(){
'use strict';
var langs=['de','en','nl','es','fr','ru','tr'],key='odpc_global_language_v1';
function get(){var m=document.cookie.match(/(?:^|;\\s*)odpc_language=([^;]+)/);var v=m?decodeURIComponent(m[1]):null;try{v=v||localStorage.getItem(key)||'de'}catch(e){v=v||'de'}return langs.includes(v)?v:'de'}
function set(v){if(!langs.includes(v))return;try{localStorage.setItem(key,v)}catch(e){}document.cookie='odpc_language='+encodeURIComponent(v)+';path=/;domain=.odpc.de;max-age=31536000;SameSite=Lax;Secure';document.dispatchEvent(new CustomEvent('odpc:languagechange',{detail:{language:v}}))}
window.ODPCLanguage={get:get,set:set,locale:function(){return ({de:'de-DE',en:'en-US',nl:'nl-NL',es:'es-ES',fr:'fr-FR',ru:'ru-RU',tr:'tr-TR'})[get()]}};
var select=document.querySelector('.goog-te-combo');
document.addEventListener('change',function(e){if(e.target.matches('.goog-te-combo,select[data-odpc-language]')){var v=e.target.value;if(langs.includes(v))set(v)}});
document.addEventListener('click',function(e){var a=e.target.closest('a[href*="lang="],a[href*="googtrans="]');if(!a)return;try{var u=new URL(a.href),v=u.searchParams.get('lang')||u.searchParams.get('googtrans')?.split('/').pop();if(langs.includes(v))set(v)}catch(err){}});
if(!document.cookie.includes('odpc_language='))set(get());
})();
</script>
<?php },79);
