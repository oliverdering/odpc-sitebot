<?php
/**
 * Plugin Name: ODPC Preise & Angebote
 * Description: Eigenständige Preisseite; übernimmt bestehende Navigation, Designumschaltung und Schwebebalken unverändert.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;
const ODPC_PRICES_SLUG = 'preise-angebote';
add_action('init', function () {
  $page = get_page_by_path(ODPC_PRICES_SLUG);
  if ($page) return;
  wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Preise & Angebote','post_name'=>ODPC_PRICES_SLUG,'post_content'=>'[odpc_preise]','comment_status'=>'closed','ping_status'=>'closed'));
}, 30);
add_shortcode('odpc_preise', function () {
  ob_start(); ?>
  <main class="odpc-pricing" id="odpc-pricing">
    <section class="op-hero">
      <span class="op-kicker">ODPC · Transparente IT-Preise</span>
      <h1>Alles wird teurer. Gute IT-Hilfe muss es nicht.</h1>
      <p>25+ Jahre IT-Erfahrung, persönliche Betreuung und KI-gestützte Effizienz. Klare Preise für Privatpersonen und Unternehmen in der Region Koblenz, Mayen und Polch.</p>
      <div class="op-actions"><a class="op-btn" href="#op-tarife">Preise ansehen</a><a class="op-btn op-outline" href="<?php echo esc_url(home_url('/contact-me/')); ?>">Individuelles Angebot anfragen</a></div>
    </section>
    <section class="op-section" id="op-tarife"><div class="op-heading"><span>Einfach & nachvollziehbar</span><h2>Unsere Stundensätze</h2><p>Du weißt vor dem Auftrag, welcher Preis gilt. Zusätzliche Leistungen stimmen wir vorher ab.</p></div>
      <div class="op-grid op-two">
        <article class="op-card"><div class="op-tag">Für Zuhause</div><h3>Privatkunden</h3><div class="op-price">59 € <small>/ Stunde</small></div><p>Endpreis für PC-Hilfe, WLAN, Drucker, Software, Einrichtung und Fernwartung.</p><ul><li>Fernwartung ab 30 Minuten</li><li>Vor Ort mindestens 60 Minuten</li><li>Danach in 15-Minuten-Schritten</li></ul><a href="<?php echo esc_url(home_url('/contact-me/')); ?>">Privat-Anfrage stellen →</a></article>
        <article class="op-card"><div class="op-tag">Für Unternehmen</div><h3>Geschäftskunden</h3><div class="op-price">89 € <small>/ Stunde</small></div><p>IT-Administration, Netzwerke, Microsoft-Umgebungen, WordPress, KI-Beratung und Automatisierung.</p><ul><li>Fernwartung ab 30 Minuten</li><li>Vor Ort mindestens 60 Minuten</li><li>Projekte auf Wunsch zum Festpreis</li></ul><a href="<?php echo esc_url(home_url('/contact-me/')); ?>">Business-Anfrage stellen →</a></article>
      </div>
      <p class="op-note">Preisangaben als Rechnungsbeträge bei Anwendung der Kleinunternehmerregelung nach § 19 UStG; es wird keine Umsatzsteuer ausgewiesen. Bei Änderung der steuerlichen Behandlung werden die Preisangaben angepasst. Anfahrt und Fremdkosten nur nach vorheriger Vereinbarung.</p>
    </section>
    <section class="op-section"><div class="op-heading"><span>Planbar statt überraschend</span><h2>Webseiten zum Festpreis</h2><p>Für kleine Unternehmen, Selbstständige und lokale Projekte. Die Preise gelten für die beschriebenen Standardumfänge.</p></div>
      <div class="op-grid op-three">
        <article class="op-card"><div class="op-tag">Kompakter Einstieg</div><h3>WebStart</h3><div class="op-price">499 €</div><p>Bis zu 3 Seiten, responsives Vorlagenlayout, Kontaktmöglichkeit und Veröffentlichung.</p><p class="op-small">Kundenseitig gelieferte Texte und Bilder · eine Korrekturrunde.</p></article>
        <article class="op-card"><div class="op-tag">Für lokale Betriebe</div><h3>WebBusiness</h3><div class="op-price">899 €</div><p>Bis zu 5 Seiten, abgestimmtes Design, Kontaktformular und technisches SEO-Basissetup.</p><p class="op-small">Kundenseitig gelieferte Inhalte · zwei Korrekturrunden.</p></article>
        <article class="op-card"><div class="op-tag">Mehr Möglichkeiten</div><h3>WebBusiness Plus</h3><div class="op-price">1.490 €</div><p>Bis zu 8 Seiten, erweiterte Gestaltung und strukturierte Inhaltsbereiche.</p><p class="op-small">Zusatzfunktionen, Übersetzungen und Integrationen nach Angebot.</p></article>
      </div>
      <p class="op-note">Domains, Hosting, kostenpflichtige Plugins, professionelle Rechtstexte, Bildlizenzen, Texterstellung, Shopfunktionen und Sonderintegrationen sind nicht enthalten, sofern nicht ausdrücklich vereinbart. Rechtliche Konformität kann nicht pauschal garantiert werden.</p>
    </section>
    <section class="op-section"><div class="op-heading"><span>Für regelmäßigen Bedarf</span><h2>Stundenkontingente</h2><p>Einfach vorausplanen. Die konkrete Laufzeit, Verwendung und Abrechnung vereinbaren wir schriftlich vor dem Kauf.</p></div>
      <div class="op-grid op-three">
        <article class="op-card"><h3>Flex · 5 Stunden</h3><div class="op-price">445 €</div><p>Zum regulären Geschäftskundentarif.</p></article>
        <article class="op-card"><h3>Team · 10 Stunden</h3><div class="op-price">845 €</div><p>Ca. 5 % Preisvorteil gegenüber Einzelstunden.</p></article>
        <article class="op-card"><h3>Partner · 20 Stunden</h3><div class="op-price">1.602 €</div><p>10 % Preisvorteil gegenüber Einzelstunden.</p></article>
      </div>
      <p class="op-note">Kontingente sind zunächst Angebote auf Anfrage, keine sofort buchbaren Online-Produkte. Geltungsdauer, Erstattung nicht verbrauchter Stunden, Leistungsumfang und Anfahrt werden im individuellen Vertrag geregelt.</p>
    </section>
    <section class="op-section op-split"><div><span class="op-kicker">KI als Effizienzvorteil</span><h2>Mehr Erfahrung. Weniger unnötiger Aufwand.</h2><p>Wir nutzen KI dort, wo sie sinnvoll hilft: bei Analyse, Dokumentation, Recherche und Automatisierung. Fachliche Prüfung, Datenschutz und Verantwortung bleiben bei uns. Du bezahlst für die vereinbarte Leistung – nicht für vermeidbare Umwege.</p></div><div class="op-soft"><h3>Individuell nach Absprache</h3><p>KI-Einführung, Microsoft-Administration, Sicherheitsprojekte, komplexe Websites und Automatisierungen kalkulieren wir passend zu Ziel, Aufwand und Risiko.</p><a class="op-btn" href="<?php echo esc_url(home_url('/contact-me/')); ?>">Projekt besprechen</a></div></section>
    <section class="op-section op-terms"><h2>Transparente Konditionen</h2><details><summary>Wie wird Arbeitszeit abgerechnet?</summary><p>Fernwartung mindestens 30 Minuten, Vor-Ort-Leistungen mindestens 60 Minuten; danach jeweils angefangene 15 Minuten. Abweichende Projektpreise gehen bei schriftlicher Vereinbarung vor.</p></details><details><summary>Was kostet die Anfahrt?</summary><p>Die Anfahrt wird vor Beauftragung abhängig vom Einsatzort vereinbart und im Angebot gesondert ausgewiesen. Ohne vorherige Vereinbarung berechnen wir keine zusätzliche Anfahrtspauschale.</p></details><details><summary>Was ist im Festpreis enthalten?</summary><p>Nur der im jeweiligen Angebot ausdrücklich beschriebene Leistungsumfang. Erweiterungen, zusätzliche Korrekturschleifen und Fremdleistungen erfolgen nach vorheriger Zustimmung.</p></details><details><summary>Gibt es ein Widerrufsrecht?</summary><p>Bei Verbraucherverträgen gelten die gesetzlichen Rechte. Vor Beginn einer Fernwartung oder eines außerhalb der Geschäftsräume geschlossenen Auftrags informieren wir gegebenenfalls gesondert über Widerruf und einen ausdrücklich gewünschten vorzeitigen Leistungsbeginn.</p></details><details><summary>Sind die Preise verbindlich?</summary><p>Die veröffentlichten Standardpreise gelten für die beschriebenen Leistungen. Bei individuellen Projekten gilt das vor Auftragserteilung vereinbarte Angebot. Ein Vertrag entsteht nicht allein durch das Absenden einer Anfrage.</p></details></section>
    <section class="op-final"><h2>Dein Anliegen. Ein klarer Preis.</h2><p>Erzähl uns, was du brauchst. Wir sagen dir vorher, welche Lösung sinnvoll ist und welche Kosten entstehen.</p><a class="op-btn" href="<?php echo esc_url(home_url('/contact-me/')); ?>">Jetzt unverbindlich anfragen</a></section>
  </main>
  <?php return ob_get_clean();
});
add_action('wp_head', function () {
 if (is_admin() || !is_page(ODPC_PRICES_SLUG)) return; ?>
 <style id="odpc-prices-style">
 .odpc-pricing{--op-bg:#f3f2ef;--op-card:#fff;--op-ink:#1b2838;--op-sub:#536071;--op-border:#d9dee4;--op-accent:#9c795f;max-width:1170px;margin:0 auto;padding:20px 0 55px;font:400 16px/1.65 system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:var(--op-ink)}
 .odpc-pricing *{box-sizing:border-box}.odpc-pricing h1,.odpc-pricing h2,.odpc-pricing h3{font-family:inherit;line-height:1.18;letter-spacing:-.025em}.odpc-pricing h2{font-size:clamp(25px,3.3vw,37px);color:var(--op-ink)}.odpc-pricing h3{font-size:22px;color:var(--op-ink)}.odpc-pricing p{margin:0 0 15px}.odpc-pricing a{text-decoration:none}.op-hero{border-radius:20px;background:linear-gradient(120deg,#142338,#25374c 75%,#3e3533);color:#f5f3ef;padding:clamp(25px,5vw,60px);border:1px solid #5b6270}.op-hero h1{font-size:clamp(30px,4.6vw,52px);color:#fff!important;max-width:850px;margin:10px 0 18px}.op-hero p{max-width:790px;font-size:clamp(16px,2vw,19px);color:#e6e8ec}.op-kicker,.op-heading>span{font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#c9a88d}.op-actions{display:flex;flex-wrap:wrap;gap:11px;margin-top:24px}.op-btn{display:inline-flex;justify-content:center;align-items:center;min-height:45px;padding:11px 20px;background:#c9a88d;color:#17202c!important;border-radius:9px;font-weight:750;border:1px solid #c9a88d}.op-outline{background:transparent;color:#fff!important;border-color:#aeb8c4}.op-section{margin-top:45px}.op-heading{max-width:780px;margin-bottom:23px}.op-heading h2{margin:7px 0 12px}.op-heading p{color:var(--op-sub)}.op-grid{display:grid;gap:16px}.op-two{grid-template-columns:repeat(2,minmax(0,1fr))}.op-three{grid-template-columns:repeat(3,minmax(0,1fr))}.op-card{background:var(--op-card);border:1px solid var(--op-border);border-radius:17px;padding:clamp(19px,3vw,30px);box-shadow:0 5px 20px rgba(20,30,45,.035)}.op-card h3{margin:9px 0 12px}.op-tag{display:inline-block;background:#e8edf3;color:#27435f;padding:5px 10px;border-radius:50px;font-size:12px;font-weight:750}.op-price{font-size:clamp(33px,4vw,45px);font-weight:800;letter-spacing:-.04em;color:#1c3957;margin:8px 0 14px}.op-price small{font-size:14px;font-weight:500;letter-spacing:0;color:#596678}.op-card ul{margin:16px 0 18px;padding-left:21px}.op-card li{margin:6px 0}.op-card>a{font-weight:750;color:#245985}.op-small,.op-note{font-size:13px;color:var(--op-sub)}.op-note{margin:14px 0 0}.op-split{display:grid;grid-template-columns:1.2fr 1fr;gap:25px;align-items:center}.op-soft{padding:25px;border-radius:16px;background:#e8edf3;border:1px solid #cad4e0}.op-soft h3{margin-top:0}.op-terms details{background:var(--op-card);border:1px solid var(--op-border);border-radius:11px;margin:9px 0;padding:15px 19px}.op-terms summary{cursor:pointer;font-weight:750}.op-terms details p{margin:11px 0 0;color:var(--op-sub)}.op-final{background:#192b3e;border-radius:18px;padding:clamp(25px,5vw,45px);color:#f7f7f4;margin-top:44px}.op-final h2{color:#fff}.op-final p{color:#e5e9ef}
 html[data-odpc-appearance=green] .odpc-pricing{--op-bg:#f5efdf;--op-card:#fff9ec;--op-ink:#182e24;--op-sub:#385447;--op-border:#78917b;--op-accent:#28513d}
 html[data-odpc-appearance=green] .op-hero,html[data-odpc-appearance=green] .op-final{background:#fff9ec!important;border:1px solid #78917b!important;color:#171b18!important}
 html[data-odpc-appearance=green] .op-hero h1,html[data-odpc-appearance=green] .op-final h2,html[data-odpc-appearance=green] .op-hero p,html[data-odpc-appearance=green] .op-final p{color:#171b18!important}
 html[data-odpc-appearance=green] .op-kicker,html[data-odpc-appearance=green] .op-heading>span{color:#28513d}
 html[data-odpc-appearance=green] .op-card,html[data-odpc-appearance=green] .op-terms details,html[data-odpc-appearance=green] .op-soft{background:#fff9ec!important;border-color:#78917b!important}
 html[data-odpc-appearance=green] .op-tag{background:#e4eadc;color:#28513d}
 html[data-odpc-appearance=green] .op-price,html[data-odpc-appearance=green] .op-card>a{color:#28513d}
 html[data-odpc-appearance=green] .op-btn{background:#28513d;color:#fff!important;border-color:#28513d}
 html[data-odpc-appearance=green] .op-outline{background:transparent;color:#28513d!important}
 @media(max-width:800px){.op-three{grid-template-columns:1fr 1fr}.op-split{grid-template-columns:1fr}}
 @media(max-width:620px){.op-two,.op-three{grid-template-columns:1fr}.odpc-pricing{padding-top:10px}.op-hero{border-radius:14px}.op-actions .op-btn{width:100%}}
 </style><?php
}, 100);
add_action('wp_footer', function () {
 if (is_admin()) return; ?>
 <script id="odpc-prices-nav">(function(){function add(){var ul=document.querySelector('#odpc-navigation .odpc-menu-list');if(!ul||ul.querySelector('[data-odpc-prices]'))return;var li=document.createElement('li');li.setAttribute('data-odpc-prices','1');var a=document.createElement('a');a.href=<?php echo wp_json_encode(home_url('/'.ODPC_PRICES_SLUG.'/')); ?>;a.textContent='Preise & Angebote';if(location.pathname.replace(/\/+$/,'')===new URL(a.href).pathname.replace(/\/+$/,''))a.setAttribute('aria-current','page');li.appendChild(a);var contact=ul.querySelector('a[href*="/contact-me/"]');if(contact&&contact.parentElement)ul.insertBefore(li,contact.parentElement);else ul.appendChild(li)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',add);else add();window.addEventListener('load',add)})();</script>
 <?php
}, 115);
