import assert from 'node:assert/strict';
export async function verifyScrolling(page,context,name,viewport,out,pass){
 const panel=page.locator('#odpc-voice');
 async function positionInline(){await panel.evaluate(el=>el.scrollIntoView({block:'start',behavior:'instant'}));await page.waitForFunction(()=>{const r=document.querySelector('#odpc-voice').getBoundingClientRect();return r.top>=0&&r.top<innerHeight-120})}
 await positionInline();let box=await panel.boundingBox();let before=await page.evaluate(()=>scrollY);
 await page.mouse.move(box.x+box.width/2,Math.min(viewport.height-130,box.y+70));await page.mouse.wheel(0,220);await page.waitForFunction(y=>scrollY>y+80,before);
 assert.equal(await panel.evaluate(e=>getComputedStyle(e).overflowY),'visible');assert.equal(await panel.evaluate(e=>getComputedStyle(e).overscrollBehaviorY),'auto');
 before=await page.evaluate(()=>scrollY);await page.mouse.wheel(0,-160);await page.waitForFunction(y=>scrollY<y-60,before);pass(name+' inline Connector allows page scrolling in both directions');
 if(name==='mobile'){
  await positionInline();box=await panel.boundingBox();before=await page.evaluate(()=>scrollY);
  const client=await context.newCDPSession(page);await client.send('Input.synthesizeScrollGesture',{x:box.x+box.width/2,y:Math.min(viewport.height-150,box.y+100),yDistance:-190,gestureSourceType:'touch',speed:500});await page.waitForFunction(y=>scrollY>y+70,before);await client.detach();pass('mobile emulated touch gesture scrolls the page over the Connector');
 }
 const saved=await page.evaluate(()=>scrollY),savedBodyPosition=await page.locator('body').evaluate(e=>getComputedStyle(e).position);await page.locator('#odpc-voice-open').click();assert.equal(await panel.getAttribute('role'),'dialog');assert.equal(await page.locator('body').evaluate(e=>getComputedStyle(e).position),'fixed');assert(Math.abs((await page.locator('body').evaluate(e=>parseFloat(getComputedStyle(e).top)))+saved)<2);
 await page.locator('#odpc-voice-settings summary').click();await page.mouse.move(viewport.width/2,120);await page.mouse.wheel(0,650);await page.waitForFunction(()=>document.querySelector('#odpc-voice').scrollTop>25);assert.equal(await page.evaluate(()=>scrollY),0);
 let close=await page.locator('#odpc-voice-topclose').boundingBox();assert(close.y>=0&&close.y+close.height<=viewport.height,'close remains visible while scrolling');
 const latest=await page.locator('#odpc-voice-log').evaluate(e=>({overflow:getComputedStyle(e).overflowY,range:e.scrollHeight-e.clientHeight}));assert.equal(latest.overflow,'visible');assert(latest.range<=1,'latest message has no nested scroll range');
 await page.locator('[data-choice="E-Mails organisieren"]').click();await page.waitForFunction(()=>document.querySelector('#odpc-voice').scrollTop===0);assert.equal(await panel.getAttribute('data-phase'),'platform');assert.equal(await page.locator('#hm-guide-history').getAttribute('open'),null);pass(name+' modal scroll stays inside the window and each next step starts at the top');
 await page.setViewportSize({width:viewport.width,height:500});await page.waitForFunction(()=>{const r=document.querySelector('#odpc-voice').getBoundingClientRect();return r.top>=0&&r.bottom<=(visualViewport?.height||innerHeight)+1});
 await page.locator('#odpc-voice-answer').focus();await page.waitForFunction(()=>{const r=document.querySelector('#odpc-voice-answer').getBoundingClientRect();return r.top>=0&&r.bottom<=(visualViewport?.height||innerHeight)});
 close=await page.locator('#odpc-voice-topclose').boundingBox();assert(close.y>=0&&close.y+close.height<=500);await page.screenshot({path:out+'/'+name+'-small-viewport-scroll.png'});
 await page.setViewportSize(viewport);await page.waitForFunction(h=>Math.abs((visualViewport?.height||innerHeight)-h)<2,viewport.height);
 await page.locator('#odpc-voice-reset').click();await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-topclose').click();await page.waitForFunction(y=>Math.abs(scrollY-y)<2,saved);assert.equal(await page.locator('body').evaluate(e=>getComputedStyle(e).position),savedBodyPosition);pass(name+' smaller viewport keeps input and close usable; closing restores the page position');
 await page.evaluate(()=>window.scrollTo({top:0,behavior:'instant'}));await page.waitForFunction(()=>scrollY===0);
}
