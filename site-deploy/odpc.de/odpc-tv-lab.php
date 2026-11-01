<?php
/**
 * Plugin Name: ODPC TV Lab
 * Description: Renders the isolated TV lab only on the WordPress page /tv/.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;
add_filter('the_content', function($content) {
  if (!is_page('tv') || !is_main_query() || !in_the_loop()) return $content;
  $channels = [
    ['Das Erste','https://daserste-live.ard-mcdn.de/daserste/live/hls/de/master.m3u8'],
    ['ZDF','https://zdf-hls-15.akamaized.net/hls/live/2016498/de/high/master.m3u8'],
    ['arte','https://artesimulcast.akamaized.net/hls/live/2030993/artelive_de/master.m3u8'],
    ['ZDFneo','https://zdf-hls-16.akamaized.net/hls/live/2016499/de/high/master.m3u8'],
    ['ZDFinfo','https://zdf-hls-17.akamaized.net/hls/live/2016500/de/high/master.m3u8'],
    ['3sat','https://zdf-hls-18.akamaized.net/hls/live/2016501/dach/veryhigh/master.m3u8']
  ];
  $json = wp_json_encode($channels, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
  $html = '<section id="odpc-tv-lab" style="background:#101c30;color:white;padding:20px;border-radius:16px"><h2 style="color:white">ODPC TV · Live Lab</h2><video id="odpc-tv-player" controls playsinline style="width:100%;aspect-ratio:16/9;background:#000;border-radius:12px"></video><p id="odpc-tv-status">Sender wählen – Quellen noch nicht verifiziert.</p><div id="odpc-tv-buttons" style="display:flex;flex-wrap:wrap;gap:8px"></div></section>';
  $html .= '<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.20/dist/hls.min.js"></script>';
  $html .= '<script>(function(){const channels='.$json.';const v=document.getElementById("odpc-tv-player"),s=document.getElementById("odpc-tv-status"),g=document.getElementById("odpc-tv-buttons");if(!v||!g)return;let h=null;channels.forEach(c=>{const b=document.createElement("button");b.textContent=c[0];b.style.cssText="padding:13px;background:#254d80;color:#fff;border:0;border-radius:9px;cursor:pointer";b.onclick=()=>{if(h){h.destroy();h=null}v.pause();v.removeAttribute("src");v.load();s.textContent="Verbinde mit "+c[0];if(window.Hls&&Hls.isSupported()){h=new Hls();h.loadSource(c[1]);h.attachMedia(v);h.on(Hls.Events.MANIFEST_PARSED,()=>v.play().catch(()=>s.textContent="Play drücken"));h.on(Hls.Events.ERROR,(_,e)=>{if(e.fatal)s.textContent="Streamfehler: "+e.type})}else if(v.canPlayType("application/vnd.apple.mpegurl")){v.src=c[1];v.play().catch(()=>s.textContent="Play drücken")}else s.textContent="HLS nicht verfügbar"};g.appendChild(b)})})();</script>';
  return $html;
}, 20);
