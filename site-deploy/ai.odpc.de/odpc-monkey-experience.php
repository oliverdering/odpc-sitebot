<?php
/**
 * Plugin Name: Helper Monkey Guided Experience
 * Description: Conversation-first entry and focused agent overview using the existing brand.
 * Version: 1.2.0
 */
if (!defined('ABSPATH')) exit;
function hm_experience_route() {
 if (is_admin() || is_feed() || is_preview()) return '';
 if (is_front_page()) return 'home';
 if (is_page('start')) return 'agents';
 if (is_page(['connector-einrichten','helper-monkey-einfach-starten-sprachassistent'])) return 'connector';
 if(is_page()) return 'content';
 return '';
}
add_action('wp_enqueue_scripts', function () {
 wp_enqueue_script('hm-dialog-copy', content_url('/mu-plugins/odpc-monkey-experience/dialog-copy.js'), [], (string)filemtime(__DIR__.'/odpc-monkey-experience/dialog-copy.js'), false);
 wp_enqueue_script('hm-guide-core', content_url('/mu-plugins/odpc-monkey-experience/guide-core.js'), ['hm-dialog-copy'], (string)filemtime(__DIR__.'/odpc-monkey-experience/guide-core.js'), false);
 wp_enqueue_style('hm-experience', content_url('/mu-plugins/odpc-monkey-experience/experience.css'), [], (string)filemtime(__DIR__.'/odpc-monkey-experience/experience.css'));
}, 20);
add_filter('template_include', function ($template) {
 return hm_experience_route() ? __DIR__.'/odpc-monkey-experience/page.php' : $template;
}, 99);
add_filter('pre_get_document_title', function($title){
 $r=hm_experience_route();
 return $r==='home'?'helper · monkey © – Deine KI. Dein Alltag. Einfach verbunden.':($r==='agents'?'Die Monkeys – Aufgaben und Entwicklungsstand | helper · monkey':($r==='connector'?'KI einrichten mit Monkey – Gespräch, Connector und persönliche Hilfe':$title));
},99);
add_action('wp_footer', function(){ ?>
<script>
(function(){
 document.querySelectorAll('[data-monkey-open]').forEach(function(b){b.addEventListener('click',function(e){e.preventDefault();var v=document.getElementById('odpc-voice-open');if(v){v.dataset.startMode=b.dataset.monkeyMode||'text';v.click();}else location.href='/helper-monkey-einfach-starten-sprachassistent/';});});
})();
</script>
<?php },99);

