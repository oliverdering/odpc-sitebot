<?php
/**
 * Plugin Name: ODPC About Me 2026
 * Description: Chronological profile with live HTML CV and original certificate links; voice dock untouched.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) { exit; }
function odpc_about_2026_target() {
    return !is_admin() && is_page('about-me');
}
add_action('wp_head', function () {
    if (!odpc_about_2026_target()) return;
    echo '<style id="odpc-about-2026-style">
    .odpc-about26{font:16px/1.65 system-ui,-apple-system,Segoe UI,sans-serif;max-width:1100px;margin:36px auto 70px;color:#242b35}
    .odpc-about26 *{box-sizing:border-box}.odpc-about26 h2{font-size:clamp(26px,3vw,36px);line-height:1.2;color:#202936;margin:0 0 16px}
    .odpc-about26 .oa-intro{padding:clamp(22px,4vw,40px);background:linear-gradient(120deg,#19212b,#303a45);color:#f5f3ef;border-radius:18px;border:1px solid #52606d;box-shadow:0 16px 38px #14223522}
    .odpc-about26 .oa-intro h2{color:#fff}.odpc-about26 .oa-kicker{color:#dfb998;font-weight:750;letter-spacing:.11em;font-size:12px;text-transform:uppercase}
    .odpc-about26 .oa-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,285px),1fr));gap:15px;margin:22px 0 30px}
    .odpc-about26 details{border:1px solid #d4d0ca;border-top:3px solid #b99b80;border-radius:13px;background:#fff;box-shadow:0 7px 22px #19212b13;overflow:hidden}
    .odpc-about26 summary{cursor:pointer;padding:19px 20px;font-weight:750;color:#19212b;list-style:none;display:flex;justify-content:space-between;gap:10px;align-items:center}
    .odpc-about26 summary::-webkit-details-marker{display:none}.odpc-about26 summary:after{content:"+";font-size:24px;color:#95745c}.odpc-about26 details[open] summary:after{content:"−"}
    .odpc-about26 .oa-body{padding:0 20px 20px;color:#4d5964}.odpc-about26 .oa-body p{margin:0 0 12px}
    .odpc-about26 a{color:#765238;font-weight:700;text-underline-offset:3px}.odpc-about26 a:focus-visible,.odpc-about26 summary:focus-visible{outline:3px solid #2978a4;outline-offset:3px}
    .odpc-about26 .oa-links{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}.odpc-about26 .oa-links a{display:inline-block;padding:11px 16px;background:#b99b80;color:#19212b;border-radius:9px;text-decoration:none;box-shadow:0 4px 0 #81634c}
    .odpc-about26 .oa-note{font-size:13px;color:#58636e}.odpc-about26 .oa-footer{padding:23px;border:1px solid #d4d0ca;border-radius:14px;background:#f5f2ed}
    @media(prefers-reduced-motion:no-preference){.odpc-about26 details{transition:box-shadow .2s,transform .2s}.odpc-about26 details:hover{transform:translateY(-2px);box-shadow:0 12px 27px #19212b22}}
    .odpc-about26 .oa-cv{margin-top:20px;overflow-wrap:anywhere}.odpc-about26 .oa-cv-entry{margin:12px 0}.odpc-about26 .oa-cv-entry .oa-body{padding:10px 20px}.odpc-about26 .oa-cv-entry .cv-list{padding-left:24px}.odpc-about26 .oa-cv img{max-width:100%;height:auto}\n    .odpc-about26{--oa-bg:#202a35;--oa-surface:#273441;--oa-line:#536273;--oa-accent:#c1a184;color:#e9eef3}\n    .odpc-about26 h2,.odpc-about26 h3{color:#f6f8fa}\n    .odpc-about26 details{background:linear-gradient(155deg,#2c3b49,#202c38);border:1px solid #596879;border-top:3px solid #b99b80;box-shadow:0 10px 24px #10192335}\n    .odpc-about26 summary{color:#f5f7f9}.odpc-about26 .oa-body{color:#e0e7ee}\n    .odpc-about26 .oa-body a,.odpc-about26 .oa-cv a{color:#d9bca0}\n    .odpc-about26 .oa-footer{background:linear-gradient(145deg,#263440,#1d2934);border:1px solid #536273;color:#e8edf2}\n    .odpc-about26 .oa-note{color:#c5d0d9}.odpc-about26 .oa-links a{background:#344657;border:1px solid #b99b80;color:#f7f9fb;box-shadow:0 4px 0 #17232d}\n    .odpc-about26 .oa-intro{display:flex;flex-wrap:wrap;align-items:center;gap:22px}\n    .odpc-about26 .oa-portrait{width:168px;height:168px;flex:0 0 168px;border-radius:50%;object-fit:cover;object-position:50% 26%;border:4px solid #b99b80;box-shadow:0 9px 28px #0006}\n    .odpc-about26 .oa-hero-copy{flex:1 1 360px;min-width:0}\n    .odpc-about26 .oa-cv .cv-wrap{max-width:100%;color:#e8edf2}.odpc-about26 .oa-cv .cv-block{background:#273441;border-color:#536273}.odpc-about26 .oa-cv .cv-role,.odpc-about26 .oa-cv .cv-date{color:#f3f5f7}\n    body.page-id-1066 .entry-content > section:first-child{display:none}\n    @media(max-width:560px){.odpc-about26 .oa-portrait{width:132px;height:132px;flex-basis:132px;margin:auto}.odpc-about26 .oa-intro{justify-content:center}}\n    .odpc-about26{margin:0 auto 70px;padding:18px 12px 40px}.odpc-about26 .oa-grid{grid-template-columns:1fr}.odpc-about26 .oa-grid details{margin-bottom:5px}.odpc-about26 .oa-intro{background:#1d2d42;border:1px solid #365373;box-shadow:none}.odpc-about26 .oa-portrait{object-position:center 12%;background:#182638}.odpc-about26 .oa-links a{background:#173c67;color:#fff;border-color:#547ca3;box-shadow:0 3px 0 #0b213d}.odpc-about26 details{background:#1c3047;border:1px solid #3c5979;border-top:2px solid #4384c0}.odpc-about26 .oa-footer{background:#1c3047;border-color:#3c5979}.odpc-about26 .oa-kicker{color:#9dc8f2}.odpc-about26 .oa-body a,.odpc-about26 .oa-cv a{color:#a9d2ff}.odpc-about26 .oa-cv .cv-chip{background:#253e58;color:#eaf2fa;border-color:#56738f}.odpc-about26 .oa-cv .cv-doc{background:#29445e;color:#f3f7fc}.odpc-about26 .oa-cv .cv-muted{color:#c1d0df}.odpc-about26 .oa-cv .cv-h{color:#fff;font-size:19px}.odpc-about26 .oa-cv .cv-center{display:none}.odpc-about26 .oa-cv .cv-wrap{color:#e9f1f8}.odpc-about26 .oa-cv .cv-block{border:0}.odpc-about26 .oa-cv-entry{border-radius:12px}.odpc-about26 .oa-cv-entry .oa-body{color:#e8f0f7}.odpc-about26 .oa-cv-entry summary{color:#f6f9fc}.odpc-about26 .oa-cv-entry .cv-date{color:#aed0f2}.odpc-about26 .oa-cv .cv-line{color:#e4ecf5}.odpc-about26 .oa-cv p,.odpc-about26 .oa-cv li{overflow-wrap:anywhere}@media(max-width:600px){.odpc-about26 .oa-portrait{object-position:center 12%}}\n    </style>';
}, 30);
add_filter('the_content', function($original) {
 if (!odpc_about_2026_target() || !in_the_loop() || !is_main_query()) return $original;
 $cv=get_post(1172);
 $cv_html=($cv && $cv->post_status==='publish') ? $cv->post_content : '<p>Lebenslauf: <a href="https://odpc.de/lebenslauf-oliver-dering/">Originalseite öffnen</a></p>';
 // Wrap the original career entries without deleting their content, certificate URLs or download links.
 $cv_html=preg_replace_callback('~<div class="cv-block">(.*?)</div>~si', function($m) {
   $label='Berufliche Station';
   if(preg_match('~<p class="cv-date">(.*?)</p>~si',$m[1],$date)) $label=wp_strip_all_tags($date[1]);
   if(preg_match('~<p class="cv-role">(.*?)</p>~si',$m[1],$role)) $label.=' · '.wp_strip_all_tags($role[1]);
   return '<details class="oa-cv-entry"><summary>'.esc_html($label).'</summary><div class="oa-body">'.$m[1].'</div></details>';
 },$cv_html);
 $groups=[
 ['01 · Leistungsspektrum','Webdesign, Websites & Hosting','Individuelle Webseiten in jedem passenden Stil: von klaren Business-Websites über Gastronomie und Dienstleister bis zu interaktiven Plattformen. WordPress, responsive Gestaltung, Domains, Hosting, Wartung, SEO, Formulare, Mehrsprachigkeit und technische Betreuung.','https://odpc.de/webdesign/'],
 ['02 · Leistungsspektrum','Enterprise-IT, Infrastruktur & Support','Windows Server und Clients, VMware vSphere/ESXi/Horizon, Netzwerk, Betrieb, Fehleranalyse, IT-Sicherheit, Backup, Automatisierung und Second-/Third-Level-Support.','https://odpc.de/'],
 ['03 · Leistungsspektrum','KI-Integration, Agenten & Automatisierung','Praktische KI-Einführung, KI-Assistenten, Connectoren, Dokumentenworkflows und Prozessautomatisierung mit kontrollierter Integration in bestehende Systeme.','https://ai.odpc.de/'],
 ['04 · Projekte','Helper Monkey & AdminMonkey','Eigene KI-Agentenplattform und Wissensautomatisierung mit Entwicklungs-, Prüf- und Monitoringprozessen.','https://ai.odpc.de/adminmonkey/monitor/'],
 ['05 · Projekte','D.R.O.L., SONDE & Engineering','Drohnen- und Sensorikentwicklung, Wissensplattform, GroundScan/AirScan und vernetzte technische Konzepte.','https://drol.odpc.de/'],
 ['06 · Referenzen','Websites und digitale Kundenprojekte','Individuelle Umsetzungen für unterschiedliche Branchen, etwa Gastronomie, Immobilien, Kanzleien und technische Dienstleistungen.','https://odpc.de/kunden-referenzen/']
 ];
 $html='<section class="odpc-about26" aria-label="Oliver Dering – Leistungen, Projekte und Lebenslauf"><div class="oa-intro"><img class="oa-portrait" src="https://odpc.de/wp-content/uploads/2026/08/1786371119667.png.webp" alt="Porträt Oliver Dering" width="168" height="168"><div class="oa-hero-copy"><p class="oa-kicker">ODPC · Oliver Dering</p><h2>25+ Jahre IT. Webseiten, Hosting, Infrastruktur und KI.</h2><p>Ich plane, gestalte, hoste und betreue Websites in unterschiedlichen Stilrichtungen – und verbinde das mit langjähriger Enterprise-IT-Erfahrung, Automatisierung und moderner KI-Integration. Von der ersten Idee bis zum stabilen Betrieb.</p><div class="oa-links"><a href="#odpc-stationen">Stationen ansehen</a><a href="#odpc-cv">Lebenslauf & Downloads</a></div></div></div><h2 id="odpc-stationen" style="margin-top:34px">Was ich heute anbiete und entwickle</h2><div class="oa-grid">';
 foreach($groups as $g) $html.='<details><summary><span><small style="display:block;color:#866b56">'.esc_html($g[0]).'</small>'.esc_html($g[1]).'</span></summary><div class="oa-body"><p>'.esc_html($g[2]).'</p><a href="'.esc_url($g[3]).'">Details ansehen →</a></div></details>';
 $html.='</div><section id="odpc-cv" class="oa-footer"><h2>Beruflicher Werdegang & detaillierter Lebenslauf</h2><p>Mein vollständiger beruflicher Werdegang – chronologisch, mit detaillierten Tätigkeiten, Zertifikaten, Zeugnissen, Gesellenbrief und den vorhandenen Downloadlinks. Jede Station lässt sich einzeln öffnen.</p><p><a href="https://odpc.de/lebenslauf-oliver-dering/">Original-Lebenslauf separat öffnen →</a></p><div class="oa-cv">'.$cv_html.'</div></section></section>';
 return $html;
}, 50);
