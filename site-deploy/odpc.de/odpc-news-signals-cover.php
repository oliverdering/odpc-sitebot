<?php
/**
 * ODPC News & Signals editorial cover (page 1897).
 * Narrowly scoped image replacement; no other page, dock or menu changes.
 */
add_filter('the_content', static function ($content) {
    if (is_admin() || !is_page(1897)) {
        return $content;
    }
    $old = 'https://odpc.de/wp-content/uploads/2026/02/1000297769-1024x683.png';
    $new = 'https://odpc.de/wp-content/uploads/2026/11/file_000000005720820ab456eb13014ca9a4.png';
    if (strpos($content, $old) === false) {
        return $content;
    }
    $replacement = '<img src="' . esc_url($new) . '" alt="News & Signals: Nachrichten, Analysen und Hintergründe zu KI, IT-Sicherheit, Drohnentechnik und Forschung" loading="eager" decoding="async" style="width:100%;height:auto;border-radius:12px;box-shadow:0 12px 28px rgba(15,35,48,.15)">';
    return preg_replace('~<img\b[^>]*\bsrc=["\']' . preg_quote($old, '~') . '["\'][^>]*>~i', $replacement, $content, 1);
}, 20);
