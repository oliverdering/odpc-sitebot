<?php
/**
 * Plugin Name: ODPC Helper Monkey Article Featured Image
 * Description: Sets the approved existing media image only on the designated article.
 */
add_action('init', static function () {
    if (get_option('odpc_hm_featured_3454_done')) { return; }
    $post = get_post(3454);
    $media = get_post(3052);
    if (!$post || $post->post_type !== 'post' || $post->post_name !== 'smartphone-ki-helfer-connector-einfach-erklaert') { return; }
    if (!$media || $media->post_type !== 'attachment' || !wp_attachment_is_image(3052)) { return; }
    if ((int) get_post_thumbnail_id(3454) === 3052) {
        update_option('odpc_hm_featured_3454_done', 1, false);
        return;
    }
    if (has_post_thumbnail(3454)) { return; }
    if (set_post_thumbnail(3454, 3052)) {
        update_option('odpc_hm_featured_3454_done', 1, false);
    }
}, 30);
