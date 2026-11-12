
(function(){
'use strict';
// SmartGuide enabled for every ODPC page; accepted pilot behavior preserved.
document.body.classList.add('odpc-smartguide-pilot');
var active=false,segments=[],index=0,token=0,current=null,voiceWorker=null,voiceAudio=null,voiceURL=null,workerReady=false,useEva=false,voiceTimer=null,pendingText='';
function stop(){
 active=false;token++;if(window.speechSynthesis)window.speechSynthesis.cancel();
 clearTimeout(voiceTimer);if(voiceWorker){voiceWorker.terminate();voiceWorker=null}workerReady=false;
 if(voiceAudio){voiceAudio.pause();voiceAudio.removeAttribute('src');voiceAudio.load();voiceAudio=null}if(voiceURL){URL.revokeObjectURL(voiceURL);voiceURL=null}
 document.getElementById('odpc-reader-play')?.removeAttribute('data-voice-state');
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
function systemSpeak(text,id){
 if(!active||id!==token)return;
 if(!window.speechSynthesis||!window.SpeechSynthesisUtterance){stop();return}
 var u=new SpeechSynthesisUtterance(text);u.lang=language();u.rate=.94;
 var voices=window.speechSynthesis.getVoices();var exact=voices.find(function(v){return v.lang.toLowerCase()===u.lang.toLowerCase()&&v.localService})||voices.find(function(v){return v.lang.toLowerCase()===u.lang.toLowerCase()})||voices.find(function(v){return v.lang.toLowerCase().split('-')[0]===u.lang.split('-')[0]});
 if(exact)u.voice=exact;
 u.onend=function(){speakNext(id)};u.onerror=function(e){if(e.error!=='interrupted'&&e.error!=='canceled')stop()};
 window.speechSynthesis.speak(u);
}
function fallback(text,id){
 if(!active||id!==token)return;useEva=false;clearTimeout(voiceTimer);
 if(voiceWorker){voiceWorker.terminate();voiceWorker=null}workerReady=false;
 if(voiceURL){URL.revokeObjectURL(voiceURL);voiceURL=null}
 var button=document.getElementById('odpc-reader-play');button.dataset.voiceState='fallback';
 systemSpeak(text,id);
}
function speakNext(id){
 if(!active||id!==token)return;
 if(current)current.classList.remove('odpc-smartguide-active');
 if(index>=segments.length){stop();return}
 var el=segments[index++];current=el;el.classList.add('odpc-smartguide-active');
 if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches)el.scrollIntoView({behavior:'smooth',block:'center'});
 var text=el.innerText.trim().slice(0,1200);pendingText=text;
 if(!useEva){systemSpeak(text,id);return}
 var button=document.getElementById('odpc-reader-play');button.dataset.voiceState='loading';
 clearTimeout(voiceTimer);voiceTimer=setTimeout(function(){fallback(text,id)},90000);
 try{
  if(!voiceWorker){
   voiceWorker=new Worker('/wp-content/plugins/speechable/assets/js/tts-worker.js?odpc_eva=20261113',{type:'module'});
   voiceWorker.onerror=function(){fallback(pendingText,id)};
   voiceWorker.onmessage=function(e){
    if(!active||id!==token)return;var message=e.data||{};
    if(message.type==='error'){fallback(pendingText,id);return}
    if(message.type==='ready'){workerReady=true;voiceWorker.postMessage({type:'processChunk',data:{text:pendingText,voiceId:'de_DE-eva_k-x_low',chunkIndex:0}});return}
    if(message.type==='chunkComplete'){
     clearTimeout(voiceTimer);if(voiceURL)URL.revokeObjectURL(voiceURL);
     voiceURL=URL.createObjectURL(new Blob([message.result.audioBuffer],{type:'audio/wav'}));
     if(!voiceAudio)voiceAudio=new Audio();
     voiceAudio.onended=function(){if(voiceURL){URL.revokeObjectURL(voiceURL);voiceURL=null}speakNext(id)};
     voiceAudio.onerror=function(){fallback(pendingText,id)};voiceAudio.src=voiceURL;
     var playing=voiceAudio.play();if(playing&&playing.then)playing.then(function(){if(active&&id===token)button.dataset.voiceState='eva-playing'}).catch(function(){fallback(pendingText,id)});
    }
   };
   voiceWorker.postMessage({type:'init',data:{voiceId:'de_DE-eva_k-x_low'}});
  }else if(workerReady)voiceWorker.postMessage({type:'processChunk',data:{text:text,voiceId:'de_DE-eva_k-x_low',chunkIndex:0}});
 }catch(e){fallback(text,id)}
}
function play(){
 stop();segments=collect();index=0;if(!segments.length)return;
 active=true;useEva=language().toLowerCase().startsWith('de')&&typeof Worker!=='undefined';
 var id=token;speakNext(id);
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
