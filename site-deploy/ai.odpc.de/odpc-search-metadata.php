<?php
/**
 * Plugin Name: ODPC Search Metadata
 * Description: Server-rendered search metadata for the Helper Monkey public pages.
 * Version: 1.0.1
 */
if (!defined('ABSPATH')) exit;

function odpc_ai_search_catalog() {
    return [
        '' => ['Helper Monkey – kostenlose KI-Agenten & Tools | ODPC', 'Kostenlose KI-Agenten für Bewerbungen, E-Mail und Alltag. Helper Monkey von Oliver Dering: Starter herunterladen, im KI-Chat nutzen und Ergebnisse prüfen.'],
        'start' => ['KI-Agenten & Downloads – Helper Monkey | ODPC', 'Vier kostenlose KI-Starter, Downloads und professionelle Fachpakete im Überblick. Passenden Helper Monkey auswählen und Schritt für Schritt starten.'],
        'helper-monkey-einfach-starten-sprachassistent' => ['KI-Einstieg mit Sprachassistent – Helper Monkey', 'Kostenloses Startinterview: Helper Monkey ermittelt deinen Einstieg anhand von Gerät, Erfahrung, Konten und Ziel. Mit Vorlesen und Spracheingabe im Browser.'],
        'helper-monkey-fuer-unternehmen-ki-kollegen' => ['KI-Assistenten für Unternehmen – Helper Monkey', 'Rollenbezogene KI-Assistenten für Büro, Marketing, Personal und Webdesign planen. Kostenlosen Bedarfscheck starten und ODPC-Begleitung kennenlernen.'],
        'connector-einrichten' => ['KI-Connector einrichten – Helper Monkey | ODPC', 'Prüfe verfügbare Kontoverbindungen für Gmail, Kalender und WordPress. Der kostenlose Connector-Starter erklärt Einrichtung, Berechtigungen und Freigaben.'],
        'werkstatt' => ['Kostenlose Browser-Tools – Helper Monkey Werkstatt', 'Dokumente, E-Mail-Entwürfe, Kalenderdateien und Bewerbungslisten im Browser vorbereiten. Kostenlose Helper-Monkey-Werkzeuge ohne Registrierung nutzen.'],
        'praxis' => ['KI-Agenten nutzen: Anleitung & FAQ – Helper Monkey', 'Helper Monkey praktisch nutzen: Anleitungen und Antworten zu KI-Agenten, Einrichtung, Kontoverbindungen und Freigaben. Verständlich erklärt von ODPC.'],
        'sprachen' => ['KI-Agenten in deiner Sprache – Helper Monkey', 'Arbeitssprache wählen und den passenden Helper Monkey einrichten. Kostenloser Spracheinstieg und Vorlagen für die Nutzung in deinem KI-Chat.'],
        'gespraechsuebersetzer' => ['Kostenloser Gesprächsübersetzer – Helper Monkey', 'Gesprächsübersetzer für deinen KI-Chat vorbereiten: Sprachpaar wählen, kostenlosen Prompt herunterladen und Aussagen abwechselnd übersetzen lassen.'],
        'webmaster-agent' => ['WordPress-Webmaster & KI-Agent – Helper Monkey', 'Professionelle KI-Unterstützung für WordPress, Inhalte und SEO kennenlernen. Einrichtung und Website-Aktionen erfolgen mit geprüften Berechtigungen.'],
        'finanzen-vertragscheck' => ['Finanzen & Verträge strukturieren – Helper Monkey', 'Ausgaben, Verträge und Fristen mit KI-Unterstützung übersichtlich vorbereiten. Helper Monkey bietet strukturierte Vorarbeit mit menschlicher Prüfung.'],
        'konfliktlotse-anwaltsvorarbeit' => ['Konflikte dokumentieren – Helper Monkey Konfliktlotse', 'Konfliktlotse im Aufbau: Fakten, Interessen und Dokumente für sachliche Gespräche und anwaltliche Prüfung ordnen. Das Konzept von Helper Monkey kennenlernen.'],
        'merchandise' => ['Merchandise & NFC-Konfigurator – Helper Monkey', 'Helper-Monkey-T-Shirts und NFC-Kontaktmedien konfigurieren. Entwicklungsedition kennenlernen und eine unverbindliche Anfrage an ODPC vorbereiten.'],
    ];
}

