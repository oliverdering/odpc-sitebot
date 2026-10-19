<?php
if(!defined('ABSPATH'))exit;
add_action('after_setup_theme',function(){add_theme_support('title-tag');add_theme_support('post-thumbnails');});
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('tl-full',get_stylesheet_uri(),[],wp_get_theme()->get('Version'));});
add_action('after_switch_theme',function(){
foreach(['Speisekarte'=>'speisekarte','Tisch anfragen'=>'tisch-anfragen','Kontakt'=>'kontakt','Anfahrt'=>'anfahrt','Galerie'=>'galerie'] as $title=>$slug){if(!get_page_by_path($slug))wp_insert_post(['post_title'=>$title,'post_name'=>$slug,'post_type'=>'page','post_status'=>'publish']);}
foreach(['Impressum'=>'impressum','Datenschutz'=>'datenschutz'] as $title=>$slug){if(!get_page_by_path($slug))wp_insert_post(['post_title'=>$title,'post_name'=>$slug,'post_type'=>'page','post_status'=>'draft']);}
});
add_action('init',function(){register_post_type('tl_dish',['labels'=>['name'=>'Speisekarte','singular_name'=>'Gericht','add_new_item'=>'Gericht hinzufügen','edit_item'=>'Gericht bearbeiten'],'public'=>true,'show_in_rest'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-food','supports'=>['title','editor','page-attributes']]);});
add_action('add_meta_boxes',function(){add_meta_box('tl_dish_data','Gericht – Kategorie und Preis',function($post){wp_nonce_field('tl_save_dish','tl_dish_nonce');echo '<p><label>Kategorie <select name="tl_category">';foreach(['Kleine Gerichte & Spezialitäten','Frische Salate','Schnitzel','Ofenkartoffeln'] as $c)echo '<option value="'.esc_attr($c).'" '.selected(get_post_meta($post->ID,'_tl_category',true),$c,false).'>'.esc_html($c).'</option>';echo '</select></label></p><p><label>Preis in Euro <input type="text" name="tl_price" value="'.esc_attr(get_post_meta($post->ID,'_tl_price',true)).'"></label></p>';},'tl_dish');});
add_action('save_post_tl_dish',function($id){if(!isset($_POST['tl_dish_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tl_dish_nonce'])),'tl_save_dish')||!current_user_can('edit_post',$id)||wp_is_post_revision($id))return;foreach(['tl_category'=>'_tl_category','tl_price'=>'_tl_price'] as $key=>$meta)if(isset($_POST[$key]))update_post_meta($id,$meta,sanitize_text_field(wp_unslash($_POST[$key])));});
function tl_menu_legacy(){ $q=new WP_Query(['post_type'=>'tl_dish','posts_per_page'=>100,'orderby'=>'menu_order title','order'=>'ASC']);$groups=[];foreach($q->posts as $p){$category=get_post_meta($p->ID,'_tl_category',true)?:'Weitere Gerichte';$groups[$category][]=$p;}foreach($groups as $category=>$items){echo '<section class="menu-group"><h2>'.esc_html($category).'</h2>';foreach($items as $p){$price=get_post_meta($p->ID,'_tl_price',true);echo '<article class="dish"><div><strong>'.esc_html($p->post_title).'</strong><p>'.wp_kses_post($p->post_content).'</p></div><b>'.($price!==''?esc_html($price).' €':'Preis auf Anfrage').'</b></article>';}echo '</section>';}wp_reset_postdata();}

function tl_menu(){
  echo '<div class="tl-menu-heading"><img src="https://odpc.de/wp-content/uploads/2026/02/FB_IMG_1771601697384.jpg" alt="Café Tante Lenchen – Logo" width="86" height="86"><div><h2>Unsere Speisekarten</h2><p>Wählen Sie Ihre Saisonkarte.</p></div></div><nav class="tl-season-tabs" aria-label="Saisonkarte wählen"><a href="#sommerkarte" data-season="sommerkarte">Sommerkarte</a><a href="#winterkarte" data-season="winterkarte">Winterkarte</a></nav><section id="sommerkarte" class="tl-season-panel"><h2>Sommerkarte</h2><p class="tl-menu-validity">Gültig vom 1. April bis 31. Oktober</p>';
  tl_menu_legacy();
  echo '</section><section id="winterkarte" class="tl-season-panel"><h2>Winterkarte</h2><p class="tl-menu-validity">Gültig ab 1. November 2026</p>';
  $groups=[
    'Vorspeisen & Suppen'=>[
      ['Kürbis-Ingwer-Suppe','9,50'],['Gulaschsuppe','10,50'],['Maronensuppe','9,50'],
      ['Avocado mit Cherrytomaten und gehobeltem Parmesan','13,50','Auf hausgemachtem Salatbett']
    ],
    'Hauptgerichte'=>[
      ['Schweineschnitzel Wiener Art','18,50','Mit Bratkartoffeln oder Pommes; Salat vom Buffet'],
      ['Schweineschnitzel mit Schmorzwiebeln','18,50','Mit Bratkartoffeln oder Pommes; Salat vom Buffet'],
      ['Schweineschnitzel mit Jalapeño und Paprika','18,50','Mit Bratkartoffeln oder Pommes; Salat vom Buffet'],
      ['Schweineschnitzel mit frischen Champignons','19,50','In Weißwein-Cognac-Sauce, mit Bratkartoffeln oder Pommes; Salat vom Buffet'],
      ['Schweineschnitzel mit Blumenkohl','19,50','Mit Käse überbacken; Bratkartoffeln oder Pommes'],
      ['Zürcher Kalbsgeschnetzeltes','21,50','Mit Rösti und Salat vom Buffet'],
      ['Schweinebraten in Biersoße','18,50','Mit Semmelknödel und Blaukraut']
    ],
    'Vegetarische Gerichte'=>[
      ['Semmelknödel in Champignon-Cognac-Sahnesauce','14,50','Mit frischen Kräutern'],
      ['Kartoffelauflauf mit Brokkoli und Blumenkohl','16,50','Mit Käse überbacken']
    ]
  ];

  foreach($groups as $category=>$items){
    echo '<section class="menu-group"><h2>'.esc_html($category).'</h2>';
    foreach($items as $item){
      echo '<article class="dish"><div><strong>'.esc_html($item[0]).'</strong>';
      if(isset($item[2]))echo '<p>'.esc_html($item[2]).'</p>';
      echo '</div><b>'.esc_html($item[1]).' €</b></article>';
    }
    echo '</section>';
  }
  echo '</section><script>(function(){var tabs=document.querySelectorAll(".tl-season-tabs a"),panels=document.querySelectorAll(".tl-season-panel");function show(id){panels.forEach(function(p){p.hidden=p.id!==id});tabs.forEach(function(a){var on=a.dataset.season===id;a.classList.toggle("is-active",on);if(on)a.setAttribute("aria-current","true");else a.removeAttribute("aria-current")})}tabs.forEach(function(a){a.addEventListener("click",function(e){e.preventDefault();show(a.dataset.season);history.replaceState(null,"","#"+a.dataset.season)})});var m=new Date().getMonth()+1;show(location.hash==="#winterkarte"?"winterkarte":location.hash==="#sommerkarte"?"sommerkarte":m>=4&&m<=10?"sommerkarte":"winterkarte")})();</script>';
}

function tl_seed_menu(){if(get_option('tl_seeded'))return;$rows=[
['Kleine Gerichte & Spezialitäten','Bayerischer Wurstsalat','12,50'],['Kleine Gerichte & Spezialitäten','Käseliebhaberteller','16,80'],['Kleine Gerichte & Spezialitäten','Ofenkartoffel mit Frühlingsquark','12,50'],['Kleine Gerichte & Spezialitäten','Pikanter Rindfleischsalat','14,80'],['Kleine Gerichte & Spezialitäten','Käsesalat mit Mandarinen','14,50'],['Kleine Gerichte & Spezialitäten','Reibekuchen mit Lachs','14,50'],['Frische Salate','Kleiner Salat',''],['Frische Salate','Bunter Salat mit Putenstreifen','19,50'],['Frische Salate','Salat mit Ziegenkäse','18,50'],['Frische Salate','Salat mit Riesengarnelen','20,50'],['Frische Salate','Salat mit Schinken und Ananas','18,50'],['Schnitzel','Schnitzel Wiener Art','17,80'],['Schnitzel','Zwiebelschnitzel','18,50'],['Schnitzel','Champignonschnitzel','18,50'],['Schnitzel','Schnitzel Teufelszauber','18,50'],['Ofenkartoffeln','Ofenkartoffel mit Putenstreifen','18,50'],['Ofenkartoffeln','Ofenkartoffel mit gebratenen Scampi','20,50']];
foreach($rows as $i=>$r){$id=wp_insert_post(['post_type'=>'tl_dish','post_status'=>'publish','post_title'=>$r[1],'menu_order'=>$i]);if(!is_wp_error($id)){update_post_meta($id,'_tl_category',$r[0]);update_post_meta($id,'_tl_price',$r[2]);}}update_option('tl_seeded',1);}
add_action('after_switch_theme','tl_seed_menu');

add_action('init',function(){if(!get_option('tl_legal_page_seed_v1')){foreach(['Impressum'=>'impressum','Datenschutz'=>'datenschutz'] as $title=>$slug){if(!get_page_by_path($slug))wp_insert_post(['post_title'=>$title,'post_name'=>$slug,'post_type'=>'page','post_status'=>'publish']);}update_option('tl_legal_page_seed_v1',1);}});

function tl_booking_request_handler(){
 if(!isset($_POST['tl_booking_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tl_booking_nonce'])),'tl_booking_request'))wp_die('Ungültige Anfrage.');
 $date=sanitize_text_field(wp_unslash($_POST['tl_date']??''));
 $time=sanitize_text_field(wp_unslash($_POST['tl_time']??''));
 $guests=absint($_POST['tl_guests']??0);
 $name=sanitize_text_field(wp_unslash($_POST['tl_name']??''));
 $email=sanitize_email(wp_unslash($_POST['tl_email']??''));
 $phone=sanitize_text_field(wp_unslash($_POST['tl_phone']??''));
 $dt=DateTimeImmutable::createFromFormat('!Y-m-d H:i',$date.' '.$time,wp_timezone());
 if(!$dt || $dt->format('Y-m-d H:i')!==$date.' '.$time || $dt->getTimestamp()<time() || $guests<1 || $guests>30 || !$name || !is_email($email) || empty($_POST['tl_consent']))wp_die('Bitte Eingaben prüfen.');
 $message=implode("\n",array('Neue unverbindliche Tischanfrage','Datum: '.$date,'Uhrzeit: '.$time,'Personen: '.$guests,'Name: '.$name,'E-Mail: '.$email,'Telefon: '.$phone,'Bitte persönlich bestätigen.'));
 $sent=wp_mail('info@tante-lenchen.odpc.de','Neue Tischanfrage',$message,array('Reply-To: '.$email));
 wp_safe_redirect(add_query_arg('anfrage',$sent?'gesendet':'fehler',home_url('/tisch-anfragen/')));exit;
}
add_action('admin_post_nopriv_tl_booking_request','tl_booking_request_handler');
add_action('admin_post_tl_booking_request','tl_booking_request_handler');
