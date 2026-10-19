<?php
/** Plugin Name: ODPC Local Search – Tante Lenchen */
if (!defined('ABSPATH')) exit;

function odpc_lenchen_search_pages() {
    return [
        '' => ['Café Tante Lenchen in Lahnstein – Kuchen & Küche', 'Café Tante Lenchen in Lahnstein: hausgemachte Kuchen und Torten, bodenständige Küche und gemütliche Atmosphäre direkt an der Lahn. Speisekarte und Kontakt.'],
        'speisekarte' => ['Speisekarte – Café Tante Lenchen in Lahnstein', 'Sommer- und Winterkarte von Café Tante Lenchen in Lahnstein: herzhafte Gerichte, Salate und vegetarische Angebote. Gerichte und Preise online ansehen.'],
        'tisch-anfragen' => ['Tisch anfragen – Café Tante Lenchen in Lahnstein', 'Tisch im Café Tante Lenchen in Lahnstein anfragen: online, telefonisch oder per WhatsApp. Die Reservierung gilt erst nach persönlicher Bestätigung.'],
        'kontakt' => ['Kontakt – Café Tante Lenchen in Lahnstein', 'Café Tante Lenchen erreichen: Telefon, E-Mail und WhatsApp sowie die Adresse Auf Ahl 2, 56112 Lahnstein. Kontakt für Tischanfragen, Gruppen und Feiern.'],
        'anfahrt' => ['Anfahrt – Café Tante Lenchen, Auf Ahl 2 in Lahnstein', 'So erreichen Sie Café Tante Lenchen direkt an der Lahn: Auf Ahl 2, 56112 Lahnstein. Adresse und Routenplanung für Ihren Besuch.'],
        'galerie' => ['Galerie – Café Tante Lenchen in Lahnstein', 'Einblicke in Café Tante Lenchen in Lahnstein. Entdecken Sie den Treffpunkt an der Lahn und weitere Informationen für Ihren Besuch.'],
        'medien' => ['Medien & Presse – Café Tante Lenchen in Lahnstein', 'Presseberichte und Video zu Café Tante Lenchen in Lahnstein. Informationen zum heutigen Standort Auf Ahl 2 und zum früheren Café am Bootshaus.'],
        'impressum' => ['Impressum – Café Tante Lenchen in Lahnstein', 'Impressum und Anbieterangaben der Website von Café Tante Lenchen in Lahnstein.'],
        'datenschutz' => ['Datenschutz – Café Tante Lenchen in Lahnstein', 'Datenschutzhinweise der Website von Café Tante Lenchen in Lahnstein und Informationen zur Verarbeitung personenbezogener Daten.'],
    ];
}

add_action('wp', function () {
    if (is_admin() || is_feed() || is_404() || is_search() || is_preview()) return;
    if (defined('REST_REQUEST') && REST_REQUEST) return;
    if (strtolower((string)wp_parse_url(home_url('/'), PHP_URL_HOST)) !== 'tante-lenchen.odpc.de') return;
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION')) return;
    $key = is_front_page() ? '' : (is_page() ? (string)get_post_field('post_name', get_queried_object_id()) : null);
    $pages = odpc_lenchen_search_pages();
    if ($key === null || !isset($pages[$key]) || post_password_required()) return;
    [$title, $description] = $pages[$key];
    $url = $key === '' ? home_url('/') : get_permalink(get_queried_object_id());
    remove_action('wp_head', 'rel_canonical');
    add_filter('pre_get_document_title', function () use ($title) { return $title; }, 99);
    add_action('wp_head', function () use ($title, $description, $url) {
        echo '<meta name="description" content="'.esc_attr($description).'">'."\n";
        echo '<link rel="canonical" href="'.esc_url($url).'">'."\n";
        $base = home_url('/');
        $business = [
            '@type' => 'CafeOrCoffeeShop', '@id' => $base.'#cafe',
            'name' => 'Café Tante Lenchen', 'url' => $base,
            'description' => odpc_lenchen_search_pages()[''][1],
            'telephone' => '+49 2621 6298644',
            'image' => 'https://odpc.de/wp-content/uploads/2026/02/FB_IMG_1771601697384.jpg',
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Auf Ahl 2', 'postalCode' => '56112', 'addressLocality' => 'Lahnstein', 'addressCountry' => 'DE'],
            'hasMenu' => home_url('/speisekarte/'),
        ];
        $graph = [$business,
            ['@type' => 'WebSite', '@id' => $base.'#website', 'url' => $base, 'name' => 'Café Tante Lenchen', 'inLanguage' => 'de-DE', 'publisher' => ['@id' => $base.'#cafe']],
            ['@type' => 'WebPage', '@id' => $url.'#webpage', 'url' => $url, 'name' => $title, 'description' => $description, 'inLanguage' => 'de-DE', 'isPartOf' => ['@id' => $base.'#website'], 'about' => ['@id' => $base.'#cafe']],
        ];
        echo '<script type="application/ld+json" id="odpc-local-search-schema">'.wp_json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).'</script>'."\n";
    }, 3);
});
