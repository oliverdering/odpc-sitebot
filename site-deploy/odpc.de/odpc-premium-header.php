<?php
/**
 * Plugin Name: ODPC Premium Header
 * Description: Responsive header presentation and compact navigation; no content changes.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) { exit; }

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
      <div class="odpc-search"><?php get_search_form(); ?></div>
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
