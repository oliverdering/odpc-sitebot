<?php
/**
 * Plugin Name: ODPC Corporate Blue
 * Description: Blue brand palette for ODPC corporate pages, excluding customer showcases.
 */
if (!defined('ABSPATH')) exit;
add_action('wp_head', function () {
    if (is_admin()) return;
    $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $excluded = ['tante-lenchen','camping-runkel-lahnstein','anwaltskanzlei-anne-kathrin-justen','praedium','prael ex','praetax','praedoc-ai','initiq','one-eye','one-home','artisan','tontoftgard-ein-haus-und-seine-geschichte'];
    if (in_array($path, $excluded, true)) return;
    echo '<style id="odpc-corporate-blue-2026">
:root{--odpc-brand-primary:#1e3a5f;--odpc-brand-secondary:#356a9a;--odpc-brand-soft:#eaf1f8;--odpc-brand-dark:#172b46}
body:not(.wp-admin) .odpcPort-sec{--op-strong:#1e3a5f!important;--op-soft:#eaf1f8!important;--op-line:rgba(30,58,95,.16)!important}
#odpc-customer-ref .hero{background:linear-gradient(130deg,#172b46,#356a9a)!important}
#odpc-customer-ref .odpc-ref-actions a,#odpc-customer-ref .button{background:#1e3a5f!important;color:#fff!important}
#odpc-customer-ref .odpc-ref-actions a.secondary{background:#eaf1f8!important;color:#1e3a5f!important}
#odpc-customer-ref .odpc-ref-tag{background:#eaf1f8!important;color:#1e3a5f!important}
#odpc-customer-ref .panel{background:#eaf1f8!important;border-left-color:#356a9a!important}
#odpc-customer-ref h2,#odpc-customer-ref .odpc-ref-card h3{color:#1e3a5f!important}
.hm-service-brand{background:linear-gradient(115deg,#172b46,#356a9a)!important}
.hm-service-brand .hm-go{background:#eaf1f8!important;color:#1e3a5f!important}
</style>';
}, 9999);
