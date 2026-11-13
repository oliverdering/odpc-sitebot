<?php
/**
 * Kleine Freiheit – authenticated, isolated customer-photo importer.
 * Accepts the 15-webp ODPC archive, never permits anonymous upload.
 * No DB or filesystem writes occur until an authenticated editor imports media.
 */
if (!defined('ABSPATH')) exit;

function odpc_kf_gallery_ids() {
  $ids = get_option('kf_gallery_attachment_ids', []);
  if (!is_array($ids)) return [];
  return array_values(array_filter(array_unique(array_map('absint', $ids)), static function($id) {
    return $id && get_post_type($id) === 'attachment' && wp_attachment_is_image($id);
  }));
}

add_action('admin_menu', static function() {
  add_menu_page(
    'Kleine Freiheit – Originalfotos',
    'KF Originalfotos',
    'upload_files',
    'odpc-kf-originalfotos',
    'odpc_kf_gallery_admin_page',
    'dashicons-format-gallery',
    65
  );
});

function odpc_kf_gallery_admin_page() {
  if (!current_user_can('upload_files')) wp_die('Keine Berechtigung.');
  $ids = odpc_kf_gallery_ids();
  $count = isset($_GET['kf_count']) ? absint($_GET['kf_count']) : null;
  echo '<div class="wrap"><h1>Kleine Freiheit · Originalfotos</h1>';
  echo '<p>Die aus dem Chat gesicherten 15 Originalaufnahmen können als einzelnes ODPC-Fotopaket hochgeladen werden. Der Import legt WebP-Dateien in der WordPress-Mediathek ab, entfernt Duplikate über SHA-256 und aktualisiert die öffentliche, als Demo gekennzeichnete Galerie.</p>';
  if ($count !== null) echo '<div class="notice notice-success"><p>Importiert/zugeordnet: '.esc_html((string)$count).' Fotos.</p></div>';
  echo '<p><strong>Aktuelle Anzahl Fotos auf der Kundenvorschau:</strong> '.esc_html((string)count($ids)).'</p>';
  echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" enctype="multipart/form-data" style="background:white;border:1px solid #ccd0d4;padding:22px;max-width:740px">';
  echo '<input type="hidden" name="action" value="odpc_kf_import_gallery">';
  wp_nonce_field('odpc_kf_import_gallery','odpc_kf_nonce');
  echo '<p><label><strong>ODPC-Fotopaket (.zip)</strong><br><input type="file" name="kf_archive" accept=".zip,application/zip" required></label></p>';
  echo '<p><label><input type="checkbox" name="kf_rights" value="1" required> Ich bestätige, dass die Fotos (einschließlich erkennbarer Personen) für diese Kundenvorschau verwendet werden dürfen.</label></p>';
  submit_button('15 Fotos importieren und Galerie aktualisieren');
  echo '</form>';
  echo '<p><a href="'.esc_url(home_url('/?yb_kleine_freiheit=1&design=klassisch')).'" target="_blank" rel="noopener">Kundenwebseite mit Galerie ansehen</a></p>';
  if ($ids) {
    echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:15px;max-width:980px">';
    foreach ($ids as $id) {
      echo '<div style="background:white;padding:10px;border:1px solid #ddd">';
      echo wp_get_attachment_image($id, 'medium', false, ['style'=>'width:100%;height:130px;object-fit:cover']);
      echo '<small>'.esc_html(get_the_title($id)).'</small></div>';
    }
    echo '</div>';
  }
  echo '</div>';
}

