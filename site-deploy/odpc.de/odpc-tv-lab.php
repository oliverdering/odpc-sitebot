<?php
/**
 * Plugin Name: ODPC TV Lab
 * Description: TV browser on /tv/ with HLS trials and official broadcaster links.
 * Version: 0.5.0
 */
if (!defined('ABSPATH')) exit;
add_filter('the_content', function($content) {
 if (!is_page('tv') || !is_main_query() || !in_the_loop()) return $content;
 $channels = [
 ['Das Erste','Öffentlich-rechtlich','hls','https://daserste-live.ard-mcdn.de/daserste/live/hls/de/master.m3u8'],
 ['ZDF','Öffentlich-rechtlich','hls','https://zdf-hls-15.akamaized.net/hls/live/2016498/de/high/master.m3u8'],
 ['arte','Öffentlich-rechtlich','hls','https://artesimulcast.akamaized.net/hls/live/2030993/artelive_de/master.m3u8'],
 ['3sat','Öffentlich-rechtlich','hls','https://zdf-hls-18.akamaized.net/hls/live/2016501/dach/veryhigh/master.m3u8'],
 ['ZDFneo','Öffentlich-rechtlich','hls','https://zdf-hls-16.akamaized.net/hls/live/2016499/de/high/master.m3u8'],
 ['ZDFinfo','Nachrichten & Wissen','hls','https://zdf-hls-17.akamaized.net/hls/live/2016500/de/high/master.m3u8'],
 ['phoenix','Nachrichten & Wissen','link','https://www.phoenix.de/livestream.html'],
 ['tagesschau24','Nachrichten & Wissen','link','https://www.tagesschau.de/multimedia/livestreams/'],
 ['Deutsche Welle','Nachrichten & Wissen','link','https://www.dw.com/de/media-center/live-tv/s-100817'],
 ['KiKA','Familie','link','https://www.kika.de/livestream'],
 ['ONE','Öffentlich-rechtlich','link','https://www.ardmediathek.de/live'],
 ['ARD alpha','Nachrichten & Wissen','link','https://www.ardmediathek.de/live'],
 ['BR Fernsehen','Regional','link','https://www.br.de/mediathek/live'],
 ['WDR Fernsehen','Regional','link','https://www.ardmediathek.de/live'],
 ['NDR Fernsehen','Regional','link','https://www.ardmediathek.de/live'],
 ['SWR Fernsehen','Regional','link','https://www.ardmediathek.de/live'],
 ['MDR Fernsehen','Regional','link','https://www.ardmediathek.de/live'],
 ['rbb Fernsehen','Regional','link','https://www.ardmediathek.de/live'],
 ['hr-fernsehen','Regional','link','https://www.ardmediathek.de/live'],
 ['SR Fernsehen','Regional','link','https://www.ardmediathek.de/live'],
 ['RTL','Private Sender','link','https://www.rtlplus.de/live-tv'],
 ['VOX','Private Sender','link','https://www.rtlplus.de/live-tv'],
 ['RTLZWEI','Private Sender','link','https://www.rtlplus.de/live-tv'],
 ['ntv','Nachrichten & Wissen','link','https://www.n-tv.de/mediathek/livestream/'],
 ['ProSieben','Private Sender','link','https://www.joyn.de/live-tv'],
 ['SAT.1','Private Sender','link','https://www.joyn.de/live-tv'],
 ['Kabel Eins','Private Sender','link','https://www.joyn.de/live-tv'],
 ['sixx','Private Sender','link','https://www.joyn.de/live-tv'],
 ['SPORT1','Sport','link','https://www.sport1.de/tv-video'],
 ['Eurosport','Sport','link','https://www.eurosport.de/'],
 ['Pluto TV','FAST & Streaming','link','https://pluto.tv/de/live-tv'],
 ['Joyn','FAST & Streaming','link','https://www.joyn.de/live-tv'],
 ['waipu.tv','FAST & Streaming','link','https://play.waipu.tv/'],
 ['Zattoo','FAST & Streaming','link','https://zattoo.com/de/watch'],
 ['2ix2 Senderübersicht','Weitere Quellen','link','https://www.2ix2.com/'],
 ['RTL auf 2ix2','Weitere Quellen','link','https://www.2ix2.com/rtl-live/'],
 ['ProSieben auf 2ix2','Weitere Quellen','link','https://www.2ix2.com/pro7/'],
 ['Das Erste auf 2ix2','Weitere Quellen','link','https://www.2ix2.com/ard-de/']
 ];
 $json=wp_json_encode($channels,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
 $html='<section id="odpc-tv-lab" style="background:#101c30;color:#fff;padding:20px;border-radius:16px;max-width:1100px;margin:auto"><h2 style="color:#fff">ODPC TV</h2><p>Ein Senderkatalog für Direktstreams und deine angemeldeten Anbieter. Joyn, waipu.tv und Zattoo spielen in ihren offiziellen Webplayern.</p><nav aria-label="TV-Anbieter" style="display:flex;flex-wrap:wrap;gap:8px;margin:12px 0"><a href="https://www.joyn.de/live-tv" target="_blank" rel="noopener noreferrer" style="color:white;background:#294e7c;padding:10px;border-radius:8px">Joyn öffnen ↗</a><a href="https://play.waipu.tv/" target="_blank" rel="noopener noreferrer" style="color:white;background:#294e7c;padding:10px;border-radius:8px">waipu.tv öffnen ↗</a><a href="https://zattoo.com/de/watch" target="_blank" rel="noopener noreferrer" style="color:white;background:#294e7c;padding:10px;border-radius:8px">Zattoo öffnen ↗</a></nav><video id="odpc-tv-player" controls playsinline style="width:100%;aspect-ratio:16/9;background:#000;border-radius:12px"></video><p id="odpc-tv-status" role="status">Sender auswählen.</p><input id="odpc-tv-search" type="search" placeholder="Sender suchen…" aria-label="Sender suchen" style="width:100%;padding:12px;border-radius:8px;margin-bottom:12px"><label style="display:block;margin-bottom:12px"><input type="checkbox" id="odpc-tv-only-favorites"> Nur Favoriten anzeigen</label><div id="odpc-tv-filters" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px"></div><div style="display:flex;gap:8px;flex-wrap:wrap;margin:12px 0"><button type="button" id="odpc-tv-import" style="padding:10px;border:0;border-radius:8px;background:#207a6b;color:white;cursor:pointer">Deutsche IPTV-Playlist laden</button><span id="odpc-tv-import-status" role="status">Externe Liste optional laden</span></div><div id="odpc-tv-buttons" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px"></div><small>Bei Anbieterlinks erfolgt die Wiedergabe auf der offiziellen Website mit deiner dortigen Anmeldung. Favoriten werden nur lokal in diesem Browser gespeichert. Externe Anbieter können Anmeldung, Geoblocking oder ein Abonnement verlangen. Direkte HLS-Quellen sind noch nicht vollständig verifiziert.</small></section>';
 $html.='<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.20/dist/hls.min.js"></script>';
 $html.='<script>(function(){let data='.$json.',root=document.getElementById("odpc-tv-lab");if(!root)return;const video=root.querySelector("video"),status=root.querySelector("#odpc-tv-status"),buttons=root.querySelector("#odpc-tv-buttons"),filters=root.querySelector("#odpc-tv-filters"),search=root.querySelector("#odpc-tv-search");let h=null,group="Alle";const favoriteToggle=root.querySelector("#odpc-tv-only-favorites");let favorites=[];try{favorites=JSON.parse(localStorage.getItem("odpc-tv-favorites-v1")||"[]");if(!Array.isArray(favorites))favorites=[]}catch(e){favorites=[]}const groups=["Alle",...new Set(data.map(x=>x[1]))];function play(c){if(h){h.destroy();h=null}video.pause();video.removeAttribute("src");video.load();status.textContent="Verbinde mit "+c[0];if(window.Hls&&Hls.isSupported()){h=new Hls();h.loadSource(c[3]);h.attachMedia(video);h.on(Hls.Events.MANIFEST_PARSED,()=>video.play().catch(()=>status.textContent="Play drücken"));h.on(Hls.Events.ERROR,(_,e)=>{if(e.fatal)status.textContent="Stream nicht verfügbar: "+e.type})}else if(video.canPlayType("application/vnd.apple.mpegurl")){video.src=c[3];video.play().catch(()=>status.textContent="Play drücken")}else status.textContent="Dieser Browser unterstützt den Stream nicht"}function render(){buttons.replaceChildren();for(const c of data){if((group!=="Alle"&&c[1]!==group)||!c[0].toLowerCase().includes(search.value.toLowerCase())||(favoriteToggle.checked&&!favorites.includes(c[0])))continue;const b=document.createElement(c[2]==="link"?"a":"button");b.textContent=c[0]+(c[2]==="link"?" ↗":" ▶");b.style.cssText="display:block;text-align:center;padding:13px;background:#254d80;color:#fff;border:0;border-radius:9px;cursor:pointer;text-decoration:none";if(c[2]==="link"){b.href=c[3];b.target="_blank";b.rel="noopener noreferrer";b.title="Offizielle Senderseite öffnen"}else b.onclick=()=>play(c);const wrapper=document.createElement("div");wrapper.style.cssText="display:flex;gap:3px;min-width:0";b.style.flex="1";const star=document.createElement("button");star.type="button";star.textContent=favorites.includes(c[0])?"★":"☆";star.setAttribute("aria-label","Favorit für "+c[0]+" umschalten");star.style.cssText="background:#36536f;color:white;border:0;border-radius:8px;padding:10px;cursor:pointer";star.onclick=()=>{favorites=favorites.includes(c[0])?favorites.filter(n=>n!==c[0]):[...favorites,c[0]];try{localStorage.setItem("odpc-tv-favorites-v1",JSON.stringify(favorites))}catch(e){}render()};wrapper.append(b,star);buttons.appendChild(wrapper)}}for(const name of groups){const b=document.createElement("button");b.textContent=name;b.style.cssText="padding:9px;border:0;border-radius:8px;background:#36536f;color:#fff;cursor:pointer";b.onclick=()=>{group=name;render()};filters.appendChild(b)}search.addEventListener("input",render);favoriteToggle.addEventListener("change",render);const load=root.querySelector("#odpc-tv-import"),msg=root.querySelector("#odpc-tv-import-status");function parseM3U(raw){const rows=raw.replace(/\\r/g,"").split("\\n");let info=null,out=[];for(const row of rows){const line=row.trim();if(line.startsWith("#EXTINF:")){const title=line.split(",").slice(1).join(",").trim();const g=(line.match(/group-title="([^"]*)"/)||[])[1]||"IPTV Deutschland";info={title:title||"Unbenannt",group:g}}else if(info&&/^https:\\/\\//i.test(line)){out.push([info.title,info.group,"hls",line]);info=null}else if(line&&!line.startsWith("#"))info=null}return out}load.addEventListener("click",async()=>{load.disabled=true;msg.textContent="Lade Senderliste…";try{const r=await fetch("https://iptv-org.github.io/iptv/countries/de.m3u",{mode:"cors",cache:"no-store"});if(!r.ok)throw Error("HTTP "+r.status);const entries=parseM3U(await r.text());if(!entries.length)throw Error("Keine HTTPS-Streams gefunden");const seen=new Set(data.map(x=>x[0]+"|"+x[3]));let added=0;for(const e of entries){const key=e[0]+"|"+e[3];if(!seen.has(key)){data.push(e);seen.add(key);added++}}group="Alle";filters.replaceChildren();for(const name of ["Alle",...new Set(data.map(x=>x[1]))]){const b=document.createElement("button");b.textContent=name;b.style.cssText="padding:9px;border:0;border-radius:8px;background:#36536f;color:#fff;cursor:pointer";b.onclick=()=>{group=name;render()};filters.appendChild(b)}render();msg.textContent=added+" zusätzliche HTTPS-Sender geladen (nicht einzeln getestet)"}catch(e){msg.textContent="Import fehlgeschlagen: "+e.message+". Bestehende Sender bleiben erhalten."}finally{load.disabled=false}});render()})();</script>';
 return $html;
},20);
