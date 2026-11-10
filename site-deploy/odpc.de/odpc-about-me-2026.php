<?php
/**
 * Plugin Name: ODPC About Me 2026
 * Description: Chronological profile with live HTML CV and original certificate links; voice dock untouched.
 * Version: 0.2.0
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
    .odpc-about26 .oa-cv{margin-top:20px;overflow-wrap:anywhere}.odpc-about26 .oa-cv-entry{margin:12px 0}.odpc-about26 .oa-cv-entry .oa-body{padding:10px 20px}.odpc-about26 .oa-cv-entry .cv-list{padding-left:24px}.odpc-about26 .oa-cv img{max-width:100%;height:auto}\n    </style>';
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
 ['2026 · ODPC & KI','Helper Monkey, AdminMonkey, initIQ, PraeLex.ai','Digitale KI-Assistenten, Wissensmanagement, Automatisierung und unternehmerische KI-Integration.','https://ai.odpc.de/'],
 ['2026 · Engineering','D.R.O.L., SONDE, one.eye, one.home','Drohnen-Engineering, Sensorik, Datenauswertung und vernetzte Sicherheitskonzepte.','https://drol.odpc.de/'],
 ['2024–2025','KI und digitale Kanzleiprozesse','Eine berufliche Station mit Fokus auf Dokumentenprozesse und KI; nicht die Haupttätigkeit der gesamten Laufbahn.','https://odpc.de/praelex-ai/'],
 ['2006–2014','Oliver Dering IT Consulting','Selbstständigkeit, Third-Level-Support, Unternehmensinfrastruktur und Kundenprojekte.','https://odpc.de/lebenslauf-oliver-dering/'],
 ['2001–2006','Internationaler IT-Support und Administration','Compaq/HP, Schott/Zeiss und Toll Collect; Support, Server, Netzwerke und GPS/GSM-Systeme.','https://odpc.de/lebenslauf-oliver-dering/'],
 ['Frühere Stationen','Ausbildung und technische Grundlagen','Kfz-Ausbildung, Luftwaffe und technischer Berufseinstieg. Zeugnisse und Gesellenbrief im vollständigen Lebenslauf.','https://odpc.de/lebenslauf-oliver-dering/']
 ];
 $html='<section class="odpc-about26" aria-label="Oliver Dering – Beruf und Projekte"><div class="oa-intro"><p class="oa-kicker">ODPC · Oliver Dering</p><h2>25+ Jahre IT. Von Enterprise-Infrastruktur zur KI-Entwicklung.</h2><p>Ich verbinde Systembetrieb, technische Beratung und internationale IT-Erfahrung mit moderner Automatisierung und eigenen Entwicklungsplattformen. Mein Maßstab ist die praktische Umsetzung.</p><div class="oa-links"><a href="#odpc-stationen">Stationen ansehen</a><a href="#odpc-cv">Lebenslauf & Downloads</a></div></div><h2 id="odpc-stationen" style="margin-top:34px">Mein beruflicher Weg und aktuelle Projekte</h2><div class="oa-grid">';
 foreach($groups as $g) $html.='<details><summary><span><small style="display:block;color:#866b56">'.esc_html($g[0]).'</small>'.esc_html($g[1]).'</span></summary><div class="oa-body"><p>'.esc_html($g[2]).'</p><a href="'.esc_url($g[3]).'">Details ansehen →</a></div></details>';
 $html.='</div><section id="odpc-cv" class="oa-footer"><h2>Detaillierter Lebenslauf</h2><p>Die vollständige Originalfassung mit allen Stationen, Zeugnissen, Gesellenbrief und vorhandenen deutsch- und englischsprachigen Downloads ist hier integriert. Alle Dokument-Links bleiben unverändert.</p><p><a href="https://odpc.de/lebenslauf-oliver-dering/">Original-Lebenslauf separat öffnen →</a></p><div class="oa-cv">'.$cv_html.'</div></section></section>';
 return $html;
}, 50);
