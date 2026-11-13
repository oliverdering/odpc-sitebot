<?php
/**
 * Plugin Name: YourBusiness Instant Demo
 * Description: Mobile-first local customer website demo, without publication or server-side personal data storage.
 * Version: 0.2.0
 * Author: ODPC
 */
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . 'discover.php';
require_once plugin_dir_path(__FILE__) . 'kf-gallery-admin.php';

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
#kf-demo .kf-float-nav{position:fixed;z-index:30;bottom:max(16px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);display:flex;align-items:center;justify-content:center;gap:4px;max-width:calc(100% - 20px);padding:7px;background:#183b34;border:1px solid #8f9e86;border-radius:100px;box-shadow:0 12px 38px rgba(0,0,0,.28);color:#fffcf5}#kf-demo .kf-float-nav a{display:inline-flex;align-items:center;justify-content:center;padding:11px 14px;border-radius:50px;text-decoration:none;font-size:12px;font-weight:700;white-space:nowrap}#kf-demo .kf-float-nav a:hover,#kf-demo .kf-float-nav a:focus-visible{background:#fffaf0;color:#183b34}#kf-demo .kf-float-nav .kf-float-primary{background:#d7bb8e;color:#183b34}#kf-demo.kf-theme-modern .kf-float-nav{background:#0b1520;border-color:#516577}#kf-demo.kf-theme-modern .kf-float-nav .kf-float-primary{background:#e9bd76;color:#111e2b}/* Single universal floating toolbar: speech + navigation, no overlapping second bar. */
#kf-demo #kf-accessibility{position:fixed;z-index:99;bottom:max(12px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);display:flex;justify-content:space-around;align-items:center;gap:4px;width:min(440px,calc(100vw - 20px));max-width:calc(100vw - 20px);padding:6px;background:var(--paper);color:var(--fg);border:1px solid var(--line);border-radius:100px;box-shadow:0 12px 35px #0005;font:13px system-ui,sans-serif}
#kf-demo #kf-accessibility .kf-tool{display:inline-flex;flex:1 1 0;min-width:0;min-height:40px;justify-content:center;align-items:center;text-align:center;border:1px solid var(--line);border-radius:100px;background:var(--bg);color:var(--fg);font:700 13px system-ui,sans-serif;text-decoration:none;padding:5px;white-space:nowrap}
#kf-demo #kf-accessibility select.kf-tool{flex:0 1 65px;min-width:59px;padding:5px 3px;background:var(--bg);color:var(--fg)}
#kf-demo #kf-accessibility .kf-tool-primary{background:var(--accent);color:var(--inverse);border-color:var(--accent)}
#kf-demo .kf-visually-hidden{position:absolute!important;width:1px;height:1px;overflow:hidden;white-space:nowrap;clip-path:inset(50%)}
#kf-demo .kf-float-nav{display:none}
@media(max-width:390px){#kf-demo #kf-accessibility{gap:3px;padding:5px}#kf-demo #kf-accessibility .kf-tool{min-height:38px;padding:4px 2px}#kf-demo #kf-accessibility select.kf-tool{min-width:55px}}
#kf-demo .kf-footer{padding:30px 0 35px;border-top:1px solid var(--line)}
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
#kf-demo .kf-brand-scene{display:flex;align-items:center;justify-content:center;min-height:510px;background:radial-gradient(circle at 50% 35%,#54715b,#15382f 70%);padding:clamp(12px,3vw,35px)}
#kf-demo .kf-brand-scene:before,#kf-demo .kf-brand-scene:after{display:none}
#kf-demo .kf-approved-logo{display:block;width:min(100%,480px);height:auto;object-fit:contain;filter:drop-shadow(0 14px 28px rgba(0,0,0,.28))}
#kf-demo .kf-brand-fallback{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:13px;color:#fff3d3;border:3px solid #d9b56c;border-radius:50%;aspect-ratio:1;width:min(100%,410px);padding:24px;background:radial-gradient(circle,#27513e,#112b25);box-shadow:inset 0 0 0 7px #17392d,0 15px 35px #0004}
#kf-demo .kf-brand-fallback strong{font:italic 700 clamp(30px,4vw,58px)/1.1 Georgia,serif;color:#f9e3b0}
#kf-demo .kf-brand-fallback span{font-size:clamp(10px,1.2vw,14px);letter-spacing:.12em}
@media(max-width:700px){#kf-demo .kf-brand-scene,#kf-demo.kf-theme-modern .kf-brand-scene{min-height:290px;max-height:none}#kf-demo .kf-brand-fallback{width:min(100%,260px)}}

/* ODPC-LICENSED-PHOTO-SET-20261113: curated Commons material, all labelled as samples. */
#kf-demo .kf-photo-gallery{display:grid;grid-template-columns:1.13fr .87fr;gap:18px;margin-top:24px}
/* Real owner-supplied photos take precedence over unrelated stock examples. */
#kf-demo .kf-gallery-originals{grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
#kf-demo .kf-gallery-originals img{height:225px;max-width:100%;object-fit:cover}
#kf-demo .kf-brand-scene .kf-hero-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
#kf-demo .kf-brand-scene .kf-photo-brand{position:absolute;z-index:2;bottom:20px;left:20px;right:20px;padding:15px 20px;background:linear-gradient(90deg,#11271fe8,#11271fbb);color:#ffefc9;border-radius:var(--radius);text-shadow:0 2px 10px #0004}
#kf-demo .kf-brand-scene .kf-photo-brand strong{display:block;font:italic 700 clamp(28px,4vw,52px)/1.1 Georgia,serif}
#kf-demo .kf-brand-scene .kf-photo-brand span{display:block;margin-top:6px;font-size:10px;letter-spacing:.14em}
@media(max-width:700px){#kf-demo .kf-gallery-originals{grid-template-columns:repeat(2,minmax(0,1fr))}#kf-demo .kf-gallery-originals img{height:175px}#kf-demo .kf-brand-scene .kf-photo-brand strong{font-size:29px}}
@media(max-width:390px){#kf-demo .kf-gallery-originals{grid-template-columns:1fr}#kf-demo .kf-gallery-originals img{height:210px}}
#kf-demo .kf-photo-gallery figure{margin:0;min-width:0;background:var(--paper);border:1px solid var(--line);padding:10px;border-radius:var(--radius)}
#kf-demo .kf-photo-gallery img{display:block;width:100%;height:clamp(180px,27vw,315px);object-fit:cover;border-radius:var(--radius)}
#kf-demo .kf-photo-gallery figcaption{padding:12px 7px 4px;color:var(--sub);font-size:12px;line-height:1.65}
#kf-demo .kf-photo-gallery figcaption a{color:var(--fg)}
@media(max-width:700px){#kf-demo .kf-photo-gallery{grid-template-columns:1fr}}

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
      <a href="#kf-start">Start</a><a href="#kf-bilder">Bilder</a><a href="#kf-about">Über uns</a><a href="#kf-drinks">Getränke</a><a href="#kf-story">Geschichte</a><a href="#kf-events">Musik &amp; Events</a><a href="#kf-visit">Anfahrt</a><a href="#kf-legal">Hinweise</a>
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
      <p class="kf-lead">Reinkommen, Freunde treffen, Musik genießen. Willkommen bei Charly in der Kleinen Freiheit – der geselligen Kultkneipe am Altenhof.</p>
      <div class="kf-actions">
        <a class="kf-button" href="#kf-drinks">Getränke entdecken <span aria-hidden="true">↗</span></a>
        <a class="kf-button kf-outline" href="https://www.google.com/maps/search/?api=1&amp;query=Kleine%20Freiheit%20Altenhof%206%20Koblenz" target="_blank" rel="noopener noreferrer">Anfahrt planen <span aria-hidden="true">↗</span></a>
      </div>
      <div class="kf-hero-facts"><span><b>Altenhof 6</b> · 56068 Koblenz</span><span><b>Altstadtkneipe</b> · Musik &amp; Begegnung</span></div>
    </div>
    <div class="kf-scene kf-brand-scene" aria-label="Kleine Freiheit – Koblenz">
      <?php
      // Publish the approved emblem through WordPress Media and set its attachment ID
      // in the option 'kf_approved_logo_attachment_id'. Never substitute a fabricated logo.
      $kf_originals = odpc_kf_gallery_ids();
      $kf_hero_id = !empty($kf_originals) ? (int)$kf_originals[0] : 0;
      $kf_logo_id = absint(get_option('kf_approved_logo_attachment_id', 0));
      $kf_logo_url = $kf_logo_id ? wp_get_attachment_image_url($kf_logo_id, 'full') : false;
      if ($kf_hero_id) :
        echo wp_get_attachment_image($kf_hero_id, 'large', false, ['class'=>'kf-hero-photo','fetchpriority'=>'high','loading'=>'eager','decoding'=>'async','alt'=>'Originalaufnahme: die Holztheke der Kleinen Freiheit in Koblenz']);
      ?>
        <div class="kf-photo-brand"><strong>Kleine Freiheit</strong><span>ECHTE EINBLICKE · ALTENHOF 6</span></div>
      <?php elseif ($kf_logo_url) : ?>
        <img class="kf-approved-logo" src="<?= esc_url($kf_logo_url) ?>" alt="Freigegebenes Kleine-Freiheit-Logo" fetchpriority="high" decoding="async">
      <?php else : ?>
        <div class="kf-brand-fallback"><span class="kf-brand-place">KOBLENZ · ALTENHOF 6</span><strong>Kleine Freiheit</strong><span>KNEIPE &amp; SCHLAGERBAR</span></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<section id="kf-about" class="kf-chapter">
  <div class="kf-wrap kf-chapter-grid">
    <div class="kf-eyebrow">Willkommen</div>
    <div><h2>Hier trifft man sich.</h2><p class="kf-prose">Die Kleine Freiheit ist ein Ort für einen gemütlichen Feierabend, gute Gespräche und unbeschwerte Stunden. Ob Stammgast oder zum ersten Mal in der Koblenzer Altstadt: An der Theke steht das Miteinander im Mittelpunkt.</p></div>
  </div>
  <div class="kf-wrap kf-cards">
    <article class="kf-card"><span class="kf-number">01 / ATMOSPHÄRE</span><h3>Ganz ohne Eile</h3><p>Echte Kneipenatmosphäre, persönliche Begegnungen und eine entspannte Auszeit mitten in Koblenz. Das Lokal ist als Raucherkneipe gekennzeichnet.</p></article>
    <article class="kf-card"><span class="kf-number">02 / MUSIK</span><h3>Kult &amp; Schlager</h3><p>Musik, Fußballschals, Dartautomat und eine lange Holztheke prägen den Gastraum – zu sehen auf den Originalfotos vom November 2026.</p></article>
    <article class="kf-card"><span class="kf-number">03 / GASTGEBER</span><h3>Bei Charly</h3><p>Gastgeber Karl-Heinz, genannt Charly, führt die Kneipe nach den Angaben vor Ort seit November 2025.</p></article>
  </div>
</section>

<section id="kf-bilder" class="kf-chapter" aria-label="Foto-Impressionen">
  <div class="kf-wrap">
    <span class="kf-eyebrow">Koblenzer Altstadt · Foto-Impressionen</span>
    <h2>Hier hat jeder Abend seine Geschichte.</h2>
    <p class="kf-prose">Die Originalaufnahmen vom 13. November 2026 zeigen Theke, Gastraum, Gäste, Eingang und Kneipenschild. Bis zum WordPress-Fotoimport werden noch gekennzeichnete Platzhalterbilder angezeigt.</p>
    <?php $kf_saved = odpc_kf_gallery_ids(); ?>
    <?php if ($kf_saved) : ?>
    <div class="kf-photo-gallery kf-gallery-originals">
    <?php foreach ($kf_saved as $kf_photo_id) : ?>
      <figure>
        <?= wp_get_attachment_image($kf_photo_id, 'large', false, ['loading'=>'lazy','decoding'=>'async','class'=>'kf-real-photo']) ?>
        <figcaption><?= esc_html(get_post_meta($kf_photo_id, '_wp_attachment_image_alt', true) ?: get_the_title($kf_photo_id)) ?> · Originalaufnahme der Kleinen Freiheit, November 2026</figcaption>
      </figure>
    <?php endforeach; ?>
    </div>
    <?php else : ?>
    <div class="kf-photo-gallery">
      <figure><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Altstadt_Koblenz%2C_der_Platz_%22Am_Plan%22.jpg/960px-Altstadt_Koblenz%2C_der_Platz_%22Am_Plan%22.jpg" alt="Historischer Platz Am Plan in der Koblenzer Altstadt; nicht die Kleine Freiheit" loading="lazy" decoding="async">
        <figcaption>Altstadt Koblenz, Platz Am Plan · Foto: <a href="https://commons.wikimedia.org/wiki/File:Altstadt_Koblenz,_der_Platz_%22Am_Plan%22.jpg" target="_blank" rel="noopener noreferrer">Willy Horsch / Wikimedia Commons (CC BY 3.0)</a> · noch kein Originalfoto des Lokals.</figcaption>
      </figure>
      <figure><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/aa/Pub_interior_-_geograph.org.uk_-_5856308.jpg/960px-Pub_interior_-_geograph.org.uk_-_5856308.jpg" alt="Symbolbild eines Pub-Interieurs in England; nicht die Kleine Freiheit" loading="lazy" decoding="async">
        <figcaption>Symbolbild Kneipenatmosphäre · Foto: <a href="https://commons.wikimedia.org/wiki/File:Pub_interior_-_geograph.org.uk_-_5856308.jpg" target="_blank" rel="noopener noreferrer">Bob Harvey / Wikimedia Commons (CC BY-SA 2.0)</a> · kein Foto des Lokals.</figcaption>
      </figure>
    </div>
    <?php endif; ?>
    <?php if (current_user_can('upload_files')) : ?>
      <p class="kf-admin-import-link"><a class="kf-button kf-outline" href="<?= esc_url(admin_url('admin.php?page=odpc-kf-originalfotos')) ?>">15 Originalfotos dauerhaft in WordPress importieren ↗</a></p>
    <?php endif; ?>
    <label id="kf-local-photo-picker" style="display:inline-block;margin-top:18px;padding:12px 18px;border:1px solid var(--line);border-radius:var(--radius);background:var(--paper);font-size:13px;font-weight:800;cursor:pointer">📷 Eigene Fotos für die Vorschau auswählen
      <input id="kf-local-photo-input" type="file" accept="image/*" multiple style="display:block;max-width:100%;font-size:12px;margin-top:8px">
    </label>
    <p class="kf-fineprint">Die ausgewählten Bilder werden ausschließlich im Browser angezeigt. Keine Übertragung oder Veröffentlichung. Erst nach Genehmigung dauerhaft in die Kundenwebsite übernehmen.</p>
  </div>
</section>
<script>
(function(){
  const picker=document.getElementById('kf-local-photo-input');
  const figures=Array.from(document.querySelectorAll('#kf-bilder .kf-photo-gallery figure'));
  if(!picker)return;
  let photoUrls=[];
  picker.addEventListener('change',()=>{
    photoUrls.forEach(url=>URL.revokeObjectURL(url));
    photoUrls=[];
    const uploaded=Array.from(picker.files||[]).filter(f=>f.type.startsWith('image/')).slice(0,2);
    uploaded.forEach((file,i)=>{
      const url=URL.createObjectURL(file);
      photoUrls.push(url);
      const img=figures[i].querySelector('img');
      img.src=url;img.alt='Lokal ausgewähltes Kundenfoto (nur Browser-Vorschau)';
      const caption=figures[i].querySelector('figcaption');
      caption.textContent='Lokal ausgewähltes Originalfoto – nur für diese Vorschau, nicht hochgeladen';
    });
  });
})();
</script>
<section id="kf-drinks" class="kf-chapter">
  <div class="kf-wrap"><span class="kf-eyebrow">An der Theke</span><h2>Für jeden Abend das Richtige.</h2><p class="kf-prose">Vom kühlen Bier bis zur alkoholfreien Erfrischung. Die konkrete Getränkekarte und aktuelle Preise werden noch mit dem Betreiber abgestimmt.</p>
    <div class="kf-cards">
      <article class="kf-card"><span class="kf-number">01</span><h3>Bier &amp; Klassiker</h3><p>Pils, Biermischgetränke und weitere Klassiker – Sortiment bitte vor Ort prüfen.</p></article>
      <article class="kf-card"><span class="kf-number">02</span><h3>Longdrinks &amp; Kurze</h3><p>Beliebte Mixgetränke, Liköre und Shots als unverbindlicher Kartenentwurf.</p></article>
      <article class="kf-card"><span class="kf-number">03</span><h3>Ohne Alkohol</h3><p>Softdrinks, Wasser und weitere erfrischende Alternativen als Sortimentsvorschlag.</p></article>
    </div>
    <p class="kf-drinks-note">Kundenvorschau: keine freigegebenen Markenlogos, Verkaufspreise oder verbindliche Getränkekarte. Bitte mit Charly abstimmen.</p>
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
        <span class="kf-fineprint">Ein weiterer Festnetz-Eintrag existiert in älteren Verzeichnissen, ist aber für die aktuelle Besetzung nicht bestätigt.</span>
        <a href="https://www.google.com/maps/search/?api=1&amp;query=Kleine%20Freiheit%20Altenhof%206%20Koblenz" target="_blank" rel="noopener noreferrer">Standort und Bewertungen bei Google Maps ansehen ↗</a>
      </div>
      <p class="kf-fineprint">Die Mobilnummer 01573 2500137 ist auf einem Originalfoto des Ladenfensters zu erkennen; eine aktuelle Bestätigung durch Charly steht noch aus. Die veröffentlichten Öffnungszeiten sind vor endgültiger Freigabe abzugleichen. Hinweis: Raucherkneipe.</p>
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

<footer class="kf-footer">
  <div class="kf-wrap kf-footer-inner"><div><strong>Kleine Freiheit</strong> · Altenhof 6 · 56068 Koblenz</div><div><a href="#kf-start">Nach oben ↑</a> · <a href="#kf-legal">Rechtliche Hinweise</a></div><div class="kf-credit">Kundenvorschau · <a href="https://odpc.de/" target="_blank" rel="noopener noreferrer">powered by odpc.de</a></div></div>
</footer>
<nav id="kf-accessibility" role="navigation" aria-label="Kleine Freiheit: Navigation, Sprache und Vorlesen">
<label for="kf-voice-language" class="kf-visually-hidden">Vorlesesprache auswählen</label>
<select id="kf-voice-language" class="kf-tool" aria-label="Vorlesesprache">
  <option value="de-DE">DE</option><option value="en-US">EN</option><option value="nl-NL">NL</option><option value="fr-FR">FR</option><option value="ru-RU">RU</option><option value="tr-TR">TR</option><option value="es-ES">ES</option>
</select>
<button type="button" class="kf-tool" id="kf-read" title="Seite vorlesen" aria-label="Seite vorlesen">▶</button>
<button type="button" class="kf-tool" id="kf-stop" title="Vorlesen stoppen" aria-label="Vorlesen stoppen">■</button>
<button type="button" class="kf-tool" id="kf-mic" title="Spracheingabe" aria-label="Spracheingabe">🎙</button>
<a href="#kf-start" class="kf-tool" title="Zur Startseite" aria-label="Zur Startseite">⌂</a>
<a href="#kf-visit" class="kf-tool kf-tool-primary" title="Anfahrt & Kontakt" aria-label="Anfahrt & Kontakt">↗</a>
</nav><div id="kf-voice-status" role="status" aria-live="polite" style="position:fixed;bottom:78px;left:12px;z-index:100;background:var(--paper);color:var(--fg);border-radius:8px;padding:4px 8px;font:12px system-ui,sans-serif;max-width:90vw"></div>
<script>
(function(){'use strict';
var panel=document.getElementById('kf-accessibility'),lang=document.getElementById('kf-voice-language'),status=document.getElementById('kf-voice-status');
function say(s){status.textContent=s;}
document.getElementById('kf-read').addEventListener('click',function(){
if(!('speechSynthesis' in window)){say('Vorlesen wird von diesem Browser nicht unterstützt.');return;}
speechSynthesis.cancel();var content=document.querySelector('#kf-inhalt');var u=new SpeechSynthesisUtterance(content?content.innerText:'');u.lang=lang.value;u.rate=.95;u.onerror=function(){say('Sprachausgabe nicht verfügbar.');};speechSynthesis.speak(u);say('Vorlesen gestartet.');
});
document.getElementById('kf-stop').addEventListener('click',function(){if('speechSynthesis' in window)speechSynthesis.cancel();say('Vorlesen gestoppt.');});
document.getElementById('kf-mic').addEventListener('click',function(){var R=window.SpeechRecognition||window.webkitSpeechRecognition;if(!R){say('Spracheingabe wird von diesem Browser nicht unterstützt.');return;}var rec=new R();rec.lang=lang.value;rec.onresult=function(e){var q=e.results[0][0].transcript;say('Erkannt: '+q);};rec.onerror=function(){say('Spracheingabe fehlgeschlagen oder nicht erlaubt.');};rec.start();say('Ich höre zu …');});
})();
</script>
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


/**
 * ODPC on-site micro demo for Emin's Grill, Koblenz (13 Nov 2026).
 * Isolated preview only. Public directory information is unverified.
 * No Google Translate, no third-party tracking, no fabricated customer material.
 */
add_action('template_redirect', function () {
  if (is_admin() || !isset($_GET['yb_emins_grill']) || $_GET['yb_emins_grill'] !== '1') return;
  status_header(200);
  nocache_headers();
  ?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow,noarchive">
<meta name="theme-color" content="#17382d">
<meta name="description" content="Emin's Grill, Koblenz: mobile und zweisprachig erweiterbare ODPC-Kundenvorschau. Noch kein offizieller Webauftritt.">
<title>Emin's Grill · Koblenz · ODPC Kundenvorschau</title>
<style>
:root{color-scheme:light;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
*{box-sizing:border-box}
html{scroll-behavior:smooth;scroll-padding-top:110px}
body{margin:0}
button,a{touch-action:manipulation}
button{font:inherit;cursor:pointer}
#em-demo{--bg:#f5f1e6;--surface:#fffdf5;--ink:#17382d;--muted:#657367;--accent:#bf6d35;--gold:#deb46f;--line:#d9dece;--deep:#102b22;--panel:#e8e7d7;--radius:6px;min-height:100vh;background:var(--bg);color:var(--ink);padding-bottom:88px;transition:background .25s,color .25s}
#em-demo.modern{--bg:#0a121c;--surface:#142331;--ink:#fff4de;--muted:#b4c5cc;--accent:#f1ad51;--gold:#f5cc81;--line:#314558;--deep:#07101a;--panel:#1b3142;--radius:22px}
#em-demo a{color:inherit;text-underline-offset:4px}
#em-demo :is(a,button,input):focus-visible{outline:3px solid var(--accent);outline-offset:4px}
.wrap{max-width:1180px;margin:auto;padding-inline:clamp(18px,5vw,46px)}
.demo-note{background:#172c25;color:#eaf2df;text-align:center;padding:8px 15px;font-size:11px;letter-spacing:.06em}
.top{position:sticky;top:0;z-index:15;background:var(--bg);border-bottom:1px solid var(--line);backdrop-filter:blur(15px)}
.top-inner{min-height:74px;display:flex;align-items:center;justify-content:space-between;gap:15px}
.brand{text-decoration:none;font-size:18px;line-height:1;font-weight:900;letter-spacing:-.025em;white-space:nowrap}
.brand span{color:var(--accent)}.brand small{display:block;font-size:9px;font-weight:650;letter-spacing:.17em;margin-top:8px;color:var(--muted)}
.top-controls{display:flex;align-items:center;gap:7px}
.pill{border:1px solid var(--line);background:var(--surface);border-radius:100px;padding:9px 13px;color:var(--ink);font-size:12px;font-weight:700}
.pill[aria-pressed="true"]{background:var(--ink);color:var(--bg)}
.lang{padding:9px 7px;border-radius:100px;border:1px solid var(--line);background:var(--surface);color:var(--ink);font-weight:700}
nav{display:flex;gap:20px;padding:0 0 12px;overflow-x:auto;white-space:nowrap;font-size:12px;font-weight:750}
nav a{text-decoration:none}
.hero{position:relative;overflow:hidden;padding:clamp(48px,7vw,95px) 0 76px}
.hero-grid{display:grid;grid-template-columns:1.12fr .88fr;gap:clamp(22px,5vw,75px);align-items:center}
.kicker{display:inline-flex;align-items:center;gap:9px;color:var(--accent);font-size:11px;font-weight:850;letter-spacing:.17em;text-transform:uppercase}
.kicker:before{content:"";height:2px;width:32px;background:currentColor}
h1,h2,h3,p{margin-top:0}
h1{font:400 clamp(57px,7.4vw,105px)/.98 Georgia,serif;letter-spacing:-.068em;margin:24px 0}
h1 em{color:var(--accent);font-weight:500}
h2{font:400 clamp(35px,5vw,61px)/1.04 Georgia,serif;letter-spacing:-.047em;margin-bottom:22px}
h3{font-size:22px;line-height:1.2}
.lead{color:var(--muted);font-size:clamp(16px,2vw,19px);line-height:1.8;max-width:49ch}
.actions{display:flex;flex-wrap:wrap;gap:11px;margin-top:30px}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:49px;padding:11px 20px;text-decoration:none;font-size:13px;font-weight:800;background:var(--ink);color:var(--bg)!important;border:1px solid var(--ink);border-radius:var(--radius)}
.btn.outline{background:transparent;color:var(--ink)!important;border-color:var(--line)}
.hero-art{min-height:440px;position:relative;isolation:isolate;border:12px solid var(--surface);border-radius:clamp(18px,4vw,38px);background:radial-gradient(circle at 40% 35%,#56805b 1%,#1a4333 63%,#10271f 100%);box-shadow:0 32px 80px #122d2428;overflow:hidden}
.hero-art .hero-photo{display:block;width:100%;height:100%;min-height:440px;object-fit:cover;position:absolute;inset:0;z-index:0;filter:saturate(1.04)}
.hero-art:before,.hero-art:after{display:none!important}
.hero-art .art-caption{z-index:3;letter-spacing:.04em;text-transform:none}
.food-card .visual{padding:0;overflow:hidden}
.food-card .visual img{width:100%;height:100%;object-fit:cover;display:block}
#em-demo .photo-row{grid-template-columns:repeat(3,minmax(0,1fr))}
#em-demo .photo-box{padding:0;position:relative;background:var(--panel);overflow:hidden}
#em-demo .photo-box img{width:100%;height:100%;object-fit:cover;display:block}
#em-demo .photo-credit{color:var(--muted);font-size:11px;line-height:1.8;margin:15px 0}
#em-demo .photo-credit a{color:var(--ink)}
@media(max-width:780px){#em-demo .hero-art .hero-photo{min-height:300px}}
@media(max-width:480px){#em-demo .hero-art .hero-photo{min-height:285px}#em-demo .photo-row{grid-template-columns:repeat(3,minmax(0,1fr));gap:7px}#em-demo .photo-box{height:112px}}
.hero-art:before{content:"";position:absolute;inset:8%;border:1px solid #ffffff55;border-radius:50%}
.hero-art:after{content:"";position:absolute;width:250px;height:250px;border:20px solid #dfc79f22;border-radius:50%;top:50%;left:50%;transform:translate(-50%,-50%)}
.plate{position:absolute;top:47%;left:50%;transform:translate(-50%,-50%) rotate(-14deg);width:290px;height:290px;border-radius:50%;background:radial-gradient(circle,#f4dfb0 0 48%,#c7ae82 51%,#faf2d8 56%,#c8b592 61%,#cab285 65%,transparent 66%);filter:drop-shadow(8px 14px 15px #061b15a0);display:grid;place-items:center}
.food-icon{font-size:130px;line-height:1;filter:drop-shadow(0 8px 5px #3e2c1a55)}
.art-caption{position:absolute;bottom:25px;left:24px;right:24px;padding:10px 15px;background:#0b201ac9;color:#fff4d8;border-radius:5px;text-align:center;font-size:11px;letter-spacing:.15em;text-transform:uppercase}
.stamp{position:absolute;z-index:2;right:20px;top:20px;border-radius:50%;width:88px;height:88px;display:grid;place-items:center;text-align:center;transform:rotate(12deg);background:var(--gold);color:#193026;font-size:11px;font-weight:900;line-height:1.2;box-shadow:0 8px 20px #0004}
.facts{display:flex;flex-wrap:wrap;gap:15px 32px;border-top:1px solid var(--line);margin-top:34px;padding-top:19px;color:var(--muted);font-size:12px}
.facts strong{color:var(--ink)}
section.section{padding:80px 0;border-top:1px solid var(--line)}
.section-intro{max-width:670px;color:var(--muted);line-height:1.7}
.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px;margin-top:34px}
.food-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:21px;min-width:0}
.food-card .visual{height:145px;display:grid;place-items:center;font-size:73px;background:var(--panel);border-radius:calc(var(--radius) - 2px);margin-bottom:19px}
.food-card h3{margin:0 0 12px}.food-card p{font-size:13px;color:var(--muted);line-height:1.7}
.ribbon{background:var(--deep);padding:74px 0;color:#f9f5e7}
.ribbon h2{max-width:790px}.ribbon p{max-width:700px;color:#cfdbd0;line-height:1.8}
.location-grid{display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:center}
.address{font-size:19px;line-height:1.8;font-style:normal;margin:25px 0}
.map-panel{min-height:330px;background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:32px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;gap:10px}
.map-panel .pin{font-size:76px}.map-panel strong{font-size:26px}
.note{margin-top:20px;color:var(--muted);font-size:12px;line-height:1.8}
.photo-row{display:grid;grid-template-columns:repeat(3,1fr);gap:13px;margin-top:24px}
.photo-box{height:190px;border:1px dashed var(--line);border-radius:var(--radius);background:var(--panel);display:flex;align-items:center;justify-content:center;gap:9px;flex-direction:column;color:var(--muted);font-size:12px;overflow:hidden;text-align:center;padding:10px}
.photo-box b{font-size:42px}.photo-box img{width:100%;height:100%;object-fit:cover}
.photo-btn{display:inline-block;margin-top:16px;cursor:pointer;font-size:13px;font-weight:750;border-bottom:1px solid var(--accent);padding-bottom:4px}
.photo-btn input{position:absolute;width:1px;height:1px;opacity:0}
footer{background:var(--deep);color:#fff2d9;padding:34px 0}
.footer-inner{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;font-size:12px}
.footer-inner a{color:#fff2d9}.footer-inner small{color:#c0ccbd}
.float{position:fixed;z-index:30;bottom:max(12px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);display:flex;gap:5px;width:min(95%,460px);padding:6px;border-radius:100px;background:#16352b;color:#fff9df;border:1px solid #668473;box-shadow:0 12px 35px #0005}
.float a,.float button{flex:1;display:grid;place-items:center;min-height:43px;background:transparent;border:0;border-radius:100px;color:inherit;text-decoration:none;font-size:12px;font-weight:800}
.float a.primary{background:#e7bd75;color:#162b22}
#em-demo.modern .hero{background:radial-gradient(ellipse at 65% 5%,#24445b 0,transparent 62%)}
#em-demo.modern h1,#em-demo.modern h2{font-family:system-ui,sans-serif;font-weight:900;letter-spacing:-.07em}
#em-demo.modern h1{font-size:clamp(61px,9vw,130px)}
#em-demo.modern .hero-art{border:1px solid var(--line);transform:rotate(3deg);background:radial-gradient(circle at 45% 45%,#34536f 0,#071320 68%)}
#em-demo.modern .hero-grid{grid-template-columns:1fr 1fr}
#em-demo.modern .food-card{background:linear-gradient(145deg,var(--surface),var(--panel))}
#em-demo.modern .float{background:#071320;border-color:#47617a}
@media(max-width:780px){.hero-grid,#em-demo.modern .hero-grid,.location-grid{grid-template-columns:1fr}.hero{padding:35px 0 55px}.hero-art{min-height:300px}.plate{width:205px;height:205px}.food-icon{font-size:94px}.cards{grid-template-columns:1fr 1fr}.location-grid{gap:28px}.top-inner{flex-wrap:wrap;min-height:66px;padding-block:12px}.top-controls{margin-left:auto}.brand{font-size:16px}nav{gap:15px}.photo-row{grid-template-columns:1fr 1fr}}
@media(max-width:480px){h1{font-size:58px}.top-inner{gap:8px}.pill{padding:8px 10px}.lang{padding:7px 5px;font-size:12px}.hero-art{min-height:285px}.cards{grid-template-columns:1fr}.food-card{display:grid;grid-template-columns:94px 1fr;gap:16px;align-items:center}.food-card .visual{height:94px;font-size:48px;margin:0}.food-card h3{font-size:19px}.food-card p{margin:0}.photo-box{height:142px}.section{padding:55px 0!important}}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
</style></head>
<body><main id="em-demo" class="classic">
<div class="demo-note" data-t="notice">ODPC-KUNDENVORSCHAU · NOCH KEINE OFFIZIELLE WEBSEITE · BETRIEBSDATEN VOR ORT PRÜFEN</div>
<header class="top"><div class="wrap">
<div class="top-inner">
<a class="brand" href="#start">EMIN'S <span>GRILL</span><small>KOBLENZ · ALTSTADT</small></a>
<div class="top-controls" aria-label="Darstellung und Sprache">
<button type="button" class="pill" id="em-classic" aria-pressed="true" data-t="classic">Klassisch</button>
<button type="button" class="pill" id="em-modern" aria-pressed="false" data-t="modern">Modern</button>
<select class="lang" id="em-lang" aria-label="Sprache"><option value="de">🇩🇪 DE</option><option value="en">🇬🇧 EN</option><option value="tr">🇹🇷 TR</option></select>
</div></div>
<nav aria-label="Navigation"><a href="#start" data-t="home">Start</a><a href="#speisekarte" data-t="menu">Speisekarte</a><a href="#fotos" data-t="photos">Fotos</a><a href="#besuch" data-t="visit">Anfahrt</a><a href="#kontakt" data-t="contact">Kontakt</a></nav>
</div></header>
<section class="hero" id="start"><div class="wrap hero-grid">
<div><span class="kicker" data-t="kicker">Dein Genussmoment in der Altstadt</span>
<h1 data-t="headline">Hier schmeckt <em>Koblenz.</em></h1>
<p class="lead" data-t="lead">Mitten in der Münzstraße. Schnell vorbeikommen, gemeinsam genießen und die Altstadt erleben. Diese Seite zeigt, wie Emin's Grill digital auftreten könnte.</p>
<div class="actions"><a class="btn" href="#speisekarte" data-t="menuSee">Speisekarte entdecken</a><a class="btn outline" href="#besuch" data-t="directions">Standort ansehen</a></div>
<div class="facts"><span>📍 <strong>Münzstraße 18</strong> · 56068 Koblenz</span><span>🥙 <span data-t="takeout">Gastronomie & Imbiss</span></span></div>
</div><div class="hero-art" role="img" aria-label="Illustrative Food-Grafik, kein echtes Produktfoto">
<div class="stamp" data-t="stamp">DEINE<br>PAUSE<br>IN KO</div><img class="hero-photo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fb/D%C3%B6ner_Kebab.jpg/960px-D%C3%B6ner_Kebab.jpg" alt="Lizenziertes Food-Symbolbild, kein Originalproduktfoto des Betriebs" fetchpriority="high" decoding="async">
<div class="art-caption" data-t="visualDisclaimer">Symbolfoto · nicht vom Betrieb</div></div>
</div></section>
<section class="section" id="speisekarte"><div class="wrap">
<span class="kicker" data-t="foodTag">Genuss auf einen Blick</span><h2 data-t="menuTitle">Für jeden Hunger<br>das Richtige.</h2>
<p class="section-intro" data-t="menuNote">Beispiel-Kategorien für die Kundenvorschau. Tatsächliches Sortiment, Allergene und Preise werden erst nach Bestätigung durch den Betreiber veröffentlicht.</p>
<div class="cards">
<article class="food-card"><div class="visual"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fb/D%C3%B6ner_Kebab.jpg/960px-D%C3%B6ner_Kebab.jpg" alt="Döner, Symbolbild" loading="lazy" decoding="async"></div><div><h3 data-t="cardOne">Döner & Klassiker</h3><p data-t="cardOneDesc">Platz für die Spezialitäten des Hauses und die beliebtesten Kombinationen.</p></div></article>
<article class="food-card"><div class="visual"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Pizza_Margherita_%289235473304%29.jpg/960px-Pizza_Margherita_%289235473304%29.jpg" alt="Pizza, Symbolbild" loading="lazy" decoding="async"></div><div><h3 data-t="cardTwo">Wraps & Snacks</h3><p data-t="cardTwoDesc">Übersichtliche Darstellung von kleinen und großen Mahlzeiten.</p></div></article>
<article class="food-card"><div class="visual"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Falafel_Sandwich_at_Utsunomiya.jpg/960px-Falafel_Sandwich_at_Utsunomiya.jpg" alt="Falafel, Symbolbild" loading="lazy" decoding="async"></div><div><h3 data-t="cardThree">Veggie & Beilagen</h3><p data-t="cardThreeDesc">Eine eigene Rubrik für weitere Angebote nach Freigabe.</p></div></article>
</div>
</div></section>
<section class="ribbon"><div class="wrap"><span class="kicker" data-t="ribbonTag">Für Stammgäste. Für Entdecker.</span>
<h2 data-t="ribbonTitle">Dein nächster Lieblingsstopp in Koblenz.</h2>
<p data-t="ribbonText">Gut auffindbar auf dem Smartphone, mit kurzer Speisekarte, direktem Kontakt und Routenplanung. Genau dafür ist diese ODPC-Demo gestaltet.</p></div></section>
<section class="section" id="fotos"><div class="wrap"><span class="kicker" data-t="photosTag">Echte Einblicke</span><h2 data-t="photosTitle">Der Laden. Das Essen. Die Menschen.</h2>
<p class="section-intro" data-t="photosText">Die Bilder sind lizenzierte Symbolfotos, keine Originalaufnahmen des Betriebs. Eigene Fotos lassen sich lokal austauschen; sie werden nicht zum Server hochgeladen.</p>
<div class="photo-row" id="em-gallery"><div class="photo-box"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Altstadt_Koblenz%2C_der_Platz_%22Am_Plan%22.jpg/960px-Altstadt_Koblenz%2C_der_Platz_%22Am_Plan%22.jpg" alt="Koblenzer Altstadt (Stadtmotiv)" loading="lazy" decoding="async"></div><div class="photo-box"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fb/D%C3%B6ner_Kebab.jpg/960px-D%C3%B6ner_Kebab.jpg" alt="Döner (Symbolbild)" loading="lazy" decoding="async"></div><div class="photo-box"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Falafel_Sandwich_at_Utsunomiya.jpg/960px-Falafel_Sandwich_at_Utsunomiya.jpg" alt="Falafel (Symbolbild)" loading="lazy" decoding="async"></div></div>
<p class="photo-credit">Demo-Symbolbilder, keine Aufnahmen des Betriebs. Bildnachweise: <a href="https://commons.wikimedia.org/wiki/File:D%C3%B6ner_Kebab.jpg" target="_blank" rel="noopener noreferrer">Döner: Tim Reckmann (CC BY 2.0)</a> · <a href="https://commons.wikimedia.org/wiki/File:Pizza_Margherita_(9235473304).jpg" target="_blank" rel="noopener noreferrer">Pizza: jeffreyw (CC BY 2.0)</a> · <a href="https://commons.wikimedia.org/wiki/File:Falafel_Sandwich_at_Utsunomiya.jpg" target="_blank" rel="noopener noreferrer">Falafel: Miyuki Meinaka (CC BY-SA 4.0)</a> · <a href="https://commons.wikimedia.org/wiki/File:Altstadt_Koblenz,_der_Platz_%22Am_Plan%22.jpg" target="_blank" rel="noopener noreferrer">Koblenz: Willy Horsch (CC BY 3.0)</a>. Bilddateien unverändert, responsiv angezeigt.</p>
<label class="photo-btn" data-t="addPhotos">📸 Fotos vor Ort auswählen <input id="em-pictures" type="file" accept="image/*" multiple></label>
</div></section>
<section class="section" id="besuch"><div class="wrap location-grid">
<div><span class="kicker" data-t="visitTag">Mitten in der Altstadt</span><h2 data-t="visitTitle">Du findest uns<br>ganz einfach.</h2>
<address class="address">Emin's Grill<br>Münzstraße 18<br>56068 Koblenz</address>
<a class="btn" href="https://www.google.com/maps/search/?api=1&amp;query=Emin%27s%20Grill%20M%C3%BCnzstra%C3%9Fe%2018%20Koblenz" target="_blank" rel="noopener noreferrer" data-t="maps">Route in Google Maps öffnen</a>
<p class="note" data-t="addressNote">Name und Adresse aus öffentlichen Brancheneinträgen. Vor Übergabe an den Betreiber auf Richtigkeit prüfen.</p>
</div><div class="map-panel"><span class="pin">📍</span><strong>KOBLENZ</strong><span>MÜNZSTRASSE 18</span><small data-t="mapLabel">Altstadt · zentral gelegen</small></div>
</div></section>
<section class="section" id="kontakt"><div class="wrap"><span class="kicker" data-t="contactTag">Dein direkter Draht</span><h2 data-t="contactTitle">Komm vorbei.</h2>
<p class="section-intro" data-t="contactText">Öffnungszeiten, Telefonnummer, WhatsApp und Impressum werden vom Betreiber vor der offiziellen Veröffentlichung bestätigt.</p>
<div class="actions"><a class="btn" href="https://www.google.com/maps/search/?api=1&amp;query=M%C3%BCnzstra%C3%9Fe%2018%2056068%20Koblenz" target="_blank" rel="noopener noreferrer" data-t="findUs">Jetzt finden</a>
<a class="btn outline" href="tel:+492619145263" data-t="call">Anrufen*</a></div>
<p class="note" data-t="callNote">*Telefonnummer stammt aus einem öffentlichen Verzeichnis und ist noch nicht durch den Betrieb bestätigt. Kein Bestellservice in dieser Demo.</p></div></section>
<footer><div class="wrap footer-inner"><div><strong>Emin's Grill · Koblenz</strong><br><small data-t="footerText">Unverbindliche private Kundendemonstration · keine offizielle Betreiberseite</small></div><div><span data-t="legal">Impressum & Datenschutz: vor Veröffentlichung durch Betreiber ergänzen</span><br><a href="https://odpc.de" target="_blank" rel="noopener noreferrer">powered by odpc.de</a></div></div></footer>
<div class="float" aria-label="Schnellaktionen"><a href="#start" data-t="floatHome">⌂ Start</a><a href="#speisekarte" data-t="floatMenu">☰ Karte</a><button type="button" id="em-speak" data-t="speak">🔊 Hören</button><a class="primary" href="#besuch" data-t="floatRoute">↗ Route</a></div>
</main>
<script>
(function(){
const root=document.getElementById('em-demo');
const classic=document.getElementById('em-classic'),modern=document.getElementById('em-modern'),langs=document.getElementById('em-lang');
const dict={
de:{notice:"ODPC-KUNDENVORSCHAU · NOCH KEINE OFFIZIELLE WEBSEITE · BETRIEBSDATEN VOR ORT PRÜFEN",classic:"Klassisch",modern:"Modern",home:"Start",menu:"Speisekarte",photos:"Fotos",visit:"Anfahrt",contact:"Kontakt",kicker:"Dein Genussmoment in der Altstadt",headline:"Hier schmeckt <em>Koblenz.</em>",lead:"Mitten in der Münzstraße. Schnell vorbeikommen, gemeinsam genießen und die Altstadt erleben. Diese Seite zeigt, wie Emin's Grill digital auftreten könnte.",menuSee:"Speisekarte entdecken",directions:"Standort ansehen",takeout:"Gastronomie & Imbiss",stamp:"DEINE<br>PAUSE<br>IN KO",visualDisclaimer:"Symbolfoto · keine Originalaufnahme des Betriebs",foodTag:"Genuss auf einen Blick",menuTitle:"Für jeden Hunger<br>das Richtige.",menuNote:"Beispiel-Kategorien für die Kundenvorschau. Tatsächliches Sortiment, Allergene und Preise werden erst nach Bestätigung durch den Betreiber veröffentlicht.",cardOne:"Döner & Klassiker",cardOneDesc:"Platz für die Spezialitäten des Hauses und die beliebtesten Kombinationen.",cardTwo:"Wraps & Snacks",cardTwoDesc:"Übersichtliche Darstellung von kleinen und großen Mahlzeiten.",cardThree:"Veggie & Beilagen",cardThreeDesc:"Eine eigene Rubrik für weitere Angebote nach Freigabe.",ribbonTag:"Für Stammgäste. Für Entdecker.",ribbonTitle:"Dein nächster Lieblingsstopp in Koblenz.",ribbonText:"Gut auffindbar auf dem Smartphone, mit kurzer Speisekarte, direktem Kontakt und Routenplanung. Genau dafür ist diese ODPC-Demo gestaltet.",photosTag:"Echte Einblicke",photosTitle:"Der Laden. Das Essen. Die Menschen.",photosText:"Die Bilder sind lizenzierte Symbolfotos, keine Originalaufnahmen des Betriebs. Eigene Fotos lassen sich lokal austauschen; sie werden nicht zum Server hochgeladen.",placeholderOne:"Ladenfoto",placeholderTwo:"Gerichte",placeholderThree:"Atmosphäre",addPhotos:"📸 Fotos vor Ort auswählen",visitTag:"Mitten in der Altstadt",visitTitle:"Du findest uns<br>ganz einfach.",maps:"Route in Google Maps öffnen",addressNote:"Name und Adresse aus öffentlichen Brancheneinträgen. Vor Übergabe an den Betreiber auf Richtigkeit prüfen.",mapLabel:"Altstadt · zentral gelegen",contactTag:"Dein direkter Draht",contactTitle:"Komm vorbei.",contactText:"Öffnungszeiten, Telefonnummer, WhatsApp und Impressum werden vom Betreiber vor der offiziellen Veröffentlichung bestätigt.",findUs:"Jetzt finden",call:"Anrufen*",callNote:"*Telefonnummer stammt aus einem öffentlichen Verzeichnis und ist noch nicht durch den Betrieb bestätigt. Kein Bestellservice in dieser Demo.",footerText:"Unverbindliche private Kundendemonstration · keine offizielle Betreiberseite",legal:"Impressum & Datenschutz: vor Veröffentlichung durch Betreiber ergänzen",floatHome:"⌂ Start",floatMenu:"☰ Karte",speak:"🔊 Hören",stop:"■ Stop",floatRoute:"↗ Route"},
en:{notice:"ODPC CLIENT PREVIEW · NOT AN OFFICIAL WEBSITE · VERIFY ALL DETAILS",classic:"Classic",modern:"Modern",home:"Home",menu:"Menu",photos:"Photos",visit:"Directions",contact:"Contact",kicker:"Your food stop in the old town",headline:"Taste <em>Koblenz.</em>",lead:"Right on Münzstraße in the heart of the old town. This preview shows what a dedicated website for Emin's Grill could look like.",menuSee:"Explore menu",directions:"Find us",takeout:"Restaurant & takeaway",stamp:"YOUR<br>FOOD<br>STOP",visualDisclaimer:"Stock example · not from the restaurant",foodTag:"At a glance",menuTitle:"Something for<br>every appetite.",menuNote:"Sample categories only. Actual menu items, allergens and prices require operator approval.",cardOne:"Kebab classics",cardOneDesc:"Space for house specialties and popular combinations.",cardTwo:"Wraps & snacks",cardTwoDesc:"An easy overview of quick and hearty meals.",cardThree:"Veggie & sides",cardThreeDesc:"A dedicated section for approved vegetarian and side options.",ribbonTag:"For locals. For visitors.",ribbonTitle:"Your next food stop in Koblenz.",ribbonText:"Easy to find on your phone, with menu, contact and directions. That is what this ODPC preview demonstrates.",photosTag:"A closer look",photosTitle:"The place. The food. The people.",photosText:"These are placeholders. Selected photos display only on your device, without server upload.",placeholderOne:"Storefront",placeholderTwo:"Food",placeholderThree:"Atmosphere",addPhotos:"📸 Select local photos",visitTag:"In the old town",visitTitle:"Easy<br>to find.",maps:"Open Google Maps route",addressNote:"Business name and address from public listings; verify in person.",mapLabel:"Central old town",contactTag:"Get in touch",contactTitle:"Drop by.",contactText:"Hours, phone, WhatsApp and legal details must be approved before launch.",findUs:"Find us",call:"Call*",callNote:"*Public-directory phone number not verified by the business. Ordering is unavailable in this preview.",footerText:"Private non-binding client demo · not an official operator site",legal:"Legal notice & privacy: to be completed before launch",floatHome:"⌂ Home",floatMenu:"☰ Menu",speak:"🔊 Listen",stop:"■ Stop",floatRoute:"↗ Route"},
tr:{notice:"ODPC MÜŞTERİ ÖN İZLEMESİ · RESMÎ WEB SİTESİ DEĞİLDİR · BİLGİLER DOĞRULANMALIDIR",classic:"Klasik",modern:"Modern",home:"Ana sayfa",menu:"Menü",photos:"Fotoğraflar",visit:"Yol tarifi",contact:"İletişim",kicker:"Eski şehirde lezzet molası",headline:"Koblenz'de <em>lezzet.</em>",lead:"Eski şehrin kalbindeki Münzstraße'de. Bu taslak, Emin's Grill için özel bir web sitesinin nasıl görünebileceğini gösterir.",menuSee:"Menüyü keşfet",directions:"Konumu gör",takeout:"Restoran ve paket servis",stamp:"LEZZET<br>MOLASI",visualDisclaimer:"Temsili fotoğraf · işletmeden değil",foodTag:"Lezzetler",menuTitle:"Her damak<br>tadına uygun.",menuNote:"Bunlar yalnızca örnek kategorilerdir. Gerçek menü, alerjen ve fiyat bilgileri işletmeci tarafından onaylanmalıdır.",cardOne:"Döner ve klasikler",cardOneDesc:"İşletmenin özel ürünleri için alan.",cardTwo:"Dürümler ve atıştırmalıklar",cardTwoDesc:"Yemek seçeneklerinin kolay sunumu.",cardThree:"Vejetaryen ve garnitür",cardThreeDesc:"Onaylanan diğer seçenekler için bölüm.",ribbonTag:"Yerel halk ve ziyaretçiler için",ribbonTitle:"Koblenz'deki bir sonraki lezzet durağın.",ribbonText:"Telefonlarda kolay erişim, menü, iletişim ve yol tarifi. ODPC demosunun amacı budur.",photosTag:"Yakından bakın",photosTitle:"Mekân. Yemekler. İnsanlar.",photosText:"Fotoğraf alanları örnektir. Seçilen fotoğraflar sunucuya yüklenmez.",placeholderOne:"Mekân",placeholderTwo:"Yemekler",placeholderThree:"Ortam",addPhotos:"📸 Fotoğraf seç",visitTag:"Eski şehrin merkezinde",visitTitle:"Bize ulaşmak<br>çok kolay.",maps:"Google Haritalar'da aç",addressNote:"İsim ve adres kamuya açık kayıtlardan; yerinde doğrulayın.",mapLabel:"Merkez · Eski şehir",contactTag:"İletişim",contactTitle:"Bekleriz.",contactText:"Çalışma saatleri, telefon, WhatsApp ve yasal bilgiler yayından önce onaylanmalıdır.",findUs:"Konumumuz",call:"Ara*",callNote:"*Telefon numarası işletme tarafından henüz doğrulanmamıştır. Demoda sipariş alınmaz.",footerText:"Bağlayıcı olmayan özel müşteri demosu · resmî site değil",legal:"Künye ve gizlilik metinleri yayından önce eklenmelidir",floatHome:"⌂ Başla",floatMenu:"☰ Menü",speak:"🔊 Dinle",stop:"■ Durdur",floatRoute:"↗ Rota"}
};
function translate(){const lang=langs.value;document.documentElement.lang=lang;for(const el of root.querySelectorAll('[data-t]')){const key=el.dataset.t;const v=(dict[lang]||dict.de)[key]||dict.de[key];if(v!==undefined){if(key==='addPhotos'){const input=el.querySelector('input');el.replaceChildren(document.createTextNode(v+' '));if(input)el.appendChild(input)}else if(['headline','stamp','menuTitle','visitTitle'].includes(key))el.innerHTML=v;else el.textContent=v}}}
function setTheme(mode){const isModern=mode==='modern';root.classList.toggle('modern',isModern);root.classList.toggle('classic',!isModern);classic.setAttribute('aria-pressed',String(!isModern));modern.setAttribute('aria-pressed',String(isModern));try{localStorage.setItem('odpc-em-mode',mode)}catch{}}
classic.addEventListener('click',()=>setTheme('classic'));modern.addEventListener('click',()=>setTheme('modern'));langs.addEventListener('change',translate);
try{setTheme(localStorage.getItem('odpc-em-mode')==='modern'?'modern':'classic')}catch{setTheme('classic')}
translate();
const file=document.getElementById('em-pictures'),gallery=document.getElementById('em-gallery');let urls=[];
file.addEventListener('change',()=>{urls.forEach(u=>URL.revokeObjectURL(u));urls=[];gallery.replaceChildren();const credit=root.querySelector('.photo-credit');if(credit)credit.hidden=true;for(const f of Array.from(file.files).filter(x=>x.type.startsWith('image/')).slice(0,3)){const u=URL.createObjectURL(f);urls.push(u);const img=document.createElement('img');img.src=u;img.alt='Vom Benutzer ausgewähltes lokales Foto';const box=document.createElement('div');box.className='photo-box';box.appendChild(img);gallery.appendChild(box)}});
const speak=document.getElementById('em-speak');let active=false;
speak.addEventListener('click',()=>{if(!('speechSynthesis' in window)){speak.textContent='TTS nicht verfügbar';return;}if(active){speechSynthesis.cancel();active=false;speak.textContent=dict[langs.value].speak;return;}speechSynthesis.cancel();const t=dict[langs.value];const msg=new SpeechSynthesisUtterance(['Emin’s Grill, Koblenz.',t.lead,t.menuNote].join(' '));msg.lang={de:'de-DE',en:'en-GB',tr:'tr-TR'}[langs.value];msg.rate=.94;msg.onend=msg.onerror=()=>{active=false;speak.textContent=t.speak};active=true;speak.textContent=t.stop;speechSynthesis.speak(msg)});
})();
</script></body></html>
  <?php
  exit;
}, 0);
/**
 * ODPC restaurant-specific micro demo for Döner Paradies, Koblenz (13 Nov 2026).
 * Isolated preview only. Public directory information is unverified.
 * No Google Translate, no third-party tracking, no fabricated customer material.
 */
add_action('template_redirect', function () {
  if (is_admin() || !isset($_GET['yb_doener_paradies']) || $_GET['yb_doener_paradies'] !== '1') return;
  status_header(200);
  nocache_headers();
  ?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow,noarchive">
<meta name="theme-color" content="#17382d">
<meta name="description" content="Döner Paradies, Koblenz: mobile und zweisprachig erweiterbare ODPC-Kundenvorschau. Noch kein offizieller Webauftritt.">
<title>Döner Paradies · Koblenz · ODPC Kundenvorschau</title>
<style>
:root{color-scheme:light;font-family:systdp-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
*{box-sizing:border-box}
html{scroll-behavior:smooth;scroll-padding-top:110px}
body{margin:0}
button,a{touch-action:manipulation}
button{font:inherit;cursor:pointer}
#dp-demo{--bg:#f5f1e6;--surface:#fffdf5;--ink:#17382d;--muted:#657367;--accent:#bf6d35;--gold:#deb46f;--line:#d9dece;--deep:#102b22;--panel:#e8e7d7;--radius:6px;min-height:100vh;background:var(--bg);color:var(--ink);padding-bottom:88px;transition:background .25s,color .25s}
#dp-demo.modern{--bg:#0a121c;--surface:#142331;--ink:#fff4de;--muted:#b4c5cc;--accent:#f1ad51;--gold:#f5cc81;--line:#314558;--deep:#07101a;--panel:#1b3142;--radius:22px}
#dp-demo a{color:inherit;text-underline-offset:4px}
#dp-demo :is(a,button,input):focus-visible{outline:3px solid var(--accent);outline-offset:4px}
.wrap{max-width:1180px;margin:auto;padding-inline:clamp(18px,5vw,46px)}
.demo-note{background:#172c25;color:#eaf2df;text-align:center;padding:8px 15px;font-size:11px;letter-spacing:.06em}
.top{position:sticky;top:0;z-index:15;background:var(--bg);border-bottom:1px solid var(--line);backdrop-filter:blur(15px)}
.top-inner{min-height:74px;display:flex;align-items:center;justify-content:space-between;gap:15px}
.brand{text-decoration:none;font-size:18px;line-height:1;font-weight:900;letter-spacing:-.025em;white-space:nowrap}
.brand span{color:var(--accent)}.brand small{display:block;font-size:9px;font-weight:650;letter-spacing:.17em;margin-top:8px;color:var(--muted)}
.top-controls{display:flex;align-items:center;gap:7px}
.pill{border:1px solid var(--line);background:var(--surface);border-radius:100px;padding:9px 13px;color:var(--ink);font-size:12px;font-weight:700}
.pill[aria-pressed="true"]{background:var(--ink);color:var(--bg)}
.lang{padding:9px 7px;border-radius:100px;border:1px solid var(--line);background:var(--surface);color:var(--ink);font-weight:700}
nav{display:flex;gap:20px;padding:0 0 12px;overflow-x:auto;white-space:nowrap;font-size:12px;font-weight:750}
nav a{text-decoration:none}
.hero{position:relative;overflow:hidden;padding:clamp(48px,7vw,95px) 0 76px}
.hero-grid{display:grid;grid-template-columns:1.12fr .88fr;gap:clamp(22px,5vw,75px);align-items:center}
.kicker{display:inline-flex;align-items:center;gap:9px;color:var(--accent);font-size:11px;font-weight:850;letter-spacing:.17em;text-transform:uppercase}
.kicker:before{content:"";height:2px;width:32px;background:currentColor}
h1,h2,h3,p{margin-top:0}
h1{font:400 clamp(57px,7.4vw,105px)/.98 Georgia,serif;letter-spacing:-.068em;margin:24px 0}
h1 em{color:var(--accent);font-weight:500}
h2{font:400 clamp(35px,5vw,61px)/1.04 Georgia,serif;letter-spacing:-.047em;margin-bottom:22px}
h3{font-size:22px;line-height:1.2}
.lead{color:var(--muted);font-size:clamp(16px,2vw,19px);line-height:1.8;max-width:49ch}
.actions{display:flex;flex-wrap:wrap;gap:11px;margin-top:30px}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:49px;padding:11px 20px;text-decoration:none;font-size:13px;font-weight:800;background:var(--ink);color:var(--bg)!important;border:1px solid var(--ink);border-radius:var(--radius)}
.btn.outline{background:transparent;color:var(--ink)!important;border-color:var(--line)}
.hero-art{min-height:440px;position:relative;isolation:isolate;border:12px solid var(--surface);border-radius:clamp(18px,4vw,38px);background:radial-gradient(circle at 40% 35%,#56805b 1%,#1a4333 63%,#10271f 100%);box-shadow:0 32px 80px #122d2428;overflow:hidden}
.hero-art .hero-photo{display:block;width:100%;height:100%;min-height:440px;object-fit:cover;position:absolute;inset:0;z-index:0;filter:saturate(1.04)}
.hero-art:before,.hero-art:after{display:none!important}
.hero-art .art-caption{z-index:3;letter-spacing:.04em;text-transform:none}
.food-card .visual{padding:0;overflow:hidden}
.food-card .visual img{width:100%;height:100%;object-fit:cover;display:block}
#dp-demo .photo-row{grid-template-columns:repeat(3,minmax(0,1fr))}
#dp-demo .photo-box{padding:0;position:relative;background:var(--panel);overflow:hidden}
#dp-demo .photo-box img{width:100%;height:100%;object-fit:cover;display:block}
#dp-demo .photo-credit{color:var(--muted);font-size:11px;line-height:1.8;margin:15px 0}
#dp-demo .photo-credit a{color:var(--ink)}
@media(max-width:780px){#dp-demo .hero-art .hero-photo{min-height:300px}}
@media(max-width:480px){#dp-demo .hero-art .hero-photo{min-height:285px}#dp-demo .photo-row{grid-template-columns:repeat(3,minmax(0,1fr));gap:7px}#dp-demo .photo-box{height:112px}}
.hero-art:before{content:"";position:absolute;inset:8%;border:1px solid #ffffff55;border-radius:50%}
.hero-art:after{content:"";position:absolute;width:250px;height:250px;border:20px solid #dfc79f22;border-radius:50%;top:50%;left:50%;transform:translate(-50%,-50%)}
.plate{position:absolute;top:47%;left:50%;transform:translate(-50%,-50%) rotate(-14deg);width:290px;height:290px;border-radius:50%;background:radial-gradient(circle,#f4dfb0 0 48%,#c7ae82 51%,#faf2d8 56%,#c8b592 61%,#cab285 65%,transparent 66%);filter:drop-shadow(8px 14px 15px #061b15a0);display:grid;place-items:center}
.food-icon{font-size:130px;line-height:1;filter:drop-shadow(0 8px 5px #3e2c1a55)}
.art-caption{position:absolute;bottom:25px;left:24px;right:24px;padding:10px 15px;background:#0b201ac9;color:#fff4d8;border-radius:5px;text-align:center;font-size:11px;letter-spacing:.15em;text-transform:uppercase}
.stamp{position:absolute;z-index:2;right:20px;top:20px;border-radius:50%;width:88px;height:88px;display:grid;place-items:center;text-align:center;transform:rotate(12deg);background:var(--gold);color:#193026;font-size:11px;font-weight:900;line-height:1.2;box-shadow:0 8px 20px #0004}
.facts{display:flex;flex-wrap:wrap;gap:15px 32px;border-top:1px solid var(--line);margin-top:34px;padding-top:19px;color:var(--muted);font-size:12px}
.facts strong{color:var(--ink)}
section.section{padding:80px 0;border-top:1px solid var(--line)}
.section-intro{max-width:670px;color:var(--muted);line-height:1.7}
.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px;margin-top:34px}
.food-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:21px;min-width:0}
.menu-snapshot{margin-top:34px;padding:27px;background:var(--surface);border:1px solid var(--line);border-radius:var(--radius)}
.menu-snapshot .menu-head{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:15px}
.menu-snapshot h3{margin:0 0 8px;font-size:clamp(22px,3vw,33px)}
.menu-snapshot ul{display:grid;grid-template-columns:1fr 1fr;gap:0 26px;padding:0;margin:23px 0;list-style:none}
.menu-snapshot li{display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid var(--line);font-size:14px}
.menu-snapshot li b{white-space:nowrap;color:var(--accent)}
.menu-snapshot .source-note{font-size:12px;color:var(--muted);line-height:1.7}
.menu-snapshot .source-note a{color:var(--ink);font-weight:700}
@media(max-width:640px){.menu-snapshot ul{grid-template-columns:1fr}.menu-snapshot{padding:18px}}
.food-card .visual{height:145px;display:grid;place-items:center;font-size:73px;background:var(--panel);border-radius:calc(var(--radius) - 2px);margin-bottom:19px}
.food-card h3{margin:0 0 12px}.food-card p{font-size:13px;color:var(--muted);line-height:1.7}
.ribbon{background:var(--deep);padding:74px 0;color:#f9f5e7}
.ribbon h2{max-width:790px}.ribbon p{max-width:700px;color:#cfdbd0;line-height:1.8}
.location-grid{display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:center}
.address{font-size:19px;line-height:1.8;font-style:normal;margin:25px 0}
.map-panel{min-height:330px;background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:32px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;gap:10px}
.map-panel .pin{font-size:76px}.map-panel strong{font-size:26px}
.note{margin-top:20px;color:var(--muted);font-size:12px;line-height:1.8}
.photo-row{display:grid;grid-template-columns:repeat(3,1fr);gap:13px;margin-top:24px}
.photo-box{height:190px;border:1px dashed var(--line);border-radius:var(--radius);background:var(--panel);display:flex;align-items:center;justify-content:center;gap:9px;flex-direction:column;color:var(--muted);font-size:12px;overflow:hidden;text-align:center;padding:10px}
.photo-box b{font-size:42px}.photo-box img{width:100%;height:100%;object-fit:cover}
.photo-btn{display:inline-block;margin-top:16px;cursor:pointer;font-size:13px;font-weight:750;border-bottom:1px solid var(--accent);padding-bottom:4px}
.photo-btn input{position:absolute;width:1px;height:1px;opacity:0}
footer{background:var(--deep);color:#fff2d9;padding:34px 0}
.footer-inner{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;font-size:12px}
.footer-inner a{color:#fff2d9}.footer-inner small{color:#c0ccbd}
.float{position:fixed;z-index:30;bottom:max(12px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);display:flex;gap:5px;width:min(95%,460px);padding:6px;border-radius:100px;background:#16352b;color:#fff9df;border:1px solid #668473;box-shadow:0 12px 35px #0005}
.float a,.float button{flex:1;display:grid;place-items:center;min-height:43px;background:transparent;border:0;border-radius:100px;color:inherit;text-decoration:none;font-size:12px;font-weight:800}
.float a.primary{background:#e7bd75;color:#162b22}
#dp-demo.modern .hero{background:radial-gradient(ellipse at 65% 5%,#24445b 0,transparent 62%)}
#dp-demo.modern h1,#dp-demo.modern h2{font-family:systdp-ui,sans-serif;font-weight:900;letter-spacing:-.07em}
#dp-demo.modern h1{font-size:clamp(61px,9vw,130px)}
#dp-demo.modern .hero-art{border:1px solid var(--line);transform:rotate(3deg);background:radial-gradient(circle at 45% 45%,#34536f 0,#071320 68%)}
#dp-demo.modern .hero-grid{grid-template-columns:1fr 1fr}
#dp-demo.modern .food-card{background:linear-gradient(145deg,var(--surface),var(--panel))}
#dp-demo.modern .float{background:#071320;border-color:#47617a}
@media(max-width:780px){.hero-grid,#dp-demo.modern .hero-grid,.location-grid{grid-template-columns:1fr}.hero{padding:35px 0 55px}.hero-art{min-height:300px}.plate{width:205px;height:205px}.food-icon{font-size:94px}.cards{grid-template-columns:1fr 1fr}.location-grid{gap:28px}.top-inner{flex-wrap:wrap;min-height:66px;padding-block:12px}.top-controls{margin-left:auto}.brand{font-size:16px}nav{gap:15px}.photo-row{grid-template-columns:1fr 1fr}}
@media(max-width:480px){h1{font-size:58px}.top-inner{gap:8px}.pill{padding:8px 10px}.lang{padding:7px 5px;font-size:12px}.hero-art{min-height:285px}.cards{grid-template-columns:1fr}.food-card{display:grid;grid-template-columns:94px 1fr;gap:16px;align-items:center}.food-card .visual{height:94px;font-size:48px;margin:0}.food-card h3{font-size:19px}.food-card p{margin:0}.photo-box{height:142px}.section{padding:55px 0!important}}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
</style></head>
<body><main id="dp-demo" class="classic">
<div class="demo-note" data-t="notice">ODPC-KUNDENVORSCHAU · NOCH KEINE OFFIZIELLE WEBSEITE · BETRIEBSDATEN VOR ORT PRÜFEN</div>
<header class="top"><div class="wrap">
<div class="top-inner">
<a class="brand" href="#start">DÖNER <span>PARADIES</span><small>KOBLENZ · MÜNZSTRASSE</small></a>
<div class="top-controls" aria-label="Darstellung und Sprache">
<button type="button" class="pill" id="dp-classic" aria-pressed="true" data-t="classic">Klassisch</button>
<button type="button" class="pill" id="dp-modern" aria-pressed="false" data-t="modern">Modern</button>
<select class="lang" id="dp-lang" aria-label="Sprache"><option value="de">🇩🇪 DE</option><option value="en">🇬🇧 EN</option><option value="tr">🇹🇷 TR</option></select>
</div></div>
<nav aria-label="Navigation"><a href="#start" data-t="home">Start</a><a href="#speisekarte" data-t="menu">Speisekarte</a><a href="#fotos" data-t="photos">Fotos</a><a href="#besuch" data-t="visit">Anfahrt</a><a href="#kontakt" data-t="contact">Kontakt</a></nav>
</div></header>
<section class="hero" id="start"><div class="wrap hero-grid">
<div><span class="kicker" data-t="kicker">Dein Genussmoment in der Altstadt</span>
<h1 data-t="headline">Hier schmeckt <em>Koblenz.</em></h1>
<p class="lead" data-t="lead">Original Berliner Kebap, Pizza und vegetarische Gerichte – mitten in der Koblenzer Altstadt. Diese ODPC-Demo zeigt eine eigenständige Website als Ergänzung zur vorhandenen Bestellplattform.</p>
<div class="actions"><a class="btn" href="#speisekarte" data-t="menuSee">Speisekarte entdecken</a><a class="btn outline" href="#besuch" data-t="directions">Standort ansehen</a><a class="btn outline" href="https://www.doenerparadieskoblenz.de/" target="_blank" rel="noopener noreferrer" data-t="order">Online bestellen ↗</a></div>
<div class="facts"><span>📍 <strong>Münzstraße 18</strong> · 56068 Koblenz</span><span>🥙 <span data-t="takeout">Gastronomie & Imbiss</span></span></div>
</div><div class="hero-art" role="img" aria-label="Illustrative Food-Grafik, kein echtes Produktfoto">
<div class="stamp" data-t="stamp">DEINE<br>PAUSE<br>IN KO</div><img class="hero-photo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Pizza_Margherita_%289235473304%29.jpg/960px-Pizza_Margherita_%289235473304%29.jpg" alt="Lizenziertes Food-Symbolbild, kein Originalproduktfoto des Betriebs" fetchpriority="high" decoding="async">
<div class="art-caption" data-t="visualDisclaimer">Symbolfoto · nicht vom Betrieb</div></div>
</div></section>
<section class="section" id="speisekarte"><div class="wrap">
<span class="kicker" data-t="foodTag">Genuss auf einen Blick</span><h2 data-t="menuTitle">Für jeden Hunger<br>das Richtige.</h2>
<p class="section-intro" data-t="menuNote">Beispiel-Kategorien für die Kundenvorschau. Tatsächliches Sortiment, Allergene und Preise werden erst nach Bestätigung durch den Betreiber veröffentlicht.</p>
<div class="cards">
<article class="food-card"><div class="visual"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fb/D%C3%B6ner_Kebab.jpg/960px-D%C3%B6ner_Kebab.jpg" alt="Döner, Symbolbild" loading="lazy" decoding="async"></div><div><h3 data-t="cardOne">Original Berliner Kebap</h3><p data-t="cardOneDesc">Drehspieß-Sandwich, Dürüm und Lahmacun als eigene Speisekartenrubrik.</p></div></article>
<article class="food-card"><div class="visual"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Pizza_Margherita_%289235473304%29.jpg/960px-Pizza_Margherita_%289235473304%29.jpg" alt="Pizza, Symbolbild" loading="lazy" decoding="async"></div><div><h3 data-t="cardTwo">Pizza & Wunsch-Pizza</h3><p data-t="cardTwoDesc">Klassische Pizza, Pizza Paradies und Wunsch-Pizza übersichtlich präsentiert.</p></div></article>
<article class="food-card"><div class="visual"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Falafel_Sandwich_at_Utsunomiya.jpg/960px-Falafel_Sandwich_at_Utsunomiya.jpg" alt="Falafel, Symbolbild" loading="lazy" decoding="async"></div><div><h3 data-t="cardThree">Falafel & Halloumi</h3><p data-t="cardThreeDesc">Vegetarische Alternativen, Salate und Beilagen – Sortiment vor Veröffentlichung prüfen.</p></div></article>
</div>
<!-- DP_MENU_SNAPSHOT_20261113: independently sourced food-menu preview, prices require operator confirmation. -->
<div class="menu-snapshot" aria-labelledby="dp-research-menu">
  <div class="menu-head"><div><span class="kicker" data-t="menuResearchTag">Recherche · Bestellkarte</span><h3 id="dp-research-menu" data-t="menuResearchTitle">Ein Blick auf die aktuelle Online-Karte</h3></div><a href="https://www.doenerparadieskoblenz.de/" target="_blank" rel="noopener noreferrer" data-t="menuResearchLink">Zur Bestellplattform ↗</a></div>
  <ul aria-label="Auswahl von angebotenen Gerichten">
    <li><span>Drehspieß-Sandwich</span><b>8,50 €</b></li>
    <li><span>Drehspießrolle / Dürüm</span><b>9,50 €</b></li>
    <li><span>Pizza Paradies</span><b>10,40 €</b></li>
    <li><span>Wunsch-Pizza Ø 26 cm</span><b>10,90 €</b></li>
    <li><span>Falafel-Rolle</span><b>7,90 €</b></li>
    <li><span>Mixed-Veggie-Teller</span><b>11,90 €</b></li>
  </ul>
  <p class="source-note" data-t="menuResearchNote">Öffentlich gelistete Beispielpreise laut bestehender Bestellplattform, recherchiert am 13.11.2026; kein verbindliches Betreiberangebot. Sortiment, Preise, Allergene und Zusatzstoffe vor regulärer Veröffentlichung überprüfen. Bestellungen erfolgen ausschließlich über die verlinkte Drittplattform.</p>
</div>

</div></section>
<section class="ribbon"><div class="wrap"><span class="kicker" data-t="ribbonTag">Für Stammgäste. Für Entdecker.</span>
<h2 data-t="ribbonTitle">Dein nächster Lieblingsstopp in Koblenz.</h2>
<p data-t="ribbonText">Gut auffindbar auf dem Smartphone, mit kurzer Speisekarte, direktem Kontakt und Routenplanung. Genau dafür ist diese ODPC-Demo gestaltet.</p></div></section>
<section class="section" id="fotos"><div class="wrap"><span class="kicker" data-t="photosTag">Echte Einblicke</span><h2 data-t="photosTitle">Der Laden. Das Essen. Die Menschen.</h2>
<p class="section-intro" data-t="photosText">Die Bilder sind lizenzierte Symbolfotos, keine Originalaufnahmen des Betriebs. Eigene Fotos lassen sich lokal austauschen; sie werden nicht zum Server hochgeladen.</p>
<div class="photo-row" id="dp-gallery"><div class="photo-box"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fb/D%C3%B6ner_Kebab.jpg/960px-D%C3%B6ner_Kebab.jpg" alt="Döner (Symbolbild)" loading="lazy" decoding="async"></div><div class="photo-box"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Pizza_Margherita_%289235473304%29.jpg/960px-Pizza_Margherita_%289235473304%29.jpg" alt="Pizza (Symbolbild)" loading="lazy" decoding="async"></div><div class="photo-box"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Falafel_Sandwich_at_Utsunomiya.jpg/960px-Falafel_Sandwich_at_Utsunomiya.jpg" alt="Falafel (Symbolbild)" loading="lazy" decoding="async"></div></div>
<p class="photo-credit">Demo-Symbolbilder, keine Aufnahmen des Betriebs. Bildnachweise: <a href="https://commons.wikimedia.org/wiki/File:D%C3%B6ner_Kebab.jpg" target="_blank" rel="noopener noreferrer">Döner: Tim Reckmann (CC BY 2.0)</a> · <a href="https://commons.wikimedia.org/wiki/File:Pizza_Margherita_(9235473304).jpg" target="_blank" rel="noopener noreferrer">Pizza: jeffreyw (CC BY 2.0)</a> · <a href="https://commons.wikimedia.org/wiki/File:Falafel_Sandwich_at_Utsunomiya.jpg" target="_blank" rel="noopener noreferrer">Falafel: Miyuki Meinaka (CC BY-SA 4.0)</a>. Bilddateien unverändert, responsiv angezeigt.</p>
<label class="photo-btn" data-t="addPhotos">📸 Fotos vor Ort auswählen <input id="dp-pictures" type="file" accept="image/*" multiple></label>
</div></section>
<section class="section" id="besuch"><div class="wrap location-grid">
<div><span class="kicker" data-t="visitTag">Mitten in der Altstadt</span><h2 data-t="visitTitle">Du findest uns<br>ganz einfach.</h2>
<address class="address">Döner Paradies<br>Münzstraße 18<br>56068 Koblenz</address>
<a class="btn" href="https://www.google.com/maps/search/?api=1&amp;query=Emin%27s%20Grill%20M%C3%BCnzstra%C3%9Fe%2018%20Koblenz" target="_blank" rel="noopener noreferrer" data-t="maps">Route in Google Maps öffnen</a>
<p class="note" data-t="addressNote">Name und Adresse aus öffentlichen Brancheneinträgen. Vor Übergabe an den Betreiber auf Richtigkeit prüfen.</p>
</div><div class="map-panel"><span class="pin">📍</span><strong>KOBLENZ</strong><span>MÜNZSTRASSE 18</span><small data-t="mapLabel">Altstadt · zentral gelegen</small></div>
</div></section>
<section class="section" id="kontakt"><div class="wrap"><span class="kicker" data-t="contactTag">Dein direkter Draht</span><h2 data-t="contactTitle">Komm vorbei.</h2>
<p class="section-intro" data-t="contactText">Öffnungszeiten, Telefonnummer, WhatsApp und Impressum werden vom Betreiber vor der offiziellen Veröffentlichung bestätigt.</p>
<div class="actions"><a class="btn" href="https://www.google.com/maps/search/?api=1&amp;query=M%C3%BCnzstra%C3%9Fe%2018%2056068%20Koblenz" target="_blank" rel="noopener noreferrer" data-t="findUs">Jetzt finden</a>
<a class="btn outline" href="https://www.doenerparadieskoblenz.de/" target="_blank" rel="noopener noreferrer" data-t="order">Online bestellen ↗</a></div>
<p class="note" data-t="callNote">Der Bestellbutton führt zur bestehenden Bestellseite des Betriebs (Lieferando). Diese ODPC-Demo nimmt selbst keine Bestellungen entgegen.</p></div></section>
<footer><div class="wrap footer-inner"><div><strong>Döner Paradies · Koblenz</strong><br><small data-t="footerText">Unverbindliche private Kundendemonstration · keine offizielle Betreiberseite</small></div><div><span data-t="legal">Impressum & Datenschutz: vor Veröffentlichung durch Betreiber ergänzen</span><br><a href="https://odpc.de" target="_blank" rel="noopener noreferrer">powered by odpc.de</a></div></div></footer>
<div class="float" aria-label="Schnellaktionen"><a href="#start" data-t="floatHome">⌂ Start</a><a href="#speisekarte" data-t="floatMenu">☰ Karte</a><button type="button" id="dp-speak" data-t="speak">🔊 Hören</button><a class="primary" href="#besuch" data-t="floatRoute">↗ Route</a></div>
</main>
<script>
(function(){
const root=document.getElementById('dp-demo');
const classic=document.getElementById('dp-classic'),modern=document.getElementById('dp-modern'),langs=document.getElementById('dp-lang');
const dict={
de:{notice:"ODPC-KUNDENVORSCHAU · NOCH KEINE OFFIZIELLE WEBSEITE · BETRIEBSDATEN VOR ORT PRÜFEN",classic:"Klassisch",modern:"Modern",home:"Start",menu:"Speisekarte",photos:"Fotos",visit:"Anfahrt",contact:"Kontakt",kicker:"Dein Genussmoment in der Altstadt",headline:"Hier schmeckt <em>Koblenz.</em>",lead:"Original Berliner Kebap, Pizza und vegetarische Gerichte – mitten in der Koblenzer Altstadt. Diese ODPC-Demo zeigt eine eigenständige Website als Ergänzung zur vorhandenen Bestellplattform.",menuSee:"Speisekarte entdecken",directions:"Standort ansehen",takeout:"Gastronomie & Imbiss",stamp:"DEINE<br>PAUSE<br>IN KO",visualDisclaimer:"Symbolfoto · keine Originalaufnahme des Betriebs",foodTag:"Genuss auf einen Blick",menuTitle:"Für jeden Hunger<br>das Richtige.",menuNote:"Beispiel-Kategorien für die Kundenvorschau. Tatsächliches Sortiment, Allergene und Preise werden erst nach Bestätigung durch den Betreiber veröffentlicht.",cardOne:"Original Berliner Kebap",cardOneDesc:"Drehspieß-Sandwich, Dürüm und Lahmacun als eigene Speisekartenrubrik.",cardTwo:"Pizza & Wunsch-Pizza",cardTwoDesc:"Klassische Pizza, Pizza Paradies und Wunsch-Pizza übersichtlich präsentiert.",cardThree:"Falafel & Halloumi",cardThreeDesc:"Vegetarische Alternativen, Salate und Beilagen – Sortiment vor Veröffentlichung prüfen.",ribbonTag:"Für Stammgäste. Für Entdecker.",ribbonTitle:"Dein nächster Lieblingsstopp in Koblenz.",ribbonText:"Gut auffindbar auf dem Smartphone, mit kurzer Speisekarte, direktem Kontakt und Routenplanung. Genau dafür ist diese ODPC-Demo gestaltet.",photosTag:"Echte Einblicke",photosTitle:"Der Laden. Das Essen. Die Menschen.",photosText:"Die Bilder sind lizenzierte Symbolfotos, keine Originalaufnahmen des Betriebs. Eigene Fotos lassen sich lokal austauschen; sie werden nicht zum Server hochgeladen.",placeholderOne:"Ladenfoto",placeholderTwo:"Gerichte",placeholderThree:"Atmosphäre",addPhotos:"📸 Fotos vor Ort auswählen",visitTag:"Mitten in der Altstadt",visitTitle:"Du findest uns<br>ganz einfach.",maps:"Route in Google Maps öffnen",addressNote:"Name und Adresse aus öffentlichen Brancheneinträgen. Vor Übergabe an den Betreiber auf Richtigkeit prüfen.",mapLabel:"Altstadt · zentral gelegen",contactTag:"Dein direkter Draht",contactTitle:"Komm vorbei.",contactText:"Öffnungszeiten, Telefonnummer, WhatsApp und Impressum werden vom Betreiber vor der offiziellen Veröffentlichung bestätigt.",findUs:"Jetzt finden",call:"Anrufen*",callNote:"Der Bestellbutton führt zur bestehenden Bestellseite des Betriebs (Lieferando). Diese ODPC-Demo nimmt selbst keine Bestellungen entgegen.",footerText:"Unverbindliche private Kundendemonstration · keine offizielle Betreiberseite",legal:"Impressum & Datenschutz: vor Veröffentlichung durch Betreiber ergänzen",floatHome:"⌂ Start",floatMenu:"☰ Karte",speak:"🔊 Hören",stop:"■ Stop",order:"Online bestellen ↗",floatRoute:"↗ Route",menuResearchTag:"Recherche · Bestellkarte",menuResearchTitle:"Ein Blick auf die aktuelle Online-Karte",menuResearchLink:"Zur Bestellplattform ↗",menuResearchNote:"Öffentlich gelistete Beispielpreise laut bestehender Bestellplattform, recherchiert am 13.11.2026; kein verbindliches Betreiberangebot. Sortiment, Preise, Allergene und Zusatzstoffe vor regulärer Veröffentlichung überprüfen. Bestellungen erfolgen ausschließlich über die verlinkte Drittplattform."},
en:{notice:"ODPC CLIENT PREVIEW · NOT AN OFFICIAL WEBSITE · VERIFY ALL DETAILS",classic:"Classic",modern:"Modern",home:"Home",menu:"Menu",photos:"Photos",visit:"Directions",contact:"Contact",kicker:"Your food stop in the old town",headline:"Taste <em>Koblenz.</em>",lead:"Kebab, pizza and vegetarian options in the heart of Koblenz. This preview demonstrates an independent website alongside the existing order platform.",menuSee:"Explore menu",directions:"Find us",takeout:"Restaurant & takeaway",stamp:"YOUR<br>FOOD<br>STOP",visualDisclaimer:"Stock example · not from the restaurant",foodTag:"At a glance",menuTitle:"Something for<br>every appetite.",menuNote:"Sample categories only. Actual menu items, allergens and prices require operator approval.",cardOne:"Original Berlin-style kebab",cardOneDesc:"Döner sandwich, dürüm and lahmacun presented in a clear menu.",cardTwo:"Pizza & custom pizza",cardTwoDesc:"Classic pizza, Pizza Paradies and build-your-own options.",cardThree:"Falafel & halloumi",cardThreeDesc:"Vegetarian meals, salads and side dishes, subject to verification.",ribbonTag:"For locals. For visitors.",ribbonTitle:"Your next food stop in Koblenz.",ribbonText:"Easy to find on your phone, with menu, contact and directions. That is what this ODPC preview demonstrates.",photosTag:"A closer look",photosTitle:"The place. The food. The people.",photosText:"These are placeholders. Selected photos display only on your device, without server upload.",placeholderOne:"Storefront",placeholderTwo:"Food",placeholderThree:"Atmosphere",addPhotos:"📸 Select local photos",visitTag:"In the old town",visitTitle:"Easy<br>to find.",maps:"Open Google Maps route",addressNote:"Business name and address from public listings; verify in person.",mapLabel:"Central old town",contactTag:"Get in touch",contactTitle:"Drop by.",contactText:"Hours, phone, WhatsApp and legal details must be approved before launch.",findUs:"Find us",call:"Call*",callNote:"The order button opens the existing Lieferando ordering platform; this ODPC preview does not accept orders.",footerText:"Private non-binding client demo · not an official operator site",legal:"Legal notice & privacy: to be completed before launch",floatHome:"⌂ Home",floatMenu:"☰ Menu",speak:"🔊 Listen",stop:"■ Stop",order:"Order online ↗",floatRoute:"↗ Route",menuResearchTag:"Online menu research",menuResearchTitle:"From the current online menu",menuResearchLink:"Open ordering platform ↗",menuResearchNote:"Publicly listed sample prices on the existing third-party ordering platform, reviewed on 13 Nov 2026. Not a binding offer. Operator must confirm products, prices, allergens and additives before launch. Orders are processed only by the linked third-party platform."},
tr:{notice:"ODPC MÜŞTERİ ÖN İZLEMESİ · RESMÎ WEB SİTESİ DEĞİLDİR · BİLGİLER DOĞRULANMALIDIR",classic:"Klasik",modern:"Modern",home:"Ana sayfa",menu:"Menü",photos:"Fotoğraflar",visit:"Yol tarifi",contact:"İletişim",kicker:"Eski şehirde lezzet molası",headline:"Koblenz'de <em>lezzet.</em>",lead:"Koblenz'in merkezinde kebap, pizza ve vejetaryen seçenekler. Bu taslak, mevcut sipariş platformuna ek olarak bağımsız bir web sitesi fikrini gösterir.",menuSee:"Menüyü keşfet",directions:"Konumu gör",takeout:"Restoran ve paket servis",stamp:"LEZZET<br>MOLASI",visualDisclaimer:"Temsili fotoğraf · işletmeden değil",foodTag:"Lezzetler",menuTitle:"Her damak<br>tadına uygun.",menuNote:"Bunlar yalnızca örnek kategorilerdir. Gerçek menü, alerjen ve fiyat bilgileri işletmeci tarafından onaylanmalıdır.",cardOne:"Orijinal Berlin kebabı",cardOneDesc:"Döner sandviç, dürüm ve lahmacun seçenekleri.",cardTwo:"Pizza ve özel pizza",cardTwoDesc:"Pizza çeşitleri ve kendi pizzanı oluştur seçeneği.",cardThree:"Falafel ve hellim",cardThreeDesc:"Vejetaryen seçenekler, salatalar ve yan ürünler; doğrulanmalıdır.",ribbonTag:"Yerel halk ve ziyaretçiler için",ribbonTitle:"Koblenz'deki bir sonraki lezzet durağın.",ribbonText:"Telefonlarda kolay erişim, menü, iletişim ve yol tarifi. ODPC demosunun amacı budur.",photosTag:"Yakından bakın",photosTitle:"Mekân. Yemekler. İnsanlar.",photosText:"Fotoğraf alanları örnektir. Seçilen fotoğraflar sunucuya yüklenmez.",placeholderOne:"Mekân",placeholderTwo:"Yemekler",placeholderThree:"Ortam",addPhotos:"📸 Fotoğraf seç",visitTag:"Eski şehrin merkezinde",visitTitle:"Bize ulaşmak<br>çok kolay.",maps:"Google Haritalar'da aç",addressNote:"İsim ve adres kamuya açık kayıtlardan; yerinde doğrulayın.",mapLabel:"Merkez · Eski şehir",contactTag:"İletişim",contactTitle:"Bekleriz.",contactText:"Çalışma saatleri, telefon, WhatsApp ve yasal bilgiler yayından önce onaylanmalıdır.",findUs:"Konumumuz",call:"Ara*",callNote:"Sipariş düğmesi mevcut Lieferando sipariş sayfasına yönlendirir; bu ODPC demosu sipariş almaz.",footerText:"Bağlayıcı olmayan özel müşteri demosu · resmî site değil",legal:"Künye ve gizlilik metinleri yayından önce eklenmelidir",floatHome:"⌂ Başla",floatMenu:"☰ Menü",speak:"🔊 Dinle",stop:"■ Durdur",order:"Online sipariş ↗",floatRoute:"↗ Rota",menuResearchTag:"Menü araştırması",menuResearchTitle:"Çevrimiçi menüden seçmeler",menuResearchLink:"Sipariş platformuna git ↗",menuResearchNote:"Mevcut sipariş platformunda herkese açık örnek fiyatlar; kontrol tarihi 13.11.2026. Bağlayıcı teklif değildir. Gerçek ürünler, fiyatlar, alerjenler ve katkı maddeleri yayımdan önce işletmeci tarafından onaylanmalıdır."}
};
function translate(){const lang=langs.value;document.documentElement.lang=lang;for(const el of root.querySelectorAll('[data-t]')){const key=el.dataset.t;const v=(dict[lang]||dict.de)[key]||dict.de[key];if(v!==undefined){if(key==='addPhotos'){const input=el.querySelector('input');el.replaceChildren(document.createTextNode(v+' '));if(input)el.appendChild(input)}else if(['headline','stamp','menuTitle','visitTitle'].includes(key))el.innerHTML=v;else el.textContent=v}}}
function setTheme(mode){const isModern=mode==='modern';root.classList.toggle('modern',isModern);root.classList.toggle('classic',!isModern);classic.setAttribute('aria-pressed',String(!isModern));modern.setAttribute('aria-pressed',String(isModern));try{localStorage.setItem('odpc-dp-mode',mode)}catch{}}
classic.addEventListener('click',()=>setTheme('classic'));modern.addEventListener('click',()=>setTheme('modern'));langs.addEventListener('change',translate);
try{setTheme(localStorage.getItem('odpc-dp-mode')==='modern'?'modern':'classic')}catch{setTheme('classic')}
translate();
const file=document.getElementById('dp-pictures'),gallery=document.getElementById('dp-gallery');let urls=[];
file.addEventListener('change',()=>{urls.forEach(u=>URL.revokeObjectURL(u));urls=[];gallery.replaceChildren();const credit=root.querySelector('.photo-credit');if(credit)credit.hidden=true;for(const f of Array.from(file.files).filter(x=>x.type.startsWith('image/')).slice(0,3)){const u=URL.createObjectURL(f);urls.push(u);const img=document.createElement('img');img.src=u;img.alt='Vom Benutzer ausgewähltes lokales Foto';const box=document.createElement('div');box.className='photo-box';box.appendChild(img);gallery.appendChild(box)}});
const speak=document.getElementById('dp-speak');let active=false;
speak.addEventListener('click',()=>{if(!('speechSynthesis' in window)){speak.textContent='TTS nicht verfügbar';return;}if(active){speechSynthesis.cancel();active=false;speak.textContent=dict[langs.value].speak;return;}speechSynthesis.cancel();const t=dict[langs.value];const msg=new SpeechSynthesisUtterance(['Döner Paradies, Koblenz.',t.lead,t.menuNote].join(' '));msg.lang={de:'de-DE',en:'en-GB',tr:'tr-TR'}[langs.value];msg.rate=.94;msg.onend=msg.onerror=()=>{active=false;speak.textContent=t.speak};active=true;speak.textContent=t.stop;speechSynthesis.speak(msg)});
})();
</script></body></html>
  <?php
  exit;
}, 0);


/** ODPC YourBusiness three-client demo launcher, isolated from theme and core pages. */
add_action('template_redirect', function () {
  if (is_admin() || !isset($_GET['yb_showcase']) || $_GET['yb_showcase'] !== '1') return;
  status_header(200);
  nocache_headers();
  ?>
<!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow,noarchive"><meta name="theme-color" content="#0d2533">
<meta name="description" content="Drei unabhängige mobile Kundenvorschauen für Koblenzer Gastronomiebetriebe von ODPC.">
<title>ODPC · Drei Kunden · Live-Vorschau</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#0e2130;color:#f7f5ee;font:16px/1.6 system-ui,-apple-system,sans-serif}
main{max-width:1150px;margin:auto;padding:clamp(28px,6vw,80px) 20px 90px}
.eyebrow{color:#e8c17c;letter-spacing:.15em;text-transform:uppercase;font-size:12px;font-weight:800}
h1{font-size:clamp(41px,7vw,79px);line-height:1.04;letter-spacing:-.05em;max-width:860px;margin:18px 0}
.lead{font-size:18px;color:#bcccd0;max-width:720px}
.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-top:45px}
.card{display:flex;flex-direction:column;gap:12px;min-width:0;background:#172f40;padding:26px;border:1px solid #3c5665;border-radius:22px}
.symbol{font-size:48px}.label{color:#e3b874;text-transform:uppercase;letter-spacing:.13em;font-size:11px;font-weight:800}
h2{font-size:28px;letter-spacing:-.04em;line-height:1.2;margin:0}.address{font-size:13px;color:#becfd5;margin:0}
.card p{color:#d6e0e1;font-size:14px;margin:4px 0 14px}
a.button{display:block;text-align:center;margin-top:auto;background:#e9bd78;color:#172838;padding:15px 18px;font-weight:800;text-decoration:none;border-radius:12px}
a:focus-visible{outline:3px solid white;outline-offset:3px}
footer{border-top:1px solid #3c5665;margin-top:50px;padding-top:24px;color:#c1d1d8;font-size:12px}
footer a{color:#e9c889}
@media(max-width:790px){.grid{grid-template-columns:1fr}.card{min-height:0}main{padding-top:32px}}
</style></head>
<body><main><span class="eyebrow">ODPC · YourBusiness · Live-Kundenpräsentation</span>
<h1>Drei Lokale.<br>Drei individuelle Websites.</h1>
<p class="lead">Direkt vor Ort recherchiert, als Smartphone-Demo gestaltet und in getrennten Vorschauen bereitgestellt. Keine dieser Demos ist die offizielle Webseite des jeweiligen Betriebs.</p>
<div class="grid" id="showcase-grid">
<article class="card"><div class="symbol">🍻</div><span class="label">01 · Kneipe & Schlagerbar</span><h2>Kleine Freiheit</h2><p class="address">Altenhof 6, 56068 Koblenz</p><p>Gemütlich-klassisches und modernes Konzept, passende Inhalte für eine Altstadtkneipe.</p><a class="button" href="/?yb_kleine_freiheit=1&amp;design=klassisch">Kundendemo öffnen ↗</a></article>
<article class="card"><div class="symbol">🥙</div><span class="label">02 · Grill & Imbiss</span><h2>Emin's Grill</h2><p class="address">Münzstraße 18, 56068 Koblenz</p><p>Individuelle Grill-Vorschau mit zwei Designs, drei Sprachen und mobiler Navigation.</p><a class="button" href="/?yb_emins_grill=1">Kundendemo öffnen ↗</a></article>
<article class="card"><div class="symbol">🍕</div><span class="label">03 · Döner & Pizza</span><h2>Döner Paradies</h2><p class="address">Münzstraße 18, 56068 Koblenz</p><p>Eigene Markenpräsenz zusätzlich zur bestehenden Lieferando-Bestellseite. Zwei Designs, drei Sprachen.</p><a class="button" href="/?yb_doener_paradies=1">Kundendemo öffnen ↗</a></article>
</div><footer>Alle Unternehmensangaben und Bilder vor kommerzieller Veröffentlichung mit dem jeweiligen Betreiber abgleichen. <a href="https://odpc.de" target="_blank" rel="noopener noreferrer">powered by odpc.de</a></footer>
</main></body></html>
  <?php
  exit;
}, 0);
