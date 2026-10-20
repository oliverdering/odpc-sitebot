import {chromium} from 'playwright';
import {mkdir,writeFile} from 'node:fs/promises';
import assert from 'node:assert/strict';
const out='qa-helpermonkey-experience';await mkdir(out,{recursive:true});
const browser=await chromium.launch({args:['--autoplay-policy=no-user-gesture-required']});
const report={time:new Date().toISOString(),checks:[],limitations:['No physical microphone recording or acoustic listening test on Samsung S24 Ultra or iPhone.','Account authorization and account-reading tests require the visitor’s own session.','Synthetic recognition test verifies handler and dialog; it does not verify the browser vendor recognition service.']};
function pass(name,details){report.checks.push({name,status:'pass',...details});console.log('PASS',name)}
try{
for(const [name,viewport]of [['desktop',{width:1440,height:1000}],['mobile',{width:390,height:844}]]){
 const context=await browser.newContext({viewport});const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 const start=Date.now();const res=await page.goto('https://ai.odpc.de/?hm_qa='+Date.now(),{waitUntil:'networkidle'});assert.equal(res.status(),200);assert.equal(await page.locator('h1').count(),1);
 assert.equal(await page.locator('.hm-header img').evaluate(e=>e.complete&&e.naturalWidth>0),true);
 assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),true);
 await page.screenshot({path:out+'/'+name+'-home.png',fullPage:true});pass(name+' homepage layout',{viewport,loadToNetworkIdleMs:Date.now()-start});
 await page.getByRole('button',{name:'Mit Monkey sprechen',exact:false}).first().click();await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-audio').uncheck();await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-stop').click();
 async function answer(t){await page.locator('#odpc-voice-answer').fill(t);await page.locator('#odpc-voice-send').click()}
 await answer('Ich möchte E-Mails organisieren');await answer('ChatGPT');await answer('Samsung mit Gmail Kalender und Drive');
 assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'setup');
 const help=new URL(await page.locator('#hm-guide-help').getAttribute('href'));assert.match(help.searchParams.get('text'),/Konto offiziell verbinden/);assert(!help.searchParams.get('text').includes('Gmail'));
 assert.equal(await page.locator('.hm-guide-next a').first().getAttribute('href'),'https://chatgpt.com/');
 await answer('Hilfe');assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'setup');
 await answer('verbunden');assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'test');assert.match(await page.locator('#hm-guide-test').inputValue(),/Nichts senden/);
 await answer('Test fehlgeschlagen');assert.equal(await page.locator('#odpc-voice').getAttribute('data-connection-result'),'failed');
 await answer('Test erfolgreich');assert.equal(await page.locator('#odpc-voice').getAttribute('data-connection-result'),'user-reported');
 const helpRect=await page.locator('#hm-guide-help').boundingBox(),inputRect=await page.locator('#odpc-voice-answer').boundingBox();assert(helpRect&&inputRect);assert(helpRect.y>inputRect.y);
 await page.screenshot({path:out+'/'+name+'-connector-test.png',fullPage:true});pass(name+' connector, help and honest test result');
 await page.locator('#odpc-voice-settings summary').click();
 for(const [scenario,answers,expected]of [['beginner',['KI einrichten','noch keine','ChatGPT','Android'],'test'],['business',['Firma möchte Dokumente mit Claude auf Windows und Drive bearbeiten'],'setup'],['website',['WordPress und SEO mit Gemini auf Android'],'setup']]){await page.locator('#odpc-voice-reset').click();for(const t of answers)await answer(t);assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),expected,scenario);pass(name+' '+scenario+' flow')}
 await page.locator('#odpc-voice-settings summary').click();
 await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-reset').click();await answer('Passwort: private-secret');assert.equal((await page.locator('#odpc-voice-log').innerText()).includes('private-secret'),false);assert.equal(await page.evaluate(()=>sessionStorage.getItem('hm-guide-v1').includes('private-secret')),false);
 await page.locator('#odpc-voice-topclose').click();await page.goto('https://ai.odpc.de/start/',{waitUntil:'networkidle'});assert.equal(await page.locator('.hm-agent').count(),10);assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),true);await page.screenshot({path:out+'/'+name+'-agents.png',fullPage:true});
 assert.equal(errors.length,0,JSON.stringify(errors));pass(name+' agent overview and no page errors');await context.close();
}
const audioContext=await browser.newContext();const audio=await audioContext.newPage();await audio.goto('https://ai.odpc.de/connector-einrichten/',{waitUntil:'networkidle'});await audio.locator('#odpc-voice-open').click();
assert.equal(await audio.locator('#odpc-voice-gender').inputValue(),'female');
const deviceVoices=await audio.evaluate(()=>speechSynthesis.getVoices().map(v=>({name:v.name,lang:v.lang})));
if(!deviceVoices.length){
 await audio.waitForFunction(()=>document.querySelector('#odpc-voice').dataset.audioState==='voice-unavailable');
 assert.equal(await audio.locator('#odpc-voice').getAttribute('data-audio-engine'),null);
 pass('empty device catalog produces honest message, no eSpeak playback');
 report.limitations.push('This Chromium runner offers no device voices. Natural voice audio cannot be acoustically tested here.');
}else{
 await audio.locator('#odpc-voice-settings summary').click();
 assert((await audio.locator('#odpc-voice-profile option').count())>1);
 await audio.locator('#odpc-voice-preview').click();
 await audio.waitForFunction(()=>['playing','ended','error'].includes(document.querySelector('#odpc-voice').dataset.audioState));
 const data=await audio.locator('#odpc-voice').evaluate(e=>({...e.dataset}));assert.notEqual(data.audioState,'error');assert.equal(data.audioEngine,'device');pass('available device voice starts',{voice:data.audioVoice,state:data.audioState});
}
assert.equal(await audio.locator('#odpc-voice-preview').isDisabled(),!deviceVoices.length);
await audio.screenshot({path:out+'/device-voice-status.png',fullPage:true});await audioContext.close();
const syntheticVoiceContext=await browser.newContext();await syntheticVoiceContext.addInitScript(()=>{
 const profiles=[{name:'Microsoft Hedda',lang:'de-DE',voiceURI:'Hedda'},{name:'Microsoft Katja Online (Natural)',lang:'de-DE',voiceURI:'Katja'}];
 const fake={getVoices:()=>profiles,cancel(){},addEventListener(){},speak(u){window.__testSpeech={name:u.voice.name,rate:u.rate,pitch:u.pitch};queueMicrotask(()=>{u.onstart?.();u.onend?.()})}};
 Object.defineProperty(window,'speechSynthesis',{value:fake,configurable:true});
 window.SpeechSynthesisUtterance=class{constructor(text){this.text=text}};
});
const syntheticVoice=await syntheticVoiceContext.newPage();await syntheticVoice.goto('https://ai.odpc.de/',{waitUntil:'networkidle'});await syntheticVoice.locator('#odpc-voice-open').click();await syntheticVoice.waitForFunction(()=>window.__testSpeech?.name==='Microsoft Katja Online (Natural)');
const selected=await syntheticVoice.evaluate(()=>window.__testSpeech);assert.equal(selected.rate,1.12);assert.equal(selected.pitch,1);pass('synthetic device catalog selects natural female at updated speed',{voice:selected.name,synthetic:true});await syntheticVoiceContext.close();
const micContext=await browser.newContext();await micContext.addInitScript(()=>{window.SpeechRecognition=class{start(){queueMicrotask(()=>this.onresult({results:[[{transcript:'Gmail ChatGPT Samsung'}]]}))}abort(){}}});const mic=await micContext.newPage();await mic.goto('https://ai.odpc.de/',{waitUntil:'networkidle'});await mic.locator('#odpc-voice-open').click();await mic.locator('#odpc-voice-settings summary').click();await mic.locator('#odpc-voice-audio').uncheck();await mic.locator('#odpc-voice-mic').click();await mic.waitForFunction(()=>document.querySelector('#odpc-voice').dataset.phase==='setup');pass('synthetic speech recognition result advances dialog');await micContext.close();
}finally{await writeFile(out+'/report.json',JSON.stringify(report,null,2));await browser.close()}
