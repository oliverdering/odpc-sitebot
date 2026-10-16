<?php
/**
 * Plugin Name: ODPC Helper Monkey V4 Global Guide
 * Description: Reusable cross-page self-service, language entry, traffic lights and WhatsApp Business.
 * Version: 4.0.4
 */
if (!defined('ABSPATH')) exit;
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
document.getElementById("hm-v4-mic").addEventListener("click",function(){var SR=window.SpeechRecognition||window.webkitSpeechRecognition;if(!SR){response.textContent="Die Spracheingabe ist in diesem Browser nicht verfügbar. Bitte die Frage eintippen.";return}var r=new SR();r.lang="de-DE";r.onresult=function(e){input.value=e.results[0][0].transcript;run()};r.onerror=function(){response.textContent="Die Sprache wurde nicht erkannt. Bitte erneut versuchen oder schreiben."};try{r.start()}catch(e){response.textContent="Mikrofon kann nicht gestartet werden."}});
document.getElementById("hm-v4-listen").addEventListener("click",function(){if(!("speechSynthesis" in window))return;window.speechSynthesis.cancel();var v=new SpeechSynthesisUtterance(response.textContent.slice(0,900));v.lang="de-DE";v.rate=.9;window.speechSynthesis.speak(v)});
})();
</script>
<?php },98);
