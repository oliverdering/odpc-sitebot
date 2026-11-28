// Public, unauthenticated end-to-end checks. Run on releases and manual refreshes.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { chromium, webkit } = require('playwright');
const dir = process.env.RUNNER_TEMP + '/ki-media-acceptance';
fs.mkdirSync(dir, {recursive:true});
const url = 'https://odpc.de/ki-mediathek';
async function browse(browser, name, width, height) {
  const page = await browser.newPage({viewport:{width,height}, locale:'de-DE', isMobile:width<900, hasTouch:width<900});
  page.setDefaultTimeout(10000);
  const errors = [];
  page.on('pageerror', error => errors.push(String(error)));
  await page.goto(url, {waitUntil:'domcontentloaded', timeout:60000});
  const guide = page.locator('#odpc-ki-guide');
  const form = guide.locator('.kg-filters');
  await form.waitFor();
  const banner = guide.locator('#kg-banner .odpc-media-track');
  await banner.waitFor();
  assert.equal(await page.locator('.odpc-media-track').count(),1);
  const links = await banner.locator('a').evaluateAll(anchors=>anchors.map(a=>a.href));
  assert(links.length>0);
  const style = await banner.evaluate(e=>[getComputedStyle(e).animationName,getComputedStyle(e).animationDuration]);
  assert.notEqual(style[0],'none');
  const pause = guide.locator('.odpc-media-pause');
  await pause.click();
  assert.equal(await pause.getAttribute('aria-pressed'),'true');
  assert.equal(await banner.evaluate(e=>getComputedStyle(e).animationPlayState),'paused');
  await pause.click();
  assert.equal(await pause.getAttribute('aria-pressed'),'false');
  await page.emulateMedia({reducedMotion:'reduce'});
  assert.equal(await banner.evaluate(e=>getComputedStyle(e).animationName),'none');
  await page.emulateMedia({reducedMotion:'no-preference'});
  const count = () => guide.locator('[data-media-kind]:visible').count();
  await form.locator('[name=q]').fill('kein-treffer-77882211');
  assert.equal(await count(),0);
  const reset = async () => { await form.locator('[type=reset]').click(); await page.waitForFunction(() => !document.querySelector('.kg-card[data-media-kind=now]').hidden); };
  await reset();
  const sender=form.locator('[name=sender]');
  await sender.selectOption({index:1});
  assert(await count()>0);
  assert.deepEqual(await guide.locator('[data-media-kind]:visible').evaluateAll(nodes=>[...new Set(nodes.map(n=>n.dataset.station))]),[await sender.inputValue()]);
  await reset();
  await form.locator('[name=tag]').selectOption('Nachrichten');
  assert(await count()>0);
  await reset();
  await form.locator('[name=kind]').selectOption('future');
  assert.equal(await guide.locator('[data-media-kind=now]:visible').count(),0);
  await reset();
  for(const name of ['date','published']){
    await form.locator('[name='+name+']').fill('2099-01-01');
    assert.equal(await count(),0);
    await reset();
  }
  const before=await count();
  if(await guide.locator('.kg-more').isVisible()){
    await guide.locator('.kg-more').click();assert(await count()>before);
  }
  const switcher=page.locator('#odpc-appearance-switch');
  const green=()=>page.locator('html').getAttribute('data-odpc-appearance');
  if(await green()==='green')await switcher.click();
  const color=()=>guide.locator('.kg-btn').first().evaluate(e=>getComputedStyle(e).backgroundColor);
  const blue=await color();
  await switcher.click();
  assert.equal(await green(),'green');
  assert.notEqual(await color(),blue);
  const overflow=await page.evaluate(()=>({page:document.documentElement.scrollWidth-document.documentElement.clientWidth,guide:document.querySelector('#odpc-ki-guide').scrollWidth-document.querySelector('#odpc-ki-guide').clientWidth}));
  console.log(name,'overflow',overflow);
  assert(overflow.guide<=1 && overflow.page<=1);
  await guide.locator('h1').scrollIntoViewIfNeeded();
  await page.screenshot({path:dir+'/'+name+'-green.png'});
  await switcher.click();
  await guide.locator('h1').scrollIntoViewIfNeeded();
  await page.screenshot({path:dir+'/'+name+'-blue.png'});
  const logos = await guide.locator('.kg-station').evaluateAll(async cards => Promise.all(cards.map(async card => {
    const img = card.querySelector('.kg-logo img');
    if (!img || new URL(img.src).origin !== location.origin) return false;
    const probe = new Image(); probe.src = img.src;
    try { await probe.decode();return probe.naturalWidth > 0 && probe.naturalHeight > 0; }
    catch { return false; }
  })));
  assert(logos.length>0 && logos.every(Boolean),'missing station logo');
  assert.equal(await banner.locator('a img').count(),links.length);
  console.log(name,'loaded station logos',logos.length,'ticker logos',links.length);
  const values=await guide.locator('.kg-stats').innerText();
  console.log(name, values.replace(/\s+/g,' '), 'ticker',style,'station cards',await guide.locator('.kg-station').count(),'page errors',errors);
  assert.equal(errors.length,0);
  if(name==='desktop'){
    await page.goto('https://odpc.de/',{waitUntil:'domcontentloaded',timeout:60000});
    const original=page.locator('section.odpc-media .odpc-media-track');
    await original.waitFor();
    assert.deepEqual(await original.locator('a').evaluateAll(nodes=>nodes.map(a=>a.href)),links);
    assert.deepEqual(await original.evaluate(e=>[getComputedStyle(e).animationName,getComputedStyle(e).animationDuration]),style);
    assert.equal(await original.evaluate(e=>e.closest('section').nextElementSibling?.matches('#colophon,footer.site-footer')),true);
  }
  await page.close();
}
(async()=>{
  const failures=[];
  for(const [engine,name,width,height] of [[chromium,'desktop',1366,900],[chromium,'android',360,800],[webkit,'iphone',390,844],[webkit,'tablet',820,1180]]){
    const browser=await engine.launch(engine===chromium?{channel:'chrome'}:{});
    try { await browse(browser,name,width,height); }
    catch(error){console.error(name,error);failures.push(name);}
    finally{await browser.close();}
  }
  assert.equal(failures.length,0,failures.join(', '));
})().catch(error=>{console.error(error);process.exitCode=1});
