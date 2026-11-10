<?php
/**
 * Plugin Name: ODPC About Me 2026
 * Description: Additive profile modernization; preserves original biography, CV links, navigation and voice dock.
 * Version: 0.1.0
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
    </style>';
}, 30);
add_filter('the_content', function ($content) {
    if (!odpc_about_2026_target() || !in_the_loop() || !is_main_query()) return $content;
    $projects = [
      ['Helper Monkey – KI-Agentenplattform','Digitale Assistenten für Bewerbungen, Postfachorganisation und weitere Aufgaben; kostenlose Einstiege und betreute Integrationen.','https://ai.odpc.de/'],
      ['AdminMonkey – Wissensautomatisierung','Laufende Entwicklung eines Systems für Wissensaufbau, Qualitätsprüfungen, Monitoring und automatisierte Workflows. Entwicklungsfunktionen sind nicht pauschal als produktiv zu verstehen.','https://ai.odpc.de/adminmonkey/monitor/'],
      ['D.R.O.L. – Drone Recon Operation Lab','Offene Forschungs- und Wissensplattform zu Drohnen, Sensorik, Firmware, SDKs, Datenauswertung und Engineering.','https://drol.odpc.de/'],
      ['SONDE – GroundScan & AirScan','Privates Entwicklungsprojekt zur Verknüpfung von Bodenmessungen, Metalldetektion, Kartierung und Luftaufnahmen.','https://odpc.de/sonde/'],
      ['PraeLex.ai, PraeDoc & PraeTax','Fachbezogene Konzepte für juristische Assistenz, Dokumentenverarbeitung und steuernahe Arbeitsabläufe; unterschiedliche Entwicklungsstände.','https://odpc.de/struktur-portfolio/'],
      ['initIQ, one.eye, one.home & PRAEDIUM','KI-Einführung für Unternehmen sowie Entwicklungsansätze für Sicherheit, Smart Home und Immobilienprozesse.','https://odpc.de/struktur-portfolio/'],
      ['Webdesign, Kundenprojekte & SmartCard','Websites, Hosting, technische Betreuung, digitale Kontaktwege sowie NFC-/QR-Visitenkarten im Webdesign-Angebot.','https://odpc.de/webdesign/'],
    ];
    $html = '<section class="odpc-about26" aria-labelledby="odpc-about26-heading"><div class="oa-intro"><p class="oa-kicker">ODPC · Profil & Entwicklung 2026</p><h2 id="odpc-about26-heading">Enterprise-IT trifft praktische KI-Entwicklung.</h2><p>Über 25 Jahre technische Erfahrung bilden die Grundlage meiner heutigen Arbeit: belastbare IT-Infrastruktur, digitale Geschäftsprozesse, KI-Integration und die Entwicklung eigener Plattformen. Ich verbinde Beratung mit konkreter Umsetzung, Tests und nachvollziehbarer Weiterentwicklung.</p><div class="oa-links"><a href="https://odpc.de/lebenslauf-oliver-dering/">Vollständigen Lebenslauf ansehen</a><a href="https://odpc.de/contact-me/">Kontakt aufnehmen</a></div></div><h2 style="margin-top:35px">Aktuelle Projekte & Entwicklungsfelder</h2><p>Die folgenden Bereiche lassen sich einzeln aufklappen. Bestehende berufliche Stationen, Referenzen und der ursprüngliche Seiteninhalt bleiben unverändert darüber erhalten.</p><div class="oa-grid">';
    foreach ($projects as $project) {
       $html .= '<details><summary>'.esc_html($project[0]).'</summary><div class="oa-body"><p>'.esc_html($project[1]).'</p><a href="'.esc_url($project[2]).'">Projekt ansehen →</a></div></details>';
    }
    $html .= '</div><div class="oa-footer"><h2>Erfahrung, die sich weiterentwickelt</h2><p>Von internationalem IT-Support und Systemadministration über Virtualisierung und Enterprise-Infrastruktur bis zur angewandten KI: Der ausführliche Lebenslauf mit den bisherigen Stationen und Nachweisen bleibt als eigenständige Seite erhalten.</p><p class="oa-note">Projektstatus und verfügbare Funktionen sind den jeweiligen Projektseiten zu entnehmen. Konzepte, Prototypen und produktive Angebote werden bewusst unterschieden.</p></div></section>';
    return $content . $html;
}, 30);