add_filter('the_content', function($content){
 if(!hm_experience_route() || !class_exists('DOMDocument')) return $content;
 $content=strtr($content, hm_experience_copy());
 $content=preg_replace('/individuelle ODPC-Beratung(?: bzw\. eingerichtete Lösungen auf Anfrage)?/iu','vorhandene Anleitungen und einzeln geprüfte Arbeitsabläufe',$content);
 $dom=new DOMDocument();$prior=libxml_use_internal_errors(true);
 $dom->loadHTML('<?xml encoding="UTF-8"><div id="hm-preserved-content">'.$content.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
 libxml_clear_errors();libxml_use_internal_errors($prior);
 $remove=[];
 foreach($dom->getElementsByTagName('a') as $link){
  $url=$link->getAttribute('href');$text=$link->textContent;
  $agent=is_page('webmaster-agent')?'web':(is_page('finanzen-vertragscheck')?'finance':(is_page('konfliktlotse-anwaltsvorarbeit')?'legal':''));
  if($agent && preg_match('/Kostenlos starten.*Connector|Kostenpflichtiges Paket anfragen/u',$text)){
   $link->setAttribute('href',home_url('/connector-einrichten/?agent='.$agent));$link->textContent='Arbeitsauftrag starten →';continue;
  }
  if(preg_match('/initIQ|Unternehmensberatung per WhatsApp|Einrichtung gegen Honorar|Webdesign und Leistungen|Webdesign und Live-Referenzen|Remote-Unterstützung direkt von ODPC|Kunden\s*(?:&|und)\s*Referenzen/u',$text)){
   $link->setAttribute('href','https://odpc.de/'.(stripos($text,'initIQ')!==false?'initiq':(stripos($text,'Webdesign')!==false?'webdesign':'struktur-portfolio#om-premium')));$link->textContent='Persönliche Einrichtung · helper monkey Premium →';continue;
  }
  // Public downloads stay on our domain; repository and release evidence are internal.
  $host=strtolower((string)parse_url($url,PHP_URL_HOST));
  if(in_array($host,['github.com','www.github.com','raw.githubusercontent.com','objects.githubusercontent.com'],true)){
   $file=basename((string)parse_url($url,PHP_URL_PATH));
   $downloads=['helpermonkey-connector-multilingual-v3.1.zip','helpermonkey-lebenslauf-multilingual-v3.1.zip','helpermonkey-bewerbung-multilingual-v3.1.zip','helpermonkey-postfach-multilingual-v3.1.zip','helpermonkey-gespraechsuebersetzer-multilingual-v1.0.zip','helpermonkey-gespraechsuebersetzer-v1.0.txt','helpermonkey-sprachmodul-v1.0.txt'];
   if($host==='raw.githubusercontent.com' && strpos((string)parse_url($url,PHP_URL_PATH),'/oliverdering/odpc-sitebot/main/downloads/')===0 && in_array($file,$downloads,true)){
    $link->setAttribute('href',content_url('/mu-plugins/odpc-monkey-experience/downloads/'.$file));
   }else{$remove[]=$link;}
   continue;
  }
 }
 foreach($remove as $link)if($link->parentNode)$link->parentNode->removeChild($link);
 $root=$dom->getElementById('hm-preserved-content');if(!$root)return $content;
 $result='';foreach($root->childNodes as $node)$result.=$dom->saveHTML($node);
 return $result;
},100);

function hm_experience_copy(){
 $copy=[
  'Kostenlose Starter und Sprachmodul; Premium-Angebote auf Anfrage.'=>'Kostenlose Arbeitsaufträge und Sprachmodul; echte Kontozugriffe separat einrichten.',
  'Kostenpflichtiges Fachangebot'=>'Textstart und Systemzugang',
  'Premium-Fachmodul · Umfang auf Anfrage'=>'Textvorlage · Webseitenzugang separat prüfen',
  'Remote-Unterstützung direkt von ODPC'=>'Persönliche Hilfe über WhatsApp',
  'Vier Gratis-Agenten entdecken'=>'Alle Monkeys entdecken',
  'Vier Gratis-Agenten herunterladen'=>'Alle Monkeys und Arbeitsaufträge',
  'Connector & vier Gratis-Agenten öffnen'=>'Monkeys und Arbeitsaufträge öffnen',
  'Dein KI-Team startet mit einem Klick'=>'Starte mit einer konkreten Aufgabe',
  'Starte mit dem kostenlosen Connector und lade vier kostenlose KI-Agenten ohne Anmeldung und ohne Formular direkt herunter.'=>'Wähle deinen Monkey und bereite einen Arbeitsauftrag für dein eigenes KI-Konto vor. Für erste Textentwürfe brauchst du keine Kontoverbindung.',
  '4 Gratis-Agenten, Premium-Katalog und 4 Browser-Werkzeuge'=>'Monkeys, Arbeitsaufträge und vier Browser-Werkzeuge',
  'Kostenlose Starter und Premium-Funktionen benötigen gegebenenfalls eigene Autorisierung oder Vereinbarung.'=>'Kontozugriffe benötigen passende Verbindungen und deine Freigaben.',
  'Freie Starter und Premium-Module'=>'Kostenlose Arbeitsaufträge und geprüfte Zugänge',
  'Leistungsumfang, benötigte Autorisierungen und mögliche Drittanbietergebühren werden vor dem produktiven Einsatz geprüft.'=>'Der Textstart ist kostenlos. Kontozugriffe hängen von deinen vorhandenen Diensten, deren Funktionen und deinen Freigaben ab.',
 ];
 if(is_page('webmaster-agent'))$copy += [
  'Der kostenpflichtige WordPress-Webmaster unterstützt Seitenstruktur, SEO, Content, Navigation und kontrollierte WordPress-Prozesse. Leistungsumfang und Admin-Anbindung werden für das jeweilige Projekt vereinbart.'=>'Der Webmaster-Monkey bereitet Inhalte und konkrete SEO- oder WordPress-Änderungen als Entwurf vor. Du startest mit einem kostenlosen Arbeitsauftrag. Zum Bearbeiten deiner Webseite ist ein eigener freigegebener Zugang erforderlich.',
  'Der WordPress-Webmaster ist ein kostenpflichtiges Profi-Angebot mit klar geregeltem Funktionsumfang. Die Einrichtung von Admin-Verbindungen und automatisierten Änderungen erfolgt erst nach Prüfung und Freigabe. Ein direkter Gratis-Download ist nicht Bestandteil dieses Angebots.'=>'Der kostenlose Textstart klärt zuerst deine Webseite und die gewünschte Änderung. Deine KI erstellt einen Entwurf. Direkte Änderungen sind erst mit einem tatsächlich verfügbaren Webseitenzugang und deiner konkreten Freigabe möglich. Ein Prompt installiert keine WordPress-Verbindung.',
  'Was ist der Unterschied zwischen gratis Connector und Webmaster-Agent?'=>'Was unterscheidet Connector und Webmaster-Monkey?',
  'Der Connector prüft Verbindungen und Berechtigungen. Der kostenpflichtige Webmaster-Agent bietet vereinbarte WordPress-, Content- und Administrationsabläufe.'=>'Der Connector erklärt die Einrichtung deiner Verbindungen. Der Webmaster-Monkey bearbeitet einen konkreten Webseitenauftrag mit den Werkzeugen, die in deinem KI-Konto tatsächlich verfügbar sind.',
  'Das Fachangebot lässt sich mit Sprachwunsch anfragen; Sprachqualität, Dokumentation und vorhandene Connector-Funktionen werden je Projekt geklärt.'=>'Der Arbeitsauftrag enthält deine gewählte Sprache. Die Antwortqualität und verfügbare Werkzeuge hängen von deinem KI-Konto ab.',
 ];
 if(is_page('finanzen-vertragscheck'))$copy += [
  'Für Haushaltsplanung, Vertragsvergleich und Belegorganisation können individuelle kostenpflichtige Agentenpakete angefragt werden. Die aktuell vorhandene Office-Datei gehört nicht zum kostenlosen Angebot. Steuer- und buchhaltungsrechtlich geschützte Leistungen bleiben qualifizierten Fachkräften vorbehalten.'=>'Der kostenlose Arbeitsauftrag hilft beim Ordnen ausgewählter Belege und Vertragsangaben. Beginne mit Beispieldaten oder anonymisierten Unterlagen. Es werden keine Zahlungen, Kündigungen oder Vertragsabschlüsse ausgeführt. Steuerliche und rechtliche Bewertungen bleiben qualifizierten Fachkräften vorbehalten.',
  'Fachpaket und Datenfreigabe klären'=>'Textvorlage und Datenfreigabe prüfen',
 ];
 if(is_page('konfliktlotse-anwaltsvorarbeit'))$copy += [
  'Ein kostenpflichtiges, individuell abgestimmtes Paket kann Sachverhalte, Chronologien und Dokumente für ein sachliches Gespräch oder eine anschließende anwaltliche Prüfung aufbereiten. Eine verbindliche Rechtsberatung oder neutrale Mediation mit allen Beteiligten ersetzt es nicht.'=>'Der kostenlose PraeLex-Arbeitsauftrag strukturiert freigegebene Sachverhalte, Chronologien und offene Fragen. Er ist eine Textvorlage für die anwaltliche Vorarbeit. Ein produktives Kanzleisystem, verbindliche Rechtsberatung oder neutrale Mediation sind damit nicht freigegeben.',
 ];
 if(is_page('sprachen'))$copy += [
  '(Premium)'=>'(Textstart)',
  '03 · Premium-Agenten – mehrsprachig anfragen'=>'03 · Weitere Monkeys in deiner Sprache starten',
  'WordPress-Webmaster, Marketing, Büro- und Dokumenten-Agenten sind <strong>kostenpflichtige Fachprodukte</strong>.'=>'Für Webmaster, Marketing und Büro gibt es kostenlose Arbeitsaufträge für dein vorhandenes KI-Konto.',
  'Nenne bei der Anfrage deine Arbeitssprache und dein Einsatzland. Preise und Bezug werden vor der Auslieferung geklärt; aktuell ist kein automatischer Premium-Checkout aktiviert.'=>'Wähle deinen Monkey im Katalog. Kontozugriffe und Veröffentlichungen brauchen separat eingerichtete Verbindungen und Freigaben.',
  'Premium-Agent in meiner Sprache anfragen'=>'Passenden Monkey starten',
  'Kann ich kostenpflichtige Agenten in meiner Sprache anfragen?'=>'Kann ich weitere Monkeys in meiner Sprache nutzen?',
  'Ja. Im Sprachportal lassen sich Premium-Agent, gewünschte Sprache und Einsatzgebiet gemeinsam für eine Angebotsanfrage übernehmen.'=>'Ja. Kopiere den Sprachstart in dein KI-Konto und starte dort den Arbeitsauftrag deines Monkeys. Die Sprachqualität hängt vom verwendeten KI-Modell ab.',
 ];
 if(is_page('helper-monkey-fuer-unternehmen-ki-kollegen'))$copy += [
  'Für Buchhaltung, Webdesign, Marketing, Büro und Personal: Wir planen rollenbezogene Helper Monkeys, die Mitarbeitende bei klar abgegrenzten Aufgaben unterstützen. Mit einfacher Einrichtung und optionaler ODPC-Begleitung.'=>'Beginnt mit einer klaren Aufgabe: einen Beitrag entwerfen, Unterlagen ordnen oder ein Dokument vorbereiten. Die kostenlosen Arbeitsaufträge laufen im vorhandenen KI-Konto. Betriebliche Kontozugriffe und der produktive Einsatz müssen separat geprüft werden.',
  'Was kann man derzeit bestellen?'=>'Was kannst du heute nutzen?',
  'Die Preise hängen vom tatsächlichen Integrationsumfang, den benötigten Konten und Drittanbietergebühren ab und werden vor Beauftragung transparent vereinbart.'=>'Für den Einstieg werden keine neuen Abos oder kostenpflichtigen APIs benötigt. Die Funktionen und Limits der bereits vorhandenen Konten gelten weiter.',
  'Individuelle Einrichtung ist heute auf Anfrage möglich. Die technische Verwaltung zentraler Unternehmenslizenzen ist als Produktfunktion in Planung.'=>'Du kannst einen Arbeitsauftrag im freigegebenen KI-Konto nutzen. Eine zentrale Zuweisung, Lizenzverwaltung oder ein Mitarbeiter-Dashboard ist damit nicht eingerichtet.',
  'Unterstützt ODPC die Einführung?'=>'Wie beginnen wir mit einem ersten Workflow?',
  'Ja. Über initIQ können betriebliche Anforderungen erfasst und passende Einführungs- und Schulungsleistungen angefragt werden.'=>'Wählt einen Monkey, testet den Arbeitsauftrag mit Beispieldaten und prüft das Ergebnis. Bei Fragen ist Oliver persönlich über den WhatsApp-Hilfeweg erreichbar.',
 ];
 return $copy;
}
