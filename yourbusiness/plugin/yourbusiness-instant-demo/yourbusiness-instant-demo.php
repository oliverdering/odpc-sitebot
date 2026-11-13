<?php
/**
 * Plugin Name: YourBusiness Instant Demo
 * Description: Mobile-first local customer website demo, without publication or server-side personal data storage.
 * Version: 0.2.0
 * Author: ODPC
 */
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . 'discover.php';

// Dedicated customer-facing preview; the generic builder remains available separately.
add_action('template_redirect', function () {
  if (is_admin() || !isset($_GET['yb_kleine_freiheit']) || $_GET['yb_kleine_freiheit'] !== '1') return;
  status_header(200); nocache_headers();
  get_header();
  ?>
  <main id="kf-demo" class="kf-modern">
    <nav class="kf-nav"><a href="#kf-demo" class="kf-brand">KLEINE FREIHEIT <small>KOBLENZ · ALTSTADT</small></a><div class="kf-controls"><a href="#kf-story" class="kf-nav-link">Geschichte</a><a href="#kf-drinks" class="kf-nav-link">Getränke</a><button type="button" id="kf-switch" aria-pressed="false">Klassisch ansehen</button><a href="https://www.google.com/maps/search/?api=1&amp;query=Kleine%20Freiheit%20Altenhof%206%20Koblenz" target="_blank" rel="noopener">Anfahrt</a></div></nav>
    <section class="kf-hero"><div class="kf-kicker">ALTENHOF 6 · 56068 KOBLENZ</div><h1>Deine kleine<br><em>Freiheit.</em></h1><p>Willkommen bei Kalle. Ein Stück Koblenzer Altstadt – gesellige Abende, Musik und Zeit mit Freunden.</p><a class="kf-cta" href="#kf-about">Die Kneipe entdecken</a></section>
    <section id="kf-about" class="kf-section"><div class="kf-eyebrow">WILLKOMMEN</div><h2>Hier trifft man sich.</h2><p>Mitten in der Koblenzer Altstadt lädt die Kleine Freiheit zum Zusammensitzen, Erzählen und Feiern ein. Ob Feierabend oder ein Abend mit Freunden – hier steht das Miteinander im Mittelpunkt.</p><div class="kf-grid"><article><span>01</span><h3>Altstadt & Atmosphäre</h3><p>Ein Treffpunkt am Altenhof, mitten in Koblenz.</p></article><article><span>02</span><h3>Musik & Geselligkeit</h3><p>Schlager- und Kneipenstimmung für gemeinsame Abende.</p></article><article><span>03</span><h3>Getränke & Begegnungen</h3><p>Ein gemütlicher Platz für Gespräche und eine gute Zeit.</p></article></div></section>
    <section id="kf-drinks" class="kf-section kf-drinks"><div class="kf-eyebrow">AN DER THEKE</div><h2>Auf gute Zeiten.</h2><p>Ob Bierklassiker, ein Longdrink mit Freunden oder etwas Alkoholfreies: In der Kleinen Freiheit zählt vor allem die gemeinsame Zeit.</p><div class="kf-grid"><article><span>01</span><h3>Bier &amp; Klassiker</h3><p>Frisch gezapft, aus der Flasche oder als Radler.</p></article><article><span>02</span><h3>Longdrinks &amp; Kurze</h3><p>Für lange Abende und einen guten Anlass zum Anstoßen.</p></article><article><span>03</span><h3>Alkoholfreies</h3><p>Erfrischungen für alle, die ohne Alkohol genießen.</p></article></div><p class="kf-demo-note">Getränkesortiment, Marken und Preise werden vor Veröffentlichung mit dem Betreiber abgestimmt.</p></section>
    <section id="kf-story" class="kf-section kf-story"><div class="kf-eyebrow">UNSER ALTENHOF</div><h2>Ein Ort mit Geschichte.</h2><p>Vom historischen Altstadtgebäude über die Rock- und Kneipenkultur bis zur heutigen geselligen Schlagerbar – hier treffen sich Menschen und Geschichten. Die Kleine Freiheit ist seit vielen Jahren Teil des Lebens am Altenhof.</p></section>
    <section class="kf-band"><div class="kf-eyebrow">DEIN ABEND</div><h2>Reinkommen.<br>Wohlfühlen.<br>Bleiben.</h2><p>Die Kleine Freiheit – mitten in Koblenz.</p></section>
    <section id="kf-visit" class="kf-section kf-visit"><div><div class="kf-eyebrow">BESUCH UNS</div><h2>Wir sehen uns<br>am Altenhof.</h2><p><strong>Kleine Freiheit</strong><br>Altenhof 6<br>56068 Koblenz</p><p>Öffnungszeiten und Kontaktdaten werden mit dem Betreiber final abgestimmt.</p><a class="kf-cta" href="https://www.google.com/maps/search/?api=1&amp;query=Kleine%20Freiheit%20Altenhof%206%20Koblenz" target="_blank" rel="noopener">Route in Google Maps</a></div><div class="kf-address">ALTENHOF<br><strong>6</strong><small>KOBLENZ</small></div></section>
    <footer class="kf-footer">Kleine Freiheit · Koblenz <span>Designvorschau von ODPC · Rechtliche Angaben und Bildmaterial vor Veröffentlichung finalisieren</span></footer>
  </main>
  <style>
  #kf-demo{--kf-bg:#101c27;--kf-fg:#f8f1e7;--kf-accent:#e6b87b;--kf-panel:#1b2a35;background:var(--kf-bg);color:var(--kf-fg);font-family:system-ui,-apple-system,sans-serif;line-height:1.55}#kf-demo *{box-sizing:border-box}#kf-demo.kf-classic{--kf-bg:#f3e9d8;--kf-fg:#352819;--kf-accent:#85512e;--kf-panel:#e5d4bb;font-family:Georgia,serif}#kf-demo a{color:inherit}#kf-demo .kf-nav{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:18px clamp(18px,5vw,80px);border-bottom:1px solid currentColor}#kf-demo .kf-brand{font-size:17px;letter-spacing:.11em;font-weight:850;text-decoration:none}#kf-demo .kf-brand small{display:block;font-size:9px;letter-spacing:.25em;font-weight:500}#kf-demo .kf-controls{display:flex;align-items:center;gap:12px;flex-wrap:wrap}#kf-demo button,#kf-demo .kf-controls a{border:1px solid var(--kf-accent);border-radius:99px;padding:9px 13px;color:var(--kf-fg);background:transparent;font:600 12px system-ui;text-decoration:none;cursor:pointer}#kf-demo .kf-hero{padding:clamp(85px,13vw,175px) clamp(20px,8vw,130px);background:radial-gradient(circle at 82% 40%,var(--kf-panel),transparent 55%);min-height:560px}#kf-demo .kf-kicker,#kf-demo .kf-eyebrow{color:var(--kf-accent);font:700 12px system-ui;letter-spacing:.22em}#kf-demo h1{font-size:clamp(64px,11vw,145px);line-height:.94;letter-spacing:-.055em;margin:30px 0}#kf-demo h1 em{color:var(--kf-accent);font-weight:450}#kf-demo .kf-hero p{max-width:540px;font-size:clamp(17px,2vw,22px)}#kf-demo .kf-cta{display:inline-block;background:var(--kf-accent);color:var(--kf-bg);padding:15px 22px;border-radius:4px;text-decoration:none;font:750 14px system-ui;margin-top:25px}#kf-demo .kf-section{padding:90px clamp(20px,8vw,130px)}#kf-demo h2{font-size:clamp(38px,6vw,75px);line-height:1.08;letter-spacing:-.035em;margin:16px 0 28px}#kf-demo .kf-section>p{max-width:720px;font-size:19px}#kf-demo .kf-nav-link{border:0!important;padding:6px 2px!important;background:none!important;text-decoration:none;font-weight:650}#kf-demo .kf-nav-link:hover,#kf-demo .kf-nav-link:focus-visible{text-decoration:underline}#kf-demo [id^="kf-"]{scroll-margin-top:28px}#kf-demo .kf-drinks>p,#kf-demo .kf-story>p{max-width:780px;font-size:19px}#kf-demo .kf-demo-note{font-size:13px!important;opacity:.8;margin-top:22px}#kf-demo .kf-story{background:var(--kf-panel)}#kf-demo .kf-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:50px}#kf-demo article{background:var(--kf-panel);padding:28px;min-height:205px}#kf-demo article span{color:var(--kf-accent);font-size:13px}#kf-demo article h3{font-size:22px}#kf-demo .kf-band{padding:95px clamp(20px,8vw,130px);background:var(--kf-panel)}#kf-demo .kf-band h2{font-size:clamp(50px,8vw,110px)}#kf-demo .kf-visit{display:grid;grid-template-columns:1fr 1fr;gap:30px;align-items:center}#kf-demo .kf-address{background:var(--kf-panel);padding:50px;text-align:center;letter-spacing:.3em;font-size:20px}#kf-demo .kf-address strong{display:block;font-size:clamp(110px,18vw,220px);line-height:1.1;letter-spacing:-.07em;color:var(--kf-accent)}#kf-demo .kf-address small{display:block}#kf-demo .kf-footer{display:flex;justify-content:space-between;gap:18px;padding:25px clamp(20px,8vw,130px);font-size:12px;border-top:1px solid var(--kf-panel)}@media(max-width:700px){#kf-demo .kf-nav{align-items:flex-start;flex-direction:column}#kf-demo .kf-hero{min-height:480px}#kf-demo .kf-grid,#kf-demo .kf-visit{grid-template-columns:1fr}#kf-demo .kf-section{padding-top:65px;padding-bottom:65px}#kf-demo .kf-footer{flex-direction:column}}
  </style>
  <script>(function(){var root=document.getElementById('kf-demo'),btn=document.getElementById('kf-switch');if(!root||!btn)return;btn.addEventListener('click',function(){var classic=root.classList.toggle('kf-classic');root.classList.toggle('kf-modern',!classic);btn.textContent=classic?'Modern ansehen':'Klassisch ansehen';btn.setAttribute('aria-pressed',classic?'true':'false');});})();</script>
  <?php
  get_footer(); exit;
}, 0);

