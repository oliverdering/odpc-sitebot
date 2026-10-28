import {chromium} from 'playwright';
import {verifyScrolling} from './scroll-qa.mjs';
import {mkdir,writeFile} from 'node:fs/promises';
import assert from 'node:assert/strict';
const out='qa-helpermonkey-experience';await mkdir(out,{recursive:true});
const browser=await chromium.launch({args:['--autoplay-policy=no-user-gesture-required']});
const report={time:new Date().toISOString(),checks:[],limitations:['No physical microphone recording or acoustic listening test on Samsung S24 Ultra or iPhone.','Account authorization and account-reading tests require the visitor’s own session.','Synthetic recognition test verifies handler and dialog; it does not verify the browser vendor recognition service.']};
function pass(name,details){report.checks.push({name,status:'pass',...details});console.log('PASS',name)}
try{
const linkContext=await browser.newContext();
const pagesResponse=await linkContext.request.get('https://ai.odpc.de/wp-json/wp/v2/pages?per_page=100&status=publish');assert.equal(pagesResponse.status(),200);
const publicPages=await pagesResponse.json();const paths=[...new Set(['https://ai.odpc.de/',...publicPages.map(p=>p.link)])];
const linkPage=await linkContext.newPage();let downloadLinks=0;
for(const url of paths){
 const response=await linkPage.goto(url,{waitUntil:'domcontentloaded'});assert.equal(response.status(),200,url);
 const hrefs=await linkPage.locator('a[href]').evaluateAll(nodes=>nodes.map(a=>a.href));
 const internalLinks=hrefs.filter(h=>{const host=new URL(h).hostname;return host==='github.com'||host.endsWith('.github.com')||host==='githubusercontent.com'||host.endsWith('.githubusercontent.com')});
 assert.deepEqual(internalLinks,[],url+' must not link visitors to repositories or source downloads');
 downloadLinks+=hrefs.filter(h=>h.includes('/odpc-monkey-experience/downloads/')).length;
}
assert(downloadLinks>=9,'preserved language downloads must remain available on our own domain');
pass('all published pages keep repository links internal and downloads on our domain',{pages:paths.length,localDownloadLinks:downloadLinks});await linkContext.close();
for(const [name,viewport]of [['desktop',{width:1440,height:1000}],['mobile',{width:390,height:844}]]){
 const context=await browser.newContext({viewport,isMobile:name==='mobile',hasTouch:name==='mobile'});await context.grantPermissions(['clipboard-read','clipboard-write'],{origin:'https://ai.odpc.de'});await context.addInitScript(()=>{window.__hmMicStarts=0;window.SpeechRecognition=class{start(){window.__hmMicStarts++}abort(){}}});const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 const start=Date.now();const res=await page.goto('https://ai.odpc.de/?hm_qa='+Date.now(),{waitUntil:'networkidle'});assert.equal(res.status(),200);assert.equal(await page.locator('h1').count(),1);
 assert.equal(await page.locator('.hm-header img').evaluate(e=>e.complete&&e.naturalWidth>0),true);
 assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),true);
 await page.screenshot({path:out+'/'+name+'-home.png',fullPage:true});pass(name+' homepage layout',{viewport,loadToNetworkIdleMs:Date.now()-start});
 await page.screenshot({path:out+'/'+name+'-home-viewport.png'});assert.equal(await page.locator('#kosten').count(),1);assert.equal(await page.locator('#praxis').count(),1);assert.match(await page.locator('#smartphone').innerText(),/SAMSUNG GALAXY S24 ULTRA/);
 await page.evaluate(()=>window.scrollTo(0,1000));await page.waitForFunction(()=>window.scrollY>500);
 const sticky=await page.locator('.hm-header').boundingBox(),logo=await page.locator('.hm-header img').boundingBox();assert(sticky.y>=0&&sticky.y<25,name+' header remains pinned');assert(logo.y>=sticky.y&&logo.y+logo.height<=sticky.y+sticky.height+1,name+' logo stays visible');
 await page.screenshot({path:out+'/'+name+'-sticky-header.png'});pass(name+' floating logo header remains visible after scrolling');await page.evaluate(()=>window.scrollTo(0,0));await page.waitForFunction(()=>window.scrollY===0);
 await verifyScrolling(page,context,name,viewport,out,pass);
 await page.getByRole('link',{name:'Connector jetzt einrichten →',exact:true}).click();assert.equal(await page.evaluate(()=>document.activeElement.id),'odpc-voice');assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'dialog');assert.equal(await page.locator('#hm-connector-backdrop').isVisible(),true);await page.locator('#odpc-voice-topclose').click();assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'region');
 await page.locator('.hm-header').getByRole('link',{name:'Connector',exact:true}).click();assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'dialog');await page.locator('#odpc-voice-topclose').click();
 assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'region');assert.equal(await page.locator('#odpc-voice').isVisible(),true);assert.equal(await page.locator('#odpc-voice-audio').isChecked(),false);
 await page.locator('[data-choice="E-Mails organisieren"]').click();await page.locator('[data-choice="ChatGPT"]').click();await page.locator('[data-choice="Android"]').click();assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'service');
 await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'service');await page.locator('[data-choice="Gmail"]').click();
 assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'setup');await page.locator('[data-choice="nicht verfügbar"]').click();assert.equal(await page.locator('#odpc-voice').getAttribute('data-connection-result'),'unavailable');await page.locator('[data-choice="verbunden"]').click();await page.locator('[data-choice="Test erfolgreich"]').click();assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'done');
 assert.equal(await page.evaluate(()=>window.__hmMicStarts),0);assert.equal(await page.locator('#odpc-voice-audio').isChecked(),false);assert.equal(await page.locator('#odpc-voice').getAttribute('data-audio-engine'),null);pass(name+' complete click-only setup works silently without microphone, including resume and unavailable route');
 await page.locator('[data-choice="Aufgabe:mail"]').click();assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work');assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/Gmail/);assert.equal(await page.locator('#hm-guide-work-open').getAttribute('href'),'https://chatgpt.com/');await page.locator('#hm-guide-work-copy').click();if(!(await page.locator('#hm-guide-work-preview').evaluate(e=>e.open)))await page.locator('#hm-guide-work-preview summary').click();await page.waitForFunction(()=>{const e=document.querySelector('#hm-guide-work-prompt');return e.scrollHeight<=e.clientHeight+2});await page.locator('#hm-guide-work-preview summary').click();await page.screenshot({path:out+'/'+name+'-task-handoff.png'});
 await page.locator('[data-choice="andere Aufgabe"]').click();await page.locator('[data-choice="KI einrichten"]').click();assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'service');await page.locator('[data-choice="Google Kalender"]').click();await page.locator('[data-choice="verbunden"]').click();await page.locator('[data-choice="Test erfolgreich"]').click();await page.locator('[data-choice="Aufgabe:calendar"]').click();assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/Gmail, Google Kalender/);await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work');assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/Gmail, Google Kalender/);await page.locator('[data-choice="andere Aufgabe"]').click();pass(name+' setup continues to task selection, copying and AI handoff; multiple services and reload retain context');
 await page.screenshot({path:out+'/'+name+'-click-connector.png'});await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-reset').click();await page.locator('#odpc-voice-settings summary').click();
 await page.locator('#odpc-voice-open').click();assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'dialog');assert.equal(await page.locator('main').evaluate(e=>e.inert),true);await page.locator('[data-choice="E-Mails organisieren"]').click();assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'platform');await page.locator('#odpc-voice-topclose').click();assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'region');assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'platform');assert.equal(await page.locator('main').evaluate(e=>e.inert),false);await page.locator('#odpc-voice-back').click();
 await page.locator('#odpc-voice-open').click();await page.keyboard.press('Escape');assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'region');await page.locator('#odpc-voice-open').click();assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'dialog');await page.screenshot({path:out+'/'+name+'-connector-open.png'});pass(name+' floating Connector button opens a real dialog; close, Escape and progress restoration work');await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-audio').uncheck();await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-stop').click();
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
 for(const [scenario,answers,expected]of [['beginner',['KI einrichten','noch keine','ChatGPT','Android','Gmail'],'setup'],['business',['Firma möchte Dokumente mit Claude auf Windows und Drive bearbeiten'],'setup'],['website',['WordPress und SEO mit Gemini auf Android'],'setup']]){await page.locator('#odpc-voice-reset').click();for(const t of answers)await answer(t);assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),expected,scenario);pass(name+' '+scenario+' flow')}
 await page.locator('#odpc-voice-settings summary').click();
 await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-reset').click();await answer('Passwort: private-secret');assert.equal((await page.locator('#odpc-voice-log').innerText()).includes('private-secret'),false);assert.equal(await page.evaluate(()=>sessionStorage.getItem('hm-guide-v1').includes('private-secret')),false);
 await page.locator('#odpc-voice-topclose').click();await page.goto('https://ai.odpc.de/start/',{waitUntil:'networkidle'});assert.equal(await page.locator('.hm-agent').count(),10);assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),true);await page.screenshot({path:out+'/'+name+'-agents.png',fullPage:true});
 await page.locator('#odpc-voice-open').click();assert.equal(await page.locator('#odpc-voice').getAttribute('role'),'dialog');await page.locator('#odpc-voice-topclose').click();assert.equal(await page.locator('#odpc-voice').isVisible(),false);assert.equal(await page.locator('main').evaluate(e=>e.inert),false);
 const agentLinks=await page.locator('.hm-agent a').evaluateAll(nodes=>nodes.map(a=>({name:a.getAttribute('aria-label'),url:a.href})));
 assert.equal(agentLinks.length,10);assert.equal(agentLinks.filter(a=>new URL(a.url).searchParams.has('agent')).length,9);
 const examples={application:'Ich heiße Oliver und brauche ein Anschreiben für eine IT-Stelle. Tatsächlich fünf Jahre Support, maximal 120 Wörter.',mail:'Beispieldaten: Nachricht vom Musterbetrieb vom 20. Oktober, Betreff Rückfrage, Antwort bis Freitag. Nur Antwortentwurf.',documents:'Erstelle aus diesen Beispieldaten eine Besprechungsnotiz: Projekt Alpha, zwei offene Fragen, Verantwortliche noch unbekannt.',web:'Beispielseite https://example.org: Bereite einen sachlichen Startseitentext mit maximal 100 Wörtern als Entwurf vor.',marketing:'Schreibe einen Beitrag über WhatsApp für unser Beispielcafé. Zielgruppe Nachbarn, keine erfundenen Preise.',interview:'Übe mit mir für eine IT-Support-Stelle. Drei Jahre tatsächliche Supporterfahrung. Eine Frage je Runde.',report:'Ausbildung Fachinformatik, Tagesbericht 20. Oktober: zwei Stunden Netzwerktechnik und vier Stunden Support. Keine weiteren Stunden ergänzen.',car:'Beispielfahrzeug Baujahr 2012, Diesel. Leerlauf unruhig, keine Fehlercodes. Bitte für die Werkstatt ordnen.',legal:'Anonymisiertes Beispiel: Partei A erhielt am 20. Oktober ein Schreiben von Partei B. Erstelle eine Chronologie; Rechtsfragen bleiben offen.'};
 for(const a of agentLinks.filter(a=>new URL(a.url).searchParams.has('agent'))){
  const id=new URL(a.url).searchParams.get('agent');await page.goto(a.url,{waitUntil:'networkidle'});
  if(await page.locator('#odpc-voice').getAttribute('data-phase')==='platform')await page.locator('[data-choice="ChatGPT"]').click();
  assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work',id);
  assert.equal(await page.locator('#hm-guide-work-prompt').count(),1,id);
  assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/tatsächlich zur Verfügung/);
  assert.equal(await page.locator('#hm-guide-test').count(),0,id+' must not require a connection test for a draft');
  assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),true);
  const detail=examples[id];await answer(detail);
  const prompt=await page.locator('#hm-guide-work-prompt').inputValue();assert(prompt.includes(detail),id+' retains examples');
  assert.equal(await page.evaluate(()=>JSON.parse(sessionStorage.getItem('hm-guide-v1')).task),id,id+' keeps selected Monkey');
  await answer('Hilfe');assert.equal(await page.locator('#hm-guide-work-prompt').inputValue(),prompt,id+' help keeps work');
  const help=new URL(await page.locator('#hm-guide-help').getAttribute('href')),helpText=help.searchParams.get('text');assert.equal(help.hostname,'wa.me');assert(!helpText.includes(detail));assert(helpText.includes(await page.locator('#odpc-voice-title').innerText()),id+' help names selected Monkey');assert(helpText.includes('ChatGPT'),id+' help names selected platform');
  await page.locator('#hm-guide-work-copy').click();assert.equal(await page.evaluate(()=>navigator.clipboard.readText()),prompt,id+' clipboard');
  await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#hm-guide-work-prompt').inputValue(),prompt,id+' reload');
 }
 pass(name+' all nine full task workflows retain examples, keep context during help, identify Monkey and platform, copy exact prompts and resume after reload');
 await page.evaluate(()=>{navigator.clipboard.writeText=async()=>{throw new Error('Synthetic clipboard denial')}});
 await page.locator('#hm-guide-work-copy').click();
 assert.equal(await page.locator('#hm-guide-work-preview').evaluate(e=>e.open),true);
 assert.equal(await page.locator('#hm-guide-work-prompt').evaluate(e=>e.selectionStart===0&&e.selectionEnd===e.value.length),true);
 assert.equal(await page.evaluate(()=>document.activeElement.id),'hm-guide-work-prompt');
 await page.screenshot({path:out+'/'+name+'-manual-copy.png'});await page.reload({waitUntil:'networkidle'});
 pass(name+' denied clipboard access opens and selects the full prompt for manual copying');
 await page.screenshot({path:out+'/'+name+'-specialist-handoff.png',fullPage:true});
 pass(name+' all nine agent cards lead to their own usable task prompts without a mandatory connection test');
 await page.locator('[data-choice="KI einrichten"]').click();
 if(await page.locator('#odpc-voice').getAttribute('data-phase')==='device')await page.locator('[data-choice="Android"]').click();
 await page.locator('[data-choice="Google Drive"]').click();await answer('Das kostet extra');
 assert.equal(await page.locator('#odpc-voice').getAttribute('data-connection-result'),'unavailable');
 await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice').getAttribute('data-connection-result'),'unavailable');
 await page.locator('[data-choice="ohne Verbindung starten"]').click();assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work');assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/anwaltliche Prüfung/);
 await page.locator('[data-choice="KI einrichten"]').click();await page.locator('[data-choice="Google Drive"]').click();await page.locator('[data-choice="verbunden"]').click();await page.locator('[data-choice="Test erfolgreich"]').click();
 assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work');assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/anwaltliche Prüfung/);assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/Google Drive/);
 pass(name+' selected specialist survives unavailable service, reload and a successful connection');
 await answer('Berichtsheft schreiben');await page.locator('[data-choice="KI einrichten"]').click();await page.locator('[data-choice="Google Drive"]').click();await answer('nicht verfügbar');
 await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'setup');await page.locator('[data-choice="ohne Verbindung starten"]').click();assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/Ausbildungsbericht/);
 pass(name+' changing tasks inside a direct agent page also preserves the chosen task on reload');
 await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work');assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/Ausbildungsbericht/);
 await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-reset').click();await page.locator('#odpc-voice-settings summary').click();
 await answer('Ich möchte kostenlos Bewerbungen schreiben');assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'platform');await page.locator('[data-choice="ChatGPT"]').click();
 assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work');assert.match(await page.locator('#hm-guide-work-prompt').inputValue(),/ATS-Abgleich/);
 assert.equal(await page.locator('[data-choice="Android"]').count(),0);pass(name+' natural free-text application starts without an unnecessary device or connection question');
 const taskDetail='Ich brauche ein Anschreiben als Marketingmanager, maximal 150 Wörter, freundlich und ohne Floskeln.';
 await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-reset').click();await page.locator('#odpc-voice-settings summary').click();
 await answer(taskDetail);await page.locator('[data-choice="ChatGPT"]').click();
 assert.match(await page.locator('#odpc-voice-title').innerText(),/Bewerbung/);assert((await page.locator('#hm-guide-work-prompt').inputValue()).includes(taskDetail));
 await answer('Die Stelle ist im Marketing und ich betreue auch E-Mails.');await answer('Korrektur: vier Jahre Berufserfahrung, bitte maximal 120 Wörter.');
 assert.match(await page.locator('#odpc-voice-title').innerText(),/Bewerbung/);await page.reload({waitUntil:'networkidle'});
 const detailed=await page.locator('#hm-guide-work-prompt').inputValue();assert(detailed.includes(taskDetail));assert(detailed.includes('vier Jahre Berufserfahrung'));
 await page.locator('#hm-guide-work-copy').click();assert.equal(await page.evaluate(()=>navigator.clipboard.readText()),detailed);
 await answer('Bitte sachlich formulieren.');assert.doesNotMatch(await page.locator('#odpc-voice-status').innerText(),/Auftrag kopiert/);
 pass(name+' specific task details, follow-up corrections, reload and clipboard keep the application context');
 await answer('Berichtsheft schreiben');assert(!(await page.locator('#hm-guide-work-prompt').inputValue()).includes('vier Jahre Berufserfahrung'));
 await page.locator('[data-choice="andere Aufgabe"]').click();await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'done');
 await page.locator('[data-choice="Aufgabe:application"]').click();assert(!(await page.locator('#hm-guide-work-prompt').inputValue()).includes(taskDetail));
 pass(name+' another Monkey and task-list reload do not inherit the previous private task details');
 const copyLabels={'de-DE':'Auftrag kopieren','en-US':'Copy task prompt','es-ES':'Copiar instrucciones','fr-FR':'Copier les instructions','it-IT':'Copia le istruzioni','pt-PT':'Copiar instruções','pl-PL':'Kopiuj instrukcję','tr-TR':'Görev metnini kopyala'};
 for(const [locale,label]of Object.entries(copyLabels)){
  if(!await page.locator('#odpc-voice-settings').evaluate(el=>el.open))await page.locator('#odpc-voice-settings summary').click();
  await page.locator('#odpc-voice-language').selectOption(locale);
  assert.equal(await page.locator('#odpc-voice').getAttribute('lang'),locale.split('-')[0]);
  assert.equal(await page.locator('#hm-guide-work-copy').innerText(),label);
  if(!['de-DE','en-US'].includes(locale)){assert.notEqual(await page.locator('#hm-guide-work-preview summary').innerText(),'View task prompt');assert(!/Schritt|Aufgabe auswählen|Mit deiner KI arbeiten/.test(await page.locator('#hm-guide-progress').innerText()));}
  await page.locator('#odpc-voice-reset').click();await page.reload({waitUntil:'networkidle'});
  assert.equal(await page.locator('#odpc-voice-language').inputValue(),locale,'reset and reload preserve '+locale);
  if(await page.locator('#odpc-voice').getAttribute('data-phase')==='platform')await page.locator('[data-choice="ChatGPT"]').click();
  assert.equal(await page.locator('#hm-guide-work-copy').innerText(),label);
 }
 pass(name+' all eight languages localize task actions, progress and handoff; reset and reload keep the selected language');
 await page.goto('https://ai.odpc.de/connector-einrichten/?agent=application&lang=fr',{waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice-language').inputValue(),'fr-FR');
 await page.locator('#odpc-voice-settings summary').click();await page.locator('#odpc-voice-language').selectOption('tr-TR');await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice-language').inputValue(),'tr-TR');
 await page.goto('https://ai.odpc.de/connector-einrichten/?agent=application&lang=es',{waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice-language').inputValue(),'es-ES');
 pass(name+' a language entry link initializes the language without overriding later choices on reload');
 const naturalLanguageTasks={
  'es':['Necesito preparar documentos para un abogado','legal'],
  'fr':['Je veux préparer un entretien d’embauche','interview'],
  'it':['Devo scrivere un rapporto di formazione','report'],
  'pt':['Quero preparar uma candidatura','application'],
  'pl':['Chcę utworzyć post marketingowy','marketing'],
  'tr':['OBD kodlarını düzenlemek istiyorum','car']
 };
 for(const[locale,[request,task]]of Object.entries(naturalLanguageTasks)){
  await page.goto('https://ai.odpc.de/connector-einrichten/?lang='+locale+'&hm_qa='+Date.now(),{waitUntil:'networkidle'});
  if(!await page.locator('#odpc-voice-settings').evaluate(el=>el.open))await page.locator('#odpc-voice-settings summary').click();
  await page.locator('#odpc-voice-reset').click();await answer(request);await page.locator('[data-choice="ChatGPT"]').click();
  assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'work',locale+' '+request);
  assert.equal(await page.evaluate(()=>JSON.parse(sessionStorage.getItem('hm-guide-v1')).task),task,locale+' '+request);
  assert((await page.locator('#hm-guide-work-prompt').inputValue()).includes(request),locale+' prompt retains request');
 }
 pass(name+' natural requests in six non-German languages select and brief the intended specialist');
 for(const[request,task]of [['Ich möchte die E-Mails zur Bewerbung sichten','mail'],['Ich möchte einen Beitrag über Bewerbungstipps schreiben','marketing'],['Ich möchte einen Termin am 30. Oktober um 10 Uhr in Berlin planen','calendar']]){
  await page.goto('https://ai.odpc.de/connector-einrichten/?lang=de&hm_qa='+Date.now(),{waitUntil:'networkidle'});
  if(!await page.locator('#odpc-voice-settings').evaluate(el=>el.open))await page.locator('#odpc-voice-settings summary').click();
  await page.locator('#odpc-voice-reset').click();await answer(request);await page.locator('[data-choice="ChatGPT"]').click();
  if(task!=='marketing')await answer('ohne Verbindung starten');
  assert.equal(await page.evaluate(()=>JSON.parse(sessionStorage.getItem('hm-guide-v1')).task),task,request);
  assert((await page.locator('#hm-guide-work-prompt').inputValue()).includes(request),request);
  await page.reload({waitUntil:'networkidle'});assert((await page.locator('#hm-guide-work-prompt').inputValue()).includes(request));
 }
 pass(name+' requested deliverable wins over its topic and calendar details survive sample-data exit and reload');
 await page.goto('https://ai.odpc.de/connector-einrichten/?lang=de&hm_qa='+Date.now(),{waitUntil:'networkidle'});
 if(!await page.locator('#odpc-voice-settings').evaluate(e=>e.open))await page.locator('#odpc-voice-settings summary').click();
 await page.locator('#odpc-voice-reset').click();await answer('Bewerbung schreiben');await answer('ChatGPT, maximal 120 Wörter.');
 assert((await page.locator('#hm-guide-work-prompt').inputValue()).includes('maximal 120 Wörter'));
 await page.locator('#odpc-voice-reset').click();
 for(const request of ['E-Mails organisieren','ChatGPT, nur Antwortentwürfe.','Computer, nur diese Woche.','Gmail, keine Löschungen.','verbunden','Test erfolgreich, aber kein Zugriff auf mein Postfach'])await answer(request);
 assert.equal(await page.locator('#odpc-voice').getAttribute('data-phase'),'test');assert.equal(await page.locator('#odpc-voice').getAttribute('data-connection-result'),'failed');
 assert.deepEqual(await page.evaluate(()=>JSON.parse(sessionStorage.getItem('hm-guide-v1')).reportedServices),[]);
 await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#odpc-voice').getAttribute('data-connection-result'),'failed');
 await answer('ohne Verbindung starten');
 const mixedPrompt=await page.locator('#hm-guide-work-prompt').inputValue();for(const detail of ['nur Antwortentwürfe','nur diese Woche','keine Löschungen'])assert(mixedPrompt.includes(detail));
 await page.locator('#hm-guide-work-copy').click();assert.equal(await page.evaluate(()=>navigator.clipboard.readText()),mixedPrompt);
 await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('#hm-guide-work-prompt').inputValue(),mixedPrompt);
 await page.screenshot({path:out+'/'+name+'-mixed-setup-handoff.png'});
 pass(name+' mixed setup answers retain requirements and contradictory success never grants reported access');
 for(const[locale,disclosure]of [['de-DE','Mein Passwort lautet demo-secret'],['en-US','My password is demo-secret'],['es-ES','Mi contraseña es demo-secret'],['fr-FR','Mon mot de passe est demo-secret'],['it-IT','La mia password è demo-secret'],['pt-PT','A minha senha é demo-secret'],['pl-PL','Moje hasło to demo-secret'],['tr-TR','Şifrem: demo-secret']]){
  if(!await page.locator('#odpc-voice-settings').evaluate(e=>e.open))await page.locator('#odpc-voice-settings summary').click();
  await page.locator('#odpc-voice-language').selectOption(locale);
  const before=await page.locator('#hm-guide-work-prompt').inputValue();await answer(disclosure);
  assert.equal(await page.locator('#hm-guide-work-prompt').inputValue(),before,locale+' rejected before handoff');
  assert(!(await page.locator('#hm-guide-history-log').innerText()).includes('demo-secret'),locale+' history');
  assert(!(await page.evaluate(()=>sessionStorage.getItem('hm-guide-v1'))).includes('demo-secret'),locale+' persistence');
  assert.equal(await page.locator('#odpc-voice-answer').inputValue(),'');
 }
 pass(name+' credential disclosures in eight languages never enter task prompt, history or saved progress');
 const offerPages=['https://ai.odpc.de/start/','https://odpc.de/','https://odpc.de/struktur-portfolio','https://odpc.de/praelex-ai','https://odpc.de/praetax','https://odpc.de/praedium','https://odpc.de/initiq','https://odpc.de/webdesign','https://odpc.de/ki-gestuetzte-dienstleistungen-automatisierung','https://odpc.de/praedoc','https://odpc.de/prompt-engineering-digitale-mitarbeiter-ki-sinnvoll-nutzen','https://odpc.de/einsatz-von-ki-je-nach-branche'];
 for(const url of offerPages){
  const response=await page.goto(url,{waitUntil:'domcontentloaded'});assert.equal(response.status(),200,url);
  assert.equal(await page.locator('#om-premium').count(),1,url);assert.equal(await page.locator('#om-wege').count(),1,url);
  assert.equal(await page.locator('#om-premium').getAttribute('data-offer-version'),'1.1.0',url);
  assert.match(await page.locator('#om-premium').innerText(),/Kostenpflichtige persönliche Leistung/);
  const cards=page.locator('#om-premium .om-card');assert((await cards.count())>=1,url);
  await cards.first().scrollIntoViewIfNeeded();await page.waitForFunction(()=>{const image=document.querySelector('#om-premium .om-card img');return image.complete&&image.naturalWidth>0});
  assert.match(await cards.first().locator('img').evaluate(e=>getComputedStyle(e).filter),/om-blue-accents/);
  assert.equal(await page.locator('#om-blue-accents').count(),1);
  for(const card of await cards.all()){const box=await card.boundingBox();assert(box.x>=0&&box.x+box.width<=viewport.width+1,url+' premium card fits viewport');}
  assert.equal(await page.locator('#om-premium .om-links a[href="https://ai.odpc.de/start/#monkeys"]').count(),1,url+' free return link');
  const slug=new URL(url).pathname.replaceAll('/','');
  if(['praelex-ai','praetax','praedium','initiq','webdesign','ki-gestuetzte-dienstleistungen-automatisierung','praedoc'].includes(slug))assert.equal(await cards.count(),1,url+' focused premium offer');else assert.equal(await cards.count(),7,url+' complete portfolio');
 }
 await page.goto('https://ai.odpc.de/start/',{waitUntil:'networkidle'});
 assert.equal(await page.locator('#monkeys .hm-agent').count(),10);assert.equal(await page.locator('#om-premium .om-card').count(),7);
 assert.match(await page.locator('#monkeys').innerText(),/GRÜN.*KOSTENLOS/s);
 await page.locator('#om-premium').screenshot({path:out+'/'+name+'-premium-blue.png'});
 await page.locator('#om-wege').screenshot({path:out+'/'+name+'-green-blue-paths.png'});
 await page.locator('#om-premium [data-product="praelex-ai"] a').click();await page.waitForURL(u=>u.hostname==='odpc.de'&&u.pathname.replace(/\/$/,'')==='/praelex-ai');
 assert.equal(await page.locator('#om-premium [data-product="praelex-ai"]').count(),1);
 await page.locator('#om-premium .om-links a[href="https://ai.odpc.de/start/#monkeys"]').click();await page.waitForURL(u=>u.hostname==='ai.odpc.de'&&u.pathname==='/start/'&&u.hash==='#monkeys');
 assert.equal(await page.locator('#monkeys .hm-agent').count(),10);
 pass(name+' green self-service and seven blue paid offers link to ODPC products and back; education, original logo and mobile cards verified');
 assert.equal(errors.length,0,JSON.stringify(errors));pass(name+' agent overview and no page errors');await context.close();
}
const audioContext=await browser.newContext();const audio=await audioContext.newPage();await audio.goto('https://ai.odpc.de/connector-einrichten/',{waitUntil:'networkidle'});await audio.getByRole('button',{name:'Mit Sprache einrichten',exact:true}).click();
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
const syntheticVoice=await syntheticVoiceContext.newPage();await syntheticVoice.goto('https://ai.odpc.de/',{waitUntil:'networkidle'});await syntheticVoice.getByRole('button',{name:'Mit Sprache einrichten',exact:true}).click();await syntheticVoice.waitForFunction(()=>window.__testSpeech?.name==='Microsoft Katja Online (Natural)');
const selected=await syntheticVoice.evaluate(()=>window.__testSpeech);assert.equal(selected.rate,1.12);assert.equal(selected.pitch,1);pass('synthetic device catalog selects natural female at updated speed',{voice:selected.name,synthetic:true});await syntheticVoiceContext.close();
const micContext=await browser.newContext();await micContext.addInitScript(()=>{window.SpeechRecognition=class{start(){queueMicrotask(()=>this.onresult({results:[[{transcript:'Gmail ChatGPT Samsung'}]]}))}abort(){}}});const mic=await micContext.newPage();await mic.goto('https://ai.odpc.de/',{waitUntil:'networkidle'});await mic.locator('#odpc-voice-open').click();await mic.locator('#odpc-voice-settings summary').click();await mic.locator('#odpc-voice-audio').uncheck();await mic.locator('#odpc-voice-mic').click();await mic.waitForFunction(()=>document.querySelector('#odpc-voice').dataset.phase==='setup');pass('synthetic speech recognition result advances dialog');await micContext.close();
}finally{await writeFile(out+'/report.json',JSON.stringify(report,null,2));await browser.close()}
