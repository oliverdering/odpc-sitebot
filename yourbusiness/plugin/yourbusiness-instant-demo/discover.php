<?php
/** YourBusiness: public business-data discovery (no paid APIs or Google scraping). */
if (!defined('ABSPATH')) exit;
add_action('wp_ajax_yb_discover', 'yb_discover_company');
add_action('wp_ajax_nopriv_yb_discover', 'yb_discover_company');

function yb_discover_company() {
  if (!check_ajax_referer('yb_discover', 'nonce', false)) {
    wp_send_json_error(['message' => 'Sicherheitsprüfung fehlgeschlagen. Seite bitte neu laden.'], 403);
  }
  $name = sanitize_text_field(wp_unslash($_POST['company'] ?? ''));
  $city = sanitize_text_field(wp_unslash($_POST['city'] ?? ''));
  $name = mb_substr($name, 0, 100);
  $city = mb_substr($city, 0, 100);
  if (mb_strlen($name) < 3 || mb_strlen($city) < 2) {
    wp_send_json_error(['message' => 'Bitte den Firmennamen und den Ort eingeben.'], 400);
  }

  $cache = 'yb_lookup_v3_' . md5(mb_strtolower($name . '|' . $city));
  $saved = get_transient($cache);
  if (is_array($saved)) wp_send_json_success($saved);

  // Public anonymous form: rate limit before any external request.
  $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');
  $key = 'yb_limit_' . md5($ip);
  $hits = (int) get_transient($key);
  if ($hits >= 6) {
    wp_send_json_error(['message' => 'Zu viele Abfragen. Bitte später erneut versuchen.'], 429);
  }
  set_transient($key, $hits + 1, MINUTE_IN_SECONDS);

  $name_slug = sanitize_title($name);
  $city_slug = sanitize_title($city);
  // Dated factual research snapshot for first real-world demonstration.
  // Not an official Google Business API result. All uncertain details require customer approval.
  if ($name_slug === 'kleine-freiheit' && $city_slug === 'koblenz') {
    $result = [
      'candidates' => [[
        'name' => 'Kleine Freiheit', 'city' => 'Koblenz',
        'address' => 'Altenhof 6, 56068 Koblenz',
        'sector' => 'Gastronomie', 'phone' => '+49 1573 2500137',
        'email' => '', 'website' => '',
        'google' => 'https://www.google.com/maps/search/?api=1&query=Kleine%20Freiheit%20Altenhof%206%20Koblenz',
        'services' => "Gemütliche Altstadtkneipe\nBier, Longdrinks und alkoholfreie Getränke (Sortiment bestätigen)\nGesellige Treffen und Musik\nVeranstaltungen und Specials (aktuelle Termine bestätigen)",
        'intro' => 'Willkommen in der Kleinen Freiheit! Mitten im historischen Herzen von Koblenz, direkt am Altenhof 6, erwartet dich ein Ort für echte Geselligkeit, Musik und gemeinsame Abende. Ob auf ein Feierabendbier oder zum Treffen mit Freunden: Komm rein und genieß deine kleine Freiheit.',
        'features' => ['Gemütliche Altstadtkneipe mit Schlager- und Kultbar-Atmosphäre', 'Musik, Treffen mit Freunden und gelegentliche Veranstaltungen (Termine bestätigen)', 'Bier, Longdrinks, Wein und alkoholfreie Getränke (Sortiment bestätigen)'],
        'history' => 'Das Wohn- und Geschäftshaus am Altenhof 6 entstand 1954 auf einem erhaltenen historischen Bruchsteinkeller; das Erdgeschoss war bereits damals als Gaststätte konzipiert. Das vorliegende Dossier nennt 2000 als Beginn des Lokals unter dem Namen Kleine Freiheit. Dieses Gründungsjahr ist vor Veröffentlichung noch unabhängig zu bestätigen.',
        'hours' => 'Mo–Do 10:00–23:00; Fr–Sa 10:00–04:00; So geschlossen',
        'imprint' => '',
        'host_draft' => 'Kalle (Gastgeberangabe aus dem Dossier, voller Name unbekannt)',
        'contact_alternatives' => ['01573 2500137', '0261 50040640'],
        'payment_draft' => 'Nur Barzahlung (vor Ort bestätigen)',
        'access_draft' => 'Hunde erlaubt; Eingang nicht barrierefrei (vor Ort bestätigen)',
        'menu_notice' => 'Die folgenden Produkte und Preise stammen aus einer unverbindlichen Getränkekarten-Vorlage im Komplettpaket v2. Es ist KEINE bestätigte Getränkekarte. Preise nicht für eine veröffentlichte Kundenseite übernehmen.',
        'menu_sections' => [
          ['title' => 'Bier & Biermischgetränke (Vorschlag)', 'items' => [
            ['name' => 'Bitburger Pils', 'size' => '0,3 / 0,5 l', 'sample_price' => '3,20 / 4,80 €'],
            ['name' => 'Kölsch', 'size' => '0,2 l', 'sample_price' => '2,40 €'],
            ['name' => 'Hefeweizen', 'size' => '0,5 l', 'sample_price' => '4,80 €'],
            ['name' => 'Radler / Alsterwasser', 'size' => '0,3 / 0,5 l', 'sample_price' => '3,20 / 4,80 €'],
            ['name' => 'Alkoholfreies Bier', 'size' => '0,33 l', 'sample_price' => '3,50 €']
          ]],
          ['title' => 'Longdrinks & Shots (Vorschlag)', 'items' => [
            ['name' => 'Klassische Longdrinks', 'size' => '0,3 l / 4 cl', 'sample_price' => '7,50 €'],
            ['name' => 'Aperol Spritz / Lillet Wild Berry', 'size' => '0,25 l', 'sample_price' => '6,90 €'],
            ['name' => 'Shots & Liköre', 'size' => '2 cl', 'sample_price' => '2,50 €']
          ]],
          ['title' => 'Wein & Alkoholfreies (Vorschlag)', 'items' => [
            ['name' => 'Wein (Riesling / Grauburgunder / Spätburgunder)', 'size' => '0,2 l', 'sample_price' => '4,90 €'],
            ['name' => 'Weinschorle', 'size' => '0,2 l', 'sample_price' => '4,20 €'],
            ['name' => 'Softdrinks', 'size' => '0,3 l', 'sample_price' => '3,20 €'],
            ['name' => 'Apfelsaftschorle / Mineralwasser', 'size' => '0,3 l', 'sample_price' => '3,00 €']
          ]]
        ],
        'publication_checks' => [
          'Vollständigen bürgerlichen Namen des aktuell verantwortlichen Betreibers erfragen',
          'Richtige Telefonnummer(n), E-Mail-Adresse und Öffnungszeiten bestätigen',
          'Getränkesortiment, Mengen, Bruttopreise und Zahlungsarten bestätigen',
          'Instagram-/Facebook-Konten und aktuelle Veranstaltungstermine offiziell zuordnen',
          'Rechte an Logos, Fotos und sonstigem Bildmaterial klären',
          'Impressum gemäß tatsächlichem Betreiber sowie Datenschutzerklärung gemäß eingesetzter Technik finalisieren'
        ],
        'source_label' => 'ODPC-Recherche und zwei vom Auftraggeber bereitgestellte PDF-Dossiers (November 2026); unbestätigte Angaben als Entwurf',
        'sources' => [
          ['label' => 'RestaurantGuru (Kontakt, Zeiten, Adresse)', 'url' => 'https://de.restaurantguru.com/Kleine-Freiheit-Koblenz'],
          ['label' => 'Das ist nah (Telefon, Adresse)', 'url' => 'https://www.dasistnah.de/gaststaette-koblenz.html'],
          ['label' => 'wie.de (Standort)', 'url' => 'https://wie.de/poi/kleine-freiheit.11584352641'],
          ['label' => 'Projekt Koblenzer Altstadt (Geschichte Altenhof 6)', 'url' => 'https://reinhard-kallenbach.de/featured_item/altenhof/']
        ],
        'warning' => 'Die vorliegenden PDF-Dossiers liefern Textentwürfe, keine Inhaber- oder Preisbestätigung. Das Gründungsjahr 2000 und Gastgeber Kalle sind noch nicht verifiziert. Zwei Telefonnummern kursieren. Öffnungszeiten, Getränke, Preise und Barzahlungsregel vor Ort bestätigen; Impressum/Datenschutz auf den tatsächlichen Betreiber abstimmen. Bilder nur mit Nutzungsrechten.'
      ]],
      'notice' => 'Öffentliche Referenzdaten gefunden; noch nicht vom Unternehmen bestätigt.'
    ];
    set_transient($cache, $result, 12 * HOUR_IN_SECONDS);
    wp_send_json_success($result);
  }

  // Generic discovery through OpenStreetMap Nominatim, respecting its public usage policy.
  // Cache repeated lookups and allow only one global outgoing request in each short interval.
  if (get_transient('yb_nominatim_busy')) {
    wp_send_json_error(['message' => 'Öffentliche Recherche gerade ausgelastet. Bitte erneut versuchen.'], 429);
  }
  set_transient('yb_nominatim_busy', 1, 2);
  $url = add_query_arg([
    'q' => $name . ', ' . $city . ', Deutschland',
    'format' => 'jsonv2', 'addressdetails' => 1, 'extratags' => 1,
    'namedetails' => 1, 'limit' => 5, 'accept-language' => 'de'
  ], 'https://nominatim.openstreetmap.org/search');
  $response = wp_safe_remote_get($url, [
    'timeout' => 12, 'redirection' => 2,
    'headers' => ['User-Agent' => 'ODPC-YourBusiness/0.2 (https://yourbusiness.odpc.de/)', 'Accept' => 'application/json']
  ]);
  if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
    wp_send_json_error(['message' => 'Die offene Standortsuche ist vorübergehend nicht erreichbar.'], 503);
  }
  $items = json_decode(wp_remote_retrieve_body($response), true);
  if (!is_array($items)) {
    wp_send_json_error(['message' => 'Keine verwertbare Antwort des Kartendienstes.'], 502);
  }
  $candidates = [];
  foreach ($items as $item) {
    if (!is_array($item)) continue;
    $a = is_array($item['address'] ?? null) ? $item['address'] : [];
    $e = is_array($item['extratags'] ?? null) ? $item['extratags'] : [];
    $n = is_array($item['namedetails'] ?? null) ? $item['namedetails'] : [];
    $found = sanitize_text_field($n['name'] ?? explode(',', (string) ($item['display_name'] ?? ''))[0]);
    $found_city = sanitize_text_field($a['city'] ?? $a['town'] ?? $a['village'] ?? $a['municipality'] ?? '');
    // Never silently select an unrelated nearby business with a similar name.
    if (sanitize_title($found) !== $name_slug || sanitize_title($found_city) !== $city_slug) continue;
    $road = sanitize_text_field($a['road'] ?? $a['pedestrian'] ?? '');
    $house = sanitize_text_field($a['house_number'] ?? '');
    $postcode = sanitize_text_field($a['postcode'] ?? '');
    $street = trim($road . ' ' . $house);
    $address = trim(($street ? $street . ', ' : '') . $postcode . ' ' . $found_city);
    $type = sanitize_text_field($item['type'] ?? '');
    $sector = in_array($type, ['pub', 'bar', 'restaurant', 'cafe', 'fast_food', 'biergarten'], true) ? 'Gastronomie' : (
      in_array($type, ['shop', 'supermarket', 'convenience', 'bakery'], true) ? 'Einzelhandel' : 'Dienstleistung'
    );
    $osm_type = sanitize_key($item['osm_type'] ?? '');
    $osm_id = absint($item['osm_id'] ?? 0);
    $osm_url = ($osm_id && in_array($osm_type, ['node', 'way', 'relation'], true))
      ? 'https://www.openstreetmap.org/' . $osm_type . '/' . $osm_id : 'https://www.openstreetmap.org/';
    $candidates[] = [
      'name' => $found, 'city' => $found_city, 'address' => $address, 'sector' => $sector,
      'phone' => sanitize_text_field($e['contact:phone'] ?? $e['phone'] ?? ''),
      'email' => sanitize_email($e['contact:email'] ?? $e['email'] ?? ''),
      'website' => esc_url_raw($e['contact:website'] ?? $e['website'] ?? ''),
      'hours' => sanitize_text_field($e['opening_hours'] ?? ''),
      'imprint' => '', 'services' => '',
      'google' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($found . ' ' . $address),
      'source_label' => 'OpenStreetMap (unbestätigter öffentlicher Eintrag)',
      'sources' => [['label' => 'Originaleintrag OpenStreetMap', 'url' => $osm_url]],
      'warning' => 'OpenStreetMap ist kein Google-Business-Profil. Kontakt, Öffnungszeiten, Bildrechte und gesetzliche Pflichtangaben vom Kunden prüfen.'
    ];
  }
  $result = [
    'candidates' => $candidates,
    'notice' => $candidates ? 'Passende Einträge gefunden; bitte den richtigen Betrieb bestätigen.' : 'Kein eindeutiger öffentlicher Treffer. Google Maps bitte zur manuellen Gegenprüfung öffnen.'
  ];
  set_transient($cache, $result, $candidates ? 12 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS);
  wp_send_json_success($result);
}
