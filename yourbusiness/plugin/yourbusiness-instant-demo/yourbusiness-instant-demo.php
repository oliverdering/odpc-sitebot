<?php
/**
 * Plugin Name: YourBusiness Instant Demo
 * Description: Mobile-first local customer website demo, without publication or server-side personal data storage.
 * Version: 0.2.0
 * Author: ODPC
 */
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . 'discover.php';

// Isolated customer presentation: no WordPress theme header, launcher or unapproved legal copy.
add_action('template_redirect', function () {
  if (is_admin() || !isset($_GET['yb_kleine_freiheit']) || $_GET['yb_kleine_freiheit'] !== '1') return;
  $design = isset($_GET['design']) ? sanitize_key(wp_unslash($_GET['design'])) : 'klassisch';
  if (!in_array($design, ['klassisch', 'modern'], true)) $design = 'klassisch';
  status_header(200);
  nocache_headers();
  ?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="<?= $design === 'klassisch' ? '#183b34' : '#111e2b' ?>">
<meta name="description" content="Kleine Freiheit in Koblenz: Kultkneipe und Schlagerbar am Altenhof. Private ODPC-Kundendemo mit klassischem und modernem Webdesign.">
<title>Kleine Freiheit · Koblenz Altstadt · Kundenvorschau</title>
<style>
/* Independent presentation, deliberately namespaced to avoid inherited theme styles. */
*{box-sizing:border-box}
html{scroll-behavior:smooth;scroll-padding-top:140px}
body{margin:0}
#kf-demo{
 --bg:#f5f1e7;--fg:#183b34;--sub:#59665b;--paper:#fffcf5;--panel:#e9e5d8;
 --line:#d8d8c9;--accent:#96622f;--inverse:#fffaf0;--deep:#102e28;
 --shadow:0 20px 55px rgba(24,59,52,.09);--radius:3px;
 min-height:100vh;padding-bottom:95px;background:var(--bg);color:var(--fg);
 font-family:system-ui,-apple-system,"Segoe UI",sans-serif;font-size:16px;line-height:1.7;
 transition:background .26s,color .26s;overflow-x:hidden
}
#kf-demo.kf-theme-modern{
 --bg:#111e2b;--fg:#f7f3ea;--sub:#c2ccd2;--paper:#1a2c3c;--panel:#23394b;
 --line:#3a5160;--accent:#e9bd76;--inverse:#111e2b;--deep:#0b1520;
 --shadow:0 24px 80px rgba(0,0,0,.24);--radius:22px
}
#kf-demo *{box-sizing:border-box}
#kf-demo a{color:inherit;text-underline-offset:4px}
#kf-demo button{font:inherit}
#kf-demo a:focus-visible,#kf-demo button:focus-visible{outline:3px solid var(--accent);outline-offset:5px}
#kf-demo h1,#kf-demo h2,#kf-demo h3,#kf-demo p{margin-top:0}
#kf-demo h1,#kf-demo h2{font-family:Georgia,"Times New Roman",serif;font-weight:400;line-height:1.09;letter-spacing:-.035em}
#kf-demo h1{font-size:clamp(51px,6.1vw,90px);margin:24px 0}
#kf-demo h1 em{font-weight:400;color:var(--accent);font-style:italic}
#kf-demo h2{font-size:clamp(36px,4.6vw,61px);margin-bottom:22px}
#kf-demo h3{font-size:23px;line-height:1.28}
#kf-demo p{color:var(--sub)}
#kf-demo .kf-wrap{width:min(1240px,calc(100% - 80px));margin:0 auto}
#kf-demo .kf-skip{position:absolute;left:12px;top:-100px;background:var(--paper);padding:12px;z-index:40}
#kf-demo .kf-skip:focus{top:12px}
#kf-demo .kf-top{position:sticky;z-index:20;top:0;background:var(--bg);border-bottom:1px solid var(--line);box-shadow:0 5px 28px rgba(0,0,0,.04)}
#kf-demo .kf-topbar{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:16px 0 12px}
#kf-demo .kf-brand{font-weight:850;letter-spacing:.09em;line-height:1.2;font-size:17px;text-decoration:none;text-transform:uppercase;white-space:nowrap}
#kf-demo .kf-brand small{display:block;margin-top:5px;font-size:10px;letter-spacing:.16em;font-weight:500;color:var(--sub)}
#kf-demo .kf-modes{display:flex;align-items:center;gap:3px;border:1px solid var(--line);border-radius:100px;padding:4px;background:var(--paper)}
#kf-demo .kf-modes a{display:inline-flex;align-items:center;justify-content:center;min-height:41px;padding:8px 15px;border-radius:100px;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap}
#kf-demo .kf-modes a[aria-current=page]{background:var(--fg);color:var(--bg)}
#kf-demo .kf-nav{display:flex;align-items:center;gap:28px;padding:8px 0 13px;overflow-x:auto;scrollbar-width:thin;font-size:13px;white-space:nowrap}
#kf-demo .kf-nav a{text-decoration:none;font-weight:650;flex-shrink:0}
#kf-demo .kf-nav a:hover{color:var(--accent)}
#kf-demo .kf-nav a.kf-nav-contact{margin-left:auto;color:var(--accent)}
#kf-demo .kf-hero{padding:65px 0 46px;position:relative}
#kf-demo .kf-hero-grid{display:grid;grid-template-columns:minmax(0,1.03fr) minmax(0,.97fr);align-items:center;gap:65px}
#kf-demo .kf-kicker,#kf-demo .kf-eyebrow{font-size:11px;font-weight:750;letter-spacing:.2em;text-transform:uppercase;color:var(--accent)}
#kf-demo .kf-kicker:before,#kf-demo .kf-eyebrow:before{content:"";display:inline-block;width:28px;height:1px;background:currentColor;vertical-align:middle;margin-right:10px}
#kf-demo .kf-lead{font-size:19px;line-height:1.9;max-width:45ch}
#kf-demo .kf-actions{display:flex;flex-wrap:wrap;gap:12px;margin:30px 0}
#kf-demo .kf-button{display:inline-flex;gap:9px;justify-content:center;align-items:center;min-height:50px;padding:12px 19px;background:var(--fg);color:var(--bg)!important;text-decoration:none;font-weight:750;font-size:13px;border:1px solid var(--fg);border-radius:var(--radius)}
#kf-demo .kf-button.kf-outline{background:transparent;color:var(--fg)!important;border-color:var(--line)}
#kf-demo .kf-button:hover{filter:brightness(1.13)}
#kf-demo .kf-hero-facts{display:flex;gap:18px 32px;flex-wrap:wrap;border-top:1px solid var(--line);padding-top:22px;font-size:12px;color:var(--sub)}
#kf-demo .kf-hero-facts b{color:var(--fg)}
/* A drawn editorial motif, not a misleading photo of the venue. */
#kf-demo .kf-scene{position:relative;min-height:510px;overflow:hidden;border-radius:var(--radius);background:linear-gradient(145deg,#1c4439,#102a29 70%);border:10px solid var(--paper);box-shadow:var(--shadow)}
#kf-demo .kf-scene:before{content:"";position:absolute;width:87%;height:85%;left:7%;top:8%;border:2px solid rgba(249,232,185,.35);border-radius:240px 240px 8px 8px;background:radial-gradient(ellipse at 50% 32%,rgba(249,208,122,.35),transparent 65%),linear-gradient(155deg,#3d513f,#18362e)}
#kf-demo .kf-scene:after{content:"";position:absolute;left:-10%;right:-10%;height:90px;bottom:0;background:repeating-linear-gradient(90deg,#6d3f2d 0,#6d3f2d 25%,#784e34 26%,#784e34 50%);border-top:12px solid #b28a5a;box-shadow:0 -20px 70px #141f18}
#kf-demo .kf-lamp{position:absolute;z-index:1;left:50%;top:0;transform:translateX(-50%);height:142px;width:2px;background:#d7bb8e}
#kf-demo .kf-lamp:after{content:"";position:absolute;left:-53px;top:130px;width:108px;height:48px;background:linear-gradient(#d9ad69,#936735);border-radius:50% 50% 10px 10px;box-shadow:0 25px 55px 20px rgba(244,193,101,.22)}
#kf-demo .kf-scene-inner{position:absolute;z-index:2;top:47%;left:50%;transform:translate(-50%,-50%);width:84%;color:#fff6df;text-align:center}
#kf-demo .kf-scene-mark{font:400 clamp(70px,9vw,125px)/1 Georgia,serif;letter-spacing:-.09em}
#kf-demo .kf-scene-inner p{color:#e5d6b9!important;margin:15px 0 0;font-size:12px;letter-spacing:.24em;text-transform:uppercase}
#kf-demo .kf-chapter{padding:78px 0;border-top:1px solid var(--line)}
#kf-demo .kf-chapter-grid{display:grid;grid-template-columns:.75fr 1.25fr;gap:55px}
#kf-demo .kf-prose{max-width:750px;font-size:17px;line-height:1.95}
#kf-demo .kf-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin:35px 0 0}
#kf-demo .kf-card{background:var(--paper);padding:27px;border:1px solid var(--line);border-radius:var(--radius);box-shadow:0 8px 25px rgba(0,0,0,.02);min-width:0}
#kf-demo .kf-card .kf-number{font-size:11px;letter-spacing:.16em;color:var(--accent)}
#kf-demo .kf-card h3{margin:12px 0}
#kf-demo .kf-card p{font-size:14px;line-height:1.85;margin-bottom:0}
#kf-demo .kf-darkband{background:var(--deep);color:#fffaf0;padding:76px 0}
#kf-demo .kf-darkband h2{max-width:750px}
#kf-demo .kf-darkband p{color:#d5d9cc;max-width:720px}
#kf-demo .kf-darkband .kf-eyebrow{color:#d7bb8e}
#kf-demo .kf-venue-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:32px}
#kf-demo .kf-venue-strip div{border-top:1px solid rgba(255,255,255,.32);padding:20px 0;font-size:14px}
#kf-demo .kf-venue-strip strong{display:block;font-family:Georgia,serif;font-size:25px;font-weight:400;margin-bottom:8px}
#kf-demo .kf-drinks-note,#kf-demo .kf-fineprint{font-size:12px;line-height:1.8}
#kf-demo .kf-visit-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:70px;align-items:center}
#kf-demo .kf-contact-list{display:flex;flex-direction:column;align-items:flex-start;gap:12px;margin:28px 0}
#kf-demo .kf-contact-list a{font-weight:700;font-size:17px;overflow-wrap:anywhere}
#kf-demo .kf-contact-list address{font-style:normal;line-height:1.9}
#kf-demo .kf-address-art{border-radius:var(--radius);background:var(--panel);padding:48px 22px;text-align:center;border:1px solid var(--line)}
#kf-demo .kf-address-art strong{display:block;font:400 clamp(115px,16vw,240px)/1 Georgia,serif;letter-spacing:-.07em;color:var(--accent)}
#kf-demo .kf-address-art span{font-size:12px;letter-spacing:.2em;text-transform:uppercase}
#kf-demo .kf-legal-note{padding:25px;background:var(--panel);border-left:3px solid var(--accent);border-radius:var(--radius)}
#kf-demo .kf-legal-note p{margin:0;font-size:13px}
#kf-demo .kf-float-nav{position:fixed;z-index:30;bottom:max(16px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);display:flex;align-items:center;justify-content:center;gap:4px;max-width:calc(100% - 20px);padding:7px;background:#183b34;border:1px solid #8f9e86;border-radius:100px;box-shadow:0 12px 38px rgba(0,0,0,.28);color:#fffcf5}#kf-demo .kf-float-nav a{display:inline-flex;align-items:center;justify-content:center;padding:11px 14px;border-radius:50px;text-decoration:none;font-size:12px;font-weight:700;white-space:nowrap}#kf-demo .kf-float-nav a:hover,#kf-demo .kf-float-nav a:focus-visible{background:#fffaf0;color:#183b34}#kf-demo .kf-float-nav .kf-float-primary{background:#d7bb8e;color:#183b34}#kf-demo.kf-theme-modern .kf-float-nav{background:#0b1520;border-color:#516577}#kf-demo.kf-theme-modern .kf-float-nav .kf-float-primary{background:#e9bd76;color:#111e2b}#kf-demo .kf-footer{padding:30px 0 35px;border-top:1px solid var(--line)}
#kf-demo .kf-footer-inner{display:flex;justify-content:space-between;flex-wrap:wrap;gap:18px;font-size:12px}
#kf-demo .kf-footer a{text-decoration:underline}
#kf-demo .kf-footer .kf-credit{color:var(--sub)}
/* Modern is a separate composition, not merely another palette. */
#kf-demo.kf-theme-modern h1,#kf-demo.kf-theme-modern h2{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;font-weight:850;letter-spacing:-.07em}
#kf-demo.kf-theme-modern h1{font-size:clamp(58px,8.6vw,125px);line-height:.97}
#kf-demo.kf-theme-modern h1 em{font-style:normal;font-weight:850}
#kf-demo.kf-theme-modern h2{font-size:clamp(42px,5.6vw,76px)}
#kf-demo.kf-theme-modern .kf-hero{padding:95px 0 55px;background:radial-gradient(ellipse at 84% 20%,#27445a,transparent 56%)}
#kf-demo.kf-theme-modern .kf-hero-grid{grid-template-columns:1.22fr .78fr;gap:24px}
#kf-demo.kf-theme-modern .kf-scene{min-height:480px;border:1px solid var(--line);transform:rotate(2deg);border-radius:35px}
#kf-demo.kf-theme-modern .kf-scene:before{border-radius:40px;background:radial-gradient(circle at center,#aa754f 2%,#455769 39%,#1a3041 80%)}
#kf-demo.kf-theme-modern .kf-scene-inner .kf-scene-mark{font-family:system-ui,sans-serif;font-weight:900;letter-spacing:-.13em}
#kf-demo.kf-theme-modern .kf-scene:after{background:linear-gradient(90deg,#283f51,#e4ab63,#253747);border-top-color:#d99d56}
#kf-demo.kf-theme-modern .kf-kicker:before,#kf-demo.kf-theme-modern .kf-eyebrow:before{width:44px}
#kf-demo.kf-theme-modern .kf-chapter{padding:95px 0}
#kf-demo.kf-theme-modern .kf-card{background:linear-gradient(145deg,var(--paper),var(--panel));box-shadow:var(--shadow)}
#kf-demo.kf-theme-modern .kf-card h3{font-size:25px}
#kf-demo.kf-theme-modern .kf-button{background:var(--accent);border-color:var(--accent);color:#162431!important}
#kf-demo.kf-theme-modern .kf-button.kf-outline{background:transparent;color:var(--fg)!important;border-color:var(--line)}
#kf-demo.kf-theme-modern .kf-darkband{background:#0b1520}
#kf-demo.kf-theme-modern .kf-address-art{background:linear-gradient(135deg,var(--paper),var(--panel))}
#kf-demo.kf-theme-modern .kf-address-art strong{font-family:system-ui,sans-serif;font-weight:850}
@media(max-width:950px){
 #kf-demo .kf-wrap{width:calc(100% - 44px)}
 #kf-demo .kf-hero-grid,#kf-demo.kf-theme-modern .kf-hero-grid{gap:25px;grid-template-columns:1fr 1fr}
 #kf-demo .kf-scene,#kf-demo.kf-theme-modern .kf-scene{min-height:390px}
 #kf-demo .kf-chapter-grid{grid-template-columns:1fr 1.5fr;gap:32px}
 #kf-demo .kf-visit-grid{gap:30px}
}
@media(max-width:700px){
 html{scroll-padding-top:145px}
 #kf-demo .kf-wrap{width:calc(100% - 36px)}
 #kf-demo .kf-topbar{display:block;padding:13px 0 10px}
 #kf-demo .kf-brand{display:block;font-size:15px;margin-bottom:13px}
 #kf-demo .kf-modes{width:max-content;max-width:100%}
 #kf-demo .kf-modes a{padding:8px 12px;font-size:11px}
 #kf-demo .kf-nav{gap:20px;font-size:12px;padding:8px 0 11px}#kf-demo .kf-float-nav{width:calc(100% - 20px);justify-content:space-around;gap:0;padding:5px}#kf-demo .kf-float-nav a{font-size:11px;padding:10px 6px;flex:1}
 #kf-demo .kf-nav a.kf-nav-contact{margin-left:0}
 #kf-demo .kf-hero,#kf-demo.kf-theme-modern .kf-hero{padding:37px 0 34px}
 #kf-demo .kf-hero-grid,#kf-demo.kf-theme-modern .kf-hero-grid{display:flex;flex-direction:column;align-items:stretch;gap:25px}
 #kf-demo .kf-hero-copy{order:2}
 #kf-demo .kf-scene,#kf-demo.kf-theme-modern .kf-scene{order:1;min-height:235px;max-height:280px;transform:none}
 #kf-demo .kf-lamp{height:55px}
 #kf-demo .kf-lamp:after{top:50px;height:25px;width:75px;left:-37px}
 #kf-demo .kf-scene-mark{font-size:74px}
 #kf-demo .kf-scene-inner{top:57%}
 #kf-demo h1,#kf-demo.kf-theme-modern h1{font-size:clamp(45px,11vw,70px)}
 #kf-demo h2,#kf-demo.kf-theme-modern h2{font-size:clamp(36px,9vw,48px)}
 #kf-demo .kf-lead{font-size:16px;line-height:1.85}
 #kf-demo .kf-actions{gap:10px}
 #kf-demo .kf-button{flex:1 1 140px;min-height:48px;padding:12px}
 #kf-demo .kf-chapter,#kf-demo.kf-theme-modern .kf-chapter{padding:52px 0}
 #kf-demo .kf-chapter-grid{display:block}
 #kf-demo .kf-prose{font-size:16px}
 #kf-demo .kf-cards{grid-template-columns:1fr;gap:12px;margin-top:24px}
 #kf-demo .kf-card{padding:20px}
 #kf-demo .kf-card h3{font-size:21px;margin:5px 0}
 #kf-demo .kf-darkband{padding:53px 0}
 #kf-demo .kf-venue-strip{grid-template-columns:1fr;gap:0}
 #kf-demo .kf-venue-strip div{padding:13px 0}
 #kf-demo .kf-visit-grid{grid-template-columns:1fr}
 #kf-demo .kf-address-art{padding:22px}
 #kf-demo .kf-address-art strong{font-size:110px}
}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}#kf-demo{transition:none}}
@media print{#kf-demo .kf-top{position:static}#kf-demo .kf-modes,#kf-demo .kf-float-nav{display:none}}
</style>
</head>
<body>
<main id="kf-demo" class="kf-theme-<?= esc_attr($design) ?>" data-kf-mode="<?= esc_attr($design) ?>">
<a class="kf-skip" href="#kf-inhalt">Zum Inhalt springen</a>
<header class="kf-top">
  <div class="kf-wrap">
    <div class="kf-topbar">
      <a class="kf-brand" href="#kf-start">Kleine Freiheit <small>Koblenz · Altenhof 6 · Kult &amp; Schlagerbar</small></a>
      <nav class="kf-modes" aria-label="Design auswählen">
        <a data-kf-mode="klassisch" href="?yb_kleine_freiheit=1&amp;design=klassisch" <?= $design === 'klassisch' ? 'aria-current="page"' : '' ?>>Gemütlich · Klassisch</a>
        <a data-kf-mode="modern" href="?yb_kleine_freiheit=1&amp;design=modern" <?= $design === 'modern' ? 'aria-current="page"' : '' ?>>Modern</a>
      </nav>
    </div>
    <nav class="kf-nav" aria-label="Seitennavigation">
      <a href="#kf-start">Start</a><a href="#kf-about">Über uns</a><a href="#kf-drinks">Getränke</a><a href="#kf-story">Geschichte</a><a href="#kf-events">Musik &amp; Events</a><a href="#kf-visit">Anfahrt</a><a href="#kf-legal">Hinweise</a>
      <a class="kf-nav-contact" href="tel:+4915732500137">Anrufen ↗</a>
    </nav>
  </div>
</header>
<div id="kf-inhalt">
<section id="kf-start" class="kf-hero">
  <div class="kf-wrap kf-hero-grid">
    <div class="kf-hero-copy">
      <div class="kf-kicker">Ein Stück Koblenzer Altstadt</div>
      <h1>Deine kleine <em>Freiheit.</em></h1>
      <p class="kf-lead">Reinkommen, Freunde treffen, Musik genießen. Willkommen bei Kalle in der Kleinen Freiheit – der geselligen Kultkneipe am Altenhof.</p>
      <div class="kf-actions">
        <a class="kf-button" href="#kf-drinks">Getränke entdecken <span aria-hidden="true">↗</span></a>
        <a class="kf-button kf-outline" href="https://www.google.com/maps/search/?api=1&amp;query=Kleine%20Freiheit%20Altenhof%206%20Koblenz" target="_blank" rel="noopener noreferrer">Anfahrt planen <span aria-hidden="true">↗</span></a>
      </div>
      <div class="kf-hero-facts"><span><b>Altenhof 6</b> · 56068 Koblenz</span><span><b>Altstadtkneipe</b> · Musik &amp; Begegnung</span></div>
    </div>
    <div class="kf-scene" role="img" aria-label="Grafisches Gestaltungsmotiv mit warm beleuchteter Kneipenszene, keine Fotografie des Lokals">
      <div class="kf-lamp" aria-hidden="true"></div>
      <div class="kf-scene-inner"><div class="kf-scene-mark">K<span style="color:#e6b87b">&amp;</span>F</div><p>Altenhof · Koblenz</p></div>
    </div>
  </div>
</section>
<section id="kf-about" class="kf-chapter">
  <div class="kf-wrap kf-chapter-grid">
    <div class="kf-eyebrow">Willkommen</div>
    <div><h2>Hier trifft man sich.</h2><p class="kf-prose">Die Kleine Freiheit ist ein Ort für einen gemütlichen Feierabend, gute Gespräche und unbeschwerte Stunden. Ob Stammgast oder zum ersten Mal in der Koblenzer Altstadt: An der Theke steht das Miteinander im Mittelpunkt.</p></div>
  </div>
  <div class="kf-wrap kf-cards">
    <article class="kf-card"><span class="kf-number">01 / ATMOSPHÄRE</span><h3>Ganz ohne Eile</h3><p>Kneipencharme, persönliche Begegnungen und eine entspannte Auszeit im Herzen der Stadt.</p></article>
    <article class="kf-card"><span class="kf-number">02 / MUSIK</span><h3>Kult &amp; Schlager</h3><p>Ein Treffpunkt für alle, die gerne gemeinsam Musik hören, feiern und mitsingen.</p></article>
    <article class="kf-card"><span class="kf-number">03 / GASTGEBER</span><h3>Bei Kalle</h3><p>Der persönliche Charakter macht aus einem einfachen Abend einen vertrauten Treffpunkt.</p></article>
  </div>
</section>
<section id="kf-drinks" class="kf-chapter">
  <div class="kf-wrap"><span class="kf-eyebrow">An der Theke</span><h2>Für jeden Abend das Richtige.</h2><p class="kf-prose">Vom kühlen Bier bis zur alkoholfreien Erfrischung. Die konkrete Getränkekarte und aktuelle Preise werden noch mit dem Betreiber abgestimmt.</p>
    <div class="kf-cards">
      <article class="kf-card"><span class="kf-number">01</span><h3>Bier &amp; Klassiker</h3><p>Pils, Biermischgetränke und weitere Klassiker – Sortiment bitte vor Ort prüfen.</p></article>
      <article class="kf-card"><span class="kf-number">02</span><h3>Longdrinks &amp; Kurze</h3><p>Beliebte Mixgetränke, Liköre und Shots als unverbindlicher Kartenentwurf.</p></article>
      <article class="kf-card"><span class="kf-number">03</span><h3>Ohne Alkohol</h3><p>Softdrinks, Wasser und weitere erfrischende Alternativen als Sortimentsvorschlag.</p></article>
    </div>
    <p class="kf-drinks-note">Kundenvorschau: keine freigegebenen Markenlogos, Verkaufspreise oder verbindliche Getränkekarte. Bitte mit Kalle abstimmen.</p>
  </div>
</section>
<section id="kf-story" class="kf-chapter">
  <div class="kf-wrap kf-chapter-grid">
    <div class="kf-eyebrow">Geschichte am Altenhof</div>
    <div><h2>Eine Adresse mit Charakter.</h2><p class="kf-prose">Das Gebäude am Altenhof 6 wurde 1954 auf einem historischen Bruchsteinkeller neu errichtet. Seine Geschichte als Gaststätte reicht in diese Zeit zurück. Später wurde die Kleine Freiheit als Rock- und Seemannskneipe bekannt; heute ist sie für ihre gesellige Kult- und Schlageratmosphäre geschätzt.</p><p class="kf-fineprint">Historische Angaben basieren auf dem ODPC-Projektdossier und werden vor endgültiger Veröffentlichung noch mit den Quellen abgeglichen.</p><a class="kf-button kf-outline" href="#kf-visit">Das Lokal besuchen ↗</a></div>
  </div>
</section>
<section id="kf-events" class="kf-darkband">
  <div class="kf-wrap">
    <div class="kf-eyebrow">Gemeinsam erleben</div><h2>Reinkommen. Wohlfühlen. Bleiben.</h2>
    <p>Ob spontane Runde mit Freunden oder ein besonderer Musikabend: In der Kleinen Freiheit wird aus dem Alltag ein geselliger Moment. Aktuelle Veranstaltungstermine werden nach Freigabe des Betreibers ergänzt.</p>
    <div class="kf-venue-strip"><div><strong>Kult &amp; Schlager</strong>Musik und Kneipenstimmung</div><div><strong>Freunde treffen</strong>Platz für Gespräche und Geselligkeit</div><div><strong>Altstadt erleben</strong>Mitten in Koblenz am Altenhof</div></div>
    <a class="kf-button" href="#kf-visit">So findest du uns ↗</a>
  </div>
</section>
<section id="kf-visit" class="kf-chapter">
  <div class="kf-wrap kf-visit-grid">
    <div><div class="kf-eyebrow">Besuch uns</div><h2>Wir sehen uns am Altenhof.</h2>
      <div class="kf-contact-list">
        <address><strong>Kleine Freiheit</strong><br>Altenhof 6<br>56068 Koblenz, Altstadt</address>
        <a href="tel:+4915732500137">01573 2500137 · Mobil anrufen</a>
        <a href="tel:+4926150040640">0261 50040640 · Festnetz anrufen</a>
        <a href="https://www.google.com/maps/search/?api=1&amp;query=Kleine%20Freiheit%20Altenhof%206%20Koblenz" target="_blank" rel="noopener noreferrer">Standort und Bewertungen bei Google Maps ansehen ↗</a>
      </div>
      <p class="kf-fineprint">Telefonnummern aus öffentlichen Verzeichnissen; noch nicht vom aktuellen Betreiber bestätigt. Die Öffnungszeiten werden für die endgültige Webseite vor Ort abgeglichen.</p>
      <div class="kf-actions"><a class="kf-button" href="https://www.google.com/maps/dir/?api=1&amp;destination=Altenhof%206%2C%2056068%20Koblenz" target="_blank" rel="noopener noreferrer">Route starten ↗</a><a class="kf-button kf-outline" href="tel:+4915732500137">Jetzt anrufen ↗</a></div>
    </div>
    <div class="kf-address-art" aria-label="Altenhof 6 in Koblenz"><span>Altenhof</span><strong>6</strong><span>Koblenz · Altstadt</span></div>
  </div>
</section>
<section id="kf-legal" class="kf-chapter">
  <div class="kf-wrap"><div class="kf-eyebrow">Transparenz</div><h2>Hinweise zur Vorschau.</h2>
    <div class="kf-legal-note"><p>Dies ist eine ODPC-Kundenpräsentation und noch keine rechtsverbindlich veröffentlichte Unternehmenswebseite. Vor dem regulären Livegang müssen der vollständige Name des Betreibers, Impressum und Datenschutzhinweise, Öffnungszeiten, Getränkesortiment und Preise sowie Nutzungsrechte für Originalfotos und Markenlogos freigegeben werden. Es wird hier kein Kontaktformular betrieben und es werden keine erfundenen Bewertungen oder Veranstaltungen angezeigt.</p></div>
  </div>
</section>
</div>
<nav class="kf-float-nav" aria-label="Schnellnavigation"><a href="#kf-start">Start</a><a href="#kf-drinks">Getränke</a><a href="#kf-visit">Anfahrt</a><a class="kf-float-primary" href="tel:+4915732500137">Anrufen ↗</a></nav>
<footer class="kf-footer">
  <div class="kf-wrap kf-footer-inner"><div><strong>Kleine Freiheit</strong> · Altenhof 6 · 56068 Koblenz</div><div><a href="#kf-start">Nach oben ↑</a> · <a href="#kf-legal">Rechtliche Hinweise</a></div><div class="kf-credit">Kundenvorschau · <a href="https://odpc.de/" target="_blank" rel="noopener noreferrer">powered by odpc.de</a></div></div>
</footer>
</main>
<script>
(function(){
  'use strict';
  var root=document.getElementById('kf-demo');
  if(!root)return;
  var controls=root.querySelectorAll('[data-kf-mode]');
  function setMode(mode,updateUrl){
    if(mode!=='modern'&&mode!=='klassisch')return;
    root.classList.remove('kf-theme-klassisch','kf-theme-modern');
    root.classList.add('kf-theme-'+mode);
    root.setAttribute('data-kf-mode',mode);
    for(var i=0;i<controls.length;i++){
      if(controls[i].getAttribute('data-kf-mode')===mode)controls[i].setAttribute('aria-current','page');
      else controls[i].removeAttribute('aria-current');
    }
    var m=document.querySelector('meta[name="theme-color"]');
    if(m)m.setAttribute('content',mode==='modern'?'#111e2b':'#183b34');
    if(updateUrl&&window.history&&window.history.replaceState){
      var url=new URL(window.location.href);
      url.searchParams.set('yb_kleine_freiheit','1');
      url.searchParams.set('design',mode);
      window.history.replaceState(null,'',url.pathname+url.search+url.hash);
    }
  }
  for(var j=0;j<controls.length;j++)controls[j].addEventListener('click',function(e){
    e.preventDefault();setMode(this.getAttribute('data-kf-mode'),true);
  });
  window.addEventListener('popstate',function(){
    var mode=new URLSearchParams(window.location.search).get('design');
    setMode(mode==='modern'?'modern':'klassisch',false);
  });
})();
</script>
</body>
</html>
<?php
  exit;
}, 0);

