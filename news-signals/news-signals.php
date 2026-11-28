<?php
/**
 * Plugin Name: ODPC News & Signals
 * Description: Eigenständiges KI-Nachrichtenportal mit täglichem RSS-Abruf.
 */
defined('ABSPATH') || exit;
const ODPC_NEWS_KEY = 'odpc_news_v2_items';
function odpc_news_sources() {
 return [
  ['OpenAI','https://openai.com/news/rss.xml','Hersteller'],
  ['Google DeepMind','https://deepmind.google/blog/rss.xml','Hersteller'],
  ['MIT Technology Review','https://www.technologyreview.com/feed/','Fachpresse'],
  ['The Decoder','https://the-decoder.com/feed/','Fachpresse'],
  ['TechCrunch AI','https://techcrunch.com/category/artificial-intelligence/feed/','Fachpresse']
 ];
}
function odpc_news_refresh() {
 include_once ABSPATH.WPINC.'/feed.php';
 $items=[];$health=[];$successful=[];
 foreach (odpc_news_sources() as [$name,$url,$kind]) {
  $feed=fetch_feed($url);
  if (is_wp_error($feed)) {$health[$name]='Fehler';continue;}
  $health[$name]='OK';$successful[$name]=true;
  foreach ($feed->get_items(0,12) as $entry) {
   $link=esc_url_raw($entry->get_permalink());$title=wp_strip_all_tags($entry->get_title());
   $ts=$entry->get_date('U');
   if (!$link || !$title || !$ts || $ts>time()+DAY_IN_SECONDS) continue;
   $items[]=['title'=>$title,'url'=>$link,'date'=>(int)$ts,'source'=>$name,'kind'=>$kind,
     'summary'=>wp_trim_words(wp_strip_all_tags($entry->get_description()),30,'…')];
  }
 }
 $prior=get_option(ODPC_NEWS_KEY,[]);
 // Retain previous articles for each source whose current feed failed.
 foreach(($prior['items']??[]) as $old) {
  if(!isset($successful[$old['source']]) && !empty($old['url']) && !empty($old['date']))$items[]=$old;
 }
 if (!$items && !empty($prior['items'])) {update_option('odpc_news_v2_health',$health,false);return;}

 $seen=[];$unique=[];
 usort($items,static fn($a,$b)=>$b['date']<=>$a['date']);
 foreach ($items as $item) {
  $key=md5(strtolower(preg_replace('~[?#].*$~','',$item['url'])));
  if(isset($seen[$key]))continue;
  $seen[$key]=true;$unique[]=$item;
 }
 update_option(ODPC_NEWS_KEY,['updated'=>time(),'items'=>array_slice($unique,0,80),'health'=>$health],false);
}
add_action('odpc_news_daily','odpc_news_refresh');
add_action('init',static function(){
 if(!wp_next_scheduled('odpc_news_daily'))wp_schedule_event(time()+300,'daily','odpc_news_daily');
});
// On busy/cached sites WP-Cron can miss a day; a real page visit triggers a bounded catch-up.
add_action('template_redirect',static function(){
 if(!is_page('news-signals'))return;
 $last=get_option(ODPC_NEWS_KEY,[]);
 if(time()-(int)($last['updated']??0)<DAY_IN_SECONDS)return;
 if(get_transient('odpc_news_refresh_lock'))return;
 set_transient('odpc_news_refresh_lock',1,15*MINUTE_IN_SECONDS);
 odpc_news_refresh();
},1);
function odpc_news_render() {
 $data=get_option(ODPC_NEWS_KEY,[]);
 if(empty($data['items']) && !get_transient('odpc_news_bootstrap_lock')) {
  set_transient('odpc_news_bootstrap_lock',1,15*MINUTE_IN_SECONDS);
  odpc_news_refresh();$data=get_option(ODPC_NEWS_KEY,[]);
 }
 ob_start();?>
 <section id="odpc-news-v2" class="odpc-news-v2">
 <header class="on-hero"><p class="on-eyebrow">ODPC · KI-Informationsportal</p><h1>News &amp; Signals</h1>
 <p>Aktuelle Entwicklungen rund um künstliche Intelligenz – unabhängige Fachpresse und offizielle Herstellerinformationen, transparent gekennzeichnet.</p>
 <nav aria-label="KI-Portal"><a href="/ki-mediathek/">Zur KI-Mediathek ↗</a><a href="#on-articles">Nachrichten</a><a href="#on-sources">Quellenstatus</a></nav>
 <small>Letzter Datenabruf: <?php echo !empty($data['updated'])?esc_html(wp_date('d.m.Y H:i',$data['updated'])) . ' Uhr':'Noch kein erfolgreicher Abruf'; ?></small></header>
 <section id="on-articles"><h2>KI-Nachrichten und Analysen</h2>
 <?php if(empty($data['items'])): ?><p>Derzeit sind keine geprüften Feed-Einträge verfügbar. Bitte später erneut versuchen.</p><?php else: ?>
 <div class="on-grid"><?php foreach($data['items'] as $item): ?>
 <article class="on-card"><p class="on-meta"><?php echo esc_html($item['source']); ?> · <?php echo esc_html($item['kind']); ?> · <time datetime="<?php echo esc_attr(gmdate('c',$item['date'])); ?>"><?php echo esc_html(wp_date('d.m.Y',$item['date'])); ?></time></p>
 <h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['summary']); ?></p><a href="<?php echo esc_url($item['url']); ?>" rel="noopener noreferrer" target="_blank">Originalartikel lesen ↗</a></article>
 <?php endforeach; ?></div><?php endif; ?></section>
 <section id="on-sources"><h2>Quellen und Transparenz</h2><p>Herstellermeldungen sind keine unabhängige Berichterstattung. Feeds werden täglich abgerufen; bei Ausfällen bleibt der letzte erfolgreiche Datenstand erhalten.</p>
 <ul><?php foreach(odpc_news_sources() as [$name,$url,$kind]): ?><li><?php echo esc_html($name.' · '.$kind.' · '.(($data['health'][$name]??'Noch nicht geprüft')==='OK'?'Erreichbar':'Abruf fehlgeschlagen oder noch nicht geprüft')); ?></li><?php endforeach; ?></ul></section>
 </section><?php return ob_get_clean();
}
add_shortcode('odpc_news_signals','odpc_news_render');
// Preserve the canonical WordPress page and its SEO metadata while replacing its body.
add_filter('the_content',static function($content){
 if(!is_admin() && is_main_query() && in_the_loop() && is_page('news-signals'))return odpc_news_render();
 return $content;
},99);
add_action('wp_enqueue_scripts',static function(){
 if(!is_page('news-signals'))return;
 wp_enqueue_style('odpc-news-v2',plugins_url('news-signals.css',__FILE__),[], '1.0.0');
});
