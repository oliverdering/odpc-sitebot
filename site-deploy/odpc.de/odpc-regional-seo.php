<?php
/**
 * Plugin Name: ODPC Regional SEO
 * Description: Regional service metadata and structured service information; no visual changes.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;
function odpc_regional_services() {
    return [
      'it-dienstleistungen-ki-integration' => [
        'IT-Support & KI-Integration für Unternehmen | ODPC Koblenz, Mayen',
        'IT-Support, Windows Server, Netzwerke, Automatisierung und KI-Integration für Unternehmen in Koblenz, Mayen, Polch und dem Westerwald. ODPC persönlich kontaktieren.',
        'IT-Support und Systembetreuung für Unternehmen'
      ],
      'webdesign' => [
        'WordPress Webdesign für Unternehmen | ODPC Koblenz & Mayen',
        'Professionelle WordPress-Websites, technische SEO und laufende Betreuung für Unternehmen in Koblenz, Mayen, Polch und dem Westerwald.',
        'WordPress-Webdesign und technische SEO'
      ],
      'initiq' => [
        'initIQ – KI-Einführung für Unternehmen | ODPC Koblenz',
        'KI-Einführung und Prozessautomatisierung für Unternehmen im Raum Koblenz, Mayen und Westerwald: praxisnahe Analyse, Integration und Begleitung.',
        'KI-Einführung und Prozessautomatisierung'
      ]
    ];
}
function odpc_regional_current() {
    if (is_admin() || !is_singular('page')) return null;
    $post = get_queried_object();
    if (!$post || empty($post->post_name)) return null;
    $services = odpc_regional_services();
    return $services[$post->post_name] ?? null;
}
// Rank Math remains the single owner of canonical and other metadata.
add_filter('rank_math/frontend/title', function($title) {
    $service=odpc_regional_current();
    return $service ? $service[0] : $title;
}, 40);
add_filter('rank_math/frontend/description', function($description) {
    $service=odpc_regional_current();
    return $service ? $service[1] : $description;
}, 40);
add_action('wp_head', function() {
    $service=odpc_regional_current();
    if (!$service) return;
    $url=get_permalink();
    if (!$url) return;
    // Service schema deliberately omits unverified opening hours, ratings and street address.
    $schema=[
      '@context'=>'https://schema.org',
      '@type'=>'Service',
      '@id'=>trailingslashit($url).'#service',
      'name'=>$service[2],
      'description'=>$service[1],
      'url'=>$url,
      'serviceType'=>$service[2],
      'provider'=>[
        '@type'=>'Organization',
        '@id'=>'https://odpc.de/#organization',
        'name'=>'ODPC – Oliver Dering Professional Consulting',
        'url'=>'https://odpc.de/'
      ],
      'areaServed'=>array_map(static function($place) {
          return ['@type'=>'Place','name'=>$place];
      }, ['Koblenz','Mayen','Polch','Neuwied','Montabaur','Westerwald'])
    ];
    echo '<script type="application/ld+json" id="odpc-regional-service-schema">'.wp_json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG).'</script>'."\n";
}, 30);
