<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header('Content-Type: application/json; charset=utf-8');
$supplied = (string) ($_SERVER['HTTP_X_ODPC_INSTALL_TOKEN'] ?? '');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals('__TOKEN_HASH__', hash('sha256', $supplied))) {
    http_response_code(404);
    exit('{}');
}
if (strtolower((string) ($_SERVER['HTTP_HOST'] ?? '')) !== 'bahnhof-grenzau.odpc.de') {
    http_response_code(400);
    exit('{"ok":false,"reason":"host"}');
}
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || strlen((string) ($input['password'] ?? '')) < 16 || !filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('{"ok":false,"reason":"input"}');
}
define('WP_INSTALLING', true);
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
global $wpdb;
$wpdb->suppress_errors(true);
$tables = $wpdb->get_col('SHOW TABLES');
if ($wpdb->last_error || !is_array($tables) || count($tables) !== 0) {
    http_response_code(409);
    exit('{"ok":false,"reason":"database_must_be_empty_and_reachable"}');
}
add_filter('pre_wp_mail', static function () { return false; });
$result = wp_install('Bahnhof Grenzau', $input['user'], $input['email'], false, '', $input['password'], 'de_DE');
if (is_wp_error($result)) {
    http_response_code(500);
    exit('{"ok":false,"reason":"installation"}');
}
update_option('home', 'https://bahnhof-grenzau.odpc.de');
update_option('siteurl', 'https://bahnhof-grenzau.odpc.de');
update_option('timezone_string', 'Europe/Berlin');
update_option('blog_public', 0);
update_option('default_comment_status', 'closed');
update_option('default_ping_status', 'closed');
update_option('blogdescription', 'Ein Ort mit Geschichte. Ein neues Kapitel.');
update_option('permalink_structure', '/%postname%/');
$front = wp_insert_post(['post_title' => 'Bahnhof Grenzau', 'post_name' => 'start', 'post_type' => 'page', 'post_status' => 'publish', 'post_content' => 'Die neue Website für Bahnhof Grenzau entsteht.'], true);
if (is_wp_error($front)) {
    http_response_code(500);
    exit('{"ok":false,"reason":"front_page"}');
}
update_option('show_on_front', 'page');
update_option('page_on_front', (int) $front);
switch_theme('bahnhof-grenzau-start');
flush_rewrite_rules(false);
echo wp_json_encode(['ok' => true, 'front_page' => (int) $front, 'version' => get_bloginfo('version')]);
