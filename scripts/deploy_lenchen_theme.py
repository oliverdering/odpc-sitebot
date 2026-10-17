#!/usr/bin/env python3
"""Deploy isolated Tante Lenchen theme; never touches WordPress core or other sites."""
import os,base64,hashlib,hmac,posixpath,stat,io,zipfile,urllib.request
import paramiko
HOST="home119567219.1and1-data.host"
USER="acc1461323326"
PIN="SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q"
ROOT="/Lenchen"
THEME=ROOT+"/wp-content/themes/tante-lenchen"
def main():
 password=os.environ.get("ODPC_SFTP_PASSWORD","")
 if not password: raise RuntimeError("Missing ODPC_SFTP_PASSWORD")
 tr=paramiko.Transport((HOST,22));tr.start_client(timeout=30)
 try:
  fingerprint="SHA256:"+base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip("=")
  if not hmac.compare_digest(fingerprint,PIN):raise RuntimeError("SSH fingerprint mismatch")
  tr.auth_password(USER,password)
  if not tr.is_authenticated():raise RuntimeError("SFTP login failed")
  s=paramiko.SFTPClient.from_transport(tr)
  try:
   for folder in (ROOT,ROOT+"/wp-admin",ROOT+"/wp-includes",ROOT+"/wp-content",ROOT+"/wp-content/themes"):
    if not stat.S_ISDIR(s.stat(folder).st_mode):raise RuntimeError("WordPress path missing: "+folder)
   try:
    s.stat(THEME)
    raise RuntimeError("Theme already exists: refuse overwrite; no files changed")
   except FileNotFoundError:pass
   s.mkdir(THEME)
   files={
    "style.css":"""/*
Theme Name: Tante Lenchen
Theme URI: https://tante-lenchen.odpc.de/
Author: ODPC
Description: Café Tante Lenchen – eigenständiges responsives WordPress-Theme.
Version: 0.3.0
Text Domain: tante-lenchen
*/
:root{--mint:#DDEFE8;--paper:#F6EFE7;--brown:#5A3A2E;--ink:#2A1C16;--blue:#2D5A82}*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:17px/1.6 system-ui,Arial,sans-serif}a{color:var(--blue)}header,footer{background:var(--mint);padding:18px max(5vw,20px)}nav{display:flex;gap:18px;flex-wrap:wrap}nav a{font-weight:650;text-decoration:none;color:var(--brown)}main{max-width:1060px;margin:auto;padding:45px 22px;min-height:55vh}.hero{background:white;border:2px solid var(--blue);border-radius:22px;padding:35px;box-shadow:0 8px 24px #0001}h1,h2{color:var(--brown);line-height:1.2}.actions{display:flex;flex-wrap:wrap;gap:12px;margin:25px 0}.button{display:inline-block;padding:12px 18px;background:var(--blue);color:white;text-decoration:none;border-radius:12px}.button.alt{background:var(--brown)}.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px}.card{background:white;border-radius:15px;padding:20px;border:1px solid #c9d8d4}footer p{margin:4px 0}iframe{width:100%;min-height:650px;border:0}@media(max-width:600px){main{padding:24px 14px}.hero{padding:22px}nav{gap:10px}}
""",
    "functions.php":"""<?php
if (!defined('ABSPATH')) exit;
add_action('after_setup_theme',function(){add_theme_support('title-tag');add_theme_support('post-thumbnails');});
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('tante-lenchen',get_stylesheet_uri(),[], '0.3.0');});
add_action('after_switch_theme',function(){
 $pages=['Speisekarte','Tisch anfragen','Kontakt','Anfahrt','Impressum','Datenschutz'];
 foreach($pages as $name){if(!get_page_by_path(sanitize_title($name))){wp_insert_post(['post_title'=>$name,'post_name'=>sanitize_title($name),'post_type'=>'page','post_status'=>in_array($name,['Impressum','Datenschutz'],true)?'draft':'publish']);}}
});
""",
    "header.php":"""<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>><?php wp_body_open(); ?><header><strong><a href="<?php echo esc_url(home_url('/')); ?>">Café Tante Lenchen</a></strong><nav aria-label="Hauptnavigation"><?php foreach(['Speisekarte'=>'speisekarte','Tisch anfragen'=>'tisch-anfragen','Kontakt'=>'kontakt','Anfahrt'=>'anfahrt','Impressum'=>'impressum','Datenschutz'=>'datenschutz'] as $label=>$slug){echo '<a href="'.esc_url(home_url('/'.$slug.'/')).'">'.esc_html($label).'</a>'; } ?></nav></header><main>
""",
    "footer.php":"""</main><footer><p><strong>Café Tante Lenchen</strong> · Auf Ahl 2, 56112 Lahnstein</p><p>Telefon <a href="tel:+4926219862644">02621 9862644</a> · <a href="mailto:info@tante-lenchen.odpc.de">E-Mail</a> · <a href="https://wa.me/4915167121588" rel="noopener noreferrer">WhatsApp</a></p></footer><?php wp_footer(); ?></body></html>
""",
    "front-page.php":"""<?php get_header(); ?><section class="hero"><h1>Willkommen im Café Tante Lenchen</h1><p>Genießen, verweilen und gemeinsam Zeit verbringen – auf dem Camping-Park Lahnbogen in Lahnstein.</p><div class="actions"><a class="button" href="<?php echo esc_url(home_url('/speisekarte/')); ?>">Speisekarte</a><a class="button alt" href="<?php echo esc_url(home_url('/tisch-anfragen/')); ?>">Tisch anfragen</a><a class="button" href="https://wa.me/4915167121588">WhatsApp</a></div></section><section class="cards"><article class="card"><h2>Öffnungszeiten</h2><p>April–Oktober: täglich 12–21 Uhr<br>November–März: Donnerstag–Sonntag 12–21 Uhr</p></article><article class="card"><h2>Kontakt</h2><p><a href="mailto:info@tante-lenchen.odpc.de">info@tante-lenchen.odpc.de</a></p></article><article class="card"><h2>Anfahrt</h2><p>Auf Ahl 2, 56112 Lahnstein</p><a href="<?php echo esc_url(home_url('/anfahrt/')); ?>">Route ansehen</a></article></section><?php get_footer(); ?>
""",
    "page.php":"""<?php get_header(); while(have_posts()):the_post(); ?><article class="hero"><h1><?php the_title(); ?></h1><?php if(is_page('tisch-anfragen')): ?><p>Reservierungen sind erst nach Bestätigung durch das Café verbindlich.</p><p><a class="button" href="mailto:info@tante-lenchen.odpc.de?subject=Tischanfrage%20Tante%20Lenchen">Tisch per E-Mail anfragen</a> <a class="button alt" href="https://wa.me/4915167121588?text=Hallo%2C%20ich%20moechte%20einen%20Tisch%20anfragen.">Per WhatsApp anfragen</a></p><?php elseif(is_page('anfahrt')): ?><p>Auf Ahl 2, 56112 Lahnstein</p><p><a class="button" href="https://www.google.com/maps/search/?api=1&query=Auf+Ahl+2%2C+56112+Lahnstein" rel="noopener noreferrer" target="_blank">Google Maps öffnen</a></p><?php elseif(is_page('kontakt')): ?><p>Telefon: <a href="tel:+4926219862644">02621 9862644</a></p><p>E-Mail: <a href="mailto:info@tante-lenchen.odpc.de">info@tante-lenchen.odpc.de</a></p><p><a href="https://wa.me/4915167121588">WhatsApp schreiben</a></p><?php elseif(is_page('speisekarte')): ?><p>Unsere aktuelle Speisekarte wird hier nach abschließender Preisprüfung veröffentlicht. Fragen zu Gerichten und Preisen beantworten wir gern telefonisch.</p><p><a class="button" href="tel:+4926219862644">Speisekarte telefonisch erfragen</a></p><?php endif; the_content(); ?></article><?php endwhile; get_footer(); ?>
""",
    "index.php":"""<?php get_header(); ?><h1><?php bloginfo('name'); ?></h1><?php while(have_posts()):the_post(); ?><article><h2><?php the_title(); ?></h2><?php the_content(); ?></article><?php endwhile; get_footer(); ?>
"""
   }
   for name,content in files.items():
    with s.open(posixpath.join(THEME,name),"wb") as f:f.write(content.encode())
   print("DEPLOY PASS: theme files uploaded to",THEME,"count",len(files))
   print("Theme is NOT activated; activate via WP admin. Existing sites unchanged.")
  finally:s.close()
 finally:tr.close()
if __name__=="__main__":main()
