<?php
/* ODPC language rollout verification: 2026-11-10; deploy via existing SFTP workflow. */
/**
 * Plugin Name: ODPC Helper Monkey V4 Bridge
 * Description: Links ODPC services to the multilingual Helper Monkey start guide and preserves the existing WhatsApp Business button.
 * Version: 4.0.3
 */
if(!defined('ABSPATH'))exit;
/* Purge stale WP Fastest Cache page HTML once per bridge release.
 * This executes on the cache-bypassed deployment smoke request, before canonical visitors arrive.
 * Never deactivate plugins, alter content, or repeatedly purge on every page view.
 */
add_action('init',function(){
 $release='odpc-v4-bridge-20261110-cache-refresh-1';
 if(get_option('odpc_bridge_page_cache_release')===$release)return;
 if(!class_exists('WpFastestCache'))return;
 try{
  $cache=new WpFastestCache();
  if(!method_exists($cache,'deleteCache'))return;
  $cache->deleteCache(true);
  update_option('odpc_bridge_page_cache_release',$release,false);
  error_log('ODPC bridge: WP Fastest Cache purge attempted for '.$release);
 }catch(Throwable $e){
  error_log('ODPC bridge: cache purge failed: '.$e->getMessage());
 }
},99);
add_action('wp_head',function(){if(!is_admin())echo '<meta name="odpc-voice-build" content="2026-11-10-audit-1">' . "\n";

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
 #odpc-reader{position:fixed;right:8px;left:auto;bottom:112px;z-index:9995;display:flex;flex-direction:column;align-items:center;gap:3px;background:#182c39e8;color:#fff;border:1px solid #8ba6b4;border-radius:22px;padding:4px;box-shadow:0 3px 12px #0004;width:43px;max-width:43px;font:11px/1.3 system-ui,sans-serif}
 #odpc-reader>label,#odpc-reader select{display:none!important}
 #odpc-reader button{display:flex;align-items:center;justify-content:center;background:#edf6f3;color:#142f29;border:0;border-radius:50%;padding:0;width:35px;height:35px;min-height:35px;cursor:pointer;font:600 18px system-ui,sans-serif}
 #odpc-reader button:focus-visible,#odpc-reader summary:focus-visible{outline:2px solid #f0b854;outline-offset:2px}
 #odpc-reader #odpc-reader-pause,#odpc-reader #odpc-reader-stop{display:none}
 #odpc-reader.is-playing #odpc-reader-pause,#odpc-reader.is-playing #odpc-reader-stop{display:flex}
 #odpc-reader details{position:relative}#odpc-reader summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:center;width:35px;height:28px;font-size:20px}#odpc-reader summary::-webkit-details-marker{display:none}
 #odpc-reader .odpc-reader-tools{position:absolute;right:41px;bottom:0;display:flex;gap:5px;padding:6px;background:#182c39;border-radius:22px;box-shadow:0 4px 14px #0005;white-space:nowrap}
 #odpc-reader .odpc-reader-tools button{width:auto;border-radius:14px;font-size:12px;padding:0 9px}
 #odpc-reader-status{display:none;position:absolute;right:49px;bottom:0;width:min(265px,calc(100vw - 75px));background:#182c39;color:#fff;border:1px solid #8ba6b4;border-radius:10px;font-size:11px;overflow-wrap:anywhere;padding:8px;box-shadow:0 4px 12px #0004}
 #odpc-reader.has-status #odpc-reader-status{display:block}
 @media(max-width:600px){#odpc-reader{right:7px;bottom:108px}}
 </style>
 <div id="odpc-reader" role="group" aria-label="Artikel vorlesen"><label>Sprache <select id="odpc-reader-language" aria-label="Vorlesesprache"><option value="de-DE">Deutsch</option><option value="en-US">English</option><option value="fr-FR">Français</option><option value="nl-NL">Nederlands</option><option value="es-ES">Español</option><option value="tr-TR">Türkçe</option><option value="ru-RU">Русский</option></select></label><label>Stimme <select id="odpc-reader-voice" aria-label="Systemstimme"><option value="">Automatisch</option></select></label><button type="button" id="odpc-reader-play" aria-label="Seite vorlesen" title="Vorlesen">▶</button><button type="button" id="odpc-reader-mic" aria-label="Spracheingabe starten" title="Sprechen">🎤</button><button type="button" id="odpc-reader-pause">⏸</button><button type="button" id="odpc-reader-stop">■</button><details id="odpc-reader-more"><summary aria-label="Sprachwerkzeuge">⋯</summary><div class="odpc-reader-tools"><button type="button" id="odpc-reader-test">🔊 Test</button><button type="button" id="odpc-reader-diagnostic">🩺 Diagnose</button></div></details><span id="odpc-reader-status" role="status" aria-live="polite"></span></div>
 <script id="odpc-reader-js">
 (function(){
 'use strict';
 var synth=window.speechSynthesis,play=document.getElementById('odpc-reader-play'),pause=document.getElementById('odpc-reader-pause'),stop=document.getElementById('odpc-reader-stop'),status=document.getElementById('odpc-reader-status'),queue=[],index=0,token=0,paused=false,languageSelect=document.getElementById('odpc-reader-language'),voiceSelect=document.getElementById('odpc-reader-voice'),voiceList=[],activeUtterance=null;
 function lang(){if(window.ODPCLanguage)return window.ODPCLanguage.get();var site=document.getElementById('odpc-ai-language');if(site)return site.value;try{var c=decodeURIComponent(document.cookie.match(/(?:^|; )googtrans=([^;]+)/)?.[1]||'');return c.split('/').pop()||'de'}catch(e){return 'de'}}
 function locale(){var current=window.ODPCLanguage&&window.ODPCLanguage.get?window.ODPCLanguage.get():lang();return ({de:'de-DE',en:'en-US',fr:'fr-FR',nl:'nl-NL',es:'es-ES',tr:'tr-TR',ru:'ru-RU'})[current]||languageSelect.value||'de-DE'}
 function refreshVoices(){if(!synth||typeof synth.getVoices!=='function')return;voiceList=synth.getVoices()||[];var selected=voiceSelect.value;voiceSelect.replaceChildren(new Option('Automatisch',''));voiceList.forEach(function(v,i){if(v.lang.toLowerCase().split(/[-_]/)[0]===locale().split('-')[0])voiceSelect.add(new Option(v.name+' ('+v.lang+')',String(i)))});if(Array.from(voiceSelect.options).some(function(o){return o.value===selected}))voiceSelect.value=selected;if(!voiceList.length)status.textContent='Warte auf Systemstimmen …'}
 languageSelect.value=({de:'de-DE',en:'en-US',fr:'fr-FR',nl:'nl-NL',es:'es-ES',tr:'tr-TR',ru:'ru-RU'})[lang()]||'de-DE';languageSelect.addEventListener('change',function(){cancel();voiceSelect.value='';refreshVoices();if(window.ODPCLanguage)window.ODPCLanguage.set(locale().slice(0,2))});document.addEventListener('odpc:languagechange',function(){cancel();languageSelect.value=locale();voiceSelect.value='';refreshVoices()});voiceSelect.addEventListener('change',cancel);if(synth){refreshVoices();if(typeof synth.addEventListener==='function')synth.addEventListener('voiceschanged',refreshVoices);else synth.onvoiceschanged=refreshVoices;setTimeout(refreshVoices,700);setTimeout(refreshVoices,2200)}
 function text(){var selected=(window.getSelection&&window.getSelection().toString()||'').trim();if(selected)return selected.slice(0,85000);var root=document.querySelector('article .entry-content,main .entry-content,main article,article,main,#content');if(!root)root=document.querySelector('main,.site-content,#page')||document.body;var clone=root.cloneNode(true);clone.querySelectorAll('script,style,nav,form,button,footer,aside,.screen-reader-text,#odpc-reader,.gtranslate_wrapper').forEach(function(e){e.remove()});return (clone.textContent||'').replace(/\s+/g,' ').trim().slice(0,85000)}
 function split(s){var parts=s.match(/[^.!?。！？]+[.!?。！？]?/g)||[s],out=[],chunk='';parts.forEach(function(p){if((chunk+p).length>175){if(chunk)out.push(chunk);chunk='';while(p.length>175){out.push(p.slice(0,175));p=p.slice(175)}chunk+=p}else{chunk+=p}});if(chunk)out.push(chunk);return out.filter(Boolean)}
 function cancel(){document.getElementById('odpc-reader').classList.remove('is-playing');token++;queue=[];index=0;paused=false;if(synth&&(synth.speaking||synth.pending||synth.paused))synth.cancel();activeUtterance=null;status.textContent='Gestoppt'}
 function next(t){
 if(t!==token)return;
 if(index>=queue.length){document.getElementById('odpc-reader').classList.remove('is-playing');status.textContent='Wiedergabe beendet';return}
 var part=queue[index++],u=new SpeechSynthesisUtterance(part);
 u.lang=locale();u.rate=0.95;u.volume=1;
 var available=typeof synth.getVoices==='function'?(synth.getVoices()||[]):[];
 var matches=available.filter(function(x){return (x.lang||'').toLowerCase().split(/[-_]/)[0]===u.lang.split('-')[0]});
 if(voiceSelect.value!==''&&voiceList[Number(voiceSelect.value)])u.voice=voiceList[Number(voiceSelect.value)];else if(matches.length)u.voice=matches.find(function(x){return x.localService})||matches[0];
 var started=false;activeUtterance=u;
 u.onstart=function(){if(t===token){started=true;document.getElementById('odpc-reader').classList.add('is-playing');status.textContent='🔊 Spricht Abschnitt '+index+' von '+queue.length+' ('+u.lang+')'}};
 u.onend=function(){if(t===token)next(t)};
 u.onerror=function(e){if(t!==token)return;var err=e.error||'unbekannt';if(!started&&err!=='canceled'&&err!=='interrupted'&&!u._retried){u._retried=true;status.textContent='Stimme fehlgeschlagen ('+err+'), versuche Standardstimme …';setTimeout(function(){if(t!==token)return;var retry=new SpeechSynthesisUtterance(part);retry.lang=u.lang;retry.rate=1;retry.onstart=u.onstart;retry.onend=u.onend;retry.onerror=function(ev){if(t===token)status.textContent='Audiofehler: '+(ev.error||'unbekannt')+' – Systemstimme prüfen'};try{activeUtterance=retry;synth.speak(retry)}catch(ex){status.textContent='Audio nicht verfügbar: '+ex.message}},150);return}document.getElementById('odpc-reader').classList.remove('is-playing');status.textContent='Audiofehler: '+err+' – Systemstimme prüfen'};
 try{status.textContent='Sprachausgabe wird gestartet ('+u.lang+') …';synth.speak(u);if(synth.paused)synth.resume()}
 catch(e){status.textContent='Sprachausgabe konnte nicht starten: '+e.message}
 setTimeout(function(){if(t===token&&!started){status.textContent='Audiotest ohne Startsignal: speaking='+synth.speaking+', pending='+synth.pending+', Stimmen='+voiceList.length+'. Bitte Gerätestimme prüfen.'}},5000);
}
 play.addEventListener('click',function(){if(!synth){status.textContent='Vorlesen in diesem Browser nicht unterstützt';return}if(paused){synth.resume();paused=false;status.textContent='Fortgesetzt';return}var wasActive=!!(synth.speaking||synth.pending||synth.paused);cancel();var s=text();if(!s){status.textContent='Kein lesbarer Text gefunden';return}if(!window.SpeechSynthesisUtterance){status.textContent='Sprachausgabe auf diesem Gerät nicht verfügbar';return}queue=split(s);index=0;var t=token;if(wasActive)setTimeout(function(){if(t===token)next(t)},120);else next(t)});
 pause.addEventListener('click',function(){if(!synth)return;if(synth.paused){synth.resume();paused=false;status.textContent='Fortgesetzt'}else if(synth.speaking){synth.pause();paused=true;status.textContent='Pausiert'}});
 document.getElementById('odpc-reader-test').addEventListener('click',function(){if(!synth||!window.SpeechSynthesisUtterance){status.textContent='Text-zu-Sprache nicht unterstützt';return}cancel();queue=[({'de-DE':'Dies ist ein Audiotest von ODPC. Kannst du mich hören?','en-US':'This is an ODPC audio test. Can you hear me?','fr-FR':'Ceci est un test audio ODPC. Vous pouvez me comprendre?','nl-NL':'Dit is een audiotest van ODPC. Kun je mij horen?','es-ES':'Esta es una prueba de audio de ODPC. ¿Puedes oírme?','tr-TR':'Bu bir ODPC ses testidir. Beni duyabiliyor musunuz?','ru-RU':'Это проверка звука ODPC. Вы меня слышите?'})[locale()]||'Dies ist ein Audiotest von ODPC.'];index=0;next(token)});
 
 function diagnose(){var sr=window.SpeechRecognition||window.webkitSpeechRecognition;var v=synth&&synth.getVoices?synth.getVoices():[];status.textContent='Audio: '+(synth?'Browser-TTS vorhanden':'TTS nicht verfügbar')+'; Stimmen: '+v.length+'; Mikrofon-API: '+(sr?'vorhanden':'nicht verfügbar')+'; HTTPS: '+(location.protocol==='https:'?'ja':'nein')+'. Lautstärke, Stummschaltung und tatsächlichen Ton kann die Webseite nicht auslesen. Bitte Medienlautstärke prüfen und Audiotest drücken.'}
 document.getElementById('odpc-reader-diagnostic').addEventListener('click',diagnose);
 var micButton=document.getElementById('odpc-reader-mic'),recognition=null;
 micButton.addEventListener('click',function(){
  var SR=window.SpeechRecognition||window.webkitSpeechRecognition;
  if(!SR){status.textContent='Spracheingabe in diesem Browser nicht verfügbar. Bitte Chrome oder einen unterstützten Browser verwenden.';return}
  if(recognition){try{recognition.stop()}catch(e){}recognition=null;micButton.textContent='🎤';return}
  try{
   recognition=new SR();recognition.lang=locale();recognition.interimResults=false;recognition.maxAlternatives=1;
   recognition.onresult=function(e){var phrase=e.results[0][0].transcript;status.textContent='Erkannt: '+phrase;var q=phrase.toLowerCase();if(/^(vorlesen|lies vor|read aloud|start reading)/.test(q)){play.click()}else if(/^(stopp|stop|halt)/.test(q)){stop.click()}else if(/^(pause|pausieren)/.test(q)){pause.click()}else if(/^(audiotest|audio test|test)/.test(q)){document.getElementById('odpc-reader-test').click()}else{status.textContent='Erkannt: '+phrase+' – Befehle: Vorlesen, Audiotest, Pause, Stopp.'}};
   recognition.onerror=function(e){status.textContent='Mikrofonfehler: '+e.error+'. Bei not-allowed die Mikrofonberechtigung in den Browser-Einstellungen prüfen.'};
   recognition.onend=function(){recognition=null;micButton.textContent='🎤 Spracheingabe'};
   recognition.start();micButton.textContent='■';status.textContent='Mikrofon gestartet – Berechtigung bei Nachfrage erlauben.';
  }catch(e){recognition=null;micButton.textContent='🎤 Spracheingabe';status.textContent='Mikrofonstart fehlgeschlagen: '+e.message}
 });
 var reader=document.getElementById('odpc-reader');new MutationObserver(function(){reader.classList.toggle('has-status',!!status.textContent&&status.textContent!=='Gestoppt')}).observe(status,{childList:true,characterData:true,subtree:true});
 stop.addEventListener('click',cancel);document.addEventListener('odpc:languagechange',function(){cancel();languageSelect.value=({de:'de-DE',en:'en-US',fr:'fr-FR',nl:'nl-NL',es:'es-ES',tr:'tr-TR',ru:'ru-RU'})[lang()]||'de-DE';refreshVoices()});window.addEventListener('pagehide',cancel);
 })();
 </script>
 <?php
},85);



/* Unified mobile utility dock: preserve original click handlers by moving nodes. */
add_action('wp_footer',function(){if(is_admin())return; ?>
<style id="odpc-unified-dock-css">
/* One controlled layout; original language and speech handlers are untouched. */
#odpc-utility-dock{position:fixed!important;z-index:99990!important;left:50%!important;right:auto!important;bottom:max(12px,env(safe-area-inset-bottom))!important;transform:translateX(-50%)!important;box-sizing:border-box!important;display:flex!important;flex-flow:row nowrap!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;width:min(310px,calc(100vw - 28px))!important;max-width:310px!important;height:56px!important;min-height:56px!important;padding:6px 10px!important;margin:0!important;border:1px solid #647e8a!important;border-radius:999px!important;background:rgba(24,44,57,.20)!important;backdrop-filter:blur(5px)!important;-webkit-backdrop-filter:blur(5px)!important;box-shadow:0 4px 16px #0002!important;overflow:visible!important}
/* Keep GTranslate operational for translations, but hide its redundant floating UI.
   The explicit dock select is the only visible language control. */
body.odpc-dock-active #gt_float_wrapper,
body.odpc-dock-active .gt_float_switcher:not(#odpc-utility-dock .gt_float_switcher),
body.odpc-dock-active .gt_switcher_wrapper:not(#odpc-utility-dock .gt_switcher_wrapper){visibility:hidden!important;opacity:0!important;pointer-events:none!important}
#odpc-utility-dock>#odpc-dock-home{order:0!important;display:flex!important;align-items:center!important;justify-content:center!important;flex:0 0 28px!important;width:28px!important;height:36px!important;min-width:28px!important;color:#eef8f5!important;background:rgba(16,37,46,.42)!important;border-radius:50%!important;text-decoration:none!important;box-shadow:none!important}
#odpc-utility-dock>#odpc-dock-home svg{display:block!important;width:19px!important;height:19px!important}
#odpc-utility-dock>.odpc-dock-language{order:0!important;position:relative!important;flex:0 0 88px!important;display:flex!important;align-items:center!important;justify-content:center!important;width:88px!important;min-width:88px!important;max-width:88px!important;height:42px!important;margin:0!important;overflow:visible!important}
#odpc-utility-dock .odpc-dock-language>.gt_float_switcher,#odpc-utility-dock .odpc-dock-language>.gt_switcher_wrapper,#odpc-utility-dock .odpc-dock-language>.gtranslate_wrapper{position:relative!important;top:auto!important;bottom:auto!important;left:auto!important;right:auto!important;transform:none!important;display:block!important;box-sizing:border-box!important;width:88px!important;min-width:0!important;max-width:88px!important;margin:0!important;box-shadow:none!important}
#odpc-utility-dock .odpc-dock-language .gt-selected,#odpc-utility-dock .odpc-dock-language .gt-current-lang{box-sizing:border-box!important;width:88px!important;max-width:88px!important;min-height:38px!important;height:38px!important;padding:5px!important;margin:0!important;overflow:hidden!important;white-space:nowrap!important;border-radius:12px!important}
#odpc-utility-dock .odpc-dock-language .gt-lang-code{display:none!important}
#odpc-utility-dock .odpc-dock-language .gt_options{position:absolute!important;bottom:calc(100% + 8px)!important;top:auto!important;left:0!important;right:auto!important;max-height:55vh!important;overflow-y:auto!important;z-index:99999!important}
#odpc-utility-dock>#odpc-reader{order:1!important;position:relative!important;inset:auto!important;transform:none!important;box-sizing:border-box!important;display:flex!important;flex:0 0 44px!important;flex-direction:row!important;align-items:center!important;justify-content:center!important;width:44px!important;min-width:44px!important;max-width:44px!important;height:44px!important;min-height:44px!important;margin:0!important;padding:0!important;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important;overflow:visible!important}
#odpc-utility-dock #odpc-reader>label,#odpc-utility-dock #odpc-reader #odpc-reader-mic,#odpc-utility-dock #odpc-reader #odpc-reader-pause,#odpc-utility-dock #odpc-reader #odpc-reader-stop,#odpc-utility-dock #odpc-reader #odpc-reader-more,#odpc-utility-dock #odpc-hm-v4-helpbtn,#odpc-utility-dock #odpc-hm-v4-help{display:none!important}
#odpc-utility-dock #odpc-reader #odpc-reader-play{display:flex!important;align-items:center!important;justify-content:center!important;box-sizing:border-box!important;width:42px!important;min-width:42px!important;height:42px!important;min-height:42px!important;padding:0!important;margin:0!important;border:0!important;border-radius:50%!important;background:#edf6f3!important;color:#142f29!important;font-size:20px!important}
#odpc-utility-dock #odpc-reader-status{display:none!important}
#odpc-utility-dock>.odpc-hm-v4-wa,#odpc-utility-dock>.odpc-wa-sticky,#odpc-utility-dock>#odpc-hm-v4-wa{order:2!important;position:relative!important;inset:auto!important;transform:none!important;display:flex!important;align-items:center!important;justify-content:center!important;box-sizing:border-box!important;flex:0 0 104px!important;width:104px!important;min-width:104px!important;max-width:104px!important;height:42px!important;min-height:42px!important;padding:0 8px!important;margin:0!important;border:0!important;border-radius:22px!important;background:#167c52!important;color:#fff!important;font:700 13px/1 system-ui,sans-serif!important;white-space:nowrap!important;overflow:hidden!important;text-decoration:none!important;box-shadow:none!important}
#odpc-utility-dock>.odpc-hm-v4-wa::after,#odpc-utility-dock>.odpc-wa-sticky::after{content:none!important}
#odpc-utility-dock>#odpc-hm-v4-wa{font-size:0!important}
#odpc-utility-dock>#odpc-hm-v4-wa::after{content:'WhatsApp'!important;display:block!important;font:700 13px/1 system-ui,sans-serif!important;color:#fff!important}
body.odpc-dock-active{padding-bottom:70px!important}
@media(max-width:360px){#odpc-utility-dock{gap:5px!important;width:calc(100vw - 16px)!important;padding:5px 7px!important}#odpc-utility-dock>.odpc-dock-language{flex-basis:80px!important;width:80px!important;min-width:80px!important;max-width:80px!important}#odpc-utility-dock .odpc-dock-language>.gt_float_switcher,#odpc-utility-dock .odpc-dock-language>.gt_switcher_wrapper,#odpc-utility-dock .odpc-dock-language>.gtranslate_wrapper,#odpc-utility-dock .odpc-dock-language .gt-selected{width:80px!important;max-width:80px!important}}

/* Fix actual nested GTranslate float positioning; keep dropdown functional. */
#odpc-utility-dock .odpc-dock-language{display:flex!important;align-items:center!important;flex:0 0 88px!important;width:88px!important;min-width:88px!important}
#odpc-utility-dock .odpc-dock-language .gt_float_switcher,#odpc-utility-dock .odpc-dock-language .gt_switcher_wrapper,#odpc-utility-dock .odpc-dock-language .gtranslate_wrapper,#odpc-utility-dock .odpc-dock-language #gt_float_wrapper{position:static!important;inset:auto!important;top:auto!important;left:auto!important;right:auto!important;bottom:auto!important;transform:none!important;margin:0!important;float:none!important;max-width:88px!important;width:88px!important;box-shadow:none!important}
#odpc-utility-dock #odpc-reader{flex-basis:88px!important;min-width:88px!important;max-width:88px!important;width:88px!important;gap:4px!important}
#odpc-utility-dock #odpc-reader #odpc-reader-stop{display:flex!important;position:static!important;align-items:center!important;justify-content:center!important;flex:0 0 40px!important;width:40px!important;min-width:40px!important;max-width:40px!important;height:40px!important;min-height:40px!important;padding:0!important;margin:0!important;background:#edf6f3!important;color:#142f29!important;border-radius:50%!important;font-size:20px!important}
#odpc-utility-dock{width:min(352px,calc(100vw - 20px))!important;max-width:352px!important;gap:5px!important;padding:6px 7px!important}
#odpc-utility-dock>.odpc-hm-v4-wa,#odpc-utility-dock>.odpc-wa-sticky,#odpc-utility-dock>#odpc-hm-v4-wa{flex-basis:95px!important;width:95px!important;min-width:95px!important;max-width:95px!important}

/* GTranslate's floating wrapper must be a normal child of the dock, not a second overlay. */
#odpc-utility-dock .odpc-dock-language #gt_float_wrapper{position:relative!important;inset:auto!important;left:auto!important;right:auto!important;top:auto!important;bottom:auto!important;transform:none!important;float:none!important;display:block!important;width:88px!important;max-width:88px!important;margin:0!important;z-index:1!important}
#odpc-utility-dock .odpc-dock-language #gt_float_wrapper .gt_float_switcher{position:relative!important;inset:auto!important;transform:none!important;margin:0!important;width:88px!important;max-width:88px!important;box-shadow:none!important}
#odpc-utility-dock .odpc-language-fallback{display:block!important;box-sizing:border-box!important;width:88px!important;max-width:88px!important;height:40px!important;border:1px solid #65737b!important;border-radius:12px!important;background:#eaf2f2!important;color:#132c2e!important;padding:0 3px!important;font:600 12px system-ui,sans-serif!important;cursor:pointer!important}
</style>
<script id="odpc-unified-dock-js">
(function(){
'use strict';
var selector='#gt_float_wrapper,.gt_float_switcher,.gt_switcher_wrapper,.gtranslate_wrapper';
function moveLanguage(dock){
 if(!dock)return false;
 var holder=dock.querySelector('.odpc-dock-language');
 if(!holder){holder=document.createElement('div');holder.className='odpc-dock-language';dock.insertBefore(holder,dock.firstChild)}
 // The GTranslate floating wrapper can exist yet render an empty box. Use an explicit,
 // accessible flag selector in the dock rather than trusting wrapper geometry.
 fallbackLanguage(dock);
 return true;
}
function fallbackLanguage(dock){
 var holder=dock.querySelector('.odpc-dock-language');if(!holder||holder.querySelector('.odpc-language-fallback'))return;
 var select=document.createElement('select');select.className='odpc-language-fallback';select.setAttribute('aria-label','Sprache auswählen');
 var choices=[['de','🇩🇪 Deutsch'],['en','🇬🇧 English'],['nl','🇳🇱 Nederlands'],['fr','🇫🇷 Français'],['es','🇪🇸 Español'],['it','🇮🇹 Italiano'],['pt','🇵🇹 Português'],['tr','🇹🇷 Türkçe'],['ru','🇷🇺 Русский']];
 choices.forEach(function(c){var option=document.createElement('option');option.value=c[0];option.textContent=c[1];select.appendChild(option)});
 var cookie=document.cookie.match(new RegExp('(?:^|;)\\s*googtrans=/de/([^;]+)'));select.value=(cookie&&cookie[1])||(window.ODPCLanguage&&window.ODPCLanguage.get())||'de';
 select.addEventListener('change',function(){
  var lang=select.value;
  if(window.ODPCLanguage&&window.ODPCLanguage.set)window.ODPCLanguage.set(lang);
  if(typeof window.doGTranslate==='function'){window.doGTranslate('de|'+lang);return}
  var value='/de/'+lang;document.cookie='googtrans='+value+';path=/;SameSite=Lax';document.cookie='googtrans='+value+';path=/;domain=.odpc.de;SameSite=Lax';
  window.location.reload();
 });
 holder.appendChild(select);
}
function mount(){
 // Domain-local home link; no changes to language, voice or WhatsApp handlers.
 var home=document.getElementById('odpc-dock-home');
 var reader=document.getElementById('odpc-reader'),help=document.getElementById('odpc-hm-v4-helpbtn');
 if(!reader||!help)return;
 var dock=document.getElementById('odpc-utility-dock');
 if(!dock){dock=document.createElement('nav');dock.id='odpc-utility-dock';dock.setAttribute('aria-label','ODPC Schnellfunktionen');document.body.appendChild(dock);
 dock.appendChild(reader);
 var stop=document.getElementById('odpc-reader-stop');if(stop){reader.insertBefore(stop,document.getElementById('odpc-reader-more'));stop.setAttribute('aria-label','Vorlesen stoppen');stop.setAttribute('title','Vorlesen stoppen')}
 dock.appendChild(help);help.setAttribute('aria-label','Fragen und Hilfe');help.setAttribute('title','Fragen und Hilfe');
 var panel=document.getElementById('odpc-hm-v4-help');if(panel)dock.appendChild(panel);
 var wa=document.querySelector('.odpc-wa-sticky')||document.getElementById('odpc-hm-v4-wa');
 if(wa){dock.appendChild(wa);wa.setAttribute('aria-label','WhatsApp Business');wa.setAttribute('title','WhatsApp Business')}
 document.body.classList.add('odpc-dock-active');
 }
 if(!home){
  home=document.createElement('a');home.id='odpc-dock-home';
  home.href=window.location.origin+'/';home.setAttribute('aria-label','Zur Startseite');
  home.setAttribute('title','Zur Startseite');
  home.innerHTML='<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>';
  dock.insertBefore(home,reader);
 }
 moveLanguage(dock);
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',mount);else mount();
window.addEventListener('load',mount);
var attempts=0;var retry=setInterval(function(){mount();var dock=document.getElementById('odpc-utility-dock');if(dock&&moveLanguage(dock)){clearInterval(retry);return}if(++attempts>=12){clearInterval(retry);if(dock)fallbackLanguage(dock)}},500);
})();
</script>

<style id="odpc-smartguide-pilot-css">
/* Pilot only: preserve transparent dock and existing language dropdown. */
body.odpc-smartguide-pilot #odpc-utility-dock{justify-content:space-between!important;gap:4px!important}
body.odpc-smartguide-pilot #odpc-utility-dock>#odpc-dock-home{order:4!important;flex:0 0 32px!important;width:32px!important;min-width:32px!important}
body.odpc-smartguide-pilot #odpc-utility-dock>.odpc-dock-language{order:0!important;flex:0 0 76px!important;width:76px!important;min-width:76px!important;max-width:76px!important}
body.odpc-smartguide-pilot #odpc-utility-dock .odpc-language-fallback{width:76px!important;max-width:76px!important}
body.odpc-smartguide-pilot #odpc-utility-dock>#odpc-reader{order:1!important;flex:0 0 78px!important;width:78px!important;min-width:78px!important;max-width:78px!important;gap:3px!important}
body.odpc-smartguide-pilot #odpc-utility-dock #odpc-reader #odpc-reader-play,
body.odpc-smartguide-pilot #odpc-utility-dock #odpc-reader #odpc-reader-stop{flex:0 0 37px!important;width:37px!important;min-width:37px!important;height:37px!important;min-height:37px!important}
body.odpc-smartguide-pilot #odpc-utility-dock>.odpc-hm-v4-wa,
body.odpc-smartguide-pilot #odpc-utility-dock>.odpc-wa-sticky,
body.odpc-smartguide-pilot #odpc-utility-dock>#odpc-hm-v4-wa{order:3!important;flex:0 0 43px!important;width:43px!important;min-width:43px!important;max-width:43px!important;height:42px!important;padding:0!important;font-size:0!important}
body.odpc-smartguide-pilot #odpc-utility-dock>#odpc-hm-v4-wa::after{content:none!important}
body.odpc-smartguide-pilot #odpc-utility-dock .odpc-smartguide-wa-icon{width:25px;height:25px;display:block;flex:none;filter:brightness(0) invert(1)}
body.odpc-smartguide-pilot .odpc-smartguide-active{outline:2px solid #1caa83!important;outline-offset:5px!important;border-radius:5px!important;scroll-margin-top:100px!important;transition:outline-color .2s ease}
@media(max-width:360px){body.odpc-smartguide-pilot #odpc-utility-dock{width:calc(100vw - 12px)!important;padding:5px!important;gap:2px!important}}
</style>
<script id="odpc-smartguide-pilot-js">
(function(){
'use strict';
// SmartGuide enabled for every ODPC page; accepted pilot behavior preserved.
document.body.classList.add('odpc-smartguide-pilot');
var active=false,segments=[],index=0,token=0,current=null;
function stop(){
 active=false;token++;if(window.speechSynthesis)window.speechSynthesis.cancel();
 if(current)current.classList.remove('odpc-smartguide-active');current=null;
}
function language(){
 var el=document.querySelector('#odpc-utility-dock .odpc-language-fallback');
 var l=(el&&el.value)||(window.ODPCLanguage&&window.ODPCLanguage.get())||'de';
 return ({de:'de-DE',en:'en-US',nl:'nl-NL',fr:'fr-FR',es:'es-ES',it:'it-IT',pt:'pt-PT',tr:'tr-TR',ru:'ru-RU'})[l]||'de-DE';
}
function collect(){
 var root=document.querySelector('article .entry-content,main .entry-content,.entry-content,main article,main');
 if(!root)return [];
 return Array.from(root.querySelectorAll('h1,h2,h3,p,li')).filter(function(el){
  return !el.closest('nav,footer,header,#odpc-utility-dock,script,style')&&
   el.getClientRects().length&&el.innerText.trim().length>24&&el.innerText.trim().length<1800;
 }).slice(0,90);
}
function speakNext(id){
 if(!active||id!==token)return;
 if(current)current.classList.remove('odpc-smartguide-active');
 if(index>=segments.length){stop();return}
 var el=segments[index++];current=el;el.classList.add('odpc-smartguide-active');
 if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches)el.scrollIntoView({behavior:'smooth',block:'center'});
 var u=new SpeechSynthesisUtterance(el.innerText.trim().slice(0,1200));u.lang=language();u.rate=.94;
 var voices=window.speechSynthesis.getVoices();var exact=voices.find(function(v){return v.lang.toLowerCase()===u.lang.toLowerCase()&&v.localService})||voices.find(function(v){return v.lang.toLowerCase()===u.lang.toLowerCase()})||voices.find(function(v){return v.lang.toLowerCase().split('-')[0]===u.lang.split('-')[0]});
 if(exact)u.voice=exact;
 u.onend=function(){speakNext(id)};
 u.onerror=function(e){if(e.error==='interrupted'||e.error==='canceled')return;stop()};
 window.speechSynthesis.speak(u);
}
function play(){
 if(!('speechSynthesis'in window)){alert('Die Sprachausgabe wird von diesem Browser nicht unterstützt.');return}
 stop();segments=collect();index=0;if(!segments.length)return;
 active=true;var id=token;speakNext(id);
}
function mount(){
 var dock=document.getElementById('odpc-utility-dock'),playButton=document.getElementById('odpc-reader-play'),stopButton=document.getElementById('odpc-reader-stop'),home=document.getElementById('odpc-dock-home');
 if(!dock||!playButton||!stopButton||!home)return false;
 playButton.setAttribute('aria-label','SmartGuide starten');playButton.title='SmartGuide starten';
 stopButton.setAttribute('aria-label','SmartGuide stoppen');stopButton.title='SmartGuide stoppen';
 home.href='#top';home.setAttribute('aria-label','Zum Seitenanfang');home.title='Zum Seitenanfang';
 home.onclick=function(e){e.preventDefault();window.scrollTo({top:0,behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'})};
 var wa=dock.querySelector('.odpc-wa-sticky,#odpc-hm-v4-wa,.odpc-hm-v4-wa');
 if(wa&&!wa.querySelector('.odpc-smartguide-wa-icon')){
  wa.setAttribute('aria-label','WhatsApp Business');wa.title='WhatsApp Business';
  wa.textContent='';
  wa.insertAdjacentHTML('afterbegin','<img class="odpc-smartguide-wa-icon" src="https://cdn.jsdelivr.net/npm/simple-icons@v13/icons/whatsapp.svg" alt="" width="25" height="25">');
 }
 return true;
}
document.addEventListener('click',function(e){
 if(!document.body.classList.contains('odpc-smartguide-pilot'))return;
 if(e.target.closest('#odpc-reader-play')){e.preventDefault();e.stopImmediatePropagation();play()}
 else if(e.target.closest('#odpc-reader-stop')){e.preventDefault();e.stopImmediatePropagation();stop()}
},true);
document.addEventListener('odpc:languagechange',stop);
window.addEventListener('pagehide',stop);
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',mount);else mount();
window.addEventListener('load',mount);
})();
</script>
<?php },99);

/* ODPC shared first-party language preference; respects existing GTranslate UI. */
add_action('wp_footer',function(){if(is_admin())return; ?>
<script id="odpc-global-language-core">
(function(){
'use strict';
var langs=['de','en','nl','es','fr','ru','tr'],key='odpc_global_language_v1';
function get(){var m=document.cookie.match(/(?:^|;\s*)odpc_language=([^;]+)/);var v=m?decodeURIComponent(m[1]):null;try{v=v||localStorage.getItem(key)||'de'}catch(e){v=v||'de'}return langs.includes(v)?v:'de'}
function set(v){if(!langs.includes(v))return;try{localStorage.setItem(key,v)}catch(e){}document.cookie='odpc_language='+encodeURIComponent(v)+';path=/;domain=.odpc.de;max-age=31536000;SameSite=Lax;Secure';document.dispatchEvent(new CustomEvent('odpc:languagechange',{detail:{language:v}}))}
window.ODPCLanguage={get:get,set:set,locale:function(){return ({de:'de-DE',en:'en-US',nl:'nl-NL',es:'es-ES',fr:'fr-FR',ru:'ru-RU',tr:'tr-TR'})[get()]}};
var select=document.querySelector('.goog-te-combo');
document.addEventListener('change',function(e){if(e.target.matches('.goog-te-combo,select[data-odpc-language]')){var v=e.target.value;if(langs.includes(v))set(v)}});
document.addEventListener('click',function(e){var a=e.target.closest('a[href*="lang="],a[href*="googtrans="]');if(!a)return;try{var u=new URL(a.href),v=u.searchParams.get('lang')||u.searchParams.get('googtrans')?.split('/').pop();if(langs.includes(v))set(v)}catch(err){}});
if(!document.cookie.includes('odpc_language='))set(get());
})();
</script>
<?php },79);

/* ODPC Remote: additive enhancement to the backed-up homepage reference dock. */
add_filter('wp_nav_menu_items',function($items,$args){
 if(is_admin()||!isset($args->theme_location)||$args->theme_location!=='primary')return $items;
 if(strpos($items,'/remote-fernwartung/')!==false)return $items;
 return $items.'<li class="menu-item menu-item-type-custom odpc-remote-menu"><a href="https://odpc.de/remote-fernwartung/">Fernhilfe</a></li>';
},20,2);
add_action('wp_footer',function(){if(is_admin())return; ?>
<style id="odpc-remote-dock-extension-css">
/* Only add one control; keep original transparent backdrop and handlers. */
body.odpc-smartguide-pilot #odpc-utility-dock{width:min(390px,calc(100vw - 14px))!important;max-width:390px!important}
#odpc-utility-dock #odpc-dock-remote{order:2!important;display:flex!important;align-items:center!important;justify-content:center!important;flex:0 0 38px!important;width:38px!important;min-width:38px!important;height:38px!important;padding:0!important;border-radius:50%!important;background:rgba(10,38,65,.48)!important;border:1px solid rgba(106,213,255,.45)!important;overflow:hidden!important;text-decoration:none!important}
#odpc-utility-dock #odpc-dock-remote img{display:block;width:34px;height:34px;object-fit:contain;border-radius:50%}
#odpc-utility-dock #odpc-dock-remote:focus-visible{outline:2px solid #77dcff!important;outline-offset:2px!important}
.odpc-home-remote{display:flex;align-items:center;gap:13px;max-width:560px;margin:20px auto;padding:10px 16px;border:1px solid rgba(53,129,178,.25);border-radius:16px;background:rgba(12,49,78,.06);font:inherit}
.odpc-home-remote img{width:64px;height:64px;flex:none;object-fit:contain}
.odpc-home-remote strong{display:block;font-size:15px;line-height:1.4}
.odpc-home-remote p{margin:3px 0 5px;font-size:13px;line-height:1.45}
.odpc-home-remote a{color:#1168a4;font-weight:700;font-size:13px}
@media(max-width:360px){body.odpc-smartguide-pilot #odpc-utility-dock{gap:2px!important;padding:5px!important}#odpc-utility-dock #odpc-dock-remote{flex-basis:32px!important;width:32px!important;min-width:32px!important;height:32px!important}#odpc-utility-dock #odpc-dock-remote img{width:30px;height:30px}}
</style>
<script id="odpc-remote-dock-extension-js">
(function(){
 'use strict';
 var url='https://odpc.de/remote-fernwartung/';
 var logo='https://odpc.de/wp-content/uploads/2026/11/file_00000000feb8820a8c57f156ec0926e2.png';
 function mount(){
  var dock=document.getElementById('odpc-utility-dock');
  if(dock&&!document.getElementById('odpc-dock-remote')){
   var a=document.createElement('a');a.id='odpc-dock-remote';a.href=url;
   a.title='Fernhilfe / Remote Support';a.setAttribute('aria-label','Fernhilfe öffnen');
   var img=document.createElement('img');img.src=logo;img.alt='';img.width=34;img.height=34;
   a.appendChild(img);dock.appendChild(a);
  }
  if(document.body.classList.contains('home')&&!document.getElementById('odpc-home-remote')){
   var main=document.querySelector('#odpc-home,main .entry-content,main');
   if(main){
    var aside=document.createElement('aside');aside.id='odpc-home-remote';aside.className='odpc-home-remote';
    aside.innerHTML='<img src="'+logo+'" alt="" loading="lazy" width="64" height="64"><div><strong>Fernhilfe · Remote Support</strong><p>Persönliche Unterstützung für PC, Notebook und Smartphone.</p><a href="'+url+'">Zur ODPC-Fernhilfe</a></div>';
    main.insertBefore(aside,main.firstChild);
   }
  }
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',mount);else mount();
 window.addEventListener('load',mount);
})();
</script>
<?php },110);
