const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: '/usr/bin/google-chrome', args: ['--no-sandbox'] });
  try {
    for (const width of [390, 768, 1365]) {
      const page = await browser.newPage({viewport:{width,height:900},deviceScaleFactor:1});
      await page.goto('https://odpc.de/?odpc_qa=' + Date.now(),{waitUntil:'domcontentloaded',timeout:50000});
      await page.waitForSelector('#odpc-navigation .odpc-nav-toggle',{timeout:15000});
      const before=await page.evaluate(()=>({
        outer:document.documentElement.scrollWidth,
        inner:window.innerWidth,
        menuVisible:!document.getElementById('odpc-nav-panel').hidden,
        toggleVisible:!!document.querySelector('#odpc-navigation .odpc-nav-toggle')?.getClientRects().length,
        oldMenuDisplay:getComputedStyle(document.querySelector('#masthead .ast-primary-header-bar')).display,
        headerBackground:getComputedStyle(document.getElementById('masthead')).backgroundColor,
        headerImagePresent:!!document.querySelector('#masthead .custom-logo'),
        navListCount:document.querySelectorAll('#odpc-nav-panel .odpc-menu-list a').length
      }));
      console.log('CLOSED '+width+' '+JSON.stringify(before));
      if(before.outer>width+4)throw Error('Horizontal overflow at width '+width+' : '+before.outer);
      if(before.menuVisible||!before.toggleVisible||before.navListCount<10||before.oldMenuDisplay!=='none')throw Error('Header state incorrect at '+width);
      if(width===390){await page.screenshot({path:'odpc-screenshots/mobile-closed.png',fullPage:false});}
      await page.locator('#odpc-navigation .odpc-nav-toggle').click();
      const expanded=await page.locator('#odpc-navigation .odpc-nav-toggle').getAttribute('aria-expanded');
      const panelVisible=await page.locator('#odpc-nav-panel').isVisible();
      console.log('OPEN '+width+' expanded='+expanded+' panelVisible='+panelVisible);
      if(expanded!=='true'||!panelVisible)throw Error('Menu fails to open '+width);
      if(width===390){await page.screenshot({path:'odpc-screenshots/mobile-open.png',fullPage:false});}
      await page.keyboard.press('Escape');
      if(await page.locator('#odpc-nav-panel').isVisible())throw Error('Escape close fails '+width);
      if(width===1365){await page.screenshot({path:'odpc-screenshots/desktop-closed.png',fullPage:false});}
      await page.close();
    }
    console.log('ODPC RESPONSIVE NAV QA PASS');
  } finally {await browser.close();}
})().catch(e=>{console.error('QA FAIL:',e);process.exit(1)});
