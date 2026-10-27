<?php
/**
 * Plugin Name: ODPC Helper Monkey – Grün & Premium Blau
 * Description: Shared offer map and reciprocal links for ODPC and Helper Monkey.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;
function om_catalog() {
 return [
  'praelex-ai'=>['Anwalt','PraeLex.ai','Akten, Sachverhalte und Entwürfe für die anwaltliche Prüfung. Persönlich an Kanzleiabläufe und Vorlagen angepasst.'],
  'praetax'=>['Buchhaltung & Lohn','PraeTax','Beleg-, Buchhaltungs- und Lohnprozesse analysieren und passende KI-Abläufe planen und einrichten. Fachliche Freigaben bleiben bei den Verantwortlichen.'],
  'praedium'=>['Immobilien & Vermarktung','PRAEDIUM','Objektdaten, Exposés, Interessentenansprache und Vermarktungsabläufe für Makler und Eigentümer verbinden.'],
  'initiq'=>['Unternehmen starten & organisieren','initIQ','Die digitale Arbeitsbasis aufbauen: Konten, Geräte, Dokumente, Prozesse und individuell eingerichtete KI-Mitarbeiter.'],
  'webdesign'=>['Webdesigner & Webmaster','Webdesign','Websites persönlich aufbauen und betreuen. Wiederkehrende Inhalts- und Webmaster-Aufgaben als geprüfte KI-Abläufe einrichten.'],
  'ki-gestuetzte-dienstleistungen-automatisierung'=>['Marketing & Büro','KI-Services','Beiträge, Kommunikation und administrative Aufgaben als passende Arbeitsabläufe einrichten – mit Vorlagen, Einweisung und vereinbarten Freigaben.'],
 ];
}
function om_symbol() { return 'https://ai.odpc.de/wp-content/themes/helpermonkey-business-v3/assets/brand/helper-monkey-symbol.png'; }
function om_paths() { ?>
<section class="om-paths" id="om-wege" aria-label="Zwei Wege mit helper monkey">
 <div class="om-choice om-green"><strong>GRÜN · Selbst einrichten</strong><p>Für Einsteiger und deinen Alltag: KI verstehen, Aufträge formulieren und mit kostenlosen Monkeys ausprobieren.</p><a href="https://ai.odpc.de/start/#monkeys">Kostenlos selbst starten →</a></div>
 <div class="om-choice om-blue"><strong>BLAU · Persönlich eingerichtet</strong><p>Für dein Business: Oliver richtet passende Agenten und Arbeitsabläufe für dein Unternehmen ein.</p><a href="#om-premium">Premium-Pakete kennenlernen →</a></div>
</section>
<?php }
function om_premium($selected='') {
 $catalog=om_catalog();$items=isset($catalog[$selected])?[$selected=>$catalog[$selected]]:$catalog;
 ?>
<section class="om-premium" id="om-premium" aria-labelledby="om-premium-title" data-offer-version="1.0.0">
 <p class="om-kicker">BLAU · HELPER · MONKEY PREMIUM · PERSÖNLICHE EINRICHTUNG</p>
 <h2 id="om-premium-title"><?php echo isset($catalog[$selected])?'Dein Premium-Monkey: '.esc_html($catalog[$selected][0]):'Dein Business. Dein persönlich eingerichteter Monkey.'; ?></h2>
 <p>Du möchtest KI in deinem Betrieb einsetzen? Ich bin Oliver Dering. Ich kläre mit dir die Aufgabe, richte die passenden Verbindungen und Arbeitsabläufe ein und zeige dir, wie du deinen Monkey führst und weiterentwickelst – vor Ort oder gemeinsam online.</p>
 <p class="om-price"><strong>Kostenpflichtige persönliche Leistung.</strong> Du bezahlst für Analyse, Einrichtung, Anpassung, Einweisung und vereinbarte Übergabe. Umfang und Preis werden vor Beauftragung geklärt; bestehende Anbieter- und Betriebskosten werden gesondert besprochen.</p>
 <div class="om-grid">
 <?php foreach($items as $slug=>$item): ?>
  <article class="om-card" data-product="<?php echo esc_attr($slug); ?>"><img class="om-blue-monkey" src="<?php echo esc_url(om_symbol()); ?>" alt="helper monkey Premium – Originalmotiv mit blauen Akzenten" width="80" height="80" loading="lazy"><p class="om-kicker">HELPER · MONKEY PREMIUM</p><h3><?php echo esc_html($item[0]); ?></h3><p class="om-product"><?php echo esc_html($item[1]); ?></p><p><?php echo esc_html($item[2]); ?></p>
  <?php if($selected!==$slug): ?><a class="om-button" href="<?php echo esc_url('https://odpc.de/'.$slug); ?>"><?php echo esc_html($item[1]); ?> auf odpc.de ansehen →</a><?php else: ?><a class="om-button" href="<?php echo esc_url('https://wa.me/492654882183?text='.rawurlencode('Hallo Oliver, ich interessiere mich für die persönliche Einrichtung von helper · monkey Premium – '.$item[1].'.')); ?>" target="_blank" rel="noopener noreferrer">Persönliche Einrichtung besprechen ↗</a><?php endif; ?></article>
 <?php endforeach; ?>
 </div>
 <p class="om-process"><strong>So läuft es:</strong> Bedarf klären → Einrichtung und Anpassung → gemeinsam mit Beispieldaten prüfen → Einweisung und Übergabe. Funktionen und Integrationen werden je Projekt geprüft. Rechts- und Steuerentscheidungen bleiben bei qualifizierten Fachleuten.</p>
 <div class="om-links"><a href="https://odpc.de/struktur-portfolio">ODPC-Portfolio ansehen →</a><a href="https://ai.odpc.de/start/#monkeys">Lieber erst kostenlos ausprobieren →</a><a href="https://odpc.de/prompt-engineering-digitale-mitarbeiter-ki-sinnvoll-nutzen">KI führen und gute Prompts schreiben →</a></div>
</section>
<?php }
function om_learning() { ?>
<section class="om-learning" id="om-lernen"><p class="om-kicker">VERSTEHEN · AUSPROBIEREN · AUSBAUEN</p><h2>Du lernst, deine KI sinnvoll einzusetzen.</h2><p>Formuliere klare Aufgaben, gib passende Informationen und prüfe die Ergebnisse. Mit Vorlagen, Beispielen und Korrekturen passt du deine KI an deine Arbeitsweise an. Persönliche Business-Einrichtung findest du bei den blauen Premium-Monkeys.</p><div class="om-links"><a href="https://odpc.de/prompt-engineering-digitale-mitarbeiter-ki-sinnvoll-nutzen">Prompt Engineering verständlich erklärt →</a><a href="https://odpc.de/einsatz-von-ki-je-nach-branche">KI nach Branche entdecken →</a></div></section>
<?php }
function om_on_odpc() {
 if(is_admin()||strtolower($_SERVER['HTTP_HOST']??'')!=='odpc.de')return false;
 return is_front_page()||is_page(array_merge(array_keys(om_catalog()),['struktur-portfolio','prompt-engineering-digitale-mitarbeiter-ki-sinnvoll-nutzen','einsatz-von-ki-je-nach-branche']));
}
add_filter('the_content',function($content){
 if(!om_on_odpc()||!is_main_query()||!in_the_loop()||strpos($content,'id="om-premium"')!==false)return $content;
 $slug=(string)get_post_field('post_name',get_queried_object_id());
 ob_start();om_paths();$paths=ob_get_clean();ob_start();om_premium($slug);$premium=ob_get_clean();
 return is_front_page()||is_page('struktur-portfolio')?$paths.$content.$premium:$paths.$premium.$content;
},120);
add_action('wp_head',function(){
 if(is_admin()||(!om_on_odpc()&&strtolower($_SERVER['HTTP_HOST']??'')!=='ai.odpc.de'))return;
 ?>
<style id="om-offer-styles">
.om-paths,.om-premium,.om-learning{font:16px/1.65 system-ui,sans-serif;box-sizing:border-box;max-width:1180px;margin:38px auto;padding:32px;scroll-margin-top:125px;overflow-wrap:anywhere}.om-paths *,.om-premium *,.om-learning *{box-sizing:border-box}.om-paths{display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:0 28px}.om-choice{border:1px solid;border-radius:18px;padding:26px}.om-choice p{margin:12px 0}.om-green{color:#164b32;background:#eef7f0;border-color:#b6ceba}.om-blue{color:#1e3a5f;background:#eaf1f8;border-color:#abc1db}.om-choice a{display:inline-block;color:inherit!important;font-weight:750;min-height:44px;padding:8px 0}.om-premium{color:#183653;background:linear-gradient(135deg,#eaf1f8,#f7faff);border:1px solid #b5c9df;border-radius:24px}.om-premium h2,.om-learning h2{font-size:clamp(27px,3.6vw,44px);line-height:1.18;margin:0 0 20px;color:#1e3a5f!important;letter-spacing:-.035em}.om-kicker{font:750 11px/1.6 system-ui!important;letter-spacing:.09em;color:#315c88!important;margin:0 0 12px!important}.om-premium p{margin:0 0 18px;color:#35516e}.om-price{padding:18px;background:#fff;border-left:4px solid #356a9a;border-radius:8px}.om-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin:28px 0}.om-card{padding:25px;border:1px solid #bfd0e3;border-radius:18px;background:#fff;display:flex;flex-direction:column;align-items:flex-start}.om-card h3{color:#1e3a5f!important;font-size:23px;line-height:1.25;margin:0 0 8px;letter-spacing:-.02em}.om-card .om-product{font-weight:750;color:#356a9a;margin-bottom:12px}.om-blue-monkey{display:block;width:80px!important;height:80px!important;object-fit:contain;border-radius:50%;margin-bottom:18px;filter:url(#om-blue-accents)}.om-card .om-button{margin-top:auto;display:inline-block;background:#1e3a5f!important;color:#fff!important;padding:12px 16px;border-radius:9px;font:700 14px/1.5 system-ui;text-decoration:none;min-height:44px}.om-process{border-top:1px solid #bfd0e3;padding-top:22px}.om-links{display:flex;flex-wrap:wrap;gap:12px 25px}.om-links a{color:#1e3a5f!important;font-weight:700;min-height:44px;padding:8px 0}.om-learning{background:#fff;border:1px solid #d8e4d6;border-radius:20px}.om-learning h2{color:#164b32!important}.om-learning .om-links a{color:#164b32!important}.om-premium :focus-visible,.om-paths :focus-visible,.om-learning :focus-visible{outline:3px solid #c48b15;outline-offset:4px}@media(max-width:760px){.om-paths,.om-grid{grid-template-columns:1fr}.om-paths{padding:0 22px}.om-premium,.om-learning{margin:28px 16px;padding:25px 21px}.om-choice,.om-card{padding:23px}.om-links{display:block}.om-links a{display:block}.om-premium h2,.om-learning h2{font-size:29px}}
</style>
<?php },9998);
add_action('wp_footer',function(){ ?>
<svg width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute;pointer-events:none"><defs><filter id="om-blue-accents" color-interpolation-filters="sRGB"><feColorMatrix in="SourceGraphic" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 -10 10 0 0 -1" result="greenMask"/><feColorMatrix in="SourceGraphic" type="hueRotate" values="105" result="blueArt"/><feComposite in="blueArt" in2="greenMask" operator="in" result="blueOnly"/><feComposite in="SourceGraphic" in2="greenMask" operator="out" result="originalRest"/><feMerge><feMergeNode in="originalRest"/><feMergeNode in="blueOnly"/></feMerge></filter></defs></svg>
<?php },60);
