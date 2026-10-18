<?php
/**
 * Plugin Name: Helper Monkey Corporate Green
 * Description: Green palette scoped to ai.odpc.de only.
 */
if (!defined('ABSPATH')) exit;
add_action('wp_head', function () {
    if (is_admin() || strtolower($_SERVER['HTTP_HOST'] ?? '') !== 'ai.odpc.de') return;
    echo '<style id="helpermonkey-corporate-green-2026">
:root{--hm-brand-primary:#155b37;--hm-brand-secondary:#278353;--hm-brand-soft:#eef7f0;--hm-brand-dark:#103f32}
body{--wp--preset--color--primary:#155b37}
.hm-service-brand{background:linear-gradient(115deg,#103f32,#278353)!important}
.hm-service-brand .hm-go{background:#eef7f0!important;color:#155b37!important}
</style>';
}, 9999);
