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

  $cache = 'yb_lookup_' . md5(mb_strtolower($name . '|' . $city));
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
        'services' => "Kneipe und Getränkeausschank\nTreffpunkt in der Koblenzer Altstadt",
        'hours' => 'Mo–Do 10:00–23:00; Fr–Sa 10:00–04:00; So geschlossen',
        'imprint' => '', 'source_label' => 'Öffentliche Verzeichnisse, recherchiert am 13.11.2026',
        'sources' => [
          ['label' => 'RestaurantGuru (Kontakt, Zeiten, Adresse)', 'url' => 'https://de.restaurantguru.com/Kleine-Freiheit-Koblenz'],
          ['label' => 'Das ist nah (Telefon, Adresse)', 'url' => 'https://www.dasistnah.de/gaststaette-koblenz.html'],
          ['label' => 'wie.de (Standort)', 'url' => 'https://wie.de/poi/kleine-freiheit.11584352641']
        ],
        'warning' => 'Andere Verzeichnisse nennen abweichende Rufnummern. Telefon, Öffnungszeiten und Betreiber/Impressum mit dem Kunden prüfen. Kein automatisches Übernehmen fremder Fotos.'
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
