#!/usr/bin/env node
// ODPC YourBusiness — browser-level release acceptance, without touching the live database.
import { chromium } from 'playwright';
import { mkdir, writeFile } from 'node:fs/promises';

const BASE = 'https://yourbusiness.odpc.de/';
const out = process.env.QA_OUT || 'qa-artifacts/yourbusiness';
const failures=[], report=[];
await mkdir(out,{recursive:true});
const browser = await chromium.launch({headless:true,args:['--no-sandbox']});
const routes = [
 {name:'kleine-freiheit-classic',query:'?yb_kleine_freiheit=1&design=klassisch',root:'#kf-demo',heading:'Kleine Freiheit'},
 {name:'kleine-freiheit-modern',query:'?yb_kleine_freiheit=1&design=modern',root:'#kf-demo',heading:'Kleine Freiheit'},
 {name:'emins-grill',query:'?yb_emins_grill=1',root:'#em-demo',heading:"Emin"},
 {name:'doener-paradies',query:'?yb_doener_paradies=1',root:'#dp-demo',heading:'Döner Paradies'},
 {name:'kunden-showcase',query:'?yb_showcase=1',root:'#showcase-grid',heading:'Kleine Freiheit'}
];
try {
 for (const route of routes) {
  const context = await browser.newContext({viewport:{width:390,height:844},deviceScaleFactor:1,isMobile:true,hasTouch:true,locale:'de-DE',reducedMotion:'reduce'});
  const page=await context.newPage();
  const errors=[];
  page.on('pageerror',e=>errors.push(e.message));
  const r={name:route.name,url:BASE+route.query,pass:true,errors:[],brokenImages:[],scrollOverflow:false,actions:[]};
  try {
   const response=await page.goto(r.url,{waitUntil:'domcontentloaded',timeout:45000});
   if (response?.status()!==200) throw new Error('HTTP '+response?.status());
   await page.locator(route.root).first().waitFor({timeout:15000});
   await page.locator('body').getByText(route.heading,{exact:false}).first().waitFor({timeout:10000});
   await page.screenshot({path:out+'/'+route.name+'-mobile.png',fullPage:true,animations:'disabled'});
   r.scrollOverflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+6);
   if (r.scrollOverflow)r.errors.push('Horizontal overflow on 390px mobile');
   if (route.name.startsWith('kleine-freiheit')) {
    const expected = route.name.endsWith('modern')?'modern':'klassisch';
    const mode=await page.locator('#kf-demo').getAttribute('data-kf-mode');
    if(mode!==expected)r.errors.push('Wrong theme: '+mode);
    if(await page.locator('#kf-bilder img').count()<2)r.errors.push('Missing local photo gallery');
    if(await page.locator('#kf-local-photo-input').count()!==1)r.errors.push('Missing local photo picker');
    r.actions.push('theme query: '+mode);
   }
   if (route.name==='emins-grill'||route.name==='doener-paradies'){
    const pre=route.name==='emins-grill'?'em':'dp';
    await page.locator('#'+pre+'-modern').click();
    if(!await page.locator('#'+pre+'-demo').evaluate(e=>e.classList.contains('modern')))r.errors.push('Modern theme toggle failed');
    await page.locator('#'+pre+'-classic').click();
    if(await page.locator('#'+pre+'-demo').evaluate(e=>e.classList.contains('modern')))r.errors.push('Classic theme toggle failed');
    await page.locator('#'+pre+'-lang').selectOption('tr');
    if(await page.locator('#'+pre+'-demo').locator('[data-t="home"]').first().textContent()!=='Ana sayfa')r.errors.push('Turkish translation did not render');
    await page.locator('#'+pre+'-lang').selectOption('de');
    if(await page.locator('#'+pre+'-pictures').count()!==1)r.errors.push('Missing photo picker');
    r.actions.push('classic/modern, Turkish language, photo control');
    if(pre==='dp'&&!await page.getByText('Drehspieß-Sandwich',{exact:false}).count())r.errors.push('Missing verified menu snapshot section');
   }
   if(route.name==='kunden-showcase'){
    const links=await page.locator('#showcase-grid a').count();
    if(links!==3)r.errors.push('Expected exactly three customer links, found '+links);
    r.actions.push('three customer links: '+links);
   }
   // Force lazy images on screen for actual browser availability validation.
   const loaded=await page.evaluate(async()=>{
    const imgs=Array.from(document.images);
    await Promise.all(imgs.map(img=>new Promise(resolve=>{
      img.loading='eager';
      if(img.complete){resolve();return;}
      const t=setTimeout(resolve,15000);
      img.addEventListener('load',()=>{clearTimeout(t);resolve()},{once:true});
      img.addEventListener('error',()=>{clearTimeout(t);resolve()},{once:true});
    })));
    return imgs.map(img=>({src:img.currentSrc||img.src,loaded:img.complete&&img.naturalWidth>0,width:img.naturalWidth}));
   });
   r.brokenImages=loaded.filter(img=>!img.loaded);
   if(r.brokenImages.length)r.errors.push(r.brokenImages.length+' broken images');
   r.errors.push(...errors);
   if (r.errors.length)r.pass=false;
  }catch(e){r.pass=false;r.errors.push(e.message||String(e))}
  report.push(r);
  console.log((r.pass?'PASS':'FAIL')+' '+r.name+' '+JSON.stringify({errors:r.errors,broken:r.brokenImages,actions:r.actions}));
  if(!r.pass)failures.push(r);
  await context.close();
 }
}finally {await browser.close();}
await writeFile(out+'/report.json',JSON.stringify({checked:new Date().toISOString(),results:report},null,2));
if(failures.length){console.error('YOURBUSINESS_BROWSER_QA_FAILED',failures.length);process.exitCode=1}
else console.log('YOURBUSINESS_BROWSER_QA_PASSED_ALL_FIVE');
