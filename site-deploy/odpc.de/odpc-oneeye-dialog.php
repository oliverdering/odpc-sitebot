<?php
/**
 * Plugin Name: ODPC one.eye dialogue enhancement
 */
defined('ABSPATH') || exit;
add_action('wp_footer', static function () {
 if (!is_page(3773)) return;
 ?>
 <style>
 #oneeye-dialog summary:focus-visible {outline:3px solid #315b48;outline-offset:3px}
 #oneeye-dialog details[open] {border-color:#315b48!important;box-shadow:0 0 0 2px #315b48}
 #oneeye-dialog .oneeye-choice {background:#fff;border-left:4px solid #315b48;padding:16px;margin:16px 0;border-radius:8px}
 #oneeye-dialog [dir=rtl]{text-align:right}
 </style>
 <script>
 (()=>{const root=document.getElementById('oneeye-dialog');if(!root)return;
 const faith=[...root.querySelectorAll('details[name="oneeye-faith"]')], languages=[...root.querySelectorAll('details[name="oneeye-language"]')];
 if(!faith.length||!languages.length)return;
 const panel=document.createElement('div');panel.className='oneeye-choice';panel.id='oneeye-combined-greeting';panel.setAttribute('aria-live','polite');
 const heading=document.createElement('strong'), body=document.createElement('p');panel.append(heading,body);
 root.querySelector('h2').after(panel);
 let f=null,l=languages[0];
 function render(){const p=l.querySelector('p');heading.textContent=f?f.querySelector('summary').textContent.trim():'one.eye';body.textContent=p.textContent;body.lang=p.lang;body.dir=p.dir||'ltr';}
 faith.forEach(d=>d.addEventListener('toggle',()=>{if(d.open){f=d;render();}}));
 languages.forEach(d=>d.addEventListener('toggle',()=>{if(d.open){l=d;render();}}));
 render();
 })();
 </script>
 <?php
}, 99);
