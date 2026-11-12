<?php
/**
 * Plugin Name: ODPC About Me 2026
 * Description: Chronological profile with live HTML CV and original certificate links; voice dock untouched.
 * Version: 2.0.2
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
    .odpc-about26 .oa-cv{margin-top:20px;overflow-wrap:anywhere}.odpc-about26 .oa-cv-entry{margin:12px 0}.odpc-about26 .oa-cv-entry .oa-body{padding:10px 20px}.odpc-about26 .oa-cv-entry .cv-list{padding-left:24px}.odpc-about26 .oa-cv img{max-width:100%;height:auto}\n    .odpc-about26{--oa-bg:#202a35;--oa-surface:#273441;--oa-line:#536273;--oa-accent:#c1a184;color:#e9eef3}\n    .odpc-about26 h2,.odpc-about26 h3{color:#f6f8fa}\n    .odpc-about26 details{background:linear-gradient(155deg,#2c3b49,#202c38);border:1px solid #596879;border-top:3px solid #b99b80;box-shadow:0 10px 24px #10192335}\n    .odpc-about26 summary{color:#f5f7f9}.odpc-about26 .oa-body{color:#e0e7ee}\n    .odpc-about26 .oa-body a,.odpc-about26 .oa-cv a{color:#d9bca0}\n    .odpc-about26 .oa-footer{background:linear-gradient(145deg,#263440,#1d2934);border:1px solid #536273;color:#e8edf2}\n    .odpc-about26 .oa-note{color:#c5d0d9}.odpc-about26 .oa-links a{background:#344657;border:1px solid #b99b80;color:#f7f9fb;box-shadow:0 4px 0 #17232d}\n    .odpc-about26 .oa-intro{display:flex;flex-wrap:wrap;align-items:center;gap:22px}\n    .odpc-about26 .oa-portrait{width:168px;height:168px;flex:0 0 168px;border-radius:50%;object-fit:cover;object-position:50% 26%;border:4px solid #b99b80;box-shadow:0 9px 28px #0006}\n    .odpc-about26 .oa-hero-copy{flex:1 1 360px;min-width:0}\n    .odpc-about26 .oa-cv .cv-wrap{max-width:100%;color:#e8edf2}.odpc-about26 .oa-cv .cv-block{background:#273441;border-color:#536273}.odpc-about26 .oa-cv .cv-role,.odpc-about26 .oa-cv .cv-date{color:#f3f5f7}\n    body.page-id-1066 .entry-content > section:first-child{display:none}\n    @media(max-width:560px){.odpc-about26 .oa-portrait{width:132px;height:132px;flex-basis:132px;margin:auto}.odpc-about26 .oa-intro{justify-content:center}}\n    .odpc-about26{margin:0 auto 70px;padding:18px 12px 40px}.odpc-about26 .oa-grid{grid-template-columns:1fr}.odpc-about26 .oa-grid details{margin-bottom:5px}.odpc-about26 .oa-intro{background:#1d2d42;border:1px solid #365373;box-shadow:none}.odpc-about26 .oa-portrait{object-position:center 12%;background:#182638}.odpc-about26 .oa-links a{background:#173c67;color:#fff;border-color:#547ca3;box-shadow:0 3px 0 #0b213d}.odpc-about26 details{background:#1c3047;border:1px solid #3c5979;border-top:2px solid #4384c0}.odpc-about26 .oa-footer{background:#1c3047;border-color:#3c5979}.odpc-about26 .oa-kicker{color:#9dc8f2}.odpc-about26 .oa-body a,.odpc-about26 .oa-cv a{color:#a9d2ff}.odpc-about26 .oa-cv .cv-chip{background:#253e58;color:#eaf2fa;border-color:#56738f}.odpc-about26 .oa-cv .cv-doc{background:#29445e;color:#f3f7fc}.odpc-about26 .oa-cv .cv-muted{color:#c1d0df}.odpc-about26 .oa-cv .cv-h{color:#fff;font-size:19px}.odpc-about26 .oa-cv .cv-center{display:none}.odpc-about26 .oa-cv .cv-wrap{color:#e9f1f8}.odpc-about26 .oa-cv .cv-block{border:0}.odpc-about26 .oa-cv-entry{border-radius:12px}.odpc-about26 .oa-cv-entry .oa-body{color:#e8f0f7}.odpc-about26 .oa-cv-entry summary{color:#f6f9fc}.odpc-about26 .oa-cv-entry .cv-date{color:#aed0f2}.odpc-about26 .oa-cv .cv-line{color:#e4ecf5}.odpc-about26 .oa-cv p,.odpc-about26 .oa-cv li{overflow-wrap:anywhere}@media(max-width:600px){.odpc-about26 .oa-portrait{object-position:center 12%}}\n    .odpc-about26,.odpc-about26 *{max-width:100%;overflow-wrap:break-word}\n    .odpc-about26{color:#f0f5fc!important;overflow:hidden}\n    .odpc-about26 h2,.odpc-about26 h3,.odpc-about26 .oa-footer h2{color:#fff!important;font-size:clamp(23px,4.5vw,34px)!important;line-height:1.25!important;overflow-wrap:anywhere}\n    .odpc-about26 p,.odpc-about26 li,.odpc-about26 .oa-body,.odpc-about26 .oa-footer p{color:#eaf2fc!important;font-size:clamp(15px,2.6vw,17px)!important;line-height:1.65!important}\n    .odpc-about26 .oa-grid{gap:12px!important;margin:18px 0 28px!important}\n    .odpc-about26 details{margin:0!important;min-width:0!important;background:#1c3047!important;border:1px solid #42668d!important;border-radius:13px!important}\n    .odpc-about26 summary,.odpc-about26 .oa-cv-entry summary{font-size:clamp(16px,3vw,20px)!important;font-weight:700!important;line-height:1.35!important;color:#fff!important;padding:18px!important;white-space:normal!important;word-break:normal!important;text-shadow:none!important}\n    .odpc-about26 summary span,.odpc-about26 summary small{color:#fff!important;min-width:0!important;font-size:inherit!important;line-height:1.4!important}\n    .odpc-about26 summary small{font-size:12px!important;letter-spacing:.07em!important;color:#a9cbed!important;margin-bottom:5px!important}\n    .odpc-about26 .oa-footer{padding:clamp(17px,3vw,28px)!important;margin-top:16px!important}\n    .odpc-about26 .oa-cv{margin-top:15px}.odpc-about26 .oa-cv .cv-wrap,.odpc-about26 .oa-cv .cv-wrap *{color:#eaf2fc!important}\n    .odpc-about26 .oa-cv .cv-chip{background:#284661!important;color:#fff!important}.odpc-about26 .oa-cv a{color:#9fd2ff!important}\n    .odpc-about26 .oa-cv .cv-center{display:block!important}.odpc-about26 .oa-cv .cv-center img{display:none!important}\n    .odpc-about26 .oa-cv-entry{margin:9px 0!important}.odpc-about26 .oa-cv-entry .oa-body{padding:8px 18px 20px!important}\n    .odpc-about26 .oa-full-cv{margin-top:18px!important}.odpc-about26 .oa-full-cv>summary{background:#28547e!important;border-radius:11px!important}\n    @media(max-width:600px){.odpc-about26{padding:12px 8px 30px!important}.odpc-about26 .oa-intro{padding:20px 16px!important}.odpc-about26 summary{padding:17px 15px!important}.odpc-about26 .oa-footer{padding:18px 14px!important}}\n    </style>';
}, 30);

/* Isolated CSS override: no change to content generation or global voice dock. */
add_action('wp_head', function () {
    if (!odpc_about_2026_target()) return;
    echo '<style id="odpc-about-2026-classic-fix">
/* Scoped About Me classic palette and compact centered portrait. */
.odpc-about26 .oa-intro{flex-direction:column!important;align-items:center!important;text-align:center!important}
.odpc-about26 .oa-portrait{display:block!important;box-sizing:border-box!important;width:124px!important;height:124px!important;min-width:124px!important;min-height:124px!important;max-width:124px!important;max-height:124px!important;flex:0 0 124px!important;aspect-ratio:1!important;border-radius:50%!important;object-fit:cover!important;object-position:50% 30%!important;margin:0 auto 6px!important}
.odpc-about26 .oa-hero-copy{flex:0 1 auto!important;width:100%!important;min-width:0!important}
.odpc-about26 .oa-links{justify-content:center!important}
html[data-odpc-appearance="green"] body.page .odpc-about26{background:#f4eedf!important;color:#294735!important;border-radius:16px!important}
html[data-odpc-appearance="green"] .odpc-about26 .oa-intro{background:#ebe5d4!important;border:1px solid #8fa98a!important;color:#294735!important;box-shadow:none!important}
html[data-odpc-appearance="green"] .odpc-about26 h2,html[data-odpc-appearance="green"] .odpc-about26 h3,html[data-odpc-appearance="green"] .odpc-about26 .oa-intro h2,html[data-odpc-appearance="green"] .odpc-about26 .oa-footer h2{color:#254a35!important}
html[data-odpc-appearance="green"] .odpc-about26 p,html[data-odpc-appearance="green"] .odpc-about26 li,html[data-odpc-appearance="green"] .odpc-about26 .oa-body,html[data-odpc-appearance="green"] .odpc-about26 .oa-footer p,html[data-odpc-appearance="green"] .odpc-about26 summary,html[data-odpc-appearance="green"] .odpc-about26 summary span{color:#294735!important}
html[data-odpc-appearance="green"] .odpc-about26 details,html[data-odpc-appearance="green"] .odpc-about26 .oa-footer,html[data-odpc-appearance="green"] .odpc-about26 .oa-cv .cv-block{background:#faf7ed!important;border:1px solid #9eb89a!important;border-top:2px solid #739873!important;box-shadow:none!important}
html[data-odpc-appearance="green"] .odpc-about26 .oa-full-cv>summary{background:#e3ead9!important;color:#254a35!important}
html[data-odpc-appearance="green"] .odpc-about26 .oa-links a{background:#dce8d3!important;color:#254a35!important;border:1px solid #8daa88!important;box-shadow:none!important}
html[data-odpc-appearance="green"] .odpc-about26 .oa-cv .cv-wrap,html[data-odpc-appearance="green"] .odpc-about26 .oa-cv .cv-wrap *,html[data-odpc-appearance="green"] .odpc-about26 .oa-cv-entry summary,html[data-odpc-appearance="green"] .odpc-about26 .oa-cv-entry .oa-body{color:#294735!important}
html[data-odpc-appearance="green"] .odpc-about26 .oa-cv .cv-chip,html[data-odpc-appearance="green"] .odpc-about26 .oa-cv .cv-doc{background:#e4eadb!important;border-color:#a6bfa0!important;color:#294735!important}
html[data-odpc-appearance="green"] .odpc-about26 a,html[data-odpc-appearance="green"] .odpc-about26 .oa-body a,html[data-odpc-appearance="green"] .odpc-about26 .oa-cv a{color:#315f40!important}
html[data-odpc-appearance="green"] .odpc-about26 summary small,html[data-odpc-appearance="green"] .odpc-about26 .oa-kicker,html[data-odpc-appearance="green"] .odpc-about26 .oa-note{color:#50754f!important}
</style>';
}, 99);

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
 $html='<section class="odpc-about26" aria-label="Oliver Dering – Leistungen, Projekte und Lebenslauf"><div class="oa-intro"><img class="oa-portrait" src="https://odpc.de/wp-content/uploads/2026/08/1786371119667.png.webp" alt="Porträt Oliver Dering" width="168" height="168"><div class="oa-hero-copy"><p class="oa-kicker">ODPC · Oliver Dering</p><h2>25+ Jahre IT. Webseiten, Hosting, Infrastruktur und KI.</h2><p>Ich plane, gestalte, hoste und betreue Websites in unterschiedlichen Stilrichtungen – und verbinde das mit langjähriger Enterprise-IT-Erfahrung, Automatisierung und moderner KI-Integration. Von der ersten Idee bis zum stabilen Betrieb.</p><div class="oa-links"><a href="#odpc-stationen">Leistungen ansehen</a><a href="#odpc-cv">Lebenslauf & Downloads</a></div></div></div><h2 id="odpc-stationen" style="margin-top:34px">Was ich heute anbiete und entwickle</h2><div class="oa-grid">';
 foreach($groups as $g) $html.='<details><summary><span><small style="display:block;color:#866b56">'.esc_html($g[0]).'</small>'.esc_html($g[1]).'</span></summary><div class="oa-body"><p>'.esc_html($g[2]).'</p><a href="'.esc_url($g[3]).'">Details ansehen →</a></div></details>';
 $html.='</div><section class="oa-career"><h2>Berufliche Erfahrung auf einen Blick</h2><div class="oa-grid"><details><summary><span><small>Aktuell · 2024–2026</small>ODPC, Applied AI & digitale Lösungen</span></summary><div class="oa-body"><p>Eigenständige Entwicklung von Websites, Hosting- und KI-Integrationslösungen, digitalen Plattformen und Automatisierungen. IT-Verantwortung und KI-Einführung im Kanzlei- und Unternehmensumfeld.</p></div></details><details><summary><span><small>2021–2023</small>Enterprise-IT · Deutsche Telekom / VMware</span></summary><div class="oa-body"><p>Second- und Third-Level-Support für produktive VMware-, Server- und VDI-Infrastrukturen im Finanz- und Leasingumfeld.</p></div></details><details><summary><span><small>Frühere IT-Laufbahn</small>IT-Beratung, Systembetrieb & internationaler Support</span></summary><div class="oa-body"><p>Mehr als 25 Jahre Erfahrung mit IT-Infrastruktur, Kundenprojekten, Systemadministration, Support und technischer Beratung. Die vollständigen Arbeitgeber, Zeiträume und Tätigkeiten stehen im detaillierten Lebenslauf.</p></div></details></div></section><section id="odpc-cv" class="oa-footer"><h2>Vollständiger Lebenslauf & Nachweise</h2><p>Alle Stationen, Tätigkeiten, Zeugnisse, Zertifikate und Dokumente bleiben vollständig erhalten. Bei Bedarf kannst du die ausführliche Fassung öffnen.</p><details class="oa-full-cv"><summary>Detaillierten Lebenslauf mit allen Stationen öffnen</summary><div class="oa-body"><p><a href="https://odpc.de/lebenslauf-oliver-dering/">Original-Lebenslauf und Downloads separat öffnen →</a></p><div class="oa-cv">'.$cv_html.'</div></div></details></section></section>';
 return $html;
}, 50);

/**
 * Global presentation copy: one concise service statement in the branded header.
 * Leaves navigation, homepage hero content, and floating voice/language dock unchanged.
 */
add_action('wp_footer', function () {
    if (is_admin()) return;
    ?>
    <script id="odpc-header-message-2026">
    (function(){
      function apply(){
        var root=document.querySelector('#masthead, .site-header, header.site-header');
        if(!root)return;
        var elements=root.querySelectorAll('h1,h2,h3,p,.site-title,.site-description,.ast-site-title-wrap span');
        var headline='Von Hardware und Software über Webdesign bis zur KI-Integration – alles aus einer Hand.';
        elements.forEach(function(el){
          if(el.children.length>0)return;
          var t=(el.textContent||'').replace(/\s+/g,' ').trim();
          if(/Oliver Dering\s*[–-]\s*IT.Consulting.*(?:KI|Projekte)/i.test(t)){
            el.textContent=headline;
            el.style.fontSize='clamp(20px,3.5vw,31px)';
            el.style.lineHeight='1.3';
            el.style.overflowWrap='anywhere';
          } else if(/^(?:IT.Consulting\s*[·|\-]\s*)?KI.Integration\s*[·|\-]\s*Digitale Projekte\.?$/i.test(t) || /^IT.Consulting\s*[·|\-]\s*KI.Integration\s*[·|\-]\s*Digitale Projekte\.?$/i.test(t)){
            el.style.display='none';
          }
        });
      }
      if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',apply,{once:true});else apply();
    })();
    </script>
    <?php
}, 99);

/**
 * Complete ODPC digital team presentation on the hardware-to-AI article.
 * Keeps original service descriptions and global voice controls intact.
 */
add_filter('the_content', function ($content) {
    if (is_admin() || !is_singular('post') || (int)get_queried_object_id() !== 2970) return $content;
    if (strpos($content, 'odpc-team-member') === false || strpos($content, 'odpc-complete-team-2026') !== false) return $content;
    $team = '<section id="odpc-complete-team-2026" aria-label="ODPC digitales Team" style="margin:0 0 26px;padding:0">'
      . '<figure style="margin:0 0 24px;border-radius:16px;overflow:hidden;background:#182b3e"><img src="https://odpc.de/wp-content/uploads/2026/11/odpc_team_hero.webp" alt="Oliver Dering mit dem erweiterten digitalen ODPC-Team" loading="lazy" style="display:block;width:100%;height:auto;max-height:520px;object-fit:contain"><figcaption style="padding:12px 18px;color:#fff;font-size:14px">Oliver Dering und das digitale ODPC-Team</figcaption></figure>'
      . '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:14px">'
      . '<article style="padding:20px;border:1px solid #c9d8e6;border-radius:14px;background:#fff"><h3>Oliver Dering</h3><p>Gründer, IT-Consulting, Strategie und Projektverantwortung.</p></article>'
      . '<article style="padding:20px;border:1px solid #c9d8e6;border-radius:14px;background:#fff"><h3>Oxana · ChatGPT</h3><p>Projektkoordination, Kommunikation und KI-Integration.</p></article>'
      . '<article style="padding:20px;border:1px solid #c9d8e6;border-radius:14px;background:#fff"><h3>Claire · Gemini</h3><p>Recherche, Analyse und fachliche Gegenprüfung.</p></article>'
      . '<article style="padding:20px;border:1px solid #c9d8e6;border-radius:14px;background:#fff"><h3>Lee · DeepSeek</h3><p>Technische Logik, Code und strukturierte Umsetzung.</p></article>'
      . '<article style="padding:20px;border:1px solid #c9d8e6;border-radius:14px;background:#fff"><h3>Claude</h3><p>Softwarearchitektur, Engineering und Qualitätssicherung.</p></article>'
      . '<article style="padding:20px;border:1px solid #c9d8e6;border-radius:14px;background:#fff"><h3>Microsoft Copilot</h3><p>Microsoft 365, Enterprise-Workflows und Administration.</p></article>'
      . '<article style="padding:20px;border:1px solid #c9d8e6;border-radius:14px;background:#fff"><h3>GitHub · DevOps</h3><p>Versionierung, Automatisierung und Deployments; technische Funktion, keine Person.</p></article>'
      . '</div><p style="margin:16px 0;color:#43546a;font-size:14px">Die dargestellten KI-Rollen sind digitale Werkzeuge und Assistenzfunktionen. Die Verantwortung für Kundenprojekte liegt bei Oliver Dering.</p></section>';
    // Keep legacy source markup intact, but display only the complete roster.
    $content = preg_replace('/(<div\\s+class="odpc-team"\\s*>)/', $team . '<style>#odpc-complete-team-2026 ~ .odpc-team{display:none!important}</style>$1', $content, 1);
    return $content;
}, 80);


/** Homepage-only press-and-hold image Easter Egg. Fixed visible button; voice dock untouched. */
add_action('wp_footer', function () {
    if (is_admin() || !is_front_page()) return;
    ?>
    <style id="odpc-easter-egg-css">
    #odpc-easter-egg-button{appearance:none!important;-webkit-appearance:none!important;box-sizing:border-box!important;position:absolute!important;left:0;top:0;z-index:2147483000!important;width:11px!important;height:11px!important;min-width:11px!important;min-height:11px!important;max-width:11px!important;max-height:11px!important;margin:0!important;padding:0!important;border:1px solid #9faebd!important;border-radius:50%!important;background:#1c3148!important;color:#e6eef5!important;font:700 8px/1 sans-serif!important;box-shadow:0 1px 2px #0008!important;display:flex!important;align-items:center!important;justify-content:center!important;cursor:pointer!important;touch-action:none!important;user-select:none!important;-webkit-user-select:none!important;opacity:.95!important;transform:translate(-50%,-50%)!important}
    #odpc-easter-egg-button::before{content:'';position:absolute;inset:-12px;border-radius:50%}
    
    #odpc-easter-egg-button:focus-visible{outline:3px solid #ffcc55;outline-offset:3px}
    </style>
    <script id="odpc-easter-egg-js">
    (function(){
      var alternate='https://odpc.de/wp-content/uploads/2026/11/9d77deaa94684412b00e1ea0dd61c2d52050c4eea38c10ac6c6023c942477844.png';
      function init(){
        if(document.getElementById('odpc-easter-egg-button'))return;
        var btn=document.createElement('button');btn.type='button';btn.id='odpc-easter-egg-button';btn.textContent='⚥';
        btn.title='Easter Egg – gedrückt halten';btn.setAttribute('aria-label','Easter Egg: alternatives Team anzeigen, gedrückt halten');
        document.body.appendChild(btn);
        var overlay=null,anchor=null;
        function placeBadge(){
          var img=findImage();if(!img)return;
          var r=img.getBoundingClientRect();
          if(!r.width||!r.height)return;
          // The jacket badge position is relative to the image, not the viewport.
          // In the source composition Oliver stands second from the left.
          btn.style.left=(r.left+window.scrollX+r.width*0.205)+'px';
          btn.style.top=(r.top+window.scrollY+r.height*0.505)+'px';
        }
        window.addEventListener('resize',placeBadge);
        window.addEventListener('scroll',placeBadge,{passive:true});
        window.addEventListener('load',placeBadge,{once:true});
        if(window.ResizeObserver){new ResizeObserver(placeBadge).observe(document.querySelector('#masthead')||document.body);}
        placeBadge();

        var loaded=false;
        var preview=new Image();
        preview.onload=function(){loaded=true;};
        preview.onerror=function(){loaded=false;btn.title='Alternatives Bild konnte nicht geladen werden';};
        preview.src=alternate;
        function findImage(){
          var imgs=document.querySelectorAll('#masthead img, .site-header img, header img');
          var best=null,area=0;
          for(var i=0;i<imgs.length;i++){
            var im=imgs[i],rect=im.getBoundingClientRect();
            var a=rect.width*rect.height;
            if(rect.width>200&&rect.height>70&&a>area){best=im;area=a;}
          }
          return best;
        }
        function positionOverlay(){
          if(!overlay||!anchor)return;
          var rect=anchor.getBoundingClientRect();
          overlay.style.left=(rect.left+window.scrollX)+'px';
          overlay.style.top=(rect.top+window.scrollY)+'px';
          overlay.style.width=rect.width+'px';
          overlay.style.height=rect.height+'px';
        }
        function show(){
          anchor=findImage();
          if(!anchor||!loaded)return;
          if(!overlay){
            overlay=document.createElement('img');
            overlay.alt='ODPC Team – Village People Easter Egg';
            overlay.src=alternate;
            overlay.style.cssText='position:absolute;z-index:2147482999;object-fit:fill;pointer-events:none;max-width:none;display:block;';
            document.body.appendChild(overlay);
          }
          positionOverlay();
          btn.setAttribute('aria-pressed','true');
        }
        function restore(){
          if(overlay){overlay.remove();overlay=null;}
          anchor=null;
          btn.setAttribute('aria-pressed','false');
        }
        window.addEventListener('resize',positionOverlay);
        window.addEventListener('scroll',positionOverlay,{passive:true});
        btn.addEventListener('pointerdown',function(e){if(e.pointerType==='mouse'&&e.button!==0)return;e.preventDefault();try{btn.setPointerCapture(e.pointerId)}catch(_e){}show()});
        ['pointerup','pointercancel','lostpointercapture','blur'].forEach(function(ev){btn.addEventListener(ev,restore)});
        btn.addEventListener('keydown',function(e){if(e.key===' '||e.key==='Enter'){e.preventDefault();if(!e.repeat)show()}});
        btn.addEventListener('keyup',function(e){if(e.key===' '||e.key==='Enter'){e.preventDefault();restore()}});
        btn.addEventListener('click',function(e){e.preventDefault()});
        window.addEventListener('blur',restore);
        document.addEventListener('visibilitychange',function(){if(document.hidden)restore()});
      }
      if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
    })();
    </script>
    <?php
}, 100);

/* Team classic appearance: scoped to unser-team only. */
add_action('wp_head', function () {
    if (is_admin() || !(is_page('unser-team') || is_single('unser-team'))) return;
    echo '<style id="odpc-team-classic-palette">
/* Cover WordPress block backgrounds and inline blue cards, not only custom team classes. */
html[data-odpc-appearance="green"] body.page .entry-content{background:#f4eedf!important;color:#294735!important}
html[data-odpc-appearance="green"] body.page .entry-content :is(.wp-block-group,.wp-block-columns,.wp-block-column,.wp-block-cover,.wp-block-media-text,.wp-block-details,.wp-block-quote,.wp-block-table,.wp-block-button,.elementor-widget-container,.elementor-container,.elementor-column-wrap,.elementor-section,.e-con,.e-con-inner,article,section,details,figure,fieldset){background-color:#faf7ed!important;background-image:none!important;border-color:#91ac8b!important;box-shadow:none!important;color:#294735!important}
html[data-odpc-appearance="green"] body.page .entry-content :is(.wp-block-group,.wp-block-columns,.wp-block-column,.wp-block-cover,.wp-block-media-text,.wp-block-details,.elementor-widget-container,.e-con,article,section,details) :is(div,header,footer){background-color:transparent!important;background-image:none!important;border-color:#91ac8b!important;box-shadow:none!important}
html[data-odpc-appearance="green"] body.page .entry-content :is(.wp-block-group,.wp-block-column,.wp-block-cover,.wp-block-media-text,.wp-block-details,.elementor-widget-container,.e-con,article,section,details) :is(h1,h2,h3,h4,h5,h6,p,span,small,strong,em,li,summary){color:#294735!important}
html[data-odpc-appearance="green"] body.page .entry-content :is(.wp-block-group,.wp-block-column,.wp-block-cover,.wp-block-media-text,.wp-block-details,.elementor-widget-container,.e-con,article,section,details) a{color:#315f40!important}
html[data-odpc-appearance="green"] body.page .entry-content :is(.wp-block-group,.wp-block-column,.wp-block-cover,.wp-block-media-text,.wp-block-details,.elementor-widget-container,.e-con,article,section,details) :is(img,video,svg){background:transparent!important}

body .entry-content .odpc-team,body .entry-content .odpc-team *{box-sizing:border-box}
html[data-odpc-appearance="green"] body .entry-content{--odpc-team-ink:#284634;--odpc-team-paper:#f6f0e3;--odpc-team-card:#faf7ef;--odpc-team-line:#91ac8b}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-grid,.odpc-team-section,.odpc-team-member,.odpc-team-card,.team-card,.team-member,.odpc-team-role,.odpc-team-panel,.odpc-team-detail,.odpc-team-details){background:var(--odpc-team-card)!important;border-color:var(--odpc-team-line)!important;color:var(--odpc-team-ink)!important;box-shadow:none!important}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-section,.odpc-team-grid){background:var(--odpc-team-paper)!important}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-section,.odpc-team-member,.odpc-team-card,.team-card,.team-member,.odpc-team-panel,.odpc-team-detail) :is(h1,h2,h3,h4,p,span,small,strong,li,summary){color:var(--odpc-team-ink)!important}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-member,.odpc-team-card,.team-card,.team-member) a{color:#315e40!important}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-card,.team-card,.odpc-team-member) :is(button,[role="button"]){border-color:#86a781!important}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-grid,.odpc-team-section) :is(article,details){background:var(--odpc-team-card)!important;border-color:var(--odpc-team-line)!important;color:var(--odpc-team-ink)!important;box-shadow:none!important}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-grid,.odpc-team-section) :is(article,details) *:not(img):not(svg):not(path){color:var(--odpc-team-ink)!important}
html[data-odpc-appearance="green"] body .entry-content :is(.odpc-team,.odpc-team-grid,.odpc-team-section) :is(article,details) :is(a,button){color:#315e40!important}
</style>';
}, 99);
