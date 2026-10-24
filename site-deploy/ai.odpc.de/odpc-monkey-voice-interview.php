<?php
/**
 * Plugin Name: ODPC Monkey Voice Interview
 * Description: Accessible opt-in voice interview and guided setup for Helper Monkey.
 * Version: 0.12.0
 * License: GPL-3.0-or-later
 */
if (!defined('ABSPATH')) exit;
add_action('wp_footer', function () {
 if (is_admin()) return;
 ?>
 <div id="odpc-voice" class="odpc-voice" data-version="0.12.0" hidden role="dialog" aria-modal="true" aria-labelledby="odpc-voice-title" aria-describedby="odpc-voice-status">
  <h2 id="odpc-voice-title">Monkey Einrichtungsassistent</h2><button type="button" id="odpc-voice-topclose" aria-label="Schließen">✕</button>
  <p id="odpc-voice-status" role="status" aria-live="polite">Bereit. Starte das Interview.</p>
  <p id="hm-guide-progress" aria-live="polite"></p>
  <div id="odpc-voice-log" role="log" aria-label="Gesprächsverlauf" aria-live="polite"></div>
  <div id="hm-guide-output"></div>
  <div id="hm-guide-choices" aria-label="Antwort auswählen"></div>
  <details id="odpc-voice-settings"><summary>Stimme, Sprache &amp; weitere Funktionen</summary>
  <label id="label-gender" for="odpc-voice-gender">Stimme</label><select id="odpc-voice-gender"><option value="female" selected>Weiblich</option><option value="male">Männlich</option></select>
  <label id="label-language" for="odpc-voice-language">Sprache</label><select id="odpc-voice-language"><option value="de-DE">Deutsch</option><option value="en-US">English</option><option value="es-ES">Español</option><option value="fr-FR">Français</option><option value="it-IT">Italiano</option><option value="pt-PT">Português</option><option value="pl-PL">Polski</option><option value="tr-TR">Türkçe</option></select>
  <label for="odpc-voice-profile">Verfügbare Gerätestimme</label><select id="odpc-voice-profile"><option value="">Automatisch die passendste Stimme wählen</option></select>
  <p id="odpc-voice-voicehelp" hidden role="status"></p><button type="button" id="odpc-voice-preview">🔊 Stimme testen</button><label id="label-audio" for="odpc-voice-audio"><input id="odpc-voice-audio" type="checkbox"> Sprachausgabe verwenden</label>
  </details>
  <p id="hm-guide-privacy">Spracheingabe: Audio kann durch den Browseranbieter verarbeitet werden. Texteingabe ist ohne Mikrofon möglich. Keine Passwörter eingeben.</p>
  <label id="label-answer" for="odpc-voice-answer">Deine Antwort</label>
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
  <details id="hm-guide-history" hidden><summary>Bisheriges Gespräch anzeigen</summary><div id="hm-guide-history-log"></div></details>
  <a id="hm-guide-help" href="https://wa.me/492654882183?text=Hallo%20Oliver%2C%20ich%20ben%C3%B6tige%20Hilfe%20beim%20KI-Einstieg." target="_blank" rel="noopener noreferrer">Du kommst nicht weiter? Oliver Dering hilft persönlich über WhatsApp ↗</a>
 </div>
 <a id="hm-personal-help" href="https://wa.me/492654882183?text=Hallo%20Oliver%2C%20ich%20ben%C3%B6tige%20Hilfe%20bei%20helper%20%C2%B7%20monkey." target="_blank" rel="noopener noreferrer">Oliver hilft · WhatsApp ↗</a>
 <button id="odpc-voice-open" type="button" aria-controls="odpc-voice" aria-expanded="false">Connector öffnen</button>
 <style>
 #odpc-voice-open{position:fixed;left:12px;bottom:12px;z-index:9998;border:2px solid #fff;border-radius:28px;background:#164735;color:#fff;padding:12px 18px;cursor:pointer;font:700 15px system-ui}
 .odpc-voice{position:fixed;left:12px;bottom:72px;z-index:9998;width:min(430px,calc(100vw - 24px));max-height:min(78dvh,700px);overflow:auto;overscroll-behavior:contain;background:#fff;color:#193b2c;border:2px solid #8dbca5;border-radius:14px;padding:18px;box-shadow:0 8px 36px #0004;font:15px/1.5 system-ui}
 .odpc-voice #odpc-voice-topclose{position:sticky;top:0;float:right;border:0;border-radius:50%;background:#164735;color:#fff;width:40px;height:40px;font-size:22px;cursor:pointer}.odpc-voice[hidden]{display:none}#odpc-voice-voicehelp{font-size:13px;margin:3px 0 10px;color:#426653}.odpc-voice #odpc-voice-preview{padding:9px 12px;border:0;border-radius:7px;background:#164735;color:#fff;cursor:pointer}.odpc-voice #odpc-voice-preview:disabled{opacity:.5}.odpc-voice[hidden]{display:none}.odpc-voice h2{margin:0 0 8px;font-size:21px}.odpc-voice input[type=text]{width:100%;box-sizing:border-box;padding:11px;border:1px solid #777;border-radius:7px;font:inherit}
 .odpc-voice,.odpc-voice *{box-sizing:border-box}.odpc-voice select{width:100%;min-width:0;max-width:100%;padding:10px;margin-bottom:8px;font:inherit}.odpc-voice select option{max-width:100%}.odpc-voice input[type=checkbox]{width:auto;height:20px;vertical-align:middle;margin-right:7px}.odpc-voice label{display:block;margin-top:8px}.odpc-voice-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}.odpc-voice-actions button{padding:10px;border-radius:7px;background:#164735;color:white;border:0;cursor:pointer}
 @media(max-width:600px){.odpc-voice{left:8px;right:8px;bottom:76px;width:auto;max-height:calc(100dvh - 105px);padding:14px}.odpc-voice-actions button{flex:1 1 42%;min-height:44px}#odpc-voice-open{bottom:8px;left:8px}}#odpc-voice-log{max-height:190px;overflow:auto;margin:8px 0}#odpc-voice-log p{margin:5px 0}#odpc-voice-open:focus-visible,.odpc-voice button:focus-visible,.odpc-voice input:focus-visible{outline:3px solid #e6b83f;outline-offset:2px}
 </style>
 <script>
 (() => {
 'use strict';
 const $=id=>document.getElementById(id), panel=$('odpc-voice'), status=$('odpc-voice-status'), input=$('odpc-voice-answer'), log=$('odpc-voice-log');
 if(!panel||!status||!input)return;
 const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
 let recognition=null, speaking='',step=0,answers={},active=false,session=0,returnFocus=null;
 const languages={
 'de-DE':{ui:['Connector · helper monkey','Sprache','Stimme','Stimme testen','Sprachausgabe verwenden','Deine Antwort','Sprechen','Antwort senden','Wiederholen','Stopp','Neu starten','Zurück','Schließen','Connector öffnen'],questions:['Was möchtest du mit Helper Monkey erreichen? Zum Beispiel eine Webseite verwalten, Bewerbungen schreiben, ein Fahrzeug diagnostizieren oder einen Assistenten einrichten.','Welches Gerät verwendest du? Android, iPhone, Windows, Mac oder etwas anderes?','Hast du bereits ein Google-Konto oder ein anderes Konto, das du verbinden möchtest? Bitte nenne niemals Passwörter.','Möchtest du zuerst kostenlos testen oder direkt die verfügbaren Einrichtungsschritte sehen?'],preview:'Hallo, ich bin dein helper monkey. Schön, dass du da bist. Wir gehen alles in Ruhe durch, Schritt für Schritt. Du kannst jederzeit nachfragen.',summary:'Danke. Ich habe deine Angaben aufgenommen. Ich zeige dir jetzt den passenden nächsten Schritt. Konten und Berechtigungen müssen ausdrücklich von dir freigegeben werden.',next:'Passenden Einrichtungsweg öffnen →'},
 'en-US':{ui:['Monkey Voice Assistant','Language','Voice','Test voice','Use speech output','Your answer','Speak','Send answer','Repeat','Stop','Restart','Back','Close','Talk to Monkey'],questions:['What would you like to do with Helper Monkey? For example manage a website, write job applications, diagnose a vehicle or set up an assistant.','What device are you using: Android, iPhone, Windows, Mac or something else?','Do you have a Google account or another account to connect? Never share passwords.','Would you like to try it for free first or see the setup steps?'],preview:'Hello, I am Helper Monkey. This is how this voice sounds.',summary:'Thank you. Here is your next step. Connecting accounts always requires your explicit permission.',next:'Open recommended setup →'},
 'es-ES':{ui:['Asistente de voz Monkey','Idioma','Voz','Probar voz','Usar voz','Tu respuesta','Hablar','Enviar respuesta','Repetir','Detener','Reiniciar','Atrás','Cerrar','Hablar con Monkey'],questions:['¿Qué quieres conseguir con Helper Monkey? Por ejemplo, gestionar una web, preparar solicitudes de empleo, diagnosticar un vehículo o configurar un asistente.','¿Qué dispositivo utilizas: Android, iPhone, Windows, Mac u otro?','¿Tienes una cuenta de Google u otra cuenta que quieras conectar? Nunca compartas contraseñas.','¿Prefieres probarlo gratis o ver los pasos de configuración?'],preview:'Hola, soy Helper Monkey. Así suena esta voz.',summary:'Gracias. Te mostraré el siguiente paso. La conexión de cuentas requiere tu autorización expresa.',next:'Abrir configuración recomendada →'},
 'fr-FR':{ui:['Assistant vocal Monkey','Langue','Voix','Tester la voix','Activer la voix','Votre réponse','Parler','Envoyer','Répéter','Arrêter','Recommencer','Retour','Fermer','Parler à Monkey'],questions:['Que souhaitez-vous faire avec Helper Monkey ? Gérer un site, préparer une candidature, diagnostiquer un véhicule ou configurer un assistant ?','Quel appareil utilisez-vous : Android, iPhone, Windows, Mac ou autre ?','Avez-vous un compte Google ou un autre compte à connecter ? Ne partagez jamais de mot de passe.','Préférez-vous essayer gratuitement ou consulter les étapes de configuration ?'],preview:'Bonjour, je suis Helper Monkey. Voici cette voix.',summary:'Merci. Voici la prochaine étape. Toute connexion de compte nécessite votre autorisation explicite.',next:'Ouvrir la configuration →'},
 'it-IT':{ui:['Assistente vocale Monkey','Lingua','Voce','Prova voce','Usa la voce','La tua risposta','Parla','Invia','Ripeti','Ferma','Ricomincia','Indietro','Chiudi','Parla con Monkey'],questions:['Cosa vuoi fare con Helper Monkey? Gestire un sito, preparare candidature, diagnosticare un veicolo o configurare un assistente?','Quale dispositivo usi: Android, iPhone, Windows, Mac o altro?','Hai un account Google o un altro account da collegare? Non condividere password.','Vuoi provare gratuitamente o vedere i passaggi di configurazione?'],preview:'Ciao, sono Helper Monkey. Questa è la voce selezionata.',summary:'Grazie. Ecco il prossimo passo. Per collegare gli account serve la tua autorizzazione esplicita.',next:'Apri la configurazione →'},
 'pt-PT':{ui:['Assistente de voz Monkey','Idioma','Voz','Testar voz','Usar voz','A sua resposta','Falar','Enviar','Repetir','Parar','Reiniciar','Voltar','Fechar','Falar com Monkey'],questions:['O que pretende fazer com o Helper Monkey? Gerir um site, preparar candidaturas, diagnosticar um veículo ou configurar um assistente?','Que dispositivo utiliza: Android, iPhone, Windows, Mac ou outro?','Tem uma conta Google ou outra conta para ligar? Nunca partilhe palavras-passe.','Prefere experimentar gratuitamente ou ver os passos de configuração?'],preview:'Olá, sou o Helper Monkey. Esta é a voz selecionada.',summary:'Obrigado. Eis o próximo passo. A ligação de contas exige a sua autorização explícita.',next:'Abrir configuração →'},
 'pl-PL':{ui:['Asystent głosowy Monkey','Język','Głos','Test głosu','Włącz głos','Twoja odpowiedź','Mów','Wyślij','Powtórz','Stop','Od nowa','Wstecz','Zamknij','Porozmawiaj z Monkey'],questions:['Co chcesz zrobić z Helper Monkey? Zarządzać stroną, przygotować CV, diagnozować samochód czy skonfigurować asystenta?','Jakiego urządzenia używasz: Android, iPhone, Windows, Mac czy innego?','Czy masz konto Google lub inne konto do połączenia? Nigdy nie podawaj haseł.','Chcesz najpierw przetestować za darmo czy zobaczyć instrukcję konfiguracji?'],preview:'Cześć, jestem Helper Monkey. Tak brzmi ten głos.',summary:'Dziękuję. Oto kolejny krok. Połączenie kont wymaga wyraźnej zgody.',next:'Otwórz konfigurację →'},
 'tr-TR':{ui:['Monkey Sesli Asistan','Dil','Ses','Sesi dene','Sesli yanıt','Yanıtınız','Konuş','Gönder','Tekrarla','Durdur','Yeniden başlat','Geri','Kapat','Monkey ile konuş'],questions:['Helper Monkey ile ne yapmak istiyorsunuz? Web sitesi yönetmek, iş başvurusu hazırlamak, araç arızası teşhis etmek veya asistan kurmak mı?','Hangi cihazı kullanıyorsunuz: Android, iPhone, Windows, Mac veya başka bir cihaz mı?','Bağlamak istediğiniz bir Google hesabınız veya başka hesabınız var mı? Şifre paylaşmayın.','Önce ücretsiz denemek mi yoksa kurulum adımlarını görmek mi istersiniz?'],preview:'Merhaba, ben Helper Monkey. Seçtiğiniz ses böyle duyuluyor.',summary:'Teşekkürler. Sıradaki adım burada. Hesap bağlantıları açık izninizi gerektirir.',next:'Kurulumu aç →'}
 };
 const uiIds=['odpc-voice-title','label-language','label-gender','odpc-voice-preview','label-audio','label-answer','odpc-voice-mic','odpc-voice-send','odpc-voice-repeat','odpc-voice-stop','odpc-voice-reset','odpc-voice-back','odpc-voice-close','odpc-voice-open'];
 const guide=window.MonkeyGuide;
 if(!guide){status.textContent='Einrichtungsdialog konnte nicht geladen werden. Bitte neu laden oder Oliver über WhatsApp fragen.';return;}
 const inlineMount=$('hm-connector-mount');
 let modalOpen=false,inertBefore=[],pagePosition={x:0,y:0};
 const backdrop=document.createElement('div');backdrop.id='hm-connector-backdrop';backdrop.hidden=true;document.body.append(backdrop);
 let flow=guide.state(),history=[],lastPrompt='';
 try{const saved=JSON.parse(sessionStorage.getItem('hm-guide-v1')||'null');flow=guide.restore(saved)}catch(e){}
 const guideLang=()=> $('odpc-voice-language').value.split('-')[0];
 function persist(){try{sessionStorage.setItem('hm-guide-v1',JSON.stringify(flow))}catch(e){}}
 function localize(){const l=languages[$('odpc-voice-language').value];uiIds.forEach((id,i)=>{const el=$(id);if(el){if(id==='label-audio'){const cb=$('odpc-voice-audio');el.replaceChildren(cb,document.createTextNode(' '+l.ui[i]))}else el.textContent=l.ui[i]}});$('odpc-voice-topclose').setAttribute('aria-label',l.ui[12]);if(active){stopAudio();ask();}}

 let voiceCatalog=[],speechTicket=0,voiceWaitTimer=null;
 function voiceGender(v){
  const n=((v.name||'')+' '+(v.voiceURI||'')).toLowerCase();
  if(/\b(female|woman|feminine|weiblich|mujer|femme|donna|femenino)\b|\b(anna|sarah|samantha|victoria|karen|helena|maria|monica|lucia|sofia|laura|hedda|heda|sabine|jenny|aria|emma|amy|susan|katja|zira|hazel|hortense|elsa|sabina|paulina|julie|denise|amelie|emilie|luciana|francisca|zofia|emel)\b/.test(n))return 'female';
  if(/\b(male|man|masculine|männlich|hombre|homme|uomo|masculino)\b|\b(david|mark|daniel|george|thomas|stefan|paul|alexander|jorge|diego|pablo|carlos|enrique|stefan|conrad|christoph|stefano|guy|andrew|matthew|liam|oliver|brian|james|ryan|henri|cosimo|antonio|duarte|marek|ahmet)\b/.test(n))return 'male';
  return 'unknown';
 }
 function voiceQuality(v){
  const n=(v.name+' '+(v.voiceURI||'')).toLowerCase();
  return (/natural|neural|online/.test(n)?300:0)+(/premium|enhanced|hochwertig/.test(n)?200:0)+(/google|samsung/.test(n)?120:0)+(voiceGender(v)==='female'?60:0)+(v.default?15:0)-(/compact|basic|espeak/.test(n)?150:0);
 }
 function refreshVoices(){
  const locale=$('odpc-voice-language').value.toLowerCase(),lang=locale.split('-')[0],gender=$('odpc-voice-gender').value;
  let all=[];try{all=window.speechSynthesis?.getVoices()||[]}catch(e){}
  voiceCatalog=all.filter(v=>(v.lang||'').toLowerCase().replace(/_/g,'-').split('-')[0]===lang && (voiceGender(v)===gender || (gender==='female'&&voiceGender(v)==='unknown')) && !/espeak/i.test(v.name||'')).sort((a,b)=>voiceQuality(b)-voiceQuality(a)||Number(b.lang.toLowerCase()===locale)-Number(a.lang.toLowerCase()===locale));
  const menu=$('odpc-voice-profile'),previous=menu.value;menu.replaceChildren();
  const auto=document.createElement('option');auto.value='';auto.textContent='Automatisch die passendste Stimme wählen';menu.append(auto);
  voiceCatalog.forEach(v=>{const option=document.createElement('option');option.value=v.voiceURI||v.name;option.textContent=v.name+(voiceGender(v)==='unknown'?' · Geschlecht nicht angegeben':'');menu.append(option)});
  if(voiceCatalog.some(v=>(v.voiceURI||v.name)===previous))menu.value=previous;
  const selected=voiceCatalog.find(v=>(v.voiceURI||v.name)===menu.value)||voiceCatalog[0];
  const native=!!window.speechSynthesis&&!!window.SpeechSynthesisUtterance&&!!selected;
  const help=$('odpc-voice-voicehelp');help.hidden=false;
  help.textContent=native?'Gerätestimme: '+selected.name+'. Zügiges, natürliches Sprechtempo.'+(voiceGender(selected)==='unknown'?' Der Anbieter nennt kein Geschlecht. Bitte mit der Hörprobe prüfen.':''):'Noch keine passende Gerätestimme verfügbar. Auf Samsung: Einstellungen → Allgemeine Verwaltung → Text-zu-Sprache → Samsung oder Google → deutsche Sprachdaten. Danach diese Seite neu öffnen. Texteingabe funktioniert weiter.';
  $('odpc-voice-preview').disabled=!native;menu.disabled=!native;
  return selected;
 }
 function stopLocalAudio(){if(voiceWaitTimer){clearTimeout(voiceWaitTimer);voiceWaitTimer=null}panel.dataset.audioState='stopped'}
 function say(t,remember=true,force=false){
  if(remember)speaking=t;if(!force&&!$('odpc-voice-audio').checked)return;
  const ticket=++speechTicket;stopLocalAudio();if(window.speechSynthesis)speechSynthesis.cancel();
  const speakWhenReady=(retry)=>{
   if(ticket!==speechTicket||panel.hidden)return;
   const selected=refreshVoices();
   if(!selected||!window.speechSynthesis||!window.SpeechSynthesisUtterance){
    if(retry){panel.dataset.audioState='waiting-for-device-voice';voiceWaitTimer=setTimeout(()=>speakWhenReady(false),1200);return}
    panel.dataset.audioState='voice-unavailable';panel.dataset.audioVoice='';status.textContent='Keine passende Gerätestimme verfügbar. Bitte deutsche Sprachdaten in den Geräteeinstellungen aktivieren oder die Texteingabe nutzen.';return;
   }
   panel.dataset.audioVoice=selected.voiceURI||selected.name;panel.dataset.audioEngine='device';
   const parts=(String(t).match(/[^.!?]+[.!?]+|[^.!?]+$/g)||[String(t)]).flatMap(sentence=>sentence.match(/.{1,220}(?:\s|$)|.{1,220}/g)||[sentence]);let index=0;
   const next=()=>{
    if(ticket!==speechTicket||panel.hidden)return;
    if(index>=parts.length){panel.dataset.audioState='ended';status.textContent='Sprachausgabe beendet.';return}
    const u=new SpeechSynthesisUtterance(parts[index++].trim());u.lang=$('odpc-voice-language').value;u.rate=1.12;u.pitch=1;u.volume=1;u.voice=selected;
    u.onstart=()=>{if(ticket===speechTicket)panel.dataset.audioState='playing'};
    u.onend=()=>{if(ticket===speechTicket)voiceWaitTimer=setTimeout(next,140)};
    u.onerror=()=>{if(ticket!==speechTicket)return;panel.dataset.audioState='error';status.textContent='Die Gerätestimme konnte nicht starten. Bitte „Stimme testen“ erneut antippen oder eine andere verfügbare Stimme wählen. Es wird keine künstliche Ersatzstimme zugeschaltet.'};
    try{speechSynthesis.speak(u)}catch(e){u.onerror()}
   };next();
  };speakWhenReady(true);
 }
 $('odpc-voice-preview').addEventListener('click',()=>say(languages[$('odpc-voice-language').value].preview,false,true));
 $('odpc-voice-language').addEventListener('change',()=>{flow.language=guideLang();persist();$('odpc-voice-profile').value='';refreshVoices();localize()});
 $('odpc-voice-profile').addEventListener('change',()=>{stopAudio();refreshVoices();if(active)say(languages[$('odpc-voice-language').value].preview,false,true)});
 $('odpc-voice-gender').addEventListener('change',()=>{stopAudio();$('odpc-voice-profile').value='';refreshVoices();status.textContent='Stimme ausgewählt. Mit „Stimme testen“ anhören.';if(active&&$('odpc-voice-audio').checked)say(languages[$('odpc-voice-language').value].preview,false)});
 $('odpc-voice-audio').addEventListener('change',()=>{if(!$('odpc-voice-audio').checked)stopAudio()});
 if('speechSynthesis' in window){speechSynthesis.addEventListener?.('voiceschanged',refreshVoices);setTimeout(refreshVoices,700);setTimeout(refreshVoices,2000);}refreshVoices();localize();

 function stopAudio(){session++;speechTicket++;stopLocalAudio();if(recognition){try{recognition.abort()}catch(e){}recognition=null}if('speechSynthesis' in window)speechSynthesis.cancel()}
 function append(role,t){
  const past=$('hm-guide-history-log');
  for(const node of Array.from(log.children)){if(node.classList.contains('hm-guide-next'))node.remove();else past.append(node)}
  const p=document.createElement('p'),b=document.createElement('strong');b.textContent=role+': ';p.append(b,document.createTextNode(t));
  (role==='Monkey'?log:past).append(p);while(past.children.length>60)past.firstElementChild.remove();$('hm-guide-history').hidden=!past.children.length;
 }
 function fitPrompts(){for(const field of panel.querySelectorAll('textarea[readonly]')){if(!field.getClientRects().length)continue;field.style.height='auto';field.style.height=(field.scrollHeight+2)+'px';}}
 function revealStep(){
  if(modalOpen){panel.scrollTop=0;return}
  if(inlineMount){const header=document.querySelector('.hm-header'),edge=header?Math.max(0,header.getBoundingClientRect().bottom):0;window.scrollBy({top:panel.getBoundingClientRect().top-edge-16,behavior:'instant'})}
 }
 function sizeViewport(){
  const view=window.visualViewport;panel.style.setProperty('--hm-view-height',(view?view.height:window.innerHeight)+'px');panel.style.setProperty('--hm-view-top',(view?view.offsetTop:0)+'px');fitPrompts();
  if(modalOpen&&document.activeElement===input)input.scrollIntoView({block:'nearest',behavior:'instant'});
 }
 window.visualViewport?.addEventListener('resize',sizeViewport);window.addEventListener('resize',sizeViewport);

 function updateHelp(){
  const phaseNames={goal:'Bedarf klären',platform:'KI-Konto auswählen',device:'Gerät auswählen',service:'Dienst auswählen',setup:'Konto offiziell verbinden',test:'Verbindung testen',done:'Aufgabe auswählen',work:'Mit deiner KI arbeiten'};
  const text='Hallo Oliver, ich richte gerade helper · monkey ein und benötige Hilfe beim Schritt „'+phaseNames[flow.phase]+'“.';
  const href='https://wa.me/492654882183?text='+encodeURIComponent(text);
  $('hm-guide-help').href=href;$('hm-personal-help').href=href;
  panel.dataset.phase=flow.phase;panel.dataset.connectionResult=flow.result;
  const stage={goal:1,platform:2,device:3,service:3,setup:4,test:5,done:5,work:5};
  $('hm-guide-progress').textContent=(['done','work'].includes(flow.phase)?(guideLang()==='de'?'Dein nächster Schritt · ':'Your next step · '):(guideLang()==='de'?'Schritt ':'Step ')+stage[flow.phase]+' / 5 · ')+phaseNames[flow.phase];
  $('odpc-voice-back').disabled=!history.length;
 }
 function controls(){
  log.querySelectorAll('.hm-guide-next').forEach(e=>e.remove());updateHelp();
  const selectedTask=guide.tasks(flow,guideLang()).find(t=>t.id===(flow.task||flow.targetTask));
  $('odpc-voice-title').textContent=selectedTask?selectedTask.title:languages[$('odpc-voice-language').value].ui[0];
  const output=$('hm-guide-output');output.replaceChildren();
  const choices=$('hm-guide-choices');choices.replaceChildren();
  for(const choice of guide.choices(flow,guideLang())){const b=document.createElement('button');b.type='button';b.textContent=choice.label;b.dataset.choice=choice.value;b.onclick=()=>{input.value=choice.value;submit()};choices.append(b)}
  if(flow.phase==='done'){
   const p=document.createElement('p');p.className='hm-task-intro';p.textContent=guideLang()==='de'?'Was möchtest du jetzt tatsächlich machen? Wähle eine Aufgabe. Wir bereiten den passenden Arbeitsauftrag für deine KI vor.':'What would you like to do now? Choose a task to prepare the prompt for your AI.';output.append(p);return;
  }
  if(flow.phase==='work'){
   const data=guide.work(flow,guideLang());if(!data)return;
   const title=document.createElement('h3');title.textContent=data.title;output.append(title);
   const note=document.createElement('p');note.textContent=guideLang()==='de'?'1. Auftrag kopieren. 2. Dein KI-Konto öffnen. 3. Dort einfügen und absenden. Deine KI fragt nach den Details und prüft ihre Zugriffe.':'1. Copy the prompt. 2. Open your AI account. 3. Paste and send it there. Your AI will ask for details and check its access.';output.append(note);
   const prompt=document.createElement('textarea');prompt.id='hm-guide-work-prompt';prompt.readOnly=true;prompt.setAttribute('aria-label',guideLang()==='de'?'Dein Arbeitsauftrag':'Your task prompt');prompt.value=data.prompt;const preview=document.createElement('details');preview.id='hm-guide-work-preview';const summary=document.createElement('summary');summary.textContent=guideLang()==='de'?'Arbeitsauftrag ansehen':'View task prompt';preview.append(summary,prompt);preview.addEventListener('toggle',fitPrompts);output.append(preview);
   const copy=document.createElement('button');copy.type='button';copy.id='hm-guide-work-copy';copy.textContent=guideLang()==='de'?'Auftrag kopieren':'Copy task prompt';copy.onclick=async()=>{try{await navigator.clipboard.writeText(data.prompt);status.textContent=guideLang()==='de'?'Auftrag kopiert. Öffne jetzt '+data.platform+' und füge ihn dort ein.':'Copied. Open '+data.platform+' and paste it there.'}catch(e){preview.open=true;fitPrompts();prompt.focus();prompt.select();status.textContent=guideLang()==='de'?'Bitte den markierten Auftrag kopieren.':'Please copy the selected text.'}};output.append(copy);
   const link=document.createElement('a');link.id='hm-guide-work-open';link.href=data.url;link.target='_blank';link.rel='noopener noreferrer';link.textContent=data.platform+(guideLang()==='de'?' öffnen ↗':' open ↗');output.append(link);
   const fine=document.createElement('p');fine.className='hm-fine';fine.textContent=guideLang()==='de'?'Die Arbeit läuft in deinem KI-Konto mit dessen Freigaben. Diese Webseite kann deine Konten weder prüfen noch selbst Aufgaben dort ausführen.':'The task runs in your AI account with its permissions. This website cannot inspect or operate your accounts.';output.append(fine);return;
  }
  if(flow.phase==='platform'){const box=document.createElement('div');box.className='hm-guide-next';const p=document.createElement('p');p.textContent=guideLang()==='de'?'Wähle deine KI oben aus. Noch kein Konto? Beim jeweiligen Originalanbieter kannst du ein kostenloses Konto anlegen.':'Choose your AI above. You can create a free account at its official website.';box.append(p);for(const name of ['chatgpt','gemini','claude']){const a=document.createElement('a');a.href=guide.setup({platform:name,services:[]},guideLang()).url;a.target='_blank';a.rel='noopener noreferrer';a.textContent=({chatgpt:'ChatGPT',gemini:'Gemini',claude:'Claude'})[name]+' ↗';box.append(a)}log.append(box);return;}
  if(!['setup','test'].includes(flow.phase))return;
  const data=guide.setup(flow,guideLang()),box=document.createElement('div');box.className='hm-guide-next';
  const p=document.createElement('p');p.textContent=data.steps;box.append(p);const cost=document.createElement('p');cost.className='hm-cost-note';cost.textContent=data.cost;box.append(cost);
  const link=document.createElement('a');link.href=data.url;link.textContent=(flow.platform||'KI-Konto')+' ↗';link.target='_blank';link.rel='noopener noreferrer';box.append(link);
  const help=document.createElement('a');help.href=data.help;help.textContent=guideLang()==='de'?'Offizielle Anleitung ↗':'Official guide ↗';help.target='_blank';help.rel='noopener noreferrer';box.append(help);
  if(flow.phase==='test'){
   const label=document.createElement('label');label.htmlFor='hm-guide-test';label.textContent=guideLang()==='de'?'Dein Testauftrag für das KI-Konto':'Test prompt for your AI account';box.append(label);
   const prompt=document.createElement('textarea');prompt.id='hm-guide-test';prompt.readOnly=true;prompt.value=data.test;box.append(prompt);
   const copy=document.createElement('button');copy.type='button';copy.textContent=guideLang()==='de'?'Testauftrag kopieren':'Copy test prompt';copy.onclick=async()=>{try{await navigator.clipboard.writeText(data.test);status.textContent=guideLang()==='de'?'Kopiert. Jetzt im KI-Konto einfügen.':'Copied. Paste in your AI account.'}catch(e){prompt.focus();prompt.select();status.textContent='Bitte markierten Text kopieren.'}};box.append(copy);
  }
  log.append(box);fitPrompts();
 }
 function ask(){if(panel.hidden)return;const q=guide.message(flow,guideLang());lastPrompt=q;status.textContent=guideLang()==='de'?'Dein Einrichtungsdialog':'Your setup guide';append('Monkey',q);controls();say(q)}
 function finish(){controls()}
 function submit(){
  const value=input.value.trim().slice(0,600);if(!value)return;
  const result=guide.reply(flow,value,guideLang());stopAudio();
  const selectedChoice=guide.choices(flow,guideLang()).find(c=>c.value===value);
  if(!result.sensitive)append(guideLang()==='de'?'Du':'You',selectedChoice?selectedChoice.label:value);
  if(result.accepted&&!result.help){history.push(JSON.parse(JSON.stringify(flow)));flow=result.state;persist()}
  input.value='';append('Monkey',result.text);lastPrompt=result.text;controls();if(result.accepted&&!result.help){panel.focus({preventScroll:true});revealStep()}say(result.text);
 }
 function openConnector(){
  returnFocus=document.activeElement;const mode=$('odpc-voice-open').dataset.startMode||'text';delete $('odpc-voice-open').dataset.startMode;
  $('odpc-voice-audio').checked=mode==='voice';if(mode!=='voice')stopAudio();
  if(inlineMount&&!modalOpen)inlineMount.style.minHeight=panel.offsetHeight+'px';
  if(!modalOpen){pagePosition={x:window.scrollX,y:window.scrollY};document.body.style.setProperty('--hm-page-y',-pagePosition.y+'px');document.body.style.setProperty('--hm-page-x',-pagePosition.x+'px');inertBefore=Array.from(document.querySelectorAll('main,header.hm-header,footer.hm-footer')).map(el=>[el,el.inert]);inertBefore.forEach(([el])=>el.inert=true)}
  modalOpen=true;document.body.append(panel);panel.classList.remove('hm-inline-connector');panel.classList.add('hm-modal-connector');panel.tabIndex=-1;panel.setAttribute('role','dialog');panel.setAttribute('aria-modal','true');panel.hidden=false;
  backdrop.hidden=false;document.documentElement.classList.add('hm-connector-modal-open');document.body.classList.add('hm-connector-modal-open');sizeViewport();$('odpc-voice-topclose').hidden=false;$('odpc-voice-close').hidden=false;$('odpc-voice-open').setAttribute('aria-expanded','true');
  if(!active){active=true;ask()}else if(mode==='voice')say(lastPrompt);
  panel.scrollTop=0;panel.focus({preventScroll:true});refreshVoices();
 }
 function closeConnector(){
  stopAudio();modalOpen=false;backdrop.hidden=true;document.documentElement.classList.remove('hm-connector-modal-open');document.body.classList.remove('hm-connector-modal-open');document.body.style.removeProperty('--hm-page-y');document.body.style.removeProperty('--hm-page-x');inertBefore.forEach(([el,value])=>el.inert=value);inertBefore=[];panel.classList.remove('hm-modal-connector');
  if(inlineMount){inlineMount.style.minHeight='';inlineMount.append(panel);panel.classList.add('hm-inline-connector');panel.setAttribute('role','region');panel.removeAttribute('aria-modal');panel.hidden=false;$('odpc-voice-topclose').hidden=true;$('odpc-voice-close').hidden=true}else panel.hidden=true;
  window.scrollTo({left:pagePosition.x,top:pagePosition.y,behavior:'instant'});
  $('odpc-voice-open').setAttribute('aria-expanded','false');(returnFocus&&returnFocus.isConnected?returnFocus:$('odpc-voice-open')).focus({preventScroll:true});
 }
 $('odpc-voice-open').addEventListener('click',openConnector);
 $('odpc-voice-topclose').addEventListener('click',closeConnector);
 $('odpc-voice-close').addEventListener('click',closeConnector);
 backdrop.addEventListener('click',closeConnector);
 $('odpc-voice-send').addEventListener('click',submit);
 $('odpc-voice-stop').addEventListener('click',()=>{stopAudio();status.textContent='Audio gestoppt. Texteingabe ist weiterhin möglich.'});
 $('odpc-voice-reset').addEventListener('click',()=>{stopAudio();flow=guide.state();history=[];persist();active=true;panel.dataset.finished='no';log.replaceChildren();$('hm-guide-history-log').replaceChildren();$('hm-guide-history').hidden=true;$('hm-guide-history').open=false;input.value='';input.disabled=false;$('odpc-voice-send').disabled=false;$('odpc-voice-mic').disabled=false;ask();panel.focus({preventScroll:true});revealStep()});
 input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();submit()}});
 $('odpc-voice-repeat').addEventListener('click',()=>say(speaking));
 $('odpc-voice-back').addEventListener('click',()=>{if(!history.length){status.textContent=guideLang()==='de'?'Kein vorheriger Schritt in dieser Sitzung.':'No earlier step in this session.';return}stopAudio();flow=history.pop();persist();input.value='';ask();panel.focus({preventScroll:true});revealStep();});
 $('odpc-voice-mic').addEventListener('click',()=>{
  if(!SpeechRecognition){status.textContent='Spracheingabe wird hier nicht unterstützt. Bitte tippe deine Antwort.';input.focus();return}
  stopAudio();
  $('odpc-voice-audio').checked=true;
  const ticket=++session;recognition=new SpeechRecognition();recognition.lang=$('odpc-voice-language').value;recognition.interimResults=false;recognition.maxAlternatives=1;
  recognition.onresult=e=>{if(ticket!==session||panel.hidden)return;input.value=e.results[0][0].transcript;status.textContent='Antwort erkannt.';submit();panel.focus({preventScroll:true})};
  recognition.onerror=e=>{if(ticket!==session||panel.hidden)return;status.textContent='Mikrofonfehler: '+e.error+'. Bitte nutze die Texteingabe.'};
  recognition.onend=()=>{if(ticket===session)recognition=null};try{recognition.start();status.textContent='Ich höre zu …'}catch(e){status.textContent='Mikrofon konnte nicht gestartet werden.'}
 });
 document.addEventListener('keydown',e=>{if(panel.hidden||!modalOpen)return;if(e.key==='Escape'){$('odpc-voice-close').click();return}if(e.key==='Tab'){const nodes=Array.from(panel.querySelectorAll('button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),summary,a[href]')).filter(el=>el.getClientRects().length);if(!nodes.length)return;const first=nodes[0],last=nodes[nodes.length-1];if(e.shiftKey&&(document.activeElement===first||document.activeElement===panel)){e.preventDefault();last.focus()}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus()}}});
 updateHelp();
 const extra=document.createElement('div');extra.className='odpc-voice-actions';
 ['odpc-voice-repeat','odpc-voice-reset','odpc-voice-close'].forEach(id=>extra.append($(id)));$('odpc-voice-settings').append(extra);
 const alias={de:'de-DE',en:'en-US',es:'es-ES',fr:'fr-FR',it:'it-IT',pt:'pt-PT',pl:'pl-PL',tr:'tr-TR'},requested=new URLSearchParams(location.search).get('lang');
 if(alias[requested])flow.language=requested;
 $('odpc-voice-language').value=alias[flow.language]||alias.de;localize();refreshVoices();
 const requestedAgent=new URLSearchParams(location.search).get('agent');
 if(requestedAgent&&flow.targetTask!==requestedAgent&&flow.returnTask!==requestedAgent){flow=guide.selectAgent(flow,requestedAgent);persist()}
 if(inlineMount){inlineMount.append(panel);panel.tabIndex=-1;panel.classList.add('hm-inline-connector');panel.setAttribute('role','region');panel.removeAttribute('aria-modal');panel.hidden=false;$('odpc-voice-topclose').hidden=true;$('odpc-voice-close').hidden=true;$('odpc-voice-open').setAttribute('aria-expanded','false');active=true;ask();}
 if(requestedAgent&&flow.targetTask===requestedAgent&&inlineMount){panel.focus({preventScroll:true});revealStep()}
 document.addEventListener('visibilitychange' ,()=>{if(document.hidden)stopAudio()});
 })();
 </script>
 <?php
}, 50);

