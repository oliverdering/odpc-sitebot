<?php
/**
 * Plugin Name: ODPC KI-Portal Ankündigung
 * Description: Veröffentlicht einmalig den redaktionellen Startbeitrag.
 */
defined('ABSPATH') || exit;
add_action('init', static function () {
    $slug = 'ki-mediathek-news-signals-sendeschluss';
    if (get_option('odpc_portal_announcement_published_v1')) return;
    $existing = get_page_by_path($slug, OBJECT, 'post');
    if ($existing) {
        if ($existing->post_status === 'publish') update_option('odpc_portal_announcement_published_v1', (int)$existing->ID, false);
        return;
    }
    $image = 'https://odpc.de/wp-content/uploads/2026/11/file_00000000cc508210943a8d297220327c.png';
    $body = '<figure class="wp-block-image size-full"><a href="https://odpc.de/ki-mediathek/"><img src="'.esc_url($image).'" alt="Retro-Fernseh-Testbild: ODPC KI-Mediathek – zur Mediathek" loading="eager" decoding="async" /></a></figure>' . '<p><strong>Sendeschluss? Von wegen! Bei ODPC geht es jetzt erst richtig los.</strong></p>'
      . '<p>Wer erinnert sich nicht an das klassische Fernseh-Testbild? Früher war damit das Programm vorbei. Wir drehen den Spieß um: Unser Retro-Testbild steht heute für den Einstieg in die Welt der künstlichen Intelligenz.</p>'
      . '<h2>📺 Die ODPC KI-Mediathek: KI zum Anschauen</h2>'
      . '<p>Ob Dokumentation, Reportage, Interview oder Nachrichtenbeitrag: Die <strong>ODPC KI-Mediathek</strong> bündelt interessante Fernsehinhalte zum Thema künstliche Intelligenz. Statt lange in einzelnen Mediatheken zu suchen, findest du ausgewählte Sendungen mit Senderangabe und direktem Link zum jeweiligen Originalangebot. Auch kommende KI-Sendungen können in der Programmvorschau auftauchen.</p>'
      . '<p><a href="https://odpc.de/ki-mediathek/"><strong>Jetzt die ODPC KI-Mediathek entdecken →</strong></a></p>'
      . '<h2>📰 News &amp; Signals: KI zum Nachlesen</h2>'
      . '<p>Was passiert gerade bei OpenAI, Google DeepMind und in der KI-Forschung? Welche Entwicklungen sind für Unternehmen und Privatpersonen relevant? <strong>News &amp; Signals</strong> ergänzt die Mediathek mit aktuellen Meldungen und Analysen aus Fachpresse und offiziellen Herstellerkanälen.</p>'
      . '<p>Die Beiträge sind nach Quelle und Datum gekennzeichnet. Herstellerankündigungen werden von unabhängiger Fachberichterstattung unterschieden. Das Portal ist für eine tägliche Aktualisierung ausgelegt.</p>'
      . '<p><a href="https://odpc.de/news-signals/"><strong>Zu ODPC News &amp; Signals →</strong></a></p>'
      . '<h2>Zwei Angebote. Ein Ziel: KI verstehen.</h2>'
      . '<p><strong>Anschauen. Nachlesen. Verstehen. Mitreden.</strong> Beide Angebote sind kostenlos und ohne Anmeldung erreichbar. Für Neugierige, Fachleute und alle, die sich fundiert über künstliche Intelligenz informieren möchten.</p>'
      . '<p><em>Ein Informationsangebot von Oliver Dering IT-Consulting · <a href="https://odpc.de/">ODPC</a></em></p>'
      . '<p><strong>Die Zukunft läuft bereits. Schalten wir gemeinsam ein!</strong> 📺</p>';
    $id = wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_name'=>$slug,
      'post_title'=>'📺 Sendeschluss? Von wegen! Die ODPC KI-Mediathek & News & Signals sind da',
      'post_excerpt'=>'KI zum Anschauen und Nachlesen: Entdecke die neue ODPC KI-Mediathek und News & Signals – zwei miteinander verknüpfte Informationsangebote.',
      'post_content'=>$body,'comment_status'=>'closed'], true);
    if (is_wp_error($id) || !$id) { error_log('ODPC announcement publish failed'); return; }
    $attachment = attachment_url_to_postid($image);
    if (!$attachment) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment = media_sideload_image($image, $id, 'ODPC KI-Mediathek im Stil eines klassischen Fernseh-Testbildes', 'id');
    }
    if (!is_wp_error($attachment) && $attachment) {
        set_post_thumbnail($id, (int)$attachment);
        update_post_meta((int)$attachment, '_wp_attachment_image_alt', 'Retro-Fernseh-Testbild mit ODPC KI-Mediathek Schriftzug');
    } else {
        error_log('ODPC announcement featured image could not be attached');
    }
    update_option('odpc_portal_announcement_published_v1', (int)$id, false);
}, 20);

/**
 * Repair the featured image on an already published article.
 * Previous publication marker must never suppress media repair.
 */
add_action('init', static function () {
    $post = get_page_by_path('ki-mediathek-news-signals-sendeschluss', OBJECT, 'post');
    if (!$post || $post->post_status !== 'publish') return;
    $image = 'https://odpc.de/wp-content/uploads/2026/11/file_00000000cc508210943a8d297220327c.png';
    if (strpos($post->post_content, $image) === false) {
        wp_update_post(['ID'=>$post->ID, 'post_content'=>'<figure class="wp-block-image size-full"><a href="https://odpc.de/ki-mediathek/"><img src="'.esc_url($image).'" alt="ODPC KI-Mediathek – Retro-Fernseh-Testbild" loading="eager" /></a></figure>'.$post->post_content]);
    }
    if (has_post_thumbnail($post->ID)) return;
    $attachment = attachment_url_to_postid($image);
    if (!$attachment) {
        require_once ABSPATH.'wp-admin/includes/media.php';
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/image.php';
        $attachment = media_sideload_image($image, $post->ID, 'ODPC KI-Mediathek Testbild', 'id');
    }
    if (!is_wp_error($attachment) && $attachment) {
        set_post_thumbnail($post->ID, (int)$attachment);
        update_post_meta((int)$attachment, '_wp_attachment_image_alt', 'Retro-Testbild mit ODPC KI-Mediathek Schriftzug');
    }
}, 30);
