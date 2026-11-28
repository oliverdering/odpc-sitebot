<?php
/**
 * Plugin Name: ODPC KI-Mediathek – Portal für den bestehenden Shortcode
 */
defined('ABSPATH') || exit;

function odpc_guide_state() {
    static $state;
    if (isset($state)) return $state;
    $dir = __DIR__.'/odpc-ki-mediathek/';
    $state = is_readable($dir.'catalogue.json') ? json_decode(file_get_contents($dir.'catalogue.json'), true) : [];
    return is_array($state) && !empty($state['items']) ? $state : [];
}

// The existing home renderer reads this option. The WordPress snapshot remains available for rollback.
add_filter('pre_option_odpc_media_data', function ($old) {
    $data = odpc_guide_state();
    if (empty($data['items'])) return $old;
    return ['updated'=>$data['updated'], 'items'=>array_slice($data['items'], 0, 64)];
});
function odpc_guide_active() {
    return is_file(__DIR__.'/odpc-ki-mediathek/verified') && !empty(odpc_guide_state()['items']);
}
add_filter('pre_schedule_event', function ($result, $event) {
    return $event->hook === 'odpc_media_refresh' && odpc_guide_active() ? false : $result;
}, 10, 2);
add_action('init', function () {
    if (!odpc_guide_active()) return;
    if (wp_next_scheduled('odpc_media_refresh')) wp_clear_scheduled_hook('odpc_media_refresh');
    remove_all_actions('odpc_media_refresh');
}, 99);
function odpc_guide_date($format, $ts) {
    return wp_date($format, (int)$ts, new DateTimeZone('Europe/Berlin'));
}
function odpc_guide_banner() {
    if (!function_exists('odpc_media_render')) return '';
    $data = odpc_guide_state();
    if (time()-$data['updated'] <= DAY_IN_SECONDS) {
        $html = odpc_media_render(false);
        if (strpos($html,'class="odpc-media-track"') !== false) {
            $cached = get_option('odpc_guide_banner_cache', []);
            if (($cached['stamp'] ?? 0) != $data['updated']) {
                update_option('odpc_guide_banner_cache',['stamp'=>$data['updated'],'html'=>$html],false);
            }
            return $html;
        }
    }
    $cached = get_option('odpc_guide_banner_cache', []);
    return $cached['html'] ?? '';
}
add_action('wp_footer', function () {
    if (is_front_page() && odpc_guide_active() && time()-odpc_guide_state()['updated'] > DAY_IN_SECONDS) {
        echo odpc_guide_banner();
    }
}, 1);
function odpc_guide_select($name, $label, $choices) {
    echo '<label>'.esc_html($label).'<select name="'.esc_attr($name).'"><option value="">Alle</option>';
    foreach ($choices as $value=>$text) echo '<option value="'.esc_attr($value).'">'.esc_html($text).'</option>';
    echo '</select></label>';
}
function odpc_guide_poster($station, $type) {
    $paths = ['play'=>'M9 5l11 7-11 7z','tv'=>'M3 5h18v13H3z M9 21h6','radio'=>'M3 7h18v13H3z M4 7l13-5 M6 11h12 M7 15h3'];
    echo '<div class="kg-art" aria-hidden="true"><span>'.esc_html($station).'</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="'.esc_attr($paths[$type]).'"/></svg></div>';
}
function odpc_guide_card($v, $future=false) {
    $topics=$v['topics'] ?? []; $formats=$v['formats'] ?? [];
    $date=odpc_guide_date('Y-m-d',$v['date']);
    ?>
    <article class="kg-card" data-media-kind="<?= $future?'future':'now' ?>" data-station="<?= esc_attr($v['sender']) ?>" data-date="<?= esc_attr($date) ?>" data-published="<?= esc_attr(!empty($v['published'])?odpc_guide_date('Y-m-d',$v['published']):'') ?>" data-tags="<?= esc_attr(implode('|',array_merge($topics,$formats))) ?>">
    <?php odpc_guide_poster($v['sender'],$future?'tv':'play'); ?>
    <div class="kg-card-body">
    <p class="kg-meta"><?php echo esc_html(odpc_guide_date($future?'d.m.Y · H:i \U\h\r':'d.m.Y',$v['date']));
    if (!empty($v['duration'])) echo ' · '.esc_html(max(1,round($v['duration']/60))).' Min.'; ?></p>
    <h3><?= esc_html($v['title']) ?></h3>
    <p><?= esc_html($v['summary']) ?></p>
    <?php if (!empty($v['published'])): ?><small>Veröffentlicht am <?= esc_html(odpc_guide_date('d.m.Y',$v['published'])) ?></small><?php endif;
    if (!empty($v['expires'])): ?><small>Verfügbar bis <?= esc_html(odpc_guide_date('d.m.Y',$v['expires'])) ?></small><?php endif; ?>
    <div class="kg-tags"><?php foreach(array_merge($formats,$topics) as $tag) echo '<span>'.esc_html($tag).'</span>'; ?></div>
    <a class="kg-btn" href="<?= esc_url($v['url']) ?>"><?= $future?'Zur Programmankündigung':'Jetzt ansehen' ?> <span aria-hidden="true">↗</span></a>
    </div></article><?php
}
function odpc_guide_render() {
    $d=odpc_guide_state();
    if (empty($d['items'])) return function_exists('odpc_media_render') ? odpc_media_render(true) : '';
    $now=time();
    $items=array_values(array_filter($d['items'],static function($i)use($now){return empty($i['expires'])||$i['expires']>$now;}));
    $future=array_values(array_filter($d['upcoming'],static function($i)use($now){return $i['date']>$now;}));
    $counts=array_count_values(array_column($items,'sender'));
    $stations=array_unique(array_merge(array_keys($counts),array_column($future,'sender'))); natcasesort($stations);
    $topics=['Dokumentationen','Reportagen','Nachrichten','Interviews','Wissenschaft und Forschung','Wirtschaft und Arbeitswelt','Gesellschaft und Politik','Robotik und Automatisierung','KI-Sicherheit und Datenschutz','KI im Alltag'];
    $dir=__DIR__.'/odpc-ki-mediathek/';
    $to_options=static function($list){return array_combine($list,$list);};
    ob_start(); ?>
    <section id="odpc-ki-guide" data-version="2" data-updated="<?= (int)$d['updated'] ?>">
    <header class="kg-hero"><h1>ODPC | KI-Mediathek</h1><p class="kg-lead">Künstliche Intelligenz im Fernsehen – aktuelle Reportagen, Dokumentationen, Nachrichten und kommende Sendungen.</p>
    <p>Dieses unabhängige Angebot von Oliver Dering IT-Consulting führt direkt zu den Sendern. Der Katalog wird täglich recherchiert. Alle Sendezeiten gelten für Deutschland.</p>
    <dl class="kg-stats"><?php foreach(['Beiträge'=>count($items),'angekündigte Sendungen'=>count($future),'Datenquellen'=>count($d['sources'])] as $k=>$v) echo '<div><dt>'.esc_html($k).'</dt><dd>'.(int)$v.'</dd></div>'; ?></dl>
    <p class="kg-stamp"><?= !empty($d['complete'])?'Letzte erfolgreiche Aktualisierung':'Letzter Datenstand · Teilabruf' ?> · <time datetime="<?= esc_attr(gmdate('c',$d['updated'])) ?>"><?= esc_html(odpc_guide_date('d.m.Y · H:i',$d['updated'])) ?> Uhr</time></p>
    <?php if (empty($d['complete']) && !empty($d['last_complete'])) echo '<p>Letzter vollständiger Abruf · '.esc_html(odpc_guide_date('d.m.Y H:i',$d['last_complete'])).' Uhr</p>'; ?>
    </header>
    <div id="kg-banner"><?= odpc_guide_banner() ?></div>
    <nav class="kg-nav" aria-label="In der KI-Mediathek"><a href="#kg-jetzt">Beiträge</a><a href="#kg-programm">Programmvorschau</a><a href="#kg-sender">Senderverzeichnis</a></nav>
    <form class="kg-filters" hidden role="search" aria-label="Mediathek filtern"><label>Suche<input type="search" name="q" placeholder="Titel, Stichwort oder Sender"></label>
    <?php odpc_guide_select('sender','Sender',$to_options($stations)); odpc_guide_select('tag','Thema oder Format',$to_options($topics)); odpc_guide_select('kind','Angebot',['now'=>'Jetzt verfügbar','future'=>'Kommende Sendungen']); ?>
    <label>Sendedatum ab<input type="date" name="date"></label><label>Veröffentlicht ab<input type="date" name="published"></label>
    <button type="reset">Filter zurücksetzen</button><output aria-live="polite"></output></form>
    <section id="kg-jetzt"><h2>Aktuelle KI-Mediathekbeiträge</h2><p>Nach Datum der Sendung sortiert. Ein separates Veröffentlichungs- oder Ablaufdatum erscheint, wenn es belegt ist.</p>
    <div class="kg-grid"><?php foreach($items as $item) odpc_guide_card($item); ?></div><button hidden type="button" class="kg-more">Weitere Beiträge anzeigen</button></section>
    <section id="kg-programm"><h2>Demnächst im Fernsehen – KI-Programm-Vorschau</h2>
    <p>Die nächsten 14 Kalendertage. Jeder Termin führt zur Ankündigung des Senders.</p>
    <?php if (!$future) echo '<p>Derzeit liegen keine bestätigten KI-Sendetermine für diesen Zeitraum vor.</p>';
    $day=''; foreach($future as $v) { $key=odpc_guide_date('l, d. F Y',$v['date']); if($day!==$key) { if($day) echo '</div></div>'; $day=$key; echo '<div class="kg-day"><h3>'.esc_html($day).'</h3><div class="kg-grid">'; } odpc_guide_card($v,true); } if($day) echo '</div></div>'; ?>
    </section>
    <section id="kg-sender"><h2>Sender und Mediatheken</h2><p>Die Zahl an jeder Karte zählt die verfügbaren Beiträge im ODPC-Katalog. Die Vorschau berücksichtigt auch einzeln redaktionell bestätigte Termine.</p><div class="kg-grid kg-stations">
    <?php foreach(file($dir.'stations.tsv',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
      [$short,$name,$site,$library,$schedule,$copy]=explode("\t",$line); $n=$counts[$short]??0; ?>
      <article class="kg-station"><?php odpc_guide_poster($short,'radio'); ?><div class="kg-card-body"><h3><?= esc_html($name) ?></h3><p><?= esc_html($copy) ?></p><small><?= (int)$n ?> Beiträge · <?= $n?'Automatisch über MediathekViewWeb erfasst':'Weiterführende Quelle · aktuell ohne erfassten Beitrag' ?></small>
      <div class="kg-links"><?php foreach(['Website'=>$site,'Mediathek'=>$library,'Programm'=>$schedule] as $label=>$target) if($target) echo '<a href="'.esc_url('https://'.$target).'">'.esc_html($label).' ↗</a>'; ?></div></div></article>
    <?php } ?></div></section>
    <footer class="kg-foot"><strong>Quellen und Bildnachweise</strong><p>Beitragsnachweise von <a href="https://mediathekviewweb.de/">MediathekViewWeb</a>. Programmrecherche mit <a href="https://www.tvmaze.com/api">TVmaze</a> (<a href="https://creativecommons.org/licenses/by-sa/4.0/">CC BY-SA</a>) und den Sendeterminen auf den Originalseiten von ARTE. Hinzu kommen redaktionell geprüfte Senderankündigungen. Die grafischen Senderkennzeichen der Karten gestaltet ODPC. Videos öffnen sich bei den jeweiligen Anbietern.</p></footer></section>
    <?php return ob_get_clean();
}
add_action('wp_loaded', function () {
    if (function_exists('odpc_media_render') && odpc_guide_state()) add_shortcode('odpc_ki_mediathek','odpc_guide_render');
});
add_action('wp_enqueue_scripts',function () {
    if (!is_page(3852) || !odpc_guide_state()) return;
    $base=plugins_url('odpc-ki-mediathek/',__FILE__);
    wp_enqueue_style('odpc-ki-guide',$base.'portal.css',[],filemtime(__DIR__.'/odpc-ki-mediathek/portal.css'));
    wp_enqueue_script('odpc-ki-guide',$base.'portal.js',[],filemtime(__DIR__.'/odpc-ki-mediathek/portal.js'),true);
});
