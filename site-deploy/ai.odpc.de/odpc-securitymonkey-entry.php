<?php
/**
 * Plugin Name: ODPC Security Monkey – Einstieg
 * Description: Verlinkt den Security-Monkey-Selbstcheck und persönliche Unterstützung.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;
add_action('wp_footer', function () {
 if (is_admin() || !(is_front_page() || is_page('start'))) return;
 ?>
 <section id="odpc-security-monkey-entry" aria-labelledby="odpc-security-monkey-title">
  <style>
  #odpc-security-monkey-entry{max-width:1100px;margin:36px auto;padding:28px;border:1px solid #a9cdbb;border-radius:18px;background:#eff8f2;color:#173c2a;font:16px/1.6 system-ui,sans-serif}
  #odpc-security-monkey-entry h2{color:#173c2a;margin:0 0 12px;font-size:clamp(25px,4vw,36px)}
  #odpc-security-monkey-entry p{margin:10px 0}
  #odpc-security-monkey-entry img{width:70px;height:70px;object-fit:contain;float:right;margin:0 0 12px 16px}
  #odpc-security-monkey-entry a{display:inline-block;margin:6px 12px 6px 0;padding:11px 14px;background:#175f3d;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;min-height:44px;box-sizing:border-box}
  #odpc-security-monkey-entry a:focus-visible{outline:3px solid #ac7811;outline-offset:3px}
  @media(max-width:600px){#odpc-security-monkey-entry{margin:24px 16px;padding:22px}#odpc-security-monkey-entry a{display:block}}
  </style>
  <img src="https://ai.odpc.de/wp-content/themes/helpermonkey-business-v3/assets/brand/helper-monkey-symbol.png" alt="helper monkey Originalsymbol" loading="lazy" width="70" height="70">
  <h2 id="odpc-security-monkey-title">🐒🔐 Security Monkey</h2>
  <p>Prüfe wichtige Grundlagen deiner IT-Sicherheit Schritt für Schritt – verständlich erklärt und ohne eigene IT-Abteilung. Der erste Selbstcheck wertet deine Angaben aus und führt keine technischen Tests durch.</p>
  <p>Du möchtest Unterstützung? Oliver hilft dir per Fernwartung oder nach Vereinbarung vor Ort. Für eine umfassende Prüfung und Einrichtung erhältst du ein individuelles Angebot.</p>
  <a href="https://ai.odpc.de/securitymonkey/">Selbstcheck starten</a>
  <a href="https://odpc.de/remote-fernwartung/">Hilfe per Fernwartung</a>
  <a href="https://wa.me/492654882183?text=Hallo%20Oliver%2C%20ich%20interessiere%20mich%20f%C3%BCr%20den%20Security%20Monkey%20und%20pers%C3%B6nliche%20Unterst%C3%BCtzung.">Persönliche Unterstützung anfragen</a>
 </section>
 <?php
}, 100);
