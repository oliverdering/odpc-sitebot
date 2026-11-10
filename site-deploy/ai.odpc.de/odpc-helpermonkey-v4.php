<?php
/**
 * Plugin Name: ODPC Helper Monkey V4 Global Guide
 * Description: Reusable cross-page self-service, language entry, traffic lights and WhatsApp Business.
 * Version: 4.0.8
 */
if (!defined('ABSPATH')) exit;

/* ODPC site-wide translation selector; separate from the interview language setting. */
add_action('wp_footer', function(){
 if(is_admin())return;
 ?>
 <style id="odpc-ai-translate-style">
 #odpc-ai-translate{position:fixed;left:12px;bottom:72px;z-index:9997;background:#123b30;color:#f4fff8;border:1px solid #4c9a77;border-radius:12px;box-shadow:0 5px 18px #09261d66;padding:6px 8px;font:600 12px/1.4 system-ui,sans-serif}
 #odpc-ai-translate label{display:inline-flex;align-items:center;gap:5px;color:inherit}
 #odpc-ai-translate select{max-width:105px;min-height:32px;background:#123b30;color:#f4fff8;border:0;border-radius:7px;padding:4px;font:inherit;cursor:pointer}
 #odpc-ai-translate select:focus-visible{outline:2px solid #a9e7c5}
 #odpc-ai-translate-element{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
 body{top:0!important} .goog-te-banner-frame,.skiptranslate iframe{display:none!important}
 @media(max-width:600px){#odpc-ai-translate{left:9px;bottom:70px;padding:4px 6px}#odpc-ai-translate select{max-width:90px}}
 </style>
 <div id="odpc-ai-translate" role="group" aria-label="Webseite übersetzen"><label for="odpc-ai-language">🌐 <select id="odpc-ai-language" aria-label="Sprache der gesamten Webseite wählen"><option value="de">🇩🇪 DE</option><option value="en">🇬🇧 EN</option><option value="nl">🇳🇱 NL</option><option value="es">🇪🇸 ES</option><option value="fr">🇫🇷 FR</option><option value="ru">🇷🇺 RU</option><option value="tr">🇹🇷 TR</option></select></label></div>
 <div id="odpc-ai-translate-element" aria-hidden="true"></div>
 <script id="odpc-ai-translate-js">
 (function(){
 'use strict';
 var key='odpc_ai_site_language',supported=['de','en','nl','es','fr','ru','tr'],sel=document.getElementById('odpc-ai-language');
 if(!sel)return;
 function read(){try{return localStorage.getItem(key)||'de'}catch(e){return 'de'}}
 function store(v){try{localStorage.setItem(key,v)}catch(e){}}
 function cookie(v){var value='/de/'+v;document.cookie='googtrans='+value+';path=/;SameSite=Lax';document.cookie='googtrans='+value+';path=/;domain=.ai.odpc.de;SameSite=Lax';if(v==='de'){document.cookie='googtrans=;Max-Age=0;path=/';document.cookie='googtrans=;Max-Age=0;path=/;domain=.ai.odpc.de'}}
 function apply(v){if(v==='de')return true;var combo=document.querySelector('.goog-te-combo');if(!combo)return false;combo.value=v;combo.dispatchEvent(new Event('change',{bubbles:true}));return true}
 var chosen=read();if(!supported.includes(chosen))chosen='de';sel.value=chosen;window.odpcAiLocale=function(){return ({de:'de-DE',en:'en-US',nl:'nl-NL',es:'es-ES',fr:'fr-FR',ru:'ru-RU',tr:'tr-TR'})[read()]||'de-DE'};
 window.odpcAiTranslateInit=function(){new google.translate.TranslateElement({pageLanguage:'de',includedLanguages:'de,en,nl,es,fr,ru,tr',autoDisplay:false},'odpc-ai-translate-element');var n=0;var timer=setInterval(function(){if(apply(chosen)||++n>35)clearInterval(timer)},200)};
 sel.addEventListener('change',function(){var v=sel.value;if(!supported.includes(v))return;store(v);cookie(v);document.documentElement.lang=v;document.dispatchEvent(new CustomEvent('odpc:languagechange',{detail:{language:v,locale:window.odpcAiLocale()}}));if(v==='de'){location.reload();return}if(!apply(v))location.reload()});
 cookie(chosen);
 var script=document.createElement('script');script.src='https://translate.google.com/translate_a/element.js?cb=odpcAiTranslateInit';script.async=true;script.onerror=function(){sel.title='Google Übersetzung derzeit nicht erreichbar'};document.head.appendChild(script);
 })();
 </script>
 <?php
}, 80);
function hm_v4_url(){return 'https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/';}
function hm_v4_wa(){return 'https://wa.me/492654882183?text='.rawurlencode('Hallo Oliver, ich brauche Hilfe bei Helper Monkey.');}
add_action('wp_head', function(){
 if(is_admin())return;
 echo '<style id="hm-v4-global-css">
 .hm-v4-ribbon{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;background:#164735;color:white;padding:9px clamp(14px,3vw,28px);font:750 14px/1.4 system-ui,sans-serif}
 .hm-v4-ribbon a{color:#fff!important;text-decoration:none!important}.hm-v4-flags{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
 .hm-v4-flags a{display:inline-flex;min-width:36px;min-height:40px;justify-content:center;align-items:center;border:1px solid #87bba2;border-radius:8px;font-size:21px}
 .hm-v4-floating{position:fixed;z-index:9996;right:13px;bottom:13px;background:#147b4c;color:#fff!important;border:2px solid #fff;border-radius:999px;padding:11px 17px;font:800 14px system-ui,sans-serif;text-decoration:none!important;box-shadow:0 5px 23px #153d2b55;min-height:46px}
 .hm-v4-helpbtn{position:fixed;z-index:9996;right:13px;bottom:72px;background:#fff;border:2px solid #b3d2c0;border-radius:999px;padding:10px 15px;color:#17503a;min-height:44px;font:750 13px system-ui,sans-serif;cursor:pointer}
 .hm-v4-help{display:none;position:fixed;z-index:9996;right:13px;bottom:122px;width:min(380px,calc(100vw - 26px));max-height:65vh;overflow:auto;background:#fff;color:#233b2d;border:1px solid #b7d6c4;box-shadow:0 6px 30px #17362650;padding:17px;border-radius:14px;font:15px/1.5 system-ui,sans-serif}
 .hm-v4-help.is-open{display:block}.hm-v4-help h2{font-size:20px;margin:0 0 8px}.hm-v4-help a{display:block;padding:9px;border-radius:8px;border:1px solid #d2e7d8;margin:6px 0;color:#165637!important;text-decoration:none!important;font-weight:720}
 .hm-v4-help button{min-height:42px;border-radius:7px;background:#187448;color:white;padding:8px 14px;border:0}
 .hm-v4-steps{display:grid;gap:8px;margin:12px auto 19px;max-width:1040px;font:15px/1.55 system-ui,sans-serif}.hm-v4-steps>section{border:1px solid #e7e7e7;border-left:7px solid #b94747;padding:12px 17px;border-radius:10px;background:#fbf8f8;color:#1b392d}
 .hm-v4-steps>section:nth-child(2){border-left-color:#b78c25;background:#fffcf4}.hm-v4-steps>section:nth-child(3){border-left-color:#22804c;background:#f4faf5}
 .hm-v4-steps strong{display:block;font-size:17px}.hm-v4-faq{max-width:1040px;margin:19px auto;padding:18px;border:1px solid #d2e4d7;border-radius:12px;background:#fff;color:#183829}.hm-v4-faq details{margin:8px 0;padding:9px 0;border-bottom:1px solid #e3eee6}.hm-v4-faq summary{cursor:pointer;font-weight:750}
 .hm-v4-helpbtn:focus-visible,.hm-v4-ribbon a:focus-visible,.hm-v4-floating:focus-visible{outline:3px solid #edc678;outline-offset:3px}
 @media(max-width:540px){.hm-v4-floating{bottom:8px;right:8px;font-size:13px}.hm-v4-helpbtn{bottom:66px;right:8px}.hm-v4-help{bottom:116px;right:8px}}
 </style>';
},99);
add_action('wp_body_open',function(){
 if(is_admin())return;
 echo '<div class="hm-v4-ribbon" role="navigation" aria-label="Helper Monkey Einstieg und Sprachen"><a href="'.esc_url(hm_v4_url()).'">🐵 helper · monkey © &nbsp; Einfach starten ↗</a><div class="hm-v4-flags"><span>Sprache:</span>';
 foreach(['de'=>['🇩🇪','Deutsch'],'en'=>['🇬🇧','English'],'fr'=>['🇫🇷','Français'],'es'=>['🇪🇸','Español'],'tr'=>['🇹🇷','Türkçe'],'ar'=>['🇸🇦','العربية']] as $code=>$data){
 echo '<a href="'.esc_url(add_query_arg('lang',$code,hm_v4_url())).'" hreflang="'.esc_attr($code).'" aria-label="Startinterview: '.esc_attr($data[1]).'" title="'.esc_attr($data[1]).'">'.esc_html($data[0]).'</a>';
 }echo '</div></div>';
},5);
add_filter('the_content',function($content){
 if(!is_page()||!is_main_query()||!in_the_loop())return $content;
 $slug=get_post_field('post_name',get_the_ID());
 if(in_array($slug,['helper-monkey-einfach-starten-sprachassistent','impressum','datenschutzerklaerung'],true))return $content;
 if(!preg_match('/ROT\s*[·–-]|🔴|hm-v4-steps|hm-stage red/iu',$content)){
 $box='<div class="hm-v4-steps" aria-label="So startest du"><section><strong>🔴 ROT – Was möchtest du erreichen?</strong>Beschreibe deine Aufgabe und prüfe, welche Geräte und Konten du bereits hast.</section><section><strong>🟡 GELB – Dein persönlicher Einrichtungsweg</strong><a href="'.esc_url(hm_v4_url()).'">Starte das kostenlose, mehrsprachige Interview</a>. Für Kontozugriffe sind separate Freigaben erforderlich.</section><section><strong>🟢 GRÜN – Passenden Agenten testen</strong>Nutze einen passenden Helper Monkey zunächst mit Beispieldaten und kontrolliere sein Ergebnis. <a href="'.esc_url(home_url('/start/#downloads')).'">Agenten entdecken</a>.</section></div>';
 $content=$box.$content;
 }
 if(!preg_match('/<details\b|FAQ|Fragen\s*(?:&amp;|&)\s*Antworten/iu',$content)){
 $content.='<section class="hm-v4-faq"><h2>Fragen &amp; Antworten</h2><details><summary>Brauche ich ein Konto?</summary><p>Für die kostenlose Browser-Werkstatt nicht. Für bestimmte KI-Funktionen gelten die jeweiligen Konto- und Zugriffsbedingungen.</p></details><details><summary>Ist Helper Monkey kostenlos?</summary><p>Vier Starter und die Browser-Werkstatt sind kostenlos. Erweiterte Agenten und Beratungsleistungen können kostenpflichtig sein.</p></details><details><summary>Wo bekomme ich Hilfe?</summary><p>Nutze den Hilfe-Button oder WhatsApp Business.</p></details></section>';
 }
 return $content;
},19);
add_action('wp_footer',function(){
 if(is_admin())return;
 $wa=hm_v4_wa();
 echo '<button class="hm-v4-helpbtn" id="hm-v4-helpbtn" aria-controls="hm-v4-help" aria-expanded="false" type="button">❔ Fragen &amp; Hilfe</button>';
 echo '<aside class="hm-v4-help" id="hm-v4-help" aria-label="Helper Monkey Selbsthilfe"><h2>Wie kann ich helfen?</h2><p>Hier findest du die passenden nächsten Schritte.</p><a href="'.esc_url(hm_v4_url()).'">🎙 Persönliches Start-Interview</a><a href="'.esc_url(home_url('/connector-einrichten/')).'">🔗 Konten und Connector</a><a href="'.esc_url(home_url('/start/#downloads')).'">🐵 Agenten und Downloads</a><a href="'.esc_url(home_url('/praxis/')).'">📖 Anleitung und FAQ</a><a href="'.esc_url('https://ai.odpc.de/helper-monkey-fuer-unternehmen-ki-kollegen/').'">🏢 Für Firmen und Teams</a><p>Du kommst nicht weiter? Über WhatsApp Business erreichst du ODPC direkt.</p><button id="hm-v4-close" type="button">Schließen</button></aside>';
 echo '<a class="hm-v4-floating" id="hm-v4-wa" href="'.esc_url($wa).'" rel="noopener noreferrer" target="_blank" aria-label="WhatsApp Business – persönliche Unterstützung">✆ WhatsApp Business</a>';
 echo '<script id="hm-v4-global-js">(function(){var b=document.getElementById("hm-v4-helpbtn"),p=document.getElementById("hm-v4-help");if(!b||!p)return;function open(v){p.classList.toggle("is-open",v);b.setAttribute("aria-expanded",String(v))}b.addEventListener("click",function(){open(!p.classList.contains("is-open"))});document.getElementById("hm-v4-close").addEventListener("click",function(){open(false);b.focus()});document.addEventListener("keydown",function(e){if(e.key==="Escape")open(false)});var extra=document.getElementById("hm-v4-wa");if(extra&&Array.from(document.querySelectorAll("a[href*=\"wa.me\"]:not(#hm-v4-wa)")).some(function(a){return getComputedStyle(a).position==="fixed"})){extra.remove()}var l=new URLSearchParams(location.search).get("lang"),s=document.getElementById("hmv4-lang");if(l&&s&&s.querySelector("option[value="+l+"]")){s.value=l;s.dispatchEvent(new Event("change",{bubbles:true}))}})();</script>';
},30);

add_action('wp_head',function(){
 if(is_admin())return;
 if(is_front_page()){
   $d='Helper Monkey: kostenlose KI-Agenten, persönliches Startinterview, Connector, Browser-Werkstatt und professionelle Fachpakete. Einfach starten auf Android, iPhone und PC.';
 }elseif(is_page('helper-monkey-einfach-starten-sprachassistent')){
   $d='Helper Monkey begleitet dich Schritt für Schritt: kostenloses Interview mit sechs Sprachen, Geräte- und Kontencheck, Vorlesen und Spracheingabe.';
 }elseif(is_page('helper-monkey-fuer-unternehmen-ki-kollegen')){
   $d='KI für Unternehmen: Helper Monkey unterstützt Mitarbeitende in Büro, Webdesign, Marketing, Personal und Buchhaltung. Kostenloses Erstinterview und ODPC-Beratung.';
 }else{
   $name=is_singular()?get_the_title():'Helper Monkey';
   $d='Helper Monkey – '.wp_strip_all_tags($name).'. Entdecke unsere verständlichen KI-Anleitungen, kostenlosen Tools und professionellen Angebote mit persönlicher ODPC-Hilfe.';
 }
 echo '<meta name="description" content="'.esc_attr($d).'">';
},8);
add_action('wp_footer',function(){
 if(is_admin())return;
 echo '<script id="hm-v4-accessibility-patch">(function(){var ts=document.querySelectorAll(".hm3-tab-buttons[role=tablist] button[aria-selected]");ts.forEach(function(t){t.setAttribute("role","tab")})})()</script>';
},97);

add_action('wp_footer',function(){ if(is_admin())return; ?>
<script id="hm-v4-chat-search">
(function(){
"use strict";
var panel=document.getElementById("hm-v4-help");if(!panel||document.getElementById("hm-v4-chat"))return;
var wrap=document.createElement("div");wrap.id="hm-v4-chat";wrap.innerHTML='<label for="hm-v4-query" style="display:block;font-weight:800;margin-top:8px">Was möchtest du wissen?</label><div style="display:flex;gap:5px;flex-wrap:wrap"><input id="hm-v4-query" type="search" maxlength="220" placeholder="z.B. Connector einrichten" style="flex:1;min-width:150px;padding:9px;border:1px solid #a9cbb8;border-radius:8px"><button type="button" id="hm-v4-ask">Suchen</button><button type="button" id="hm-v4-mic" aria-label="Frage sprechen">🎙</button><button type="button" id="hm-v4-listen" aria-label="Antwort vorlesen">🔊</button></div><p style="font-size:12px;margin:6px 0">Dieser Helfer sucht geprüfte Seiten und FAQ. Er ist kein frei sprechender KI-Chatbot. Keine Passwörter oder persönlichen Gesundheitsdaten eingeben.</p><div id="hm-v4-response" aria-live="polite" style="border-left:4px solid #268251;background:#f4faf6;padding:9px;margin:8px 0;min-height:36px"></div>';
panel.querySelector("p").insertAdjacentElement("afterend",wrap);
var input=document.getElementById("hm-v4-query"),response=document.getElementById("hm-v4-response");
function result(title,body,url){var box=document.createElement("div");box.style.margin="7px 0";var strong=document.createElement("strong");strong.textContent=title;box.appendChild(strong);if(body){var p=document.createElement("p");p.style.margin="3px 0";p.textContent=body.slice(0,340);box.appendChild(p)}if(url){var a=document.createElement("a");a.href=url;a.textContent="Dazu die Anleitung öffnen ↗";a.rel="noopener";box.appendChild(a)}response.appendChild(box)}
function score(s,words){var text=s.toLocaleLowerCase(),n=0;words.forEach(function(w){if(w.length>2&&text.indexOf(w)>=0)n++});return n}
async function run(){
var q=input.value.trim();response.replaceChildren();if(!q)return;
var words=q.toLocaleLowerCase().split(/[^\p{L}\p{N}]+/u).filter(function(x){return x.length>2}).slice(0,8);
var faq=Array.from(document.querySelectorAll("main details,article details,.hm-v4-faq details")).map(function(d){return {question:d.querySelector("summary")?.textContent||"",answer:d.querySelector("p")?.textContent||"",text:d.textContent||""}}).map(function(d){return Object.assign(d,{weight:score(d.text,words)})}).filter(function(x){return x.weight>0}).sort(function(a,b){return b.weight-a.weight}).slice(0,3);
if(faq.length){faq.forEach(function(x){result(x.question,x.answer,"")});return}
response.textContent="Ich suche passende Inhalte auf dieser Webseite …";
try{
 var u=new URL("/wp-json/wp/v2/search",location.origin);u.searchParams.set("search",q);u.searchParams.set("per_page","5");
 var res=await fetch(u.toString(),{credentials:"omit",headers:{Accept:"application/json"}});
 if(!res.ok)throw Error("Search not available");
 var entries=await res.json();response.replaceChildren();
 if(!Array.isArray(entries)||!entries.length){result("Noch keine passende Antwort","Probiere einen kürzeren Suchbegriff oder starte das persönliche Interview.","https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/");return}
 entries.forEach(function(x){var link=typeof x.url==="string"&&x.url.startsWith(location.origin)?x.url:"";result(x.title||"Passende Seite","",link)})
}catch(e){response.replaceChildren();result("Bitte nutze den geführten Einstieg","Die Seitensuche ist gerade nicht verfügbar. Persönliche Hilfe findest du jederzeit über WhatsApp.","https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/")}
}
document.getElementById("hm-v4-ask").addEventListener("click",run);
input.addEventListener("keydown",function(e){if(e.key==="Enter"){e.preventDefault();run()}});
document.getElementById("hm-v4-mic").addEventListener("click",function(){var SR=window.SpeechRecognition||window.webkitSpeechRecognition;if(!SR){response.textContent="Die Spracheingabe ist in diesem Browser nicht verfügbar. Bitte die Frage eintippen.";return}var r=new SR();r.lang=(window.odpcAiLocale?window.odpcAiLocale():"de-DE");r.onresult=function(e){input.value=e.results[0][0].transcript;run()};r.onerror=function(){response.textContent="Die Sprache wurde nicht erkannt. Bitte erneut versuchen oder schreiben."};try{r.start()}catch(e){response.textContent="Mikrofon kann nicht gestartet werden."}});
document.getElementById("hm-v4-listen").addEventListener("click",function(){if(!("speechSynthesis" in window))return;window.speechSynthesis.cancel();var v=new SpeechSynthesisUtterance(response.textContent.slice(0,900));v.lang=(window.odpcAiLocale?window.odpcAiLocale():"de-DE");v.rate=1.05;var language=v.lang.split("-")[0];var voices=window.speechSynthesis.getVoices().filter(function(x){return x.lang.toLowerCase().split(/[-_]/)[0]===language});voices.sort(function(a,b){function score(x){return (/Google Deutsch|Google German|Microsoft Katja|Microsoft Hedda|Anna|Petra/i.test(x.name)?100:0)+(/natural|neural|premium/i.test(x.name)?30:0)+(/female|weiblich/i.test(x.name)?10:0)}return score(b)-score(a)});if(voices.length)v.voice=voices[0];window.speechSynthesis.speak(v)});
})();
</script>
<?php },98);

add_action('wp_footer', function(){
 if (is_admin()) return;
 echo '<div id="odpc-universal-credit" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin:28px auto 0;padding:18px max(20px,4vw);border-top:1px solid #c5d9ca;font:inherit"><a href="https://odpc.de/" target="_blank" rel="noopener noreferrer" aria-label="odpc.de – Oliver Dering"><img src="https://odpc.de/wp-content/uploads/2026/08/1786371119667.png.webp" alt="Oliver Dering" width="64" height="64" loading="lazy" style="width:64px;height:64px;object-fit:cover;border-radius:50%;border:2px solid #fff"></a><div><span style="display:block;font-size:.8rem">Webdesign, Technik &amp; KI-Integration</span><a href="https://odpc.de/" target="_blank" rel="noopener noreferrer" style="font-weight:700">Powered by odpc.de · Oliver Dering</a></div></div>';
}, 99);

/**
 * AdminMonkey: front-page premium listing, with a non-destructive /start/ fallback.
 * Client-side fallback handles page builders which bypass the_content.
 */
add_action('wp_footer', function () {
 if (is_admin() || !(is_front_page() || is_home() || is_page('start'))) return;
 ?>
 <script id="odpc-adminmonkey-premium">
 (function(){
  function insert(){
   if(document.querySelector('[data-product="adminmonkey"]'))return;
   var headings=[...document.querySelectorAll('h1,h2,h3,h4')];
   var heading=headings.find(function(el){return /Kostenpflichtige Agenten und Fachlösungen|Premium.Monkeys|Premium.Agent|Dein Business/i.test(el.textContent||'')});
   var candidates=[...document.querySelectorAll('a')].filter(function(a){return /PraeLex|Anwalt/i.test(a.textContent||'')});
   var first=null;
   if(heading){
    var scope=heading.closest('section')||heading.parentElement?.parentElement;
    first=scope?.querySelector('article')||scope?.querySelector('a[href*="praelex"]')?.closest('article,div');
   }
   if(!first&&candidates.length){first=candidates[0].closest('article')||candidates[0].closest('.wp-block-column')||candidates[0].parentElement}
   if(!first||!first.parentElement)return;
   var card=first.cloneNode(true);
   card.setAttribute('data-product','adminmonkey');
   card.setAttribute('aria-label','AdminMonkey – Microsoft 365 und Copilot Administration');
   // Keep original premium monkey artwork and layout; replace every cloned PraeLex text.
   var title=card.querySelector('h2,h3,h4')||card.querySelector('strong');
   if(title)title.textContent='AdminMonkey';
   var replacements=[
    ['PraeLex.ai','Microsoft 365 & Copilot'],
    ['PraeLex','AdminMonkey'],
    ['Anwalt','Microsoft-Administration'],
    ['Rechtliche Sachverhalte und Entwürfe','Microsoft 365, Copilot und Cloud-Administration']
   ];
   var walker=document.createTreeWalker(card,NodeFilter.SHOW_TEXT);
   var node;
   while((node=walker.nextNode())){
    if(!node.parentElement||node.parentElement.closest('script,style'))continue;
    replacements.forEach(function(pair){node.nodeValue=node.nodeValue.replace(new RegExp(pair[0].replace(/[.*+?^$\x7b\x7d()|[\]\\]/g,'\\$&'),'gi'),pair[1])});
   }
   var links=[...card.querySelectorAll('a')];
   links.forEach(function(a){a.href='https://ai.odpc.de/adminmonkey/';if(/PraeLex|Anwalt/i.test(a.textContent||''))a.textContent='AdminMonkey ansehen'});
   var paragraphs=[...card.querySelectorAll('p')];
   if(paragraphs.length)paragraphs[paragraphs.length-1].textContent='Microsoft 365 · Microsoft 365 Copilot · Copilot Studio · Exchange Online · Teams · SharePoint · Entra ID · Intune · Azure · Windows Server. Administration, Automatisierung und kontinuierlicher Kompetenzaufbau.';
   var description=document.createElement('p');
   description.textContent='Microsoft-Lernpfade: MS-900 (Microsoft 365 Fundamentals), MS-102 (Microsoft 365 Administrator), MD-102 (Endpoint Administrator), SC-300 (Identity and Access Administrator), AZ-104 (Azure Administrator) und AB-900 (Copilot & Agent Administration Fundamentals).';
   description.style.cssText='font-size:.92em;line-height:1.55;margin:12px 0';
   var badge=document.createElement('p');
   badge.setAttribute('data-adminmonkey-certification-status','');
   badge.textContent='🍌 Zertifizierungsstatus: Lernpfade in Bearbeitung · bestandene Prüfungen werden erst nach verifiziertem Nachweis ausgezeichnet.';
   badge.style.cssText='font-size:.88em;line-height:1.5;margin:12px 0;font-weight:650';
   var anchor=links.length?links[links.length-1]:null;
   if(anchor){anchor.parentElement.insertBefore(description,anchor);anchor.parentElement.insertBefore(badge,anchor)}
   else{card.appendChild(description);card.appendChild(badge)}
   card.querySelectorAll('img').forEach(function(img){img.alt='AdminMonkey – Microsoft 365 und Copilot Administration'});
   first.parentElement.insertBefore(card,first);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',insert);else insert();
 })();
 </script>
 <?php
}, 101);

/** AdminMonkey Academy: public, evidence-based progress dashboard. */
add_action('wp_footer', function () {
 if (is_admin() || !(is_front_page() || is_home() || is_page('start') || is_page('adminmonkey'))) return;
 ?>
 <style id="amk-academy-css">
 .amk-academy{margin:18px auto 28px;padding:20px;max-width:1050px;border:1px solid #c5d9ca;border-radius:18px;background:#fff;color:#203a50;font:inherit;box-shadow:0 6px 18px #183a4b12}
 .amk-academy h2{margin:0 0 8px;font-size:clamp(22px,4vw,30px)}.amk-academy p{line-height:1.5}
 .amk-academy-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin:16px 0}
 .amk-academy-stats>div{padding:14px;background:#edf5f1;border-radius:12px}.amk-academy-stats strong{display:block;font-size:24px}
 .amk-track{margin:13px 0}.amk-track-head{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;font-weight:650}
 .amk-meter{height:10px;background:#e4eaf0;border-radius:99px;overflow:hidden;margin-top:6px}
 .amk-meter>span{display:block;height:100%;width:0;background:#258356;transition:width .4s}
 .amk-note{font-size:13px;color:#526779}.amk-banana{padding:10px 13px;border-radius:10px;background:#fff5d8;font-weight:650}
 </style>
 <script id="amk-academy-dashboard">
 (function(){
  'use strict';
  var card=document.querySelector('[data-product="adminmonkey"]');
  if(!card||document.getElementById('amk-academy'))return;
  var section=document.createElement('section');
  section.id='amk-academy';section.className='amk-academy';
  section.setAttribute('aria-label','AdminMonkey Academy Lernfortschritt');
  section.innerHTML='<h2>🍌 AdminMonkey Academy – Lernfortschritt</h2><p>Microsoft 365, Azure, Identität, Geräteverwaltung und Copilot. Nachweise statt geschätzter Prozentwerte.</p><div class="amk-academy-stats"><div><strong id="amk-sources">–</strong>Microsoft-Learn-Quellen erfasst</div><div><strong id="amk-reviewed">–</strong>Quellen freigegeben</div><div><strong id="amk-passes">0</strong>Bestandene Kompetenztests</div></div><div id="amk-tracks"></div><p class="amk-banana">🍌 Bananen-Badges: Noch keine nachgewiesene Kompetenzstufe</p><p class="amk-note" id="amk-updated">Aktuelle Daten werden geladen …</p><p class="amk-note">Interne ODPC-Lernstandsanzeige, keine offizielle Microsoft-Zertifizierung. Ein Balken ohne geprüfte Antworten bleibt unbewertet.</p>';
  card.insertAdjacentElement('afterend',section);
  var tracks=[['MS-102','Microsoft 365 Administrator'],['AZ-104','Azure Administrator'],['SC-300','Entra ID / Identity'],['MD-102','Intune / Endpoint'],['Copilot','Microsoft Copilot & Copilot Studio']];
  var host=document.getElementById('amk-tracks');
  tracks.forEach(function(t){
   var row=document.createElement('div');row.className='amk-track';
   var head=document.createElement('div');head.className='amk-track-head';
   var name=document.createElement('span');name.textContent=t[1]+' · '+t[0];
   var value=document.createElement('span');value.textContent='Nicht bewertet';
   head.append(name,value);
   var meter=document.createElement('div');meter.className='amk-meter';meter.setAttribute('role','progressbar');
   meter.setAttribute('aria-label',t[1]+' Kompetenzfortschritt');
   meter.setAttribute('aria-valuetext','Noch keine verifizierte Bewertung');
   var fill=document.createElement('span');meter.appendChild(fill);row.append(head,meter);host.appendChild(row);
  });
  var base='https://raw.githubusercontent.com/oliverdering/odpc-sitebot/main/adminmonkey/knowledge/';
  fetch(base+'knowledge_index.json',{cache:'no-store'}).then(function(r){if(!r.ok)throw Error('Unavailable');return r.json()}).then(function(data){
   var sources=Object.values(data.sources||{});
   document.getElementById('amk-sources').textContent=String(sources.filter(function(s){return s.status==='ok'}).length);
   document.getElementById('amk-reviewed').textContent=String(sources.filter(function(s){return s.review_status==='approved'}).length);
   document.getElementById('amk-updated').textContent='Wissensindex aus GitHub geladen · Prüfungsfortschritt derzeit nicht bewertet.';
  }).catch(function(){document.getElementById('amk-updated').textContent='Wissensindex momentan nicht erreichbar; keine Fortschrittswerte erfunden.'});
 })();
 </script>
 <?php
}, 105);


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
 function lang(){var site=document.getElementById('odpc-ai-language');if(site)return site.value;try{var c=decodeURIComponent(document.cookie.match(/(?:^|; )googtrans=([^;]+)/)?.[1]||'');return c.split('/').pop()||'de'}catch(e){return 'de'}}
 function locale(){return ({de:'de-DE',en:'en-US',nl:'nl-NL',es:'es-ES',fr:'fr-FR',ru:'ru-RU',tr:'tr-TR'})[lang()]||'de-DE'}
 function text(){var root=document.querySelector('article .entry-content,main .entry-content,main article,article,main,#content');if(!root)return '';var clone=root.cloneNode(true);clone.querySelectorAll('script,style,nav,form,button,footer,aside,.screen-reader-text,#odpc-reader,.gtranslate_wrapper').forEach(function(e){e.remove()});return (clone.innerText||clone.textContent||'').replace(/\\s+/g,' ').trim().slice(0,85000)}
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
