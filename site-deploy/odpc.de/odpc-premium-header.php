<?php
/**
 * Plugin Name: ODPC Premium Header
 * Description: Responsive header presentation and compact navigation; no content changes.
 * Version: 1.2.1
 */
if (!defined('ABSPATH')) { exit; }

add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {
  if (is_admin() || (int) $attachment->ID !== (int) get_theme_mod('custom_logo')) {
    return $attr;
  }
  $metadata = wp_get_attachment_metadata($attachment->ID);
  $source = wp_get_attachment_url($attachment->ID);
  if (!$source || empty($metadata['width']) || empty($metadata['height'])) {
    return $attr;
  }
  $attr['src'] = $source;
  $attr['width'] = (int) $metadata['width'];
  $attr['height'] = (int) $metadata['height'];
  $srcset = wp_calculate_image_srcset(
    array($attr['width'], $attr['height']), $source, $metadata, $attachment->ID
  );
  if ($srcset) {
    $attr['srcset'] = $srcset;
  }
  $attr['sizes'] = '(max-width: 800px) calc(100vw - 24px), (max-width: 1316px) calc(100vw - 56px), 1260px';
  return $attr;
}, 99, 2);

add_action('wp_head', function () {
  if (is_admin()) { return; }
  ?>
  <style id="odpc-premium-header-style">
  :root{--odpc-ink:#111823;--odpc-dark:#0b111a;--odpc-silver:#e9e6e1;--odpc-muted:#adb4ba;--odpc-warm:#bb916f;--odpc-surface:#f6f4ef}
  html,body{overflow-x:clip}
  body{background:var(--odpc-surface)}
  #masthead{background:linear-gradient(145deg,#111824,#202733 65%,#161a22)!important;padding:14px clamp(12px,3vw,28px) 0}
  #masthead #ast-desktop-header,
  #masthead #ast-mobile-header{background:transparent!important}
  #masthead .ast-above-header-bar,
  #masthead .site-above-header-wrap{background:transparent!important;border:0!important;box-shadow:none!important}
  #masthead .site-above-header-wrap{width:100%;max-width:1260px;margin:auto;padding:0!important}
  #masthead .ast-above-header-bar .ast-builder-grid-row,
  #masthead .site-above-header-wrap .ast-builder-grid-row{display:block!important}
  #masthead .site-header-above-section-left{display:block!important;width:100%!important;max-width:none!important}
  #masthead .site-header-above-section-right{display:none!important}
  #masthead .site-branding{display:flex!important;flex-direction:column;align-items:stretch;width:100%;gap:0}
  #masthead .site-logo-img,
  #masthead .custom-logo-link{display:block!important;width:100%;max-width:100%!important}
  #masthead img.custom-logo{display:block;width:100%!important;max-width:100%!important;height:auto!important;max-height:430px;object-fit:cover;object-position:center;border-radius:16px 16px 0 0;box-shadow:none}
  #masthead .ast-site-title-wrap{display:block;width:100%;padding:14px clamp(16px,3vw,30px) 15px;background:linear-gradient(110deg,#181e2a,#111923);border-top:1px solid rgba(233,230,225,.14)}
  #masthead .site-title,
  #masthead .site-title a{font-size:clamp(22px,3.3vw,35px)!important;font-weight:720!important;line-height:1.22;color:var(--odpc-silver)!important;letter-spacing:-.025em;text-decoration:none}
  #masthead .site-description{font-size:clamp(12px,1.4vw,15px)!important;color:var(--odpc-muted)!important;margin:5px 0 0!important;line-height:1.45}
  #masthead .ast-primary-header-bar,
  #masthead .main-header-bar-wrap,
  #masthead .ast-below-header-wrap{display:none!important}
  #odpc-navigation{max-width:1260px;margin:0 auto 0;padding:0 clamp(12px,3vw,28px) 16px;box-sizing:border-box;background:#111823;color:var(--odpc-silver);font-family:system-ui,-apple-system,"Segoe UI",Arial,sans-serif}
  #odpc-navigation .odpc-menu-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:54px;padding:7px 12px 7px clamp(12px,2vw,23px);border:1px solid rgba(233,230,225,.2);border-top:0;border-radius:0 0 15px 15px;background:linear-gradient(125deg,#1c2530,#101721)}
  #odpc-navigation .odpc-nav-note{font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:#b9bfc3;font-weight:650}
  #odpc-navigation .odpc-nav-toggle{display:inline-flex;gap:11px;align-items:center;justify-content:center;min-width:116px;padding:10px 15px;border:1px solid rgba(224,200,177,.55);border-radius:9px;background:rgba(255,255,255,.055);color:#f5efea;font:750 14px/1.2 system-ui,Arial,sans-serif;cursor:pointer;transition:background .16s ease,border-color .16s ease}
  #odpc-navigation .odpc-nav-toggle:hover,
  #odpc-navigation .odpc-nav-toggle:focus-visible{background:#34302f;border-color:#d9b18d;outline-offset:3px}
  #odpc-navigation .odpc-nav-toggle svg{width:19px;height:19px;flex:none}
  #odpc-navigation #odpc-nav-panel{margin:9px 0 0;padding:12px;border:1px solid rgba(233,230,225,.18);border-radius:13px;background:#171e28;box-shadow:0 15px 35px rgba(0,0,0,.2)}
  #odpc-navigation #odpc-nav-panel[hidden]{display:none!important}
  #odpc-navigation .odpc-menu-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:5px 12px;margin:0;padding:0;list-style:none}
  #odpc-navigation .odpc-menu-list li{list-style:none;margin:0;padding:0;min-width:0}
  #odpc-navigation .odpc-menu-list a{display:block;padding:11px 12px;color:#e5e4e2!important;font-size:14px;font-weight:560;line-height:1.5;text-decoration:none;border-radius:8px;border:1px solid transparent;overflow-wrap:anywhere}
  #odpc-navigation .odpc-menu-list a:hover,
  #odpc-navigation .odpc-menu-list a:focus-visible,
  #odpc-navigation .odpc-menu-list .current-menu-item>a{background:#2d3540;color:#fff!important;border-color:#625447}
  #odpc-navigation .odpc-search{margin-top:13px;padding:12px 0 2px;border-top:1px solid rgba(233,230,225,.15)}
  #odpc-navigation .odpc-search form{display:flex;gap:10px;flex-wrap:wrap}
  #odpc-navigation .odpc-search input[type=search]{flex:1 1 180px;min-width:0;border:1px solid #64707b;border-radius:8px;background:#fff;color:#151b24;padding:10px 12px}
  #odpc-navigation .odpc-search button{border:1px solid #d1ab89;border-radius:8px;background:#bc9371;color:#141922;padding:10px 17px;font-weight:750;cursor:pointer}
  #content{background:var(--odpc-surface)}
  .site-content>.ast-container{max-width:1260px;margin:0 auto;padding-left:clamp(12px,3vw,28px);padding-right:clamp(12px,3vw,28px)}
  @media(max-width:800px){
    #masthead{padding:12px 12px 0}
    #masthead .ast-site-title-wrap{padding:13px 16px 15px}
    #masthead img.custom-logo{border-radius:12px 12px 0 0;max-height:none}
    #odpc-navigation{padding:0 12px 13px}
    #odpc-navigation .odpc-menu-list{grid-template-columns:repeat(2,minmax(0,1fr))}
    #odpc-navigation .odpc-nav-note{font-size:11px}
    #odpc-navigation .odpc-menu-bar{padding:7px 9px 7px 14px}
    .site-content>.ast-container{padding-left:12px;padding-right:12px}
  }
  @media(max-width:520px){
    #odpc-navigation .odpc-menu-list{grid-template-columns:1fr}
    #odpc-navigation .odpc-nav-toggle{min-width:106px}
    #masthead .site-title,#masthead .site-title a{font-size:clamp(20px,5.5vw,27px)!important}
    #masthead .site-description{font-size:12px!important}
  }

  /* ODPC redesign v1.1: unified silver/bronze instead of WP/Astra blue or old green */
  body.home #masthead .ast-site-title-wrap{display:none!important}
  body.home .site-content .entry-header,body.home .site-content .entry-title,
  body.home .site-content .page-title{display:none!important}
  #masthead,#masthead .ast-above-header-wrap{background:#161b24!important}
  #masthead .custom-logo-link{line-height:0}
  #masthead img.custom-logo{object-fit:contain;max-height:none}
  #odpc-navigation{background:#161b24!important;max-width:none!important;padding-bottom:13px!important}
  #odpc-navigation .odpc-menu-bar{width:min(100%,1210px);margin:0 auto;background:#171e28;border:1px solid #48505b;border-top:0;border-radius:0 0 13px 13px}
  #odpc-navigation .odpc-nav-toggle{color:#efece7;background:#262932;border:1px solid #ad907a}
  #odpc-navigation .odpc-nav-toggle:hover{background:#34333a}
  #odpc-navigation #odpc-nav-panel{width:min(100%,1210px);margin:8px auto 0}
  #odpc-navigation .odpc-search{display:block}
  #odpc-navigation .odpc-search form.search-form{display:flex;align-items:center;gap:9px;padding:0}
  #odpc-navigation .odpc-search .search-field{flex:1;min-width:0;max-width:none;background:#f7f6f3!important;color:#1b222d!important;border:1px solid #9ca3ae!important;border-radius:9px!important;min-height:44px}
  #odpc-navigation .odpc-search button.search-submit,
  #odpc-navigation .odpc-search input.search-submit,
  #odpc-navigation .odpc-search button[type=submit],
  #odpc-navigation .odpc-search input[type=submit]{display:inline-flex!important;align-items:center;justify-content:center;flex:0 0 auto;min-height:44px;padding:10px 18px!important;background:#bc967b!important;color:#171b23!important;border:1px solid #bc967b!important;border-radius:9px!important;box-shadow:none!important;font-weight:750}
  #odpc-navigation .odpc-search button.search-submit:hover,
  #odpc-navigation .odpc-search input.search-submit:hover{background:#dec0a3!important}
  #ast-scroll-top,.ast-scroll-to-top,#scrollUp{background:#bc967b!important;color:#171b23!important;border-radius:11px!important;border:1px solid #9b785e!important;box-shadow:0 5px 19px rgba(0,0,0,.2)!important}
  #ast-scroll-top svg,#ast-scroll-top .ast-icon{fill:#171b23!important;color:#171b23!important}
  body.home #content{background:#f3f2ef!important}
  body.home .site-content>.ast-container{max-width:1250px!important;padding:0 clamp(10px,2.6vw,25px)!important}
  body.home article.page{background:transparent!important;border:0!important;box-shadow:none!important;margin:0!important;padding:0!important}
  body.home article.page>.entry-content,
  body.home .entry-content{margin:0!important}
  body.home .ast-article-single{padding:0!important}
  body.home .ast-container .site-main{padding:0!important}
  .odpc-home,.odpc-home *{box-sizing:border-box}
  .odpc-home{--oh-dark:#151c27;--oh-dark2:#202a35;--oh-muted:#9ba7b3;--oh-bronze:#bc967b;--oh-silver:#ebe8e4;--oh-ink:#242b35;--oh-line:#d7d5d0;max-width:1170px;margin:0 auto;padding:clamp(17px,3vw,34px) 0 40px;color:var(--oh-ink);font:400 16px/1.58 system-ui,-apple-system,"Segoe UI",Arial,sans-serif}
  .odpc-home a{transition:background-color .18s ease,border-color .18s ease,transform .18s ease}
  .odpc-home .oh-hero{background:linear-gradient(120deg,#18212c 0%,#272e37 76%,#39302d 100%);border-radius:20px;padding:clamp(23px,5vw,49px);color:#f5f2ee;position:relative;overflow:hidden;border:1px solid #55545b}
  .odpc-home .oh-eyebrow{margin:0 0 10px;color:#d5bda7;font-size:12px;font-weight:790;letter-spacing:.14em;text-transform:uppercase}
  .odpc-home .oh-hero h1{color:#fff!important;font-size:clamp(29px,4.5vw,49px)!important;line-height:1.12;letter-spacing:-.035em;margin:0 0 14px}
  .odpc-home .oh-hero h1 em{color:#d4b39b;font-style:normal}
  .odpc-home .oh-lead{max-width:750px;color:#e5e2df;font-size:clamp(15px,1.9vw,18px);line-height:1.65;margin:0 0 19px}
  .odpc-home .oh-actions{display:flex;flex-wrap:wrap;gap:10px}
  .odpc-home .oh-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:44px;padding:12px 17px;border-radius:9px;font-weight:760;font-size:14px;text-decoration:none!important}
  .odpc-home .oh-button-primary{color:#1c2027!important;background:#c5a083!important;border:1px solid #c5a083}
  .odpc-home .oh-button-primary:hover{background:#e0bda1!important}
  .odpc-home .oh-button-outline{color:#ece8e4!important;border:1px solid #b8aa9e;background:transparent}
  .odpc-home .oh-button-outline:hover{background:#3e4044}
  .odpc-home .oh-button-soft{color:#313640!important;background:#f1e6dc;border:1px solid #e0c9b6}
  .odpc-home .oh-stats{display:flex;flex-wrap:wrap;gap:9px;margin:23px 0 0}
  .odpc-home .oh-stats span{border:1px solid rgba(232,224,215,.22);color:#dedfe1;border-radius:999px;padding:6px 11px;font-size:12px;font-weight:650}
  .odpc-home .oh-section{margin-top:clamp(32px,4.5vw,55px)}
  .odpc-home .oh-section-head{display:flex;align-items:end;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:17px}
  .odpc-home .oh-section-kicker{color:#7c6554;font-size:11px;letter-spacing:.13em;font-weight:820;text-transform:uppercase;margin:0 0 5px}
  .odpc-home .oh-section h2{color:#1b2736!important;font-size:clamp(24px,3vw,34px)!important;line-height:1.17;margin:0!important}
  .odpc-home .oh-section-sub{color:#5c6673;margin:7px 0 0;max-width:760px;font-size:15px}
  .odpc-home .oh-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
  .odpc-home .oh-card{display:flex;flex-direction:column;gap:11px;min-width:0;background:#fff;border:1px solid #d9d7d4;border-top:3px solid #b59a85;border-radius:14px;padding:clamp(17px,2.1vw,25px);box-shadow:0 5px 25px rgba(20,27,35,.045)}
  .odpc-home .oh-card:hover{border-color:#bba08a;box-shadow:0 10px 33px rgba(20,27,35,.09)}
  .odpc-home .oh-card-head{display:flex;align-items:center;gap:12px;min-width:0}
  .odpc-home .oh-monogram{flex:none;display:flex;align-items:center;justify-content:center;width:47px;height:47px;border-radius:11px;background:#242d38;color:#e7d2bd;font-size:15px;font-weight:840;letter-spacing:-.03em}
  .odpc-home .oh-logo{flex:0 0 72px;width:72px;height:72px;display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:14px;border:1px solid #e6e2dd;background:#fff;box-shadow:0 2px 8px rgba(25,32,44,.06)}
  .odpc-home .oh-logo img{display:block;width:100%!important;height:100%!important;max-width:100%!important;max-height:100%!important;object-fit:contain;object-position:center;padding:3px;margin:0!important;border-radius:10px}
  .odpc-home .oh-logo-station{background:#26303b;color:#e9d1bb;border-color:#26303b}
  .odpc-home .oh-logo-station svg{display:block;width:52px;height:52px}
  .odpc-home .oh-card-head>div{flex:1;min-width:0}
  @media(max-width:390px){.odpc-home .oh-logo{flex-basis:64px;width:64px;height:64px}.odpc-home .oh-logo-station svg{width:46px;height:46px}}
  .odpc-home .oh-label{color:#826b5b;text-transform:uppercase;font-weight:800;font-size:10.5px;letter-spacing:.09em}
  .odpc-home .oh-card h3{font-size:clamp(17px,2.1vw,21px)!important;color:#1c2836!important;line-height:1.2;margin:2px 0 0}
  .odpc-home .oh-card p{font-size:14px;line-height:1.59;color:#56616d;margin:0;flex:1}
  .odpc-home .oh-card .oh-status{font-size:12px;color:#6e7780;font-weight:670}
  .odpc-home .oh-card a.oh-card-link{align-self:flex-start;font-size:13px;font-weight:800;color:#755845!important;text-underline-offset:4px;text-decoration:underline}
  .odpc-home .oh-card a.oh-card-link:hover{color:#30241f!important}
  .odpc-home .oh-products{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
  .odpc-home .oh-product{background:#fff;border:1px solid #d9d8d5;border-radius:12px;padding:20px 19px;min-width:0}
  .odpc-home .oh-product small{display:block;font-size:11px;font-weight:800;color:#89715f;letter-spacing:.07em;text-transform:uppercase;margin-bottom:8px}
  .odpc-home .oh-product h3{color:#1e2a38!important;font-size:19px!important;line-height:1.25;margin:0 0 10px}
  .odpc-home .oh-product p{font-size:14px;color:#59636e;line-height:1.6;margin:0 0 14px}
  .odpc-home .oh-product a{color:#755845!important;font-size:13px;font-weight:800;text-underline-offset:4px}
  .odpc-home .oh-wide{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(240px,.7fr);gap:20px;align-items:center;background:linear-gradient(125deg,#222b35,#151e28);color:#f4f0eb;border-radius:18px;padding:clamp(22px,3vw,34px);margin-top:39px}
  .odpc-home .oh-wide h2{color:#fff!important;font-size:clamp(23px,3vw,31px)!important;margin:0 0 12px}
  .odpc-home .oh-wide p{color:#d0d4d7;margin:0;font-size:15px}
  .odpc-home .oh-wide .oh-actions{justify-content:flex-end}
  .odpc-home .oh-links{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
  .odpc-home .oh-links a{display:block;padding:12px 14px;background:#fff;border:1px solid #dbd8d4;border-radius:9px;color:#3b424e!important;font-weight:700;font-size:13px;text-decoration:none}
  .odpc-home .oh-links a:hover{border-color:#bc967b;background:#faf6f1}
  .odpc-home .oh-transparency{font-size:12px;color:#6a737f;margin:21px 0 0}
  @media(max-width:800px){.odpc-home .oh-products{grid-template-columns:repeat(2,minmax(0,1fr))}.odpc-home .oh-wide{grid-template-columns:1fr}.odpc-home .oh-wide .oh-actions{justify-content:flex-start}}
  @media(max-width:550px){
    .odpc-home{padding-top:15px}
    .odpc-home .oh-hero{border-radius:14px;padding:25px 20px}
    .odpc-home .oh-cards,.odpc-home .oh-products,.odpc-home .oh-links{grid-template-columns:1fr}
    .odpc-home .oh-card{padding:19px}
    .odpc-home .oh-section{margin-top:31px}
    .odpc-home .oh-section-head{gap:8px}
    #odpc-navigation .odpc-nav-note{font-size:10px}
    #odpc-navigation .odpc-menu-bar{padding:5px 9px 5px 11px}
    #odpc-navigation .odpc-nav-toggle{min-width:96px;padding:9px 10px}
    #odpc-navigation .odpc-search form.search-form{flex-wrap:nowrap}
    #odpc-navigation .odpc-search .search-field{width:100%;min-width:0}
  }
  </style>
  <?php
}, 99);

add_action('wp_footer', function () {
  if (is_admin()) { return; }
  ?>
  <div id="odpc-navigation">
    <div class="odpc-menu-bar">
      <span class="odpc-nav-note">ODPC · IT, KI &amp; Projekte</span>
      <button type="button" class="odpc-nav-toggle" aria-controls="odpc-nav-panel" aria-expanded="false" aria-label="Navigation öffnen">
        <span>Menü</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
    <nav id="odpc-nav-panel" hidden aria-label="ODPC Hauptnavigation">
      <?php wp_nav_menu(array('menu'=>7,'container'=>false,'menu_class'=>'odpc-menu-list','fallback_cb'=>false,'depth'=>3)); ?>
      <div class="odpc-search"><form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>"><label style="flex:1;min-width:0"><span class="screen-reader-text">Website durchsuchen</span><input type="search" name="s" class="search-field" placeholder="Website durchsuchen …" aria-label="Suchbegriff" required></label><button type="submit" class="search-submit">Suchen</button></form></div>
    </nav>
  </div>
  <script>
  (function(){
    function init(){
      var w=document.getElementById('odpc-navigation');
      var h=document.getElementById('masthead');
      if(!w||!h||w.dataset.init==='1')return;
      w.dataset.init='1';
      h.insertAdjacentElement('afterend',w);
      var b=w.querySelector('.odpc-nav-toggle'),p=w.querySelector('#odpc-nav-panel');
      if(!b||!p)return;
      function open(v){p.hidden=!v;b.setAttribute('aria-expanded',String(v));b.setAttribute('aria-label',v?'Navigation schließen':'Navigation öffnen')}
      b.addEventListener('click',function(){open(p.hidden)});
      document.addEventListener('keydown',function(e){if(e.key==='Escape'&&!p.hidden){open(false);b.focus()}});
      document.addEventListener('click',function(e){if(!p.hidden&&!w.contains(e.target))open(false)});
      p.addEventListener('click',function(e){if(e.target.closest('a')&&!e.target.closest('.menu-item-has-children'))open(false)});
    }
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);
    else init();
  })();
  </script>
  <?php
}, 98);
