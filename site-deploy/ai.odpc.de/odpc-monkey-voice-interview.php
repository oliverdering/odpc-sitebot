<?php
/**
 * Plugin Name: ODPC Monkey Voice Interview
 * Description: Accessible opt-in voice interview and guided setup for Helper Monkey.
 * Version: 0.3.0
 */
if (!defined('ABSPATH')) exit;
add_action('wp_footer', function () {
 if (is_admin()) return;
 ?>
 <div id="odpc-voice" class="odpc-voice" hidden role="dialog" aria-modal="true" aria-labelledby="odpc-voice-title" aria-describedby="odpc-voice-status">
  <h2 id="odpc-voice-title">🐵 Monkey Sprachassistent</h2>
  <p id="odpc-voice-status" role="status" aria-live="polite">Bereit. Starte das Interview.</p>
  <div id="odpc-voice-log" role="log" aria-label="Gesprächsverlauf" aria-live="polite"></div>
  <label for="odpc-voice-language">Sprache</label><select id="odpc-voice-language"><option value="de-DE">Deutsch</option><option value="en-US">English</option></select>
  <label for="odpc-voice-persona">Stimme</label><select id="odpc-voice-persona"><option value="auto">Automatisch</option><option value="female">Frau</option><option value="male">Mann</option></select><button type="button" id="odpc-voice-preview">🔊 Stimme testen</button><label for="odpc-voice-audio"><input id="odpc-voice-audio" type="checkbox" checked> Sprachausgabe verwenden</label>
  <label for="odpc-voice-answer">Deine Antwort</label>
  <input id="odpc-voice-answer" type="text" autocomplete="off">
  <div class="odpc-voice-actions">
   <button type="button" id="odpc-voice-mic">🎙 Sprechen</button>
   <button type="button" id="odpc-voice-send">Antwort senden</button>
   <button type="button" id="odpc-voice-repeat">🔊 Wiederholen</button>
   <button type="button" id="odpc-voice-stop">■ Stopp</button>
   <button type="button" id="odpc-voice-reset">↻ Neu starten</button>
   <button type="button" id="odpc-voice-back">Zurück</button>
   <button type="button" id="odpc-voice-close">Schließen</button>
  </div>
 </div>
 <button id="odpc-voice-open" type="button" aria-controls="odpc-voice" aria-expanded="false">🎙 Monkey sprechen</button>
 <style>
 #odpc-voice-open{position:fixed;left:12px;bottom:12px;z-index:9998;border:2px solid #fff;border-radius:28px;background:#164735;color:#fff;padding:12px 18px;cursor:pointer;font:700 15px system-ui}
 .odpc-voice{position:fixed;left:12px;bottom:72px;z-index:9998;width:min(430px,calc(100vw - 24px));max-height:75vh;overflow:auto;background:#fff;color:#193b2c;border:2px solid #8dbca5;border-radius:14px;padding:18px;box-shadow:0 8px 36px #0004;font:15px/1.5 system-ui}
 .odpc-voice[hidden]{display:none}.odpc-voice h2{margin:0 0 8px;font-size:21px}.odpc-voice input{width:100%;box-sizing:border-box;padding:11px;border:1px solid #777;border-radius:7px;font:inherit}
 .odpc-voice select{width:100%;padding:8px;margin-bottom:8px}.odpc-voice label{display:block;margin-top:8px}.odpc-voice-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}.odpc-voice-actions button{padding:10px;border-radius:7px;background:#164735;color:white;border:0;cursor:pointer}
 #odpc-voice-log{max-height:190px;overflow:auto;margin:8px 0}#odpc-voice-log p{margin:5px 0}#odpc-voice-open:focus-visible,.odpc-voice button:focus-visible,.odpc-voice input:focus-visible{outline:3px solid #e6b83f;outline-offset:2px}
 </style>
 <script>
 (() => {
 'use strict';
 const $=id=>document.getElementById(id), panel=$('odpc-voice'), status=$('odpc-voice-status'), input=$('odpc-voice-answer'), log=$('odpc-voice-log');
 if(!panel||!status||!input)return;
 const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
 let recognition=null, speaking='',step=0,answers={},active=false,session=0,returnFocus=null;
 const questions=[
  ['goal','Was möchtest du mit Helper Monkey erreichen? Zum Beispiel eine Webseite verwalten, Bewerbungen schreiben, ein Fahrzeug diagnostizieren oder einen Assistenten einrichten.'],
  ['device','Welches Gerät verwendest du? Android, iPhone, Windows, Mac oder etwas anderes?'],
  ['account','Hast du bereits ein Google-Konto oder ein anderes Konto, das du verbinden möchtest? Bitte nenne niemals Passwörter.'],
  ['mode','Möchtest du zuerst kostenlos testen oder direkt die verfügbaren Einrichtungsschritte sehen?']
 ];
 function voiceChoice(){const lang=$('odpc-voice-language').value,persona=$('odpc-voice-persona').value;const voices=speechSynthesis.getVoices().filter(v=>v.lang.toLowerCase().startsWith(lang.slice(0,2).toLowerCase()));const female=/katja|hedda|anna|petra|helena|marlene|vicki|female|weiblich|sophie|google deutsch/i,male=/stefan|conrad|markus|male|männlich|thomas|daniel|martin|ralf/i;const matches=voices.filter(v=>persona==='auto'||(persona==='female'?female:male).test(v.name));const score=v=>(/natural|neural|premium/i.test(v.name)?40:0)+(/google|microsoft/i.test(v.name)?15:0)+(v.localService?5:0);matches.sort((a,b)=>score(b)-score(a));return {voice:matches[0]||null,available:matches.length>0};}
 function say(t){speaking=t;if(!$('odpc-voice-audio').checked)return;if(!('speechSynthesis' in window)||!window.SpeechSynthesisUtterance){status.textContent='Sprachausgabe nicht verfügbar. Text bleibt lesbar.';return}try{speechSynthesis.cancel();const u=new SpeechSynthesisUtterance(t);u.lang=$('odpc-voice-language').value;u.rate=1.17;const choice=voiceChoice();if(choice.voice)u.voice=choice.voice;else if($('odpc-voice-persona').value!=='auto')status.textContent='Gewählte Stimme auf diesem Gerät nicht verfügbar. Systemstimme wird verwendet.';u.onerror=()=>{status.textContent='Sprachausgabe fehlgeschlagen. Bitte Text lesen.'};speechSynthesis.speak(u)}catch(e){status.textContent='Sprachausgabe nicht verfügbar. Bitte Text lesen.'}}
 $('odpc-voice-preview').addEventListener('click',()=>say('Hallo! Ich bin Helper Monkey. So klingt meine ausgewählte Stimme.'));
 function stopAudio(){session++;if(recognition){try{recognition.abort()}catch(e){}recognition=null}if('speechSynthesis' in window)speechSynthesis.cancel()}
 function append(role,t){const p=document.createElement('p');const b=document.createElement('strong');b.textContent=role+': ';p.append(b,document.createTextNode(t));log.append(p);log.scrollTop=log.scrollHeight}
 function ask(){if(panel.hidden)return;if(step>=questions.length){finish();return}const q=questions[step][1];status.textContent='Frage '+(step+1)+' von '+questions.length;append('Monkey',q);say(q)}
 function finish(){if(panel.dataset.finished==='yes')return;panel.dataset.finished='yes';const goal=(answers.goal||'').toLowerCase();let path='/start/';if(/auto|fahrzeug|obd|motor/.test(goal))path='/start/';else if(/web|homepage|wordpress|seite/.test(goal))path='/webmaster-agent/';else if(/konto|connector|gmail|google/.test(goal))path='/connector-einrichten/';else if(/recht|anwalt|kanzlei/.test(goal))path='/konfliktlotse-anwaltsvorarbeit/';
 const summary='Danke. Ich habe deine Angaben aufgenommen. Ich zeige dir jetzt den passenden nächsten Schritt. Konten und Berechtigungen müssen ausdrücklich von dir freigegeben werden.';
 append('Monkey',summary);say(summary);
 const a=document.createElement('a');a.href=path;a.textContent='Passenden Einrichtungsweg öffnen →';a.style.display='block';a.style.marginTop='12px';log.append(a);
 const pre=document.createElement('p');pre.textContent='Dein Gespräch bleibt in diesem Browserfenster und wird von diesem Modul nicht an einen Server gesendet.';log.append(pre);
 status.textContent='Interview abgeschlossen';input.disabled=true;$('odpc-voice-send').disabled=true;$('odpc-voice-mic').disabled=true;
 }
 function submit(){if(step>=questions.length)return;const value=input.value.trim().slice(0,1000);if(!value)return;append('Du',value);answers[questions[step][0]]=value;step++;input.value='';ask()}
 $('odpc-voice-open').addEventListener('click',()=>{returnFocus=document.activeElement;panel.hidden=false;$('odpc-voice-open').setAttribute('aria-expanded','true');if(!active){active=true;ask()}input.focus()});
 $('odpc-voice-close').addEventListener('click',()=>{panel.hidden=true;$('odpc-voice-open').setAttribute('aria-expanded','false');stopAudio();(returnFocus&&returnFocus.isConnected?returnFocus:$('odpc-voice-open')).focus()});
 $('odpc-voice-send').addEventListener('click',submit);
 $('odpc-voice-stop').addEventListener('click',()=>{stopAudio();status.textContent='Audio gestoppt. Texteingabe ist weiterhin möglich.'});
 $('odpc-voice-reset').addEventListener('click',()=>{stopAudio();step=0;answers={};active=true;panel.dataset.finished='no';log.replaceChildren();input.value='';input.disabled=false;$('odpc-voice-send').disabled=false;$('odpc-voice-mic').disabled=false;ask();input.focus()});
 input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();submit()}});
 $('odpc-voice-repeat').addEventListener('click',()=>say(speaking));
 $('odpc-voice-back').addEventListener('click',()=>{if(step===0)return;stopAudio();if(panel.dataset.finished==='yes'){panel.dataset.finished='no';log.querySelectorAll('a[href]').forEach(a=>a.remove())}step--;delete answers[questions[step][0]];input.disabled=false;$('odpc-voice-send').disabled=false;$('odpc-voice-mic').disabled=false;ask()});
 $('odpc-voice-mic').addEventListener('click',()=>{
  if(!SpeechRecognition){status.textContent='Spracheingabe wird hier nicht unterstützt. Bitte tippe deine Antwort.';input.focus();return}
  if(recognition){try{recognition.abort()}catch(e){}}if('speechSynthesis' in window)speechSynthesis.cancel();
  const ticket=++session;recognition=new SpeechRecognition();recognition.lang=$('odpc-voice-language').value;recognition.interimResults=false;recognition.maxAlternatives=1;
  recognition.onresult=e=>{if(ticket!==session||panel.hidden)return;input.value=e.results[0][0].transcript;status.textContent='Antwort erkannt. Du kannst sie korrigieren oder senden.';input.focus()};
  recognition.onerror=e=>{if(ticket!==session||panel.hidden)return;status.textContent='Mikrofonfehler: '+e.error+'. Bitte nutze die Texteingabe.'};
  recognition.onend=()=>{if(ticket===session)recognition=null};try{recognition.start();status.textContent='Ich höre zu …'}catch(e){status.textContent='Mikrofon konnte nicht gestartet werden.'}
 });
 document.addEventListener('keydown',e=>{if(panel.hidden)return;if(e.key==='Escape'){$('odpc-voice-close').click();return}if(e.key==='Tab'){const nodes=Array.from(panel.querySelectorAll('button:not([disabled]),input:not([disabled]),select,a[href]')).filter(el=>el.getClientRects().length);if(!nodes.length)return;const first=nodes[0],last=nodes[nodes.length-1];if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus()}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus()}}});
 document.addEventListener('visibilitychange',()=>{if(document.hidden)stopAudio()});
 })();
 </script>
 <?php
}, 50);
