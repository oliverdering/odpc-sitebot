<?php
/**
 * Plugin Name: ODPC Universal Podcast (Pilot)
 * Description: Page-aware multilingual audio summaries. Does not modify the existing floating dock.
 * Version: 0.1.0
 */
defined('ABSPATH') || exit;

/* Configure in wp-config.php, never commit credentials:
 * define('ODPC_PODCAST_GEMINI_KEY', '...');
 * define('ODPC_PODCAST_GOOGLE_TTS_KEY', '...');
 * Both keys are required; without them the old reader stays intact.
 */
add_action('rest_api_init', function () {
 register_rest_route('odpc-podcast/v1', '/audio', [
  'methods' => 'GET',
  'permission_callback' => '__return_true',
  'callback' => 'odpc_podcast_audio'
 ]);
});
function odpc_podcast_audio(WP_REST_Request $request) {
 $post_id = absint($request->get_param('post_id'));
 $lang = sanitize_key($request->get_param('lang') ?: 'de');
 $languages = ['de'=>'de-DE','en'=>'en-US','tr'=>'tr-TR','fr'=>'fr-FR','nl'=>'nl-NL','es'=>'es-ES','ru'=>'ru-RU'];
 if (!isset($languages[$lang])) return new WP_Error('language','Unsupported language',['status'=>400]);
 $post = get_post($post_id);
 if (!$post || $post->post_status !== 'publish' || !in_array($post->post_type,['page','post'],true))
  return new WP_Error('page','Published page not found',['status'=>404]);
 if (!defined('ODPC_PODCAST_GEMINI_KEY') || !defined('ODPC_PODCAST_GOOGLE_TTS_KEY'))
  return new WP_Error('not_configured','Podcast service not configured; original reader remains available',['status'=>503]);
 $raw = strip_shortcodes($post->post_content);
 $raw = preg_replace('~<(script|style|nav|footer|form|button|aside)[^>]*>.*?</\\1>~is',' ',$raw);
 $raw = trim(preg_replace('/\\s+/u',' ',wp_strip_all_tags($raw)));
 $raw = mb_substr($raw,0,16000);
 if (mb_strlen($raw)<80) return new WP_Error('empty','Insufficient editorial content',['status'=>422]);
 $cache_key='odpc_podcast_'.md5($post_id.'|'.$post->post_modified_gmt.'|'.$lang.'|v1');
 $cached=get_transient($cache_key);
 if (is_array($cached)) return rest_ensure_response($cached);
 // Bound unauthenticated API expenditure. Apply stricter gateway limits in production.
 $ip=isset($_SERVER['REMOTE_ADDR'])?sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])):'unknown';
 $rate_key='odpc_podcast_rl_'.md5($ip);
 $count=(int)get_transient($rate_key);
 if ($count>=8) return new WP_Error('rate_limit','Please try later',['status'=>429]);
 set_transient($rate_key,$count+1,HOUR_IN_SECONDS);
 $prompt='Create a faithful, natural spoken MONOLOG in language '.$languages[$lang].'. Summarize this website article for a customer in 180-280 words. Preserve key facts, caveats and product names; do not invent claims. Explain practical meaning with a concrete example. No navigation, buttons, menus, URLs, markdown, headings or stage directions. Speak as ODPC, not as a cloned person. Article title: '.$post->post_title.'. Article text: '.$raw;
 $gemini=wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',[
  'timeout'=>35,'headers'=>['Content-Type'=>'application/json','x-goog-api-key'=>ODPC_PODCAST_GEMINI_KEY],
  'body'=>wp_json_encode(['contents'=>[['parts'=>[['text'=>$prompt]]]],'generationConfig'=>['temperature'=>0.35,'maxOutputTokens'=>1000]])
 ]);
 if(is_wp_error($gemini)||wp_remote_retrieve_response_code($gemini)!==200)
  return new WP_Error('summary_failed','AI summary unavailable',['status'=>502]);
 $data=json_decode(wp_remote_retrieve_body($gemini),true);
 $script=trim(implode(' ',array_map(static function($p){return $p['text']??'';},$data['candidates'][0]['content']['parts']??[])));
 if(mb_strlen($script)<80) return new WP_Error('summary_empty','No usable summary',['status'=>502]);
 $tts=wp_remote_post('https://texttospeech.googleapis.com/v1/text:synthesize?key='.rawurlencode(ODPC_PODCAST_GOOGLE_TTS_KEY),[
  'timeout'=>35,'headers'=>['Content-Type'=>'application/json'],
  'body'=>wp_json_encode(['input'=>['text'=>$script],'voice'=>['languageCode'=>$languages[$lang]],'audioConfig'=>['audioEncoding'=>'MP3','speakingRate'=>0.95]])
 ]);
 if(is_wp_error($tts)||wp_remote_retrieve_response_code($tts)!==200)
  return new WP_Error('tts_failed','Natural audio unavailable',['status'=>502]);
 $audio=json_decode(wp_remote_retrieve_body($tts),true)['audioContent']??'';
 if(!$audio||strlen($audio)>8000000)return new WP_Error('audio_invalid','Invalid audio response',['status'=>502]);
 $result=['audio'=>'data:audio/mpeg;base64,'.$audio,'language'=>$lang,'summary'=>$script,'source_modified'=>$post->post_modified_gmt];
 set_transient($cache_key,$result,7*DAY_IN_SECONDS);
 return rest_ensure_response($result);
}
add_action('wp_footer',function(){
 if(is_admin()||!is_singular(['page','post']))return;
 $id=get_queried_object_id();if(!$id)return;
 $endpoint=rest_url('odpc-podcast/v1/audio');
 ?>
 <script id="odpc-universal-podcast">
 (function(){
 'use strict';
 const id=<?php echo (int)$id; ?>,endpoint=<?php echo wp_json_encode($endpoint); ?>;
 const setup=()=>{
  const dock=document.getElementById('odpc-utility-dock'),reader=document.getElementById('odpc-reader');
  const play=document.getElementById('odpc-reader-play'),stop=document.getElementById('odpc-reader-stop');
  if(!dock||!reader||!play||!stop||reader.dataset.universalPodcast)return;
  reader.dataset.universalPodcast='1';
  const audio=new Audio();let pending=null;
  const status=document.getElementById('odpc-reader-status');
  const label=(message)=>{if(status)status.textContent=message;};
  const language=()=>{
   const current=window.ODPCLanguage?.get?.()||document.querySelector('#odpc-utility-dock select.odpc-language-fallback')?.value||document.documentElement.lang?.slice(0,2)||'de';
   return ['de','en','tr','fr','nl','es','ru'].includes(current)?current:'de';
  };
  play.setAttribute('aria-label','Seiten-Podcast abspielen');play.title='Seiten-Podcast';
  stop.setAttribute('aria-label','Podcast stoppen');stop.title='Podcast stoppen';
  const style=document.createElement('style');
  style.textContent='#odpc-utility-dock #odpc-reader #odpc-reader-stop{display:flex!important;position:absolute!important;top:0!important;right:-7px!important;width:16px!important;height:16px!important;min-height:16px!important;font-size:10px!important;background:#f3f5f4!important;color:#162c35!important;z-index:2!important}';
  document.head.appendChild(style);
  const halt=()=>{pending?.abort();pending=null;audio.pause();audio.currentTime=0;reader.classList.remove('is-playing');};
  stop.addEventListener('click',e=>{e.preventDefault();e.stopImmediatePropagation();halt();label('Podcast gestoppt');},true);
  play.addEventListener('click',async e=>{
   e.preventDefault();e.stopImmediatePropagation();
   if(!audio.paused){audio.pause();reader.classList.remove('is-playing');label('Podcast pausiert');return;}
   if(audio.src && audio.dataset.language===language()){try{await audio.play();reader.classList.add('is-playing');label('Podcast läuft');}catch(err){label('Audiowiedergabe blockiert');}return;}
   halt();pending=new AbortController();label('Podcast wird erstellt …');
   try{
    const res=await fetch(endpoint+'?post_id='+id+'&lang='+encodeURIComponent(language()),{signal:pending.signal,credentials:'same-origin'});
    const data=await res.json();if(!res.ok)throw Error(data.message||'Podcast momentan nicht verfügbar');
    audio.src=data.audio;audio.dataset.language=data.language;pending=null;
    await audio.play();reader.classList.add('is-playing');label('Podcast läuft');
   }catch(err){if(err.name!=='AbortError')label(err.message||'Podcast nicht verfügbar');}
  },true);
  audio.addEventListener('ended',()=>{reader.classList.remove('is-playing');label('Podcast beendet');});
  document.addEventListener('odpc:languagechange',halt);
  window.addEventListener('pagehide',halt);
 };
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>setTimeout(setup,500));else setTimeout(setup,500);
 })();
 </script>
 <?php
},120);
