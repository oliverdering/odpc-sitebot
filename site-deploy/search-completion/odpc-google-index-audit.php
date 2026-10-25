<?php
/** Plugin Name: ODPC Direct Google Index Audit
 * One-off, administrator-only checks through the existing Site Kit authorization.
 * No tokens are returned, copied or logged; no new Google permissions are requested.
 */
if (!defined('ABSPATH')) exit;
function odpc_gsc_allowed_url($url) {
    $p = wp_parse_url($url);
    return is_array($p) && ($p['scheme'] ?? '') === 'https' && empty($p['user']) && empty($p['pass']) && empty($p['port']) && empty($p['query']) && empty($p['fragment']) && in_array(strtolower($p['host'] ?? ''), ['odpc.de','ai.odpc.de','tante-lenchen.odpc.de','atrium191-rb.odpc.de','bahnhof-grenzau.odpc.de','meinkraut.odpc.de','ra-justen.de'], true);
}
function odpc_gsc_service() {
    if (!class_exists('Google\\Site_Kit\\Plugin') || !class_exists('Google\\Site_Kit_Dependencies\\Google\\Service\\SearchConsole')) throw new RuntimeException('Existing Site Kit installation required');
    $auth = new \Google\Site_Kit\Core\Authentication\Authentication(\Google\Site_Kit\Plugin::instance()->context());
    if (!$auth->is_authenticated()) throw new RuntimeException('Existing Site Kit user authorization required');
    $client = $auth->get_oauth_client()->get_client();
    $client->setDefer(false);
    return new \Google\Site_Kit_Dependencies\Google\Service\SearchConsole($client);
}
function odpc_gsc_property($url) { return wp_parse_url($url, PHP_URL_HOST) === 'ra-justen.de' ? 'https://ra-justen.de/' : 'sc-domain:odpc.de'; }
function odpc_gsc_job_view($job, $full = false) {
    if (!$job) return ['state'=>'not_started'];
    $out = array_intersect_key($job, array_flip(['state','started','updated','finished','total','cursor','summary','sitemaps','error_code','error_type']));
    if ($full) $out['results'] = $job['results'];
    return $out;
}
add_action('rest_api_init', function () {
    if (strtolower((string)wp_parse_url(home_url('/'), PHP_URL_HOST)) !== 'odpc.de') return;
    register_rest_route('odpc-search/v1', '/audit', [
        ['methods'=>'GET', 'permission_callback'=>function () { return current_user_can('manage_options'); }, 'callback'=>function ($request) { return odpc_gsc_job_view(get_option('odpc_gsc_oneoff_audit', []), rest_sanitize_boolean($request->get_param('full'))); }],
        ['methods'=>'POST', 'permission_callback'=>function () { return current_user_can('manage_options'); }, 'callback'=>function ($request) {
            $existing = get_option('odpc_gsc_oneoff_audit', []);
            if (($existing['state'] ?? '') === 'running') return odpc_gsc_job_view($existing);
            $urls = $request->get_param('urls');
            if (!is_array($urls) || !$urls || count($urls)>1500) return new WP_Error('invalid_urls', 'Supply between 1 and 1500 public URLs.', ['status'=>400]);
            foreach ($urls as $url) if (!is_string($url) || !odpc_gsc_allowed_url($url)) return new WP_Error('invalid_url', 'A URL is outside the managed public hosts.', ['status'=>400]);
            $maps = $request->get_param('sitemaps') ?: [];
            $allowed_maps = ['https://odpc.de/sitemap_index.xml','https://ai.odpc.de/wp-sitemap.xml','https://tante-lenchen.odpc.de/wp-sitemap.xml','https://atrium191-rb.odpc.de/sitemap.xml','https://bahnhof-grenzau.odpc.de/sitemap.xml','https://meinkraut.odpc.de/sitemap.xml','https://ra-justen.de/wp-sitemap.xml'];
            if (!is_array($maps) || array_diff($maps, $allowed_maps)) return new WP_Error('invalid_sitemap', 'Unsupported sitemap.', ['status'=>400]);
            try { $service = odpc_gsc_service(); $service->sites->listSites(); } catch (Throwable $e) { return new WP_Error('google_authorization_unavailable', 'Existing Site Kit authorization could not access Search Console.', ['status'=>403]); }
            $urls = array_values(array_unique($urls));
            $job = ['state'=>'running','user_id'=>get_current_user_id(),'started'=>gmdate('c'),'updated'=>gmdate('c'),'total'=>count($urls),'cursor'=>0,'urls'=>$urls,'results'=>[],'summary'=>[],'sitemaps'=>[],'pending_sitemaps'=>array_values(array_unique($maps))];
            update_option('odpc_gsc_oneoff_audit', $job, false);
            wp_schedule_single_event(time()+1, 'odpc_gsc_oneoff_step');
            return odpc_gsc_job_view($job);
        }],
    ]);
});
add_action('odpc_gsc_oneoff_step', function () {
    $lock_key='odpc_gsc_oneoff_lock';
    if (!add_option($lock_key, time(), '', false)) {
        if ((int)get_option($lock_key)>time()-120) return;
        delete_option($lock_key);
        if (!add_option($lock_key,time(),'',false)) return;
    }
    $job=get_option('odpc_gsc_oneoff_audit', []); $previous=get_current_user_id();
    try {
        if (($job['state'] ?? '')!=='running') return;
        if (!user_can($job['user_id'], 'manage_options')) throw new RuntimeException('Original administrator authorization no longer available');
        wp_set_current_user($job['user_id']); $service=odpc_gsc_service(); $started=microtime(true); $processed=0;
        while ($job['pending_sitemaps'] && microtime(true)-$started<18) {
            $map=array_shift($job['pending_sitemaps']);
            $check=wp_remote_get($map,['timeout'=>10,'redirection'=>0,'limit_response_size'=>800000]);
            if (is_wp_error($check) || wp_remote_retrieve_response_code($check)!==200 || !preg_match('~<(?:sitemapindex|urlset)\b~',wp_remote_retrieve_body($check))) { $job['sitemaps'][$map]=['status'=>'not_submitted','reason'=>'Sitemap did not return XML HTTP 200']; continue; }
            try { $service->sitemaps->submit(odpc_gsc_property($map),$map); $job['sitemaps'][$map]=['status'=>'submitted','at'=>gmdate('c')]; }
            catch (Throwable $e) { $job['sitemaps'][$map]=['status'=>'error','error_code'=>(int)$e->getCode()]; }
            update_option('odpc_gsc_oneoff_audit',$job,false);
        }
        while ($job['cursor']<$job['total'] && $processed<30 && microtime(true)-$started<18) {
            $url=$job['urls'][$job['cursor']];
            try {
                $request=new \Google\Site_Kit_Dependencies\Google\Service\SearchConsole\InspectUrlIndexRequest(['inspectionUrl'=>$url,'siteUrl'=>odpc_gsc_property($url),'languageCode'=>'de-DE']);
                $result=$service->urlInspection_index->inspect($request)->getInspectionResult();
                $r=$result ? $result->getIndexStatusResult() : null;
                $row=['url'=>$url,'checked_at'=>gmdate('c'),'verdict'=>$r ? $r->getVerdict() : null,'coverage'=>$r ? $r->getCoverageState() : null,'indexing_state'=>$r ? $r->getIndexingState() : null,'last_crawl'=>$r ? $r->getLastCrawlTime() : null,'google_canonical'=>$r ? $r->getGoogleCanonical() : null,'user_canonical'=>$r ? $r->getUserCanonical() : null];
            } catch (Throwable $e) {
                $row=['url'=>$url,'checked_at'=>gmdate('c'),'verdict'=>'ERROR','error_code'=>(int)$e->getCode()];
                if (in_array((int)$e->getCode(),[401,403,429],true)) { $job['state']='blocked'; $job['error_code']=(int)$e->getCode(); }
            }
            $job['results'][]=$row; $job['cursor']++; $processed++;
            $key=$row['verdict'] ?: 'UNKNOWN'; $job['summary'][$key]=($job['summary'][$key] ?? 0)+1;
            update_option('odpc_gsc_oneoff_audit',$job,false);
            if ($job['state']!=='running') break;
            usleep(150000);
        }
        if ($job['cursor']===$job['total'] && !$job['pending_sitemaps']) { $job['state']='completed'; $job['finished']=gmdate('c'); }
        $job['updated']=gmdate('c'); update_option('odpc_gsc_oneoff_audit',$job,false);
        if ($job['state']==='running') wp_schedule_single_event(time()+2,'odpc_gsc_oneoff_step');
    } catch (Throwable $e) {
        if ($job) { $job['state']='blocked'; $job['error_code']=(int)$e->getCode(); $job['error_type']=get_class($e); $job['updated']=gmdate('c'); update_option('odpc_gsc_oneoff_audit',$job,false); }
    } finally { wp_set_current_user($previous); delete_option($lock_key); }
});
