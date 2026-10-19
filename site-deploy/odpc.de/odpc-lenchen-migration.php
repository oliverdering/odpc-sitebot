<?php
/** Plugin Name: ODPC – Tante Lenchen permanent move */
if (!defined('ABSPATH')) exit;

if (strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST)) !== 'odpc.de') return;

add_action('init', function () {
    if (get_option('odpc_lenchen_migration_cache_v1') || !is_callable(['RankMath\\Sitemap\\Cache', 'invalidate_storage'])) return;
    \RankMath\Sitemap\Cache::invalidate_storage('page');
    update_option('odpc_lenchen_migration_cache_v1', 1, false);
}, 99);

add_action('template_redirect', function () {
    if (is_admin() || is_preview() || (defined('REST_REQUEST') && REST_REQUEST)) return;
    if (is_page(2352)) {
        wp_redirect('https://tante-lenchen.odpc.de/', 301, 'ODPC Tante Lenchen migration');
        exit;
    }
}, 0);

add_filter('wpseo_exclude_from_sitemap_by_post_ids', function ($ids) {
    return array_values(array_unique(array_merge((array) $ids, [2352])));
});
add_filter('rank_math/sitemap/entry', function ($entry, $type, $object) {
    if ($type === 'post' && isset($object->ID) && (int) $object->ID === 2352) return false;
    return $entry;
}, 10, 3);
add_filter('wp_sitemaps_posts_query_args', function ($args, $post_type) {
    if ($post_type === 'page') {
        $args['post__not_in'] = array_values(array_unique(array_merge($args['post__not_in'] ?? [], [2352])));
    }
    return $args;
}, 10, 2);

add_filter('page_link', function ($link, $id) {
    return (int) $id === 2352 && !is_admin() ? 'https://tante-lenchen.odpc.de/' : $link;
}, 10, 2);
