<?php
/* ODPC language rollout verification: 2026-11-10; deploy via existing SFTP workflow. */
/**
 * Plugin Name: ODPC Helper Monkey V4 Bridge
 * Description: Links ODPC services to the multilingual Helper Monkey start guide and preserves the existing WhatsApp Business button.
 * Version: 4.0.3
 */
if(!defined('ABSPATH'))exit;
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
 languageSelect.value=({de:'de-DE',en:'en-US',fr:'fr-FR',nl:'nl-NL',es:'es-ES',tr:'tr-TR',ru:'ru-RU'})[lang()]||'de-DE';languageSelect.addEventListener('change',function(){cancel();voiceSelect.value='';refreshVoices();if(window.ODPCLanguage)window.ODPCLanguage.set(locale().slice(0,2))});voiceSelect.addEventListener('change',cancel);if(synth){refreshVoices();if(typeof synth.addEventListener==='function')synth.addEventListener('voiceschanged',refreshVoices);else synth.onvoiceschanged=refreshVoices;setTimeout(refreshVoices,700);setTimeout(refreshVoices,2200)}
 function text(){var root=document.querySelector('article .entry-content,main .entry-content,main article,article,main,#content');if(!root)return '';var clone=root.cloneNode(true);clone.querySelectorAll('script,style,nav,form,button,footer,aside,.screen-reader-text,#odpc-reader,.gtranslate_wrapper').forEach(function(e){e.remove()});return (clone.textContent||'').replace(/\s+/g,' ').trim().slice(0,85000)}
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
 play.addEventListener('click',function(){if(!synth){status.textContent='Vorlesen in diesem Browser nicht unterstützt';return}if(paused){synth.resume();paused=false;status.textContent='Fortgesetzt';return}cancel();var s=text();if(!s){status.textContent='Kein Artikeltext gefunden';return}if(!window.SpeechSynthesisUtterance){status.textContent='Sprachausgabe auf diesem Gerät nicht verfügbar';return}queue=split(s);index=0;var t=token;next(t)});
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
#odpc-utility-dock{position:fixed!important;z-index:99990!important;bottom:max(8px,env(safe-area-inset-bottom))!important;left:50%!important;transform:translateX(-50%)!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:4px!important;width:max-content!important;max-width:calc(100vw - 12px)!important;min-height:43px!important;padding:4px 6px!important;border:1px solid #9baab2!important;border-radius:999px!important;background:#182c39f5!important;box-shadow:0 3px 14px #0005!important;overflow:visible!important}
#odpc-utility-dock #odpc-reader{position:static!important;display:flex!important;flex-direction:row!important;transform:none!important;width:auto!important;max-width:none!important;padding:0!important;margin:0!important;border:0!important;box-shadow:none!important;background:transparent!important;gap:3px!important}
#odpc-utility-dock #odpc-reader button{width:34px!important;height:34px!important;min-height:34px!important;font-size:17px!important}
#odpc-utility-dock #odpc-reader details{position:relative!important}
#odpc-utility-dock #odpc-reader .odpc-reader-tools{right:0!important;left:auto!important;bottom:40px!important}
#odpc-utility-dock #odpc-reader-status{right:0!important;bottom:46px!important}
#odpc-utility-dock .odpc-hm-v4-helpbtn,#odpc-utility-dock .odpc-hm-v4-wa,#odpc-utility-dock .odpc-wa-sticky{position:static!important;display:flex!important;align-items:center!important;justify-content:center!important;transform:none!important;margin:0!important;min-width:34px!important;width:34px!important;max-width:34px!important;height:34px!important;min-height:34px!important;padding:0!important;border:0!important;border-radius:50%!important;box-shadow:none!important;font-size:0!important;line-height:1!important;overflow:hidden!important}
#odpc-utility-dock .odpc-hm-v4-helpbtn::after{content:'❔';font-size:18px}
#odpc-utility-dock .odpc-hm-v4-wa::after,#odpc-utility-dock .odpc-wa-sticky::after{content:'☎';font-size:18px}
#odpc-utility-dock .odpc-hm-v4-help{position:fixed!important;bottom:56px!important;right:0!important;left:auto!important;max-width:calc(100vw - 20px)!important}
#odpc-utility-dock .odpc-dock-language{display:flex!important;align-items:center!important;justify-content:center!important;min-width:47px!important;max-width:75px!important;position:relative!important}
#odpc-utility-dock .odpc-dock-language .gt_float_switcher,#odpc-utility-dock .odpc-dock-language .gt_switcher_wrapper,#odpc-utility-dock .odpc-dock-language .gtranslate_wrapper{position:static!important;transform:none!important;margin:0!important}
#odpc-utility-dock .odpc-dock-language .gt-selected,#odpc-utility-dock .odpc-dock-language .gt-current-lang{min-height:34px!important;padding:4px!important}
@media(max-width:430px){#odpc-utility-dock{gap:2px!important;padding:3px 4px!important}#odpc-utility-dock #odpc-reader button{width:31px!important;height:31px!important;min-height:31px!important}}

/* Mobile release: one row, flag first, WhatsApp last; status never covers controls. */
#odpc-utility-dock{box-sizing:border-box!important;width:calc(100vw - 20px)!important;max-width:430px!important;min-width:0!important;justify-content:space-between!important;flex-wrap:nowrap!important;gap:2px!important;padding:4px 7px!important}
#odpc-utility-dock>*{flex-shrink:0!important}
#odpc-utility-dock .odpc-dock-language{order:0!important;flex:0 0 65px!important;min-width:0!important;max-width:65px!important;width:65px!important;overflow:visible!important}
#odpc-utility-dock .odpc-dock-language .gt_float_switcher,#odpc-utility-dock .odpc-dock-language .gt_switcher_wrapper,#odpc-utility-dock .odpc-dock-language .gtranslate_wrapper{max-width:65px!important;width:65px!important}
#odpc-utility-dock .odpc-dock-language .gt-selected,#odpc-utility-dock .odpc-dock-language .gt-current-lang{max-width:65px!important;width:65px!important;box-sizing:border-box!important;white-space:nowrap!important;overflow:hidden!important}
#odpc-utility-dock .odpc-dock-language .gt-lang-code,#odpc-utility-dock .odpc-dock-language .gt_float_switcher .gt-selected .gt-lang-code{display:none!important}
#odpc-utility-dock #odpc-reader{order:1!important;flex:0 1 auto!important;min-width:0!important;gap:2px!important;align-items:center!important;flex-wrap:nowrap!important}
#odpc-utility-dock #odpc-reader>label{display:none!important}
#odpc-utility-dock #odpc-reader button{flex-shrink:0!important;width:34px!important;height:34px!important;min-height:34px!important;padding:0!important}
#odpc-utility-dock #odpc-reader summary{width:30px!important;height:34px!important}
#odpc-utility-dock #odpc-reader #odpc-reader-pause,#odpc-utility-dock #odpc-reader #odpc-reader-stop{display:none!important}
#odpc-utility-dock #odpc-reader .odpc-reader-tools #odpc-reader-pause,#odpc-utility-dock #odpc-reader .odpc-reader-tools #odpc-reader-stop{display:flex!important}
#odpc-utility-dock #odpc-reader .odpc-reader-tools{bottom:48px!important;right:-48px!important;flex-wrap:wrap!important;max-width:calc(100vw - 36px)!important}
#odpc-utility-dock #odpc-reader-status{position:fixed!important;bottom:calc(100% + 9px)!important;left:8px!important;right:8px!important;width:auto!important;max-width:none!important;box-sizing:border-box!important;pointer-events:none!important}
#odpc-utility-dock #odpc-hm-v4-helpbtn{order:2!important;flex:0 0 34px!important}
#odpc-utility-dock .odpc-hm-v4-wa,#odpc-utility-dock .odpc-wa-sticky,#odpc-utility-dock #odpc-hm-v4-wa{order:3!important;flex:0 0 34px!important;margin-left:auto!important}
@media(max-width:390px){#odpc-utility-dock{padding:3px 5px!important}#odpc-utility-dock .odpc-dock-language{flex-basis:56px!important;max-width:56px!important;width:56px!important}#odpc-utility-dock .odpc-dock-language .gt_float_switcher,#odpc-utility-dock .odpc-dock-language .gt_switcher_wrapper,#odpc-utility-dock .odpc-dock-language .gtranslate_wrapper,#odpc-utility-dock .odpc-dock-language .gt-selected{max-width:56px!important;width:56px!important}#odpc-utility-dock #odpc-reader button{width:31px!important;height:31px!important;min-height:31px!important}#odpc-utility-dock #odpc-hm-v4-helpbtn,#odpc-utility-dock .odpc-hm-v4-wa,#odpc-utility-dock .odpc-wa-sticky{width:31px!important;min-width:31px!important;max-width:31px!important;height:31px!important;min-height:31px!important}}

/* ODPC minimal shared toolbar: exactly language, read aloud and WhatsApp. */
#odpc-utility-dock{width:min(290px,calc(100vw - 24px))!important;max-width:290px!important;min-height:46px!important;justify-content:space-between!important;gap:8px!important;padding:5px 9px!important;box-sizing:border-box!important;isolation:isolate!important}
#odpc-utility-dock .odpc-dock-language{order:0!important;flex:0 0 78px!important;width:78px!important;min-width:78px!important;max-width:78px!important;overflow:visible!important;align-self:center!important}
#odpc-utility-dock .odpc-dock-language .gt_float_switcher,#odpc-utility-dock .odpc-dock-language .gt_switcher_wrapper,#odpc-utility-dock .odpc-dock-language .gtranslate_wrapper{position:relative!important;inset:auto!important;transform:none!important;max-width:78px!important;width:78px!important;z-index:3!important}
#odpc-utility-dock .odpc-dock-language .gt-selected,#odpc-utility-dock .odpc-dock-language .gt-current-lang{width:78px!important;max-width:78px!important;overflow:hidden!important}
#odpc-utility-dock #odpc-reader{order:1!important;flex:0 0 42px!important;width:42px!important;max-width:42px!important;min-width:42px!important;overflow:visible!important}
#odpc-utility-dock #odpc-reader>label,#odpc-utility-dock #odpc-reader #odpc-reader-mic,#odpc-utility-dock #odpc-reader #odpc-reader-pause,#odpc-utility-dock #odpc-reader #odpc-reader-stop,#odpc-utility-dock #odpc-reader #odpc-reader-more,#odpc-utility-dock #odpc-hm-v4-helpbtn,#odpc-utility-dock #odpc-hm-v4-help{display:none!important}
#odpc-utility-dock #odpc-reader #odpc-reader-play{display:flex!important;align-items:center!important;justify-content:center!important;width:40px!important;height:36px!important;min-height:36px!important;font-size:19px!important}
#odpc-utility-dock #odpc-reader-status{position:absolute!important;bottom:calc(100% + 8px)!important;left:0!important;right:0!important;width:100%!important;max-width:290px!important;pointer-events:none!important}
#odpc-utility-dock .odpc-hm-v4-wa,#odpc-utility-dock .odpc-wa-sticky,#odpc-utility-dock #odpc-hm-v4-wa{order:2!important;margin-left:auto!important;flex:0 0 40px!important;min-width:40px!important;max-width:40px!important;width:40px!important;height:40px!important;min-height:40px!important;display:flex!important}
#odpc-utility-dock .odpc-dock-language .gt_float_switcher .gt_options{max-height:55vh!important;overflow-y:auto!important}
body.odpc-dock-active{padding-bottom:65px!important}
</style>
<script id="odpc-unified-dock-js">
(function(){
'use strict';
function mount(){
 if(document.getElementById('odpc-utility-dock'))return;
 var reader=document.getElementById('odpc-reader'),help=document.getElementById('odpc-hm-v4-helpbtn');
 if(!reader||!help)return;
 var dock=document.createElement('nav');dock.id='odpc-utility-dock';dock.setAttribute('aria-label','ODPC Schnellfunktionen');
 document.body.appendChild(dock);
 var lang=document.querySelector('.gt_float_switcher,.gt_switcher_wrapper,.gtranslate_wrapper');
 if(lang&&lang!==reader&&lang!==help){var holder=document.createElement('div');holder.className='odpc-dock-language';dock.appendChild(holder);holder.appendChild(lang)}
 dock.appendChild(reader);
 var tools=reader.querySelector('.odpc-reader-tools');if(tools){['odpc-reader-pause','odpc-reader-stop'].forEach(function(id){var b=document.getElementById(id);if(b)tools.insertBefore(b,tools.firstChild)})}
 dock.appendChild(help);help.setAttribute('aria-label','Fragen und Hilfe');help.setAttribute('title','Fragen und Hilfe');
 var panel=document.getElementById('odpc-hm-v4-help');if(panel)dock.appendChild(panel);
 var wa=document.querySelector('.odpc-wa-sticky')||document.getElementById('odpc-hm-v4-wa');
 if(wa){dock.appendChild(wa);wa.setAttribute('aria-label','WhatsApp Business');wa.setAttribute('title','WhatsApp Business')}
 document.body.classList.add('odpc-dock-active');
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',mount);else mount();
window.addEventListener('load',function(){if(!document.querySelector('#odpc-utility-dock .odpc-dock-language')){var lang=document.querySelector('.gt_float_switcher,.gt_switcher_wrapper,.gtranslate_wrapper');var dock=document.getElementById('odpc-utility-dock');if(lang&&dock){var holder=document.createElement('div');holder.className='odpc-dock-language';dock.insertBefore(holder,dock.firstChild);holder.appendChild(lang)}}});
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