// Direct mobile builder route; independent of pretty-permalink configuration.
add_action('template_redirect', function () {
  if (is_admin() || !isset($_GET['yb_start']) || $_GET['yb_start'] !== '1') return;
  status_header(200);
  nocache_headers();
  get_header();
  echo '<main id="yb-mobile-builder" style="max-width:1440px;margin:30px auto;padding:0 16px">';
  echo '<h1>YourBusiness – Webseite direkt vor Ort erstellen</h1>';
  echo do_shortcode('[yourbusiness_demo]');
  echo '</main>';
  get_footer();
  exit;
}, 1);

// Keep the presentation homepage intact while offering an obvious mobile entry point.
add_action('wp_footer', function () {
  if (is_admin() || !is_front_page() || isset($_GET['yb_start']) || isset($_GET['yb_kleine_freiheit'])) return;
  $link = add_query_arg('yb_start', '1', home_url('/'));
  echo '<div class="yb-launch-cta" style="position:fixed;z-index:9999;bottom:max(18px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);width:min(420px,calc(100% - 32px));text-align:center">';
  echo '<a href="' . esc_url($link) . '" style="display:block;background:#155eef;color:#fff;border-radius:14px;padding:15px 20px;box-shadow:0 6px 24px #162a4b55;font:700 16px system-ui,sans-serif;text-decoration:none">Jetzt Kundenwebseite live erstellen</a>';
  echo '</div>';
});
add_shortcode('yourbusiness_demo', function () {
  wp_enqueue_script(
    'yourbusiness-research',
    plugins_url('research.js', __FILE__),
    [],
    '0.2.0',
    true
  );
  wp_add_inline_script('yourbusiness-research', 'window.YourBusinessResearch = ' . wp_json_encode([
    'url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('yb_discover')
  ]) . ';', 'before');
  ob_start(); ?>
<div id="yb-demo" class="yb-app">
  <div class="yb-editor">
    <h2>Website in Minuten</h2><p><strong>Nur Firmenname und Ort eingeben.</strong> Öffentliche Angaben automatisch recherchieren und danach eine private Webseiten-Vorschau erstellen.</p>
    <label>Firma <input id="yb-name" maxlength="100" placeholder="z. B. Müller Gebäudetechnik" required></label>
    <label>Branche <select id="yb-sector"><option>Handwerk</option><option>Gastronomie</option><option>Immobilien</option><option>Beratung</option><option>Einzelhandel</option><option>Dienstleistung</option><option>Gesundheit</option><option>Sonstiges</option></select></label>
    <label>Ort <input id="yb-city" maxlength="100" placeholder="z. B. Koblenz"></label>
    <button type="button" id="yb-location">Standort übernehmen (optional)</button>
    <label>Bestehende Website <input id="yb-site" type="url" placeholder="https://..."></label>
    <label>Google-Unternehmensprofil / Maps-Link <input id="yb-google" type="url" placeholder="https://maps.google.com/..."></label>
    <button type="button" id="yb-google-search">Firma in Google Maps suchen</button>
    <p class="yb-note">Der Suchbutton öffnet Google Maps. Firmendaten werden nicht automatisch übernommen; den geprüften Profil-Link bitte selbst eintragen.</p>
    <label>Leistungen (eine pro Zeile) <textarea id="yb-services" rows="3" placeholder="Beratung&#10;Montage&#10;Wartung"></textarea></label>
    <label>Telefon <input id="yb-phone" type="tel" placeholder="+49 ..."></label>
    <label>WhatsApp-Nummer <input id="yb-whatsapp" type="tel" placeholder="+49 ..."></label>
    <label>E-Mail <input id="yb-email" type="email" placeholder="kontakt@firma.de"></label>
    <label>Visitenkarte / Referenzfoto <input id="yb-card" type="file" accept="image/*" capture="environment"></label>
    <p class="yb-note">Visitenkarten werden hier nur als Referenz angezeigt; automatische Texterkennung folgt in einem weiteren Ausbauschritt.</p>
    <label>Logo <input id="yb-logo" type="file" accept="image/*"></label>
    <label>Fotos <input id="yb-photos" type="file" accept="image/*" multiple></label>
    <label>Hauptfarbe <input id="yb-color" type="color" value="#155eef"></label>
    <label><input id="yb-consent" type="checkbox"> Ich darf diese Unternehmensdaten und Bilder für die private Demo verwenden.</label>
    <button type="button" id="yb-build">Vorschau erstellen</button>
    <p id="yb-status" role="status" aria-live="polite"></p>
  </div>
  <div class="yb-preview" id="yb-preview" aria-label="Website Vorschau"><div class="yb-empty">Hier erscheint die Kundenvorschau.</div></div>
</div>
<style>
.yb-app{display:grid;grid-template-columns:minmax(280px,370px) minmax(0,1fr);gap:24px;align-items:start;font-family:system-ui,sans-serif}.yb-editor{background:#f5f7fb;padding:22px;border-radius:20px}.yb-editor h2{margin:0 0 8px}.yb-editor label{display:block;font-weight:650;margin:12px 0}.yb-editor input:not([type=checkbox]),.yb-editor textarea,.yb-editor select{display:block;width:100%;box-sizing:border-box;padding:11px;border:1px solid #cbd5e1;border-radius:9px;margin-top:5px;font:inherit}.yb-editor input[type=color]{height:45px;padding:3px}.yb-editor button{background:#155eef;color:white;border:0;border-radius:10px;padding:12px 15px;cursor:pointer;margin:6px 6px 6px 0;font-weight:700}.yb-note{font-size:12px;color:#526179}.yb-preview{min-height:500px;border:1px solid #d7deea;border-radius:18px;overflow:hidden;background:white;box-shadow:0 15px 50px #152c5218}.yb-empty{padding:80px 24px;text-align:center;color:#64748b}.yb-hero{padding:60px 30px;color:white}.yb-hero h1{font-size:clamp(28px,4vw,54px);line-height:1.08;margin:10px 0}.yb-hero a{display:inline-block;background:white;color:#17243b;padding:12px 18px;border-radius:12px;text-decoration:none;font-weight:750}.yb-content{padding:30px}.yb-services{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px}.yb-service{padding:18px;background:#f3f6fa;border-radius:12px}.yb-gallery{display:flex;gap:8px;overflow:auto}.yb-gallery img{width:140px;height:110px;object-fit:cover;border-radius:10px}.yb-logo{max-height:70px;max-width:160px;object-fit:contain}.yb-contact{padding:25px;background:#f4f6f9}.yb-contact a{margin-right:14px;word-break:break-all}.yb-card-preview{max-width:100%;max-height:140px;object-fit:contain}@media(max-width:850px){.yb-app{grid-template-columns:1fr}.yb-hero{padding:36px 22px}}
</style>
<script>
(function(){
const root=document.getElementById('yb-demo');if(!root)return;
const el=id=>root.querySelector('#yb-'+id), val=id=>el(id).value.trim();
const status=s=>el('status').textContent=s;
const esc=s=>String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fileUrl=f=>f?URL.createObjectURL(f):'';
const safeUrl=s=>{try{const u=new URL(s);return ['https:','http:'].includes(u.protocol)?u.href:''}catch{return ''}};
el('google-search').addEventListener('click',()=>{
 const name=val('name'),city=val('city');
 if(!name||!city){status('Bitte zuerst Firmenname und Ort eingeben.');return}
 window.open('https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(name+' '+city),'_blank','noopener,noreferrer');
});
el('location').addEventListener('click',()=>{
 if(!navigator.geolocation){status('Standortfunktion nicht verfügbar. Bitte Ort manuell eingeben.');return}
 status('Standortfreigabe wird angefragt …');
 navigator.geolocation.getCurrentPosition(p=>{status('Standortkoordinaten erkannt ('+p.coords.latitude.toFixed(4)+', '+p.coords.longitude.toFixed(4)+'). Bitte Ort zusätzlich manuell eintragen; automatische Ortsauflösung folgt.')},()=>status('Keine Standortfreigabe. Ort bitte manuell eingeben.'),{timeout:10000,maximumAge:60000});
});
el('build').addEventListener('click',()=>{
 const name=val('name'),city=val('city'),sector=val('sector');
 if(!name||!city){status('Bitte Firma und Ort eingeben.');return}
 if(!el('consent').checked){status('Bitte die Berechtigung zur Nutzung der Daten für die Demo bestätigen.');return}
 const phone=val('phone'),wa=val('whatsapp').replace(/[^0-9]/g,''),email=val('email'),site=safeUrl(val('site')),google=safeUrl(val('google'));
 const services=val('services').split('\n').map(x=>x.trim()).filter(Boolean).slice(0,12);
 const items=services.length?services:['Persönliche Beratung','Zuverlässiger Service','Individuelle Lösungen'];
 const color=val('color'),logo=fileUrl(el('logo').files[0]),card=fileUrl(el('card').files[0]);
 const photos=Array.from(el('photos').files).slice(0,8).map(fileUrl);
 const heading=sector==='Gastronomie'?'Genuss und Gastfreundschaft':sector==='Handwerk'?'Qualität, auf die Sie bauen können':sector==='Immobilien'?'Immobilien mit Perspektive':'Persönlich. Professionell. Vor Ort.';
 const waLink=wa.length>=8&&wa.length<=15?'https://wa.me/'+wa+'?text='+encodeURIComponent('Guten Tag, ich habe Ihre Website gesehen und möchte eine Anfrage stellen.'):'';
 const contact=waLink?'<a href="'+esc(waLink)+'" target="_blank" rel="noopener">WhatsApp-Anfrage</a>':'';
 const preview='<div class="yb-hero" style="background:'+esc(color)+'">'+(logo?'<img class="yb-logo" src="'+logo+'" alt="Firmenlogo">':'')+'<p>'+esc(sector)+' · '+esc(city)+'</p><h1>'+esc(name)+'</h1><h2>'+esc(heading)+'</h2><p>Wir freuen uns auf Ihre Anfrage.</p><a href="#yb-contact">Kontakt aufnehmen</a></div><div class="yb-content"><h2>Unsere Leistungen</h2><div class="yb-services">'+items.map(s=>'<div class="yb-service">'+esc(s)+'</div>').join('')+'</div>'+(photos.length?'<h2>Einblicke</h2><div class="yb-gallery">'+photos.map(u=>'<img src="'+u+'" alt="Kundenfoto">').join('')+'</div>':'')+(card?'<h2>Visitenkarte (Referenz)</h2><img class="yb-card-preview" src="'+card+'" alt="Visitenkarte">':'')+'</div><div class="yb-contact" id="yb-contact"><h2>Kontakt</h2><p>'+esc(name)+' · '+esc(city)+'</p>'+contact+(phone?'<a href="tel:'+esc(phone.replace(/[^+0-9]/g,''))+'">Anrufen</a>':'')+(email?'<a href="mailto:'+encodeURIComponent(email)+'">E-Mail</a>':'')+(site?'<a href="'+esc(site)+'" target="_blank" rel="noopener">Website</a>':'')+(google?'<a href="'+esc(google)+'" target="_blank" rel="noopener">Google Maps</a>':'')+'</div>';
 el('preview').innerHTML=preview;
 status('Private Vorschau erstellt. Keine Veröffentlichung, keine Datenübertragung an den Server.');
 el('preview').scrollIntoView({behavior:'smooth',block:'start'});
});
})();
</script>
<?php return ob_get_clean();
});