// Direct mobile builder route; independent of pretty-permalink configuration.
add_action('template_redirect', function () {
  if (is_admin() || !isset($_GET['yb_start']) || $_GET['yb_start'] !== '1') return;
  status_header(200);
  nocache_headers();
  get_header();
  echo '<main id="yb-mobile-builder" style="max-width:1440px;margin:30px auto;padding:0 16px">';
  echo '<h1>YourBusiness – Webseite direkt vor Ort erstellen</h1>';
  echo do_shortcode('[yourbusiness_demo]');
  echo '</main>';
  get_footer();
  exit;
}, 1);

// Keep the presentation homepage intact while offering an obvious mobile entry point.
add_action('wp_footer', function () {
  if (is_admin() || !is_front_page() || isset($_GET['yb_start']) || isset($_GET['yb_kleine_freiheit'])) return;
  $link = add_query_arg('yb_start', '1', home_url('/'));
  echo '<div class="yb-launch-cta" style="position:fixed;z-index:9999;bottom:max(18px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);width:min(420px,calc(100% - 32px));text-align:center">';
  echo '<a href="' . esc_url($link) . '" style="display:block;background:#155eef;color:#fff;border-radius:14px;padding:15px 20px;box-shadow:0 6px 24px #162a4b55;font:700 16px system-ui,sans-serif;text-decoration:none">Jetzt Kundenwebseite live erstellen</a>';
  echo '</div>';
});
add_shortcode('yourbusiness_demo', function () {
  wp_enqueue_script(
    'yourbusiness-research',
    plugins_url('research.js', __FILE__),
    [],
    '0.2.0',
    true
  );
  wp_add_inline_script('yourbusiness-research', 'window.YourBusinessResearch = ' . wp_json_encode([
    'url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('yb_discover')
  ]) . ';', 'before');
  ob_start(); ?>
<div id="yb-demo" class="yb-app">
  <div class="yb-editor">
    <h2>Website in Minuten</h2><p><strong>Nur Firmenname und Ort eingeben.</strong> Öffentliche Angaben automatisch recherchieren und danach eine private Webseiten-Vorschau erstellen.</p>
    <label>Firma <input id="yb-name" maxlength="100" placeholder="z. B. Müller Gebäudetechnik" required></label>
    <label>Branche <select id="yb-sector"><option>Handwerk</option><option>Gastronomie</option><option>Immobilien</option><option>Beratung</option><option>Einzelhandel</option><option>Dienstleistung</option><option>Gesundheit</option><option>Sonstiges</option></select></label>
    <label>Ort <input id="yb-city" maxlength="100" placeholder="z. B. Koblenz"></label>
    <button type="button" id="yb-location">Standort übernehmen (optional)</button>
    <label>Bestehende Website <input id="yb-site" type="url" placeholder="https://..."></label>
    <label>Google-Unternehmensprofil / Maps-Link <input id="yb-google" type="url" placeholder="https://maps.google.com/..."></label>
    <button type="button" id="yb-google-search">Firma in Google Maps suchen</button>
    <p class="yb-note">Der Suchbutton öffnet Google Maps. Firmendaten werden nicht automatisch übernommen; den geprüften Profil-Link bitte selbst eintragen.</p>
    <label>Leistungen (eine pro Zeile) <textarea id="yb-services" rows="3" placeholder="Beratung&#10;Montage&#10;Wartung"></textarea></label>
    <label>Telefon <input id="yb-phone" type="tel" placeholder="+49 ..."></label>
    <label>WhatsApp-Nummer <input id="yb-whatsapp" type="tel" placeholder="+49 ..."></label>
    <label>E-Mail <input id="yb-email" type="email" placeholder="kontakt@firma.de"></label>
    <label>Visitenkarte / Referenzfoto <input id="yb-card" type="file" accept="image/*" capture="environment"></label>
    <p class="yb-note">Visitenkarten werden hier nur als Referenz angezeigt; automatische Texterkennung folgt in einem weiteren Ausbauschritt.</p>
    <label>Logo <input id="yb-logo" type="file" accept="image/*"></label>
    <label>Fotos <input id="yb-photos" type="file" accept="image/*" multiple></label>
    <label>Hauptfarbe <input id="yb-color" type="color" value="#155eef"></label>
    <label><input id="yb-consent" type="checkbox"> Ich darf diese Unternehmensdaten und Bilder für die private Demo verwenden.</label>
    <button type="button" id="yb-build">Vorschau erstellen</button>
    <p id="yb-status" role="status" aria-live="polite"></p>
  </div>
  <div class="yb-preview" id="yb-preview" aria-label="Website Vorschau"><div class="yb-empty">Hier erscheint die Kundenvorschau.</div></div>
</div>
<style>
.yb-app{display:grid;grid-template-columns:minmax(280px,370px) minmax(0,1fr);gap:24px;align-items:start;font-family:system-ui,sans-serif}.yb-editor{background:#f5f7fb;padding:22px;border-radius:20px}.yb-editor h2{margin:0 0 8px}.yb-editor label{display:block;font-weight:650;margin:12px 0}.yb-editor input:not([type=checkbox]),.yb-editor textarea,.yb-editor select{display:block;width:100%;box-sizing:border-box;padding:11px;border:1px solid #cbd5e1;border-radius:9px;margin-top:5px;font:inherit}.yb-editor input[type=color]{height:45px;padding:3px}.yb-editor button{background:#155eef;color:white;border:0;border-radius:10px;padding:12px 15px;cursor:pointer;margin:6px 6px 6px 0;font-weight:700}.yb-note{font-size:12px;color:#526179}.yb-preview{min-height:500px;border:1px solid #d7deea;border-radius:18px;overflow:hidden;background:white;box-shadow:0 15px 50px #152c5218}.yb-empty{padding:80px 24px;text-align:center;color:#64748b}.yb-hero{padding:60px 30px;color:white}.yb-hero h1{font-size:clamp(28px,4vw,54px);line-height:1.08;margin:10px 0}.yb-hero a{display:inline-block;background:white;color:#17243b;padding:12px 18px;border-radius:12px;text-decoration:none;font-weight:750}.yb-content{padding:30px}.yb-services{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px}.yb-service{padding:18px;background:#f3f6fa;border-radius:12px}.yb-gallery{display:flex;gap:8px;overflow:auto}.yb-gallery img{width:140px;height:110px;object-fit:cover;border-radius:10px}.yb-logo{max-height:70px;max-width:160px;object-fit:contain}.yb-contact{padding:25px;background:#f4f6f9}.yb-contact a{margin-right:14px;word-break:break-all}.yb-card-preview{max-width:100%;max-height:140px;object-fit:contain}@media(max-width:850px){.yb-app{grid-template-columns:1fr}.yb-hero{padding:36px 22px}}
</style>
<script>
(function(){
const root=document.getElementById('yb-demo');if(!root)return;
const el=id=>root.querySelector('#yb-'+id), val=id=>el(id).value.trim();
const status=s=>el('status').textContent=s;
const esc=s=>String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fileUrl=f=>f?URL.createObjectURL(f):'';
const safeUrl=s=>{try{const u=new URL(s);return ['https:','http:'].includes(u.protocol)?u.href:''}catch{return ''}};
el('google-search').addEventListener('click',()=>{
 const name=val('name'),city=val('city');
 if(!name||!city){status('Bitte zuerst Firmenname und Ort eingeben.');return}
 window.open('https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(name+' '+city),'_blank','noopener,noreferrer');
});
el('location').addEventListener('click',()=>{
 if(!navigator.geolocation){status('Standortfunktion nicht verfügbar. Bitte Ort manuell eingeben.');return}
 status('Standortfreigabe wird angefragt …');
 navigator.geolocation.getCurrentPosition(p=>{status('Standortkoordinaten erkannt ('+p.coords.latitude.toFixed(4)+', '+p.coords.longitude.toFixed(4)+'). Bitte Ort zusätzlich manuell eintragen; automatische Ortsauflösung folgt.')},()=>status('Keine Standortfreigabe. Ort bitte manuell eingeben.'),{timeout:10000,maximumAge:60000});
});
el('build').addEventListener('click',()=>{
 const name=val('name'),city=val('city'),sector=val('sector');
 if(!name||!city){status('Bitte Firma und Ort eingeben.');return}
 if(!el('consent').checked){status('Bitte die Berechtigung zur Nutzung der Daten für die Demo bestätigen.');return}
 const phone=val('phone'),wa=val('whatsapp').replace(/[^0-9]/g,''),email=val('email'),site=safeUrl(val('site')),google=safeUrl(val('google'));
 const services=val('services').split('\n').map(x=>x.trim()).filter(Boolean).slice(0,12);
 const items=services.length?services:['Persönliche Beratung','Zuverlässiger Service','Individuelle Lösungen'];
 const color=val('color'),logo=fileUrl(el('logo').files[0]),card=fileUrl(el('card').files[0]);
 const photos=Array.from(el('photos').files).slice(0,8).map(fileUrl);
 const heading=sector==='Gastronomie'?'Genuss und Gastfreundschaft':sector==='Handwerk'?'Qualität, auf die Sie bauen können':sector==='Immobilien'?'Immobilien mit Perspektive':'Persönlich. Professionell. Vor Ort.';
 const waLink=wa.length>=8&&wa.length<=15?'https://wa.me/'+wa+'?text='+encodeURIComponent('Guten Tag, ich habe Ihre Website gesehen und möchte eine Anfrage stellen.'):'';
 const contact=waLink?'<a href="'+esc(waLink)+'" target="_blank" rel="noopener">WhatsApp-Anfrage</a>':'';
 const preview='<div class="yb-hero" style="background:'+esc(color)+'">'+(logo?'<img class="yb-logo" src="'+logo+'" alt="Firmenlogo">':'')+'<p>'+esc(sector)+' · '+esc(city)+'</p><h1>'+esc(name)+'</h1><h2>'+esc(heading)+'</h2><p>Wir freuen uns auf Ihre Anfrage.</p><a href="#yb-contact">Kontakt aufnehmen</a></div><div class="yb-content"><h2>Unsere Leistungen</h2><div class="yb-services">'+items.map(s=>'<div class="yb-service">'+esc(s)+'</div>').join('')+'</div>'+(photos.length?'<h2>Einblicke</h2><div class="yb-gallery">'+photos.map(u=>'<img src="'+u+'" alt="Kundenfoto">').join('')+'</div>':'')+(card?'<h2>Visitenkarte (Referenz)</h2><img class="yb-card-preview" src="'+card+'" alt="Visitenkarte">':'')+'</div><div class="yb-contact" id="yb-contact"><h2>Kontakt</h2><p>'+esc(name)+' · '+esc(city)+'</p>'+contact+(phone?'<a href="tel:'+esc(phone.replace(/[^+0-9]/g,''))+'">Anrufen</a>':'')+(email?'<a href="mailto:'+encodeURIComponent(email)+'">E-Mail</a>':'')+(site?'<a href="'+esc(site)+'" target="_blank" rel="noopener">Website</a>':'')+(google?'<a href="'+esc(google)+'" target="_blank" rel="noopener">Google Maps</a>':'')+'</div>';
 el('preview').innerHTML=preview;
 status('Private Vorschau erstellt. Keine Veröffentlichung, keine Datenübertragung an den Server.');
 el('preview').scrollIntoView({behavior:'smooth',block:'start'});
});
})();
</script>
<?php return ob_get_clean();
});
