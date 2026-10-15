import {chromium} from "playwright";
const browser=await chromium.launch({headless:true});
const services=[
{label:"Webdesign",url:"https://odpc.de/webdesign",kind:"web"},
{label:"Auto Monkey",url:"https://odpc.de/obd-fahrzeugdiagnose-und-programmierung-fuer-alle-fahrzeuge",kind:"auto"}];
const pages=[
[26,"connector-einrichten"],[31,"webmaster-agent"],[32,"finanzen-vertragscheck"],[33,"konfliktlotse-anwaltsvorarbeit"],
[21,"praxis"],[74,"werkstatt"],[92,"merchandise"],[94,"sprachen"],[95,"gespraechsuebersetzer"]]
.map(([id,slug])=>({label:"AI "+slug,url:"https://ai.odpc.de/"+slug+"/",kind:"ai",id}));
const specs=[...services.flatMap(s=>[390,1440].map(width=>({...s,width}))),...pages.map(s=>({...s,width:390}))];
let fails=0;
for(const s of specs){
const ctx=await browser.newContext({viewport:{width:s.width,height:830},isMobile:s.width===390,hasTouch:s.width===390});
const page=await ctx.newPage(),errors=[];
page.on("pageerror",e=>errors.push(e.message));
try{
const res=await page.goto(s.url,{waitUntil:"domcontentloaded",timeout:50000});
await page.waitForTimeout(350);
if(s.kind==="ai"){
 const img=page.locator(".hm-unified-extras img[src*='helper-monkey-rundsiegel-original.webp']").first();
 await img.scrollIntoViewIfNeeded();
 await img.evaluate(async element=>{if(!element.complete)await new Promise(resolve=>{element.addEventListener("load",resolve,{once:true});element.addEventListener("error",resolve,{once:true});setTimeout(resolve,4000)});});
}
const obj=await page.evaluate(kind=>{
const root=kind==="web"?document.querySelector(".hm-service-brand"):kind==="auto"?document.querySelector(".hm-service-brand"):document.querySelector(".hm-unified-extras");
const imgs=[...root.querySelectorAll('img[src*="helper-monkey-rundsiegel-original.webp"]')];
const imgGood=imgs.length>0&&imgs.some(img=>img.complete&&img.naturalWidth>25);
const faq=kind==="web"?document.querySelector(".odpc-wd4x-faq"):kind==="auto"?document.querySelector("#hm-auto-faq details"):document.querySelector(".hm-unified-extras details");
const hasGrey=kind==="web"?!!document.querySelector(".odpc-wd5-card"):kind==="auto"?!!document.querySelector("#hm-auto-auf-einen-blick"):!!document.querySelector(".hm-unified-extras .hm-quick-panel");
const robot=/file_0000000092447246ae36a380a8fd4c25|file_000000004764724691cf95d1c8b9e11b/.test(document.body.innerHTML);
return {width:innerWidth,docWidth:document.documentElement.scrollWidth,root:!!root,imgGood,faqCount:kind==="web"?document.querySelectorAll(".odpc-wd4x-faq").length:kind==="auto"?document.querySelectorAll("#hm-auto-faq details").length:document.querySelectorAll(".hm-unified-extras details").length,faq:!!faq,hasGrey,robot,svg:kind==="ai"?true:!!root.querySelector(".hm-tool svg"),ampels:kind==="auto"?["#am-rot","#am-orange","#am-gruen"].map(x=>!!document.querySelector(x)):kind==="web"?["#odpc-wd1-start",".odpc-wd2-card",".odpc-wd3-card"].map(x=>!!document.querySelector(x)):[],id:root?.id||null};
},s.kind);
let issue=[];
if(res?.status()!==200)issue.push("HTTP "+res?.status());
if(!obj.root||!obj.imgGood)issue.push("Missing original branded logo or image not loaded");
if(!obj.faq||obj.faqCount<(s.kind==="auto"?14:s.kind==="web"?10:4))issue.push("FAQ missing or too small "+obj.faqCount);
if(!obj.hasGrey)issue.push("Final information block missing");
if(obj.robot)issue.push("Old robot artwork is still embedded");
if(obj.docWidth>obj.width+12)issue.push("Horizontal overflow");
if(!obj.svg||obj.ampels.some(x=>!x))issue.push("Missing hero accessory or phase");
const details=page.locator(s.kind==="web"?".odpc-wd4x-faq":s.kind==="auto"?"#hm-auto-faq details":".hm-unified-extras details").first();
await details.locator("summary").click();
const expanded=await details.getAttribute("open");
if(expanded===null)issue.push("FAQ does not open");
if(errors.length)issue.push("JS errors "+errors.join(";").slice(0,400));
console.log(JSON.stringify({page:s.label,viewport:s.width,http:res?.status(),...obj,errors,passed:issue.length===0,issues:issue}));
if(issue.length)fails++;
}catch(e){fails++;console.error("FAIL "+s.label+" "+s.width+" "+String(e.stack||e));}
finally{await ctx.close()}
}
await browser.close();
console.log("SUMMARY "+JSON.stringify({tests:specs.length,failures:fails}));
if(fails)process.exitCode=1;