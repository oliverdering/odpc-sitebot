import { chromium } from "playwright";
const specs=[
{label:"Webdesign",url:"https://odpc.de/webdesign",boxes:[[".odpc-wd1-card","#c93a32"],[".odpc-wd2-card","#df962a"],[".odpc-wd3-card","#4daa57"]],other:[".odpc-wd4x-card",".odpc-wd5-card"],expected:["1 · ROT","2 · ORANGE","3 · GRÜN","https://odpc.de/obd-fahrzeugdiagnose-und-programmierung-fuer-alle-fahrzeuge","ai.odpc.de/sprachen/"]},
{label:"Auto Monkey",url:"https://odpc.de/obd-fahrzeugdiagnose-und-programmierung-fuer-alle-fahrzeuge",boxes:[["#am-rot","#c24945"],["#am-orange","#df962a"],["#am-gruen","#278353"]],other:["#am-ocr","#am-green-status"],expected:["Fahrzeug erkennen","Software","KI-DIAGNOSE","https://odpc.de/webdesign","Gesellenbrief.pdf"]}
];
const browser=await chromium.launch({headless:true});
let failures=0;
for(const s of specs){
for(const viewport of [{width:390,height:844},{width:1440,height:900}]){
 const ctx=await browser.newContext({viewport,isMobile:viewport.width===390,hasTouch:viewport.width===390});
 const page=await ctx.newPage();
 const errors=[];page.on("pageerror",e=>errors.push(e.message));
 try{
  const response=await page.goto(s.url,{waitUntil:"domcontentloaded",timeout:45000});
  await page.waitForTimeout(500);
  const result=await page.evaluate(({s,viewport})=>{
   const article=document.querySelector("article")||document.body;
   const html=article.innerHTML;
   const boxes=s.boxes.map(([selector])=>{const el=document.querySelector(selector),style=el&&getComputedStyle(el);return{selector,exists:!!el,visible:!!el&&el.getBoundingClientRect().height>90,borderColor:style?.borderTopColor}});
   const images=[...article.querySelectorAll("img")].map(x=>({src:x.currentSrc||x.src,loaded:x.complete&&x.naturalWidth>0})).filter(x=>/helper|monkey|file_0000/.test(x.src)).slice(0,15);
   const whatsapp=[...article.querySelectorAll('a[href*="wa.me/"]')].map(a=>a.href);
   const external=[...article.querySelectorAll('a[href]')].filter(a=>a.getAttribute("href")?.startsWith("https://")).length;
   const other=s.other.map(css=>({css,exists:!!document.querySelector(css)}));
   let faq=null;
   if(s.label==="Webdesign"){let d=document.querySelector(".odpc-wd4x-faq");if(d){d.open=true;faq=d.open&&d.textContent.length>100;}}
   return {screen:viewport.width,bodyWidth:document.body.scrollWidth,docWidth:document.documentElement.scrollWidth,boxes,images,whatsapp,external,other,faq,keywords:s.expected.map(x=>({word:x,found:html.includes(x)}))};
  },{s,viewport});
  const issue=[];
  if(response?.status()!==200)issue.push("http "+response?.status());
  if(result.docWidth>viewport.width+10||result.bodyWidth>viewport.width+10)issue.push("horizontal overflow");
  if(result.boxes.some(b=>!b.exists||!b.visible))issue.push("missing colored card");
  if(result.other.some(x=>!x.exists))issue.push("missing supporting section");
  if(result.keywords.some(x=>!x.found))issue.push("missing expected copy or crosslink");
  if(result.images.some(x=>!x.loaded))issue.push("image unavailable");
  if(result.whatsapp.some(x=>!x.includes("492654882183")))issue.push("private WhatsApp link");
  if(errors.length)issue.push("page JS errors "+errors.join(";"));
  if(s.label==="Webdesign"&&result.faq!==true)issue.push("FAQ not interactive");
  console.log(JSON.stringify({page:s.label,screen:viewport.width,...result,errors,pass:issue.length===0,issue}));
  if(issue.length)failures++;
 }catch(e){failures++;console.error("FAIL "+s.label+" "+viewport.width+" "+(e.stack||String(e)));}
 finally{await ctx.close();}
}
}
await browser.close();
console.log(JSON.stringify({summary:"ODPC Webdesign vs Auto Monkey",tests:4,failures}));
if(failures)process.exitCode=1;