<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
?><?php
// Server-rendered public content for crawlers and visitors without JavaScript.
// The existing client application replaces #wga-root after initialization.
$mkAtlas = []; $mkExtra = [];
try {
    define('MK_INTERNAL', true);
    require_once __DIR__.'/server/database.php';
    [$mkAtlas, $mkExtra] = mk_public_data();
} catch (Throwable $e) { error_log('MeinKraut SSR fallback: '.$e->getMessage()); }
function mk_h($value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#123e34">
<title>Mein Kraut. Dein Geschmack.</title><meta name="description" content="Mische Gewürze selbst: Steakpfeffer, BBQ-Rubs und Küchen der Welt. Mit Mengenrechner, eigenem Gewürzregal und Etiketten. Jetzt kostenlos ausprobieren.">
<link rel="canonical" href="https://meinkraut.odpc.de/"><link rel="icon" type="image/webp" href="assets/mein-kraut-logo-192.webp?v=final-1">
<link rel="apple-touch-icon" href="assets/mein-kraut-logo-192.webp?v=final-1">
<meta property="og:title" content="Mein Kraut. Dein Geschmack."><meta property="og:description" content="My spice. Your taste. Entdecke die Welt und mische deine Gewürze selbst."><meta property="og:image" content="https://meinkraut.odpc.de/assets/mein-kraut-logo-640.webp?v=final-1"><meta property="og:image:alt" content="Mein Kraut – Gewürze selbst mischen: grauer Steinmörser mit Gewürzen"><meta property="og:image:width" content="640"><meta property="og:image:height" content="640"><meta property="og:type" content="website"><meta property="og:url" content="https://meinkraut.odpc.de/">
<link rel="stylesheet" href="meinkraut.css?v=2.0.3"><link rel="preload" as="image" href="assets/mein-kraut-logo-640.webp?v=final-1">
</head><body class="meinkraut-standalone"><a class="mk-skip" href="#mk-main">Zum Inhalt</a><div id="wga-root" aria-label="Mein Kraut – Gewürzregal"><main id="mk-main">
<section class="mk-hero"><div class="mk-hero-copy"><div class="mk-eyebrow">Gewürzmischungen selber machen. Schritt für Schritt.</div><h1>Mein Kraut.<br><em>Dein Geschmack.</em></h1><p>Mische deinen eigenen Steakpfeffer, BBQ-Rub oder deine Lieblingskräuter. Entdecke Zutaten, Mengen und Gewürzwissen und gestalte persönliche Etiketten für deine Gewürzgläser.</p><p>Rezept wählen. Abwiegen. Mischen. Beschriften.</p></div><img class="mk-hero-logo" src="assets/mein-kraut-logo-640.webp?v=final-1" width="470" height="470" alt="Mein Kraut – Steinmörser und Gewürze"></section>
<section class="mk-section"><h2>Gewürzmischungen und Rezeptideen</h2><div class="mk-grid">
<?php foreach (array_slice(array_merge($mkExtra['mixes'] ?? [], $mkAtlas['recipes'] ?? []), 0, 80) as $recipe): ?>
<article class="mk-card recipe"><h3><?= mk_h($recipe['name'] ?? '') ?></h3><p><?= mk_h($recipe['description'] ?? '') ?></p>
<?php if (!empty($recipe['ingredients']) && is_array($recipe['ingredients'])): ?><p>Zutaten: <?= mk_h(implode(', ', array_map(static function($part) use ($mkAtlas) { $id=$part['ingredient']??''; foreach(($mkAtlas['ingredients']??[]) as $i) { if(($i['id']??'')===$id) return $i['name']??$id; } return $id; }, $recipe['ingredients']))) ?></p><?php endif; ?></article>
<?php endforeach; ?></div></section>
<section class="mk-section"><h2>Gewürze entdecken</h2><div class="mk-grid"><?php foreach (array_slice($mkAtlas['ingredients'] ?? [], 0, 100) as $ingredient): ?><article class="mk-card"><h3><?= mk_h($ingredient['name'] ?? '') ?></h3><p><?= mk_h($ingredient['description'] ?? $ingredient['form'] ?? '') ?></p></article><?php endforeach; ?></div></section>
<section class="mk-section"><h2>Dein Gewürzregal und persönliche Etiketten</h2><p>Stelle deine eigene Gewürzmischung zusammen, speichere Rezepturen und beschrifte deine wiederverwendbaren Gewürzgläser. Entdecke außerdem ausgewählte Gewürze und passende Gläser.</p></section>
</main><noscript>Bitte aktiviere JavaScript für den interaktiven Mischungsrechner.</noscript></div>
<script src="data.php?v=2.0.0"></script><script>window.WGA_ASSET_BASE='assets/icons/';window.MEINKRAUT_CONFIG={base:'./'};</script><script src="meinkraut.js?v=2.0.3" defer></script></body></html>
