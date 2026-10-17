<?php
/**
 * Plugin Name: ODPC Monkey Voice Interview
 * Description: Accessible opt-in voice interview and guided setup for Helper Monkey.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;
add_action('wp_footer', function () {
 if (is_admin()) return;
 ?>
 <div id="odpc-voice" class="odpc-voice" hidden role="dialog" aria-modal="false" aria-labelledby="odpc-voice-title">
  <h2 id="odpc-voice-title">🐵 Monkey Sprachassistent</h2>
  <p id="odpc-voice-status" role="status" aria-live="polite">Bereit. Starte das Interview.</p>
  <div id="odpc-voice-log" aria-live="polite"></div>
  <label for="odpc-voice-answer">Deine Antwort</label>
  <input id="odpc-voice-answer" type="text" autocomplete="off">
  <div class="odpc-voice-actions">
   <button type="button" id="odpc-voice-mic">🎙 Sprechen</button>
   <button type="button" id="odpc-voice-send">Antwort senden</button>
   <button type="button" id="odpc-voice-repeat">🔊 Wiederholen</button>
   <button type="button" id="odpc-voice-back">Zurück</button>
   <button type="button" id="odpc-voice-close">Schließen</button>
  </div>
 </div>
 <button id="odpc-voice-open" type="button" aria-controls="odpc-voice" aria-expanded="false">🎙 Monkey sprechen</button>
 <style>
 #odpc-voice-open{position:fixed;left:12px;bottom:12px;z-index:9998;border:2px solid #fff;border-radius:28px;background:#164735;color:#fff;padding:12px 18px;cursor:pointer;font:700 15px system-ui}
 .odpc-voice{position:fixed;left:12px;bottom:72px;z-index:9998;width:min(430px,calc(100vw - 24px));max-height:75vh;overflow:auto;background:#fff;color:#193b2c;border:2px solid #8dbca5;border-radius:14px;padding:18px;box-shadow:0 8px 36px #0004;font:15px/1.5 system-ui}
 .odpc-voice[hidden]{display:none}.odpc-voice h2{margin:0 0 8px;font-size:21px}.odpc-voice input{width:100%;box-sizing:border-box;padding:11px;border:1px solid #777;border-radius:7px;font:inherit}
 .odpc-voice-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}.odpc-voice-actions button{padding:10px;border-radius:7px;background:#164735;color:white;border:0;cursor:pointer}
 #odpc-voice-log{max-height:190px;overflow:auto;margin:8px 0}#odpc-voice-log p{margin:5px 0}#odpc-voice-open:focus-visible,.odpc-voice button:focus-visible,.odpc-voice input:focus-visible{outline:3px solid #e6b83f;outline-offset:2px}
 </style>
 <script>
 (() => {
 'use strict';
 const $=id=>document.getElementById(id), panel=$('odpc-voice'), status=$('odpc-voice-status'), input=$('odpc-voice-answer'), log=$('odpc-voice-log');
 if(!panel||!status||!input)return;
 const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
 let recognition=null, speaking='',step=0,answers={},active=false;
 const questions=[
  ['goal','Was möchtest du mit Helper Monkey erreichen? Zum Beispiel eine Webseite verwalten, Bewerbungen schreiben, ein Fahrzeug diagnostizieren oder einen Assistenten einrichten.'],
  ['device','Welches Gerät verwendest du? Android, iPhone, Windows, Mac oder etwas anderes?'],
  ['account','Hast du bereits ein Google-Konto oder ein anderes Konto, das du verbinden möchtest? Bitte nenne niemals Passwörter.'],
  ['mode','Möchtest du zuerst kostenlos testen oder direkt die verfügbaren Einrichtungsschritte sehen?']
 ];
 function say(t){speaking=t; if(!('speechSynthesis' in window)){status.textContent='Sprachausgabe ist in diesem Browser nicht verfügbar.';return}speechSynthesis.cancel();const u=new SpeechSynthesisUtterance(t);u.lang='de-DE';u.rate=.94;speechSynthesis.speak(u)}
 function append(role,t){const p=document.createElement('p');const b=document.createElement('strong');b.textContent=role+': ';p.append(b,document.createTextNode(t));log.append(p);log.scrollTop=log.scrollHeight}
 function ask(){if(step>=questions.length){finish();return}const q=questions[step][1];status.textContent='Frage '+(step+1)+' von '+questions.length;append('Monkey',q);say(q)}
 function finish(){const goal=(answers.goal||'').toLowerCase();let path='/start/';if(/auto|fahrzeug|obd|motor/.test(goal))path='/start/';else if(/web|homepage|wordpress|seite/.test(goal))path='/webmaster-agent/';else if(/konto|connector|gmail|google/.test(goal))path='/connector-einrichten/';else if(/recht|anwalt|kanzlei/.test(goal))path='/konfliktlotse-anwaltsvorarbeit/';
 const summary='Danke. Ich habe deine Angaben aufgenommen. Ich zeige dir jetzt den passenden nächsten Schritt. Konten und Berechtigungen müssen ausdrücklich von dir freigegeben werden.';
 append('Monkey',summary);say(summary);
 const a=document.createElement('a');a.href=path;a.textContent='Passenden Einrichtungsweg öffnen →';a.style.display='block';a.style.marginTop='12px';log.append(a);
 const pre=document.createElement('p');pre.textContent='Dein Gespräch bleibt in diesem Browserfenster und wird von diesem Modul nicht an einen Server gesendet.';log.append(pre);
 status.textContent='Interview abgeschlossen';input.disabled=true;$('odpc-voice-send').disabled=true;$('odpc-voice-mic').disabled=true;
 }
 function submit(){const value=input.value.trim();if(!value)return;append('Du',value);answers[questions[step][0]]=value;step++;input.value='';ask()}
 $('odpc-voice-open').addEventListener('click',()=>{panel.hidden=false;$('odpc-voice-open').setAttribute('aria-expanded','true');if(!active){active=true;ask()}input.focus()});
 $('odpc-voice-close').addEventListener('click',()=>{panel.hidden=true;$('odpc-voice-open').setAttribute('aria-expanded','false');if(recognition)recognition.stop();if('speechSynthesis' in window)speechSynthesis.cancel();$('odpc-voice-open').focus()});
 $('odpc-voice-send').addEventListener('click',submit);
 input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();submit()}});
 $('odpc-voice-repeat').addEventListener('click',()=>say(speaking));
 $('odpc-voice-back').addEventListener('click',()=>{if(step===0)return;step--;delete answers[questions[step][0]];input.disabled=false;$('odpc-voice-send').disabled=false;$('odpc-voice-mic').disabled=false;ask()});
 $('odpc-voice-mic').addEventListener('click',()=>{
  if(!SpeechRecognition){status.textContent='Spracheingabe wird hier nicht unterstützt. Bitte tippe deine Antwort.';input.focus();return}
  if(recognition)recognition.abort();if('speechSynthesis' in window)speechSynthesis.cancel();
  recognition=new SpeechRecognition();recognition.lang='de-DE';recognition.interimResults=false;recognition.maxAlternatives=1;
  recognition.onresult=e=>{input.value=e.results[0][0].transcript;status.textContent='Antwort erkannt. Du kannst sie korrigieren oder senden.';input.focus()};
  recognition.onerror=e=>{status.textContent='Mikrofonfehler: '+e.error+'. Bitte nutze die Texteingabe.'};
  recognition.onend=()=>{recognition=null};try{recognition.start();status.textContent='Ich höre zu …'}catch(e){status.textContent='Mikrofon konnte nicht gestartet werden.'}
 });
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!panel.hidden)$('odpc-voice-close').click()});
 })();
 </script>
 <?php
}, 50);