add_action('admin_post_odpc_kf_import_gallery', static function() {
  if (!current_user_can('upload_files')) wp_die('Keine Berechtigung.');
  check_admin_referer('odpc_kf_import_gallery','odpc_kf_nonce');
  if (empty($_POST['kf_rights'])) wp_die('Bildrechte müssen vor dem Import bestätigt werden.');
  if (!class_exists('ZipArchive')) wp_die('Die PHP-ZIP-Erweiterung fehlt auf diesem Server.');
  if (empty($_FILES['kf_archive']) || !is_array($_FILES['kf_archive'])) wp_die('Kein Fotopaket übermittelt.');
  $file = $_FILES['kf_archive'];
  if ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) wp_die('Datei konnte nicht hochgeladen werden.');
  if ((int)($file['size'] ?? 0) < 100 || (int)($file['size'] ?? 0) > 20 * 1024 * 1024) wp_die('Fotopaket muss zwischen 100 Byte und 20 MB liegen.');
  $tmp = (string)($file['tmp_name'] ?? '');
  if (!$tmp || !is_uploaded_file($tmp)) wp_die('Ungültige Uploadquelle.');
  $zip = new ZipArchive();
  if ($zip->open($tmp) !== true) wp_die('ZIP-Paket ist beschädigt.');
  $candidates = []; $total = 0;
  for ($i=0; $i<$zip->numFiles; $i++) {
    $entry = (string)$zip->getNameIndex($i);
    if (!preg_match('/^[0-9]{2}-kleine-freiheit-[a-z0-9-]+\.webp$/D', $entry)) continue;
    $stat = $zip->statIndex($i);
    $size = (int)($stat['size'] ?? 0);
    if ($size < 100 || $size > 4 * 1024 * 1024) { $zip->close(); wp_die('Bilddatei außerhalb zulässiger Grenzen.'); }
    $total += $size;
    if ($total > 40 * 1024 * 1024 || count($candidates) >= 20) { $zip->close(); wp_die('Zu viele oder zu große Fotos.'); }
    $candidates[] = [$entry, $i];
  }
  usort($candidates, static fn($a, $b) => strcmp($a[0], $b[0]));
  if (!$candidates) { $zip->close(); wp_die('Kein passendes ODPC-WebP-Foto im ZIP-Paket gefunden.'); }
  $manifestRaw = $zip->getFromName('manifest.json');
  $captions=[];
  if (is_string($manifestRaw) && strlen($manifestRaw) <= 50000) {
    $manifest = json_decode($manifestRaw, true);
    foreach (($manifest['photos'] ?? []) as $p) {
      if (isset($p['file'],$p['alt']) && is_string($p['file']) && is_string($p['alt'])) $captions[$p['file']] = sanitize_text_field($p['alt']);
    }
  }
  require_once ABSPATH . 'wp-admin/includes/image.php';
  $ids = [];
  foreach ($candidates as [$name, $index]) {
    $data = $zip->getFromIndex($index);
    if (!is_string($data)) continue;
    $meta = @getimagesizefromstring($data);
    if (!$meta || ($meta['mime'] ?? '') !== 'image/webp') continue;
    $hash = hash('sha256', $data);
    $existing = get_posts([
      'post_type'=>'attachment', 'post_status'=>'inherit', 'posts_per_page'=>1,
      'fields'=>'ids', 'meta_key'=>'_odpc_kf_sha256', 'meta_value'=>$hash
    ]);
    if ($existing) { $ids[]=(int)$existing[0]; continue; }
    $upload = wp_upload_bits(sanitize_file_name($name), null, $data);
    if (!empty($upload['error']) || empty($upload['file'])) continue;
    $alt = $captions[$name] ?? ucwords(str_replace('-', ' ', pathinfo($name, PATHINFO_FILENAME)));
    $attachmentId = wp_insert_attachment([
      'post_mime_type'=>'image/webp','post_title'=>$alt,'post_status'=>'inherit'
    ], $upload['file']);
    if (is_wp_error($attachmentId) || !$attachmentId) continue;
    update_post_meta($attachmentId,'_wp_attachment_image_alt', $alt);
    update_post_meta($attachmentId,'_odpc_kf_sha256', $hash);
    update_post_meta($attachmentId,'_odpc_kf_source','ODPC, 13.11.2026, Originalaufnahme vom Auftraggeber');
    $generated = wp_generate_attachment_metadata($attachmentId, $upload['file']);
    if (is_array($generated)) wp_update_attachment_metadata($attachmentId, $generated);
    $ids[] = (int)$attachmentId;
  }
  $zip->close();
  if (!$ids) wp_die('Keine verwendbaren Bilder importiert. Bisherige Galerie unverändert.');
  update_option('kf_gallery_attachment_ids', $ids, false);
  wp_safe_redirect(add_query_arg(['page'=>'odpc-kf-originalfotos','kf_count'=>count($ids)], admin_url('admin.php')));
  exit;
});