function odpc_ai_search_rewrite($html, $data) {
    if (!preg_match('~<head\b[^>]*>.*?</head\s*>~is', $html, $match, PREG_OFFSET_CAPTURE)) return $html;
    $head = $match[0][0];
    $head = preg_replace('~<title\b[^>]*>.*?</title\s*>\s*~is', '', $head);
    $head = preg_replace('~<meta\b(?=[^>]*\b(?:name|property)\s*=\s*([\x22\x27])(?:description|og:[^\x22\x27]+|twitter:[^\x22\x27]+)\1)[^>]*>\s*~is', '', $head);
    $head = preg_replace('~<link\b(?=[^>]*\brel\s*=\s*([\x22\x27])canonical\1)[^>]*>\s*~is', '', $head);
    $extra = '<title>'.esc_html($data['title']).'</title>' . "\n";
    $extra .= '<meta name="description" content="'.esc_attr($data['description']).'">' . "\n";
    $extra .= '<link rel="canonical" href="'.esc_url($data['url']).'">' . "\n";
    foreach (['og:type'=>'website', 'og:locale'=>'de_DE', 'og:site_name'=>'helper · monkey ©', 'og:title'=>$data['title'], 'og:description'=>$data['description'], 'og:url'=>$data['url']] as $key=>$value) {
        $extra .= '<meta property="'.esc_attr($key).'" content="'.esc_attr($value).'">' . "\n";
    }
    foreach (['twitter:card'=>'summary', 'twitter:title'=>$data['title'], 'twitter:description'=>$data['description']] as $key=>$value) {
        $extra .= '<meta name="'.esc_attr($key).'" content="'.esc_attr($value).'">' . "\n";
    }
    $extra .= '<script type="application/ld+json" id="odpc-search-metadata-1">'.wp_json_encode($data['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).'</script>' . "\n";
    $head = preg_replace_callback('~</head\s*>~i', function () use ($extra) { return $extra.'</head>'; }, $head, 1);
    return substr_replace($html, $head, $match[0][1], strlen($match[0][0]));
}

add_action('template_redirect', function () {
    if (is_admin() || is_feed() || is_404() || is_search() || is_preview() || is_embed() || wp_doing_ajax()) return;
    if (defined('REST_REQUEST') && REST_REQUEST) return;
    if (defined('RANK_MATH_VERSION') || defined('WPSEO_VERSION')) return;
    $path = trim((string)wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    if ($path !== '' && !is_singular()) return;
    if (is_singular() && (get_post_status() !== 'publish' || post_password_required())) return;
    $catalog = odpc_ai_search_catalog();
    $slug = $path === '' ? '' : get_post_field('post_name', get_queried_object_id());
    $url = $path === '' ? home_url('/') : get_permalink(get_queried_object_id());
    $name = $path === '' ? 'Helper Monkey – kostenlose KI-Agenten' : wp_strip_all_tags(get_the_title(get_queried_object_id()));
    $fallback = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', get_queried_object_id())))));
    $info = $catalog[$slug] ?? [$name.' | Helper Monkey', wp_trim_words($fallback, 24, ' …')];
    $org = ['@type'=>'Organization', '@id'=>'https://odpc.de/#person', 'name'=>'Oliver Dering IT-Consulting', 'alternateName'=>'ODPC – Oliver Dering Professional Consulting', 'url'=>'https://odpc.de/', 'logo'=>['@type'=>'ImageObject', 'url'=>'https://odpc.de/wp-content/uploads/2026/10/cropped-file_000000008d58820abefbd8e885c5afce-447x191.png', 'width'=>447, 'height'=>191], 'founder'=>['@id'=>'https://odpc.de/#oliver-dering']];
    $person = ['@type'=>'Person', '@id'=>'https://odpc.de/#oliver-dering', 'name'=>'Oliver Dering', 'url'=>'https://odpc.de/about-me'];
    $site = ['@type'=>'WebSite', '@id'=>home_url('/').'#website', 'url'=>home_url('/'), 'name'=>'helper · monkey ©', 'inLanguage'=>'de-DE', 'publisher'=>['@id'=>$org['@id']]];
    $page = ['@type'=>'WebPage', '@id'=>$url.'#webpage', 'url'=>$url, 'name'=>$info[0], 'description'=>$info[1], 'inLanguage'=>'de-DE', 'isPartOf'=>['@id'=>$site['@id']], 'publisher'=>['@id'=>$org['@id']]];
    $graph = [$org, $person, $site, $page];
    if ($path !== '') {
        $graph[3]['breadcrumb'] = ['@id'=>$url.'#breadcrumb'];
        $graph[] = ['@type'=>'BreadcrumbList', '@id'=>$url.'#breadcrumb', 'itemListElement'=>[
            ['@type'=>'ListItem', 'position'=>1, 'name'=>'Helper Monkey', 'item'=>home_url('/')],
            ['@type'=>'ListItem', 'position'=>2, 'name'=>$name, 'item'=>$url],
        ]];
    }
    $data = ['title'=>$info[0], 'description'=>$info[1], 'url'=>$url, 'schema'=>['@context'=>'https://schema.org', '@graph'=>$graph]];
    ob_start(function ($html) use ($data) { return odpc_ai_search_rewrite($html, $data); });
}, 0);
