import { chromium } from "playwright";
const browser=await chromium.launch({headless:true});
const context=await browser.newContext({viewport:{width:390,height:844},isMobile:true,hasTouch:true});
const page=await context.newPage();
const errors=[];
page.on("pageerror",e=>errors.push(e.message));
const expect=(condition,message)=>{if(!condition)throw Error(message)};
const summary={};
try{
  await page.goto("https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/?lang=en",{waitUntil:"domcontentloaded",timeout:60000});
  await page.locator("#hmv4-options button").first().waitFor({timeout:25000});
  summary.language=await page.locator("#hmv4-lang").inputValue();
  expect(summary.language==="en","English language query flag was not applied");
  expect((await page.locator("#hmv4-question").textContent()).includes("How experienced"),"Question not localized");
  const choose=async indices=>{
    for(const i of indices)await page.locator("#hmv4-options button").nth(i).click();
    await page.locator("#hmv4-next").click();
  };
  await choose([0]); // beginner
  await choose([0]); // android
  await choose([0,1]); // Gmail + ChatGPT (multi-select)
  await choose([2]); // office
  await page.locator("#hmv4-result:not(.hm-hidden)").waitFor({timeout:10000});
  summary.result=await page.locator("#hmv4-yellow").textContent();
  expect(summary.result.includes("Gmail / Google")&&summary.result.includes("ChatGPT"),"Multiple accounts not retained in result");
  expect(await page.locator("#hmv4-agentlink").getAttribute("href")!==null,"Missing next-action link");
  summary.faqCount=await page.locator("#hmv4-faqlist details").count();
  expect(summary.faqCount>=5,"Interview FAQ missing");
  await page.goto("https://ai.odpc.de/connector-einrichten/",{waitUntil:"domcontentloaded",timeout:60000});
  summary.globalGuide=await page.locator(".hm-v4-ribbon").count();
  summary.globalWa=await page.locator("#hm-v4-wa").count();
  summary.traffic=await page.locator(".hm-v4-steps section").count();
  expect(summary.globalGuide===1&&summary.globalWa===1&&summary.traffic===3,"Global UI/traffic not available on connector guide");
  await page.locator("#hm-v4-helpbtn").click();
  await page.locator("#hm-v4-help.is-open").waitFor();
  summary.chat=await page.locator("#hm-v4-chat").count();
  expect(summary.chat===1,"Global searchable knowledge assistant not loaded");
  await page.locator("#hm-v4-query").fill("Connector");
  await page.locator("#hm-v4-ask").click();
  await page.waitForTimeout(800);
  summary.answer=await page.locator("#hm-v4-response").textContent();
  expect(summary.answer.trim().length>12,"Search assistant produced no answer");
  const resp=await page.goto("https://odpc.de/",{waitUntil:"domcontentloaded",timeout:60000});
  await page.waitForTimeout(1000);
  summary.odpc={status:resp?.status(),url:page.url(),title:await page.title(),bodyHead:(await page.locator("body").innerText()).slice(0,360),help:await page.locator("#odpc-hm-v4-helpbtn").count(),marker:await page.locator("script#odpc-hm-v4-js").count()};
  expect(summary.odpc.help===1,"ODPC global help missing; browser snapshot: "+JSON.stringify(summary.odpc));
  expect(await page.locator(".odpc-hm-v4-flags a").count()>=6,"ODPC language entry missing");
  await page.goto("https://odpc.de/l-i-f-e-mpu-selbstlernkurs-neun-lernmodule/",{waitUntil:"domcontentloaded",timeout:60000});
  expect((await page.locator("body").textContent()).includes("Modul 9"),"L.I.F.E. lesson 9 missing");
  expect(errors.length===0,"JavaScript errors: "+errors.join("; "));
  console.log("PASS Helper Monkey V4 browser acceptance "+JSON.stringify(summary));
}catch(e){
  console.error("FAIL Helper Monkey V4 browser acceptance "+JSON.stringify(summary));
  console.error(e.stack||String(e));
  process.exitCode=1;
}finally{await browser.close()}
