<?php
/**
 * Plugin Name: YourBusiness Instant Demo
 * Description: Mobile-first local customer website demo, without publication or server-side personal data storage.
 * Version: 0.1.0
 * Author: ODPC
 */
if (!defined('ABSPATH')) exit;
add_shortcode('yourbusiness_demo', function () {
  ob_start(); ?>
<div id="yb-demo" class="yb-app">
  <div class="yb-editor">
    <h2>Website in Minuten</h2><p>Unternehmensdaten eingeben oder übernehmen. Die Vorschau bleibt zunächst privat in diesem Browser.</p>
    <label>Firma <input id="yb-name" maxlength="100" placeholder="z. B. Müller Gebäudetechnik" required></label>
    <label>Branche <select id="yb-sector"><option>Handwerk</option><option>Gastronomie</option><option>Immobilien</option><option>Beratung</option><option>Einzelhandel</option><option>Dienstleistung</option><option>Gesundheit</option><option>Sonstiges</option></select></label>
    <label>Ort <input id="yb-city" maxlength="100" placeholder="z. B. Koblenz"></label>
    <button type="button" id="yb-location">Standort übernehmen (optional)</button>
    <label>Bestehende Website <input id="yb-site" type="url" placeholder="https://..."></label>
    <label>Google-Unternehmensprofil / Maps-Link <input id="yb-google" type="url" placeholder="https://maps.google.com/..."></label>
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
