<?php
/**
 * Plugin Name: ODPC Fernhilfe Landing
 * Description: Isolated remote support page, preserving the WordPress theme elsewhere.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;
add_action('init', function () {
  add_rewrite_rule('^remote-fernwartung/?$', 'index.php?odpc_remote_help=1', 'top');
});
add_filter('query_vars', function ($vars) { $vars[]='odpc_remote_help'; return $vars; });
add_action('template_redirect', function () {
  if (!get_query_var('odpc_remote_help') && trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/') !== 'remote-fernwartung') return;
  $file=__DIR__.'/odpc-remote-landing.html';
  if (!is_readable($file)) { status_header(503); exit('Fernhilfe wird vorbereitet'); }
  status_header(200);
  header('Content-Type: text/html; charset=UTF-8');
  header('X-Content-Type-Options: nosniff');
  readfile($file);
  exit;
}, 0);
