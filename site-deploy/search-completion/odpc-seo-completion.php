<?php
/** Plugin Name: ODPC Search Completion
 * Version: 1.0.0
 * Head-only corrections for the explicitly released public project pages.
 */
if (!defined('ABSPATH')) exit;
function odpc_search_completion_host() { return strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST)); }
function odpc_search_completion_description($host, $path, $title, $excerpt = '') {
    $pages = [
        'odpc.de' => [
            '/schach' => 'Schach im Browser spielen: Das Schachprojekt von Oliver Dering auf ODPC entdecken und die vorhandenen Spielmöglichkeiten kennenlernen.',
            '/wga' => 'Weltgewürz Atlas von ODPC: Gewürze, Zutaten und internationale Gewürzmischungen entdecken und die digitale Rezeptdatenbank kennenlernen.',
            '/leck-im-system' => 'Leck im System: das Projekt auf ODPC entdecken. Hintergründe und Informationen von Oliver Dering im Überblick.',
        ],
        'ra-justen.de' => [
            '/impressum' => 'Impressum der Rechtsanwaltskanzlei Anne-Kathrin Justen: Anbieterangaben, Kontakt und berufsrechtliche Informationen zur Kanzlei.',
            '/datenschutz' => 'Datenschutzerklärung der Rechtsanwaltskanzlei Anne-Kathrin Justen: Informationen zur Verarbeitung personenbezogener Daten auf dieser Website.',
        ],
    ];
    $path = rtrim($path, '/');
    if (isset($pages[$host][$path])) return $pages[$host][$path];
    if ($host === 'odpc.de' && str_starts_with($path, '/category/')) return $title.' bei ODPC: Beiträge und Praxisberichte zu IT, KI, Digitalisierung und den Projekten von Oliver Dering.';
    if ($host === 'tante-lenchen.odpc.de') {
        if (str_starts_with($path, '/tl_dish/')) return $title.' im Café Tante Lenchen in Lahnstein: Informationen zum Gericht und zur aktuellen Speisekarte. Verfügbarkeit vor Ort erfragen.';
        if (str_starts_with($path, '/author/')) return 'Beiträge und Neuigkeiten von Café Tante Lenchen in Lahnstein: Informationen zum Café, zur Küche und zum Besuch an der Lahn.';
        if (str_starts_with($path, '/category/')) return 'Neuigkeiten von Café Tante Lenchen in Lahnstein: Beiträge, Einblicke und Informationen zum Café an der Lahn.';
        return $excerpt ?: $title.' – ein Beitrag von Café Tante Lenchen in Lahnstein. Aktuelle Informationen und Kontakt zum Café an der Lahn.';
    }
    if ($host === 'atrium191-rb.odpc.de') return $excerpt ?: $title.' – Informationen zum Wohnprojekt ATRIUM 191 in Ransbach-Baumbach. Planung, Standort und Kontakt im Überblick.';
    return '';
}
function odpc_search_completion_head($source, $description, $canonical, $release = false) {
    if (!preg_match('~<head\b[^>]*>.*?</head\s*>~is', $source, $match, PREG_OFFSET_CAPTURE)) return $source;
    $head = $match[0][0];
    if ($release) {
        $head = preg_replace('~<meta\b(?=[^>]*\bname\s*=\s*([\x22\x27])(?:robots|googlebot)\1)[^>]*>\s*~is', '', $head);
        $head = preg_replace('~</head\s*>~i', '<meta name="robots" content="index, follow, max-image-preview:large">'."\n".'</head>', $head, 1);
    }
    $extra = '';
    if ($description !== '' && !preg_match('~<meta\b[^>]*\bname\s*=\s*([\x22\x27])description\1~i', $head)) $extra .= '<meta name="description" content="'.esc_attr($description).'">'."\n";
    if ($canonical !== '' && !preg_match('~<link\b[^>]*\brel\s*=\s*([\x22\x27])canonical\1~i', $head)) $extra .= '<link rel="canonical" href="'.esc_url($canonical).'">'."\n";
    if ($extra !== '') $head = preg_replace_callback('~</head\s*>~i', function () use ($extra) { return $extra.'</head>'; }, $head, 1);
    return substr_replace($source, $head, $match[0][1], strlen($match[0][0]));
}
add_action('template_redirect', function () {
    if (is_admin() || is_feed() || is_404() || is_search() || is_preview() || is_embed() || wp_doing_ajax()) return;
    if (defined('REST_REQUEST') && REST_REQUEST) return;
    $host = odpc_search_completion_host();
    if (!in_array($host, ['odpc.de','ai.odpc.de','tante-lenchen.odpc.de','atrium191-rb.odpc.de','ra-justen.de'], true)) return;
    if (is_singular() && (get_post_status() !== 'publish' || post_password_required())) return;
    $release = $host === 'atrium191-rb.odpc.de' && (is_front_page() || is_page());
    // Unverified apartment records, previews and all other intentionally private content retain their rules.
    if ($host === 'atrium191-rb.odpc.de' && !$release) return;
    $path = (string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $title = is_singular() ? get_the_title(get_queried_object_id()) : get_the_archive_title();
    $title = trim(wp_strip_all_tags($title));
    $excerpt = is_singular() ? trim(wp_strip_all_tags(get_post_field('post_excerpt', get_queried_object_id()))) : '';
    $description = odpc_search_completion_description($host, $path, $title, $excerpt);
    $canonical = '';
    if (is_front_page()) $canonical = home_url('/');
    elseif (is_singular()) $canonical = get_permalink(get_queried_object_id());
    elseif (is_author()) $canonical = get_author_posts_url(get_queried_object_id());
    elseif (is_category() || is_tag() || is_tax()) { $link = get_term_link(get_queried_object()); if (!is_wp_error($link)) $canonical = $link; }
    if (is_paged() && !is_singular()) $canonical = get_pagenum_link(max(1, (int)get_query_var('paged')));
    if ($release) header_remove('X-Robots-Tag');
    ob_start(function ($html) use ($description, $canonical, $release) { return odpc_search_completion_head($html, $description, $canonical, $release); });
}, -1000);
