import { chromium } from "playwright";
const pages=[
["Kunden & Live-Referenzen","https://odpc.de/kunden-referenzen","kunden"],
["Helper Monkey Partnerbonus","https://odpc.de/partnerbonus-helper-monkey","partner"]
];
const browser=await chromium.launch({headless:true});
const results=[];
let failures=0;
for(const [label,url,kind] of pages){
 for(const size of [{w:390,h:844},{w:1440,h:900}]){
  const context=await browser.newContext({viewport:{width:size.w,height:size.h},deviceScaleFactor:1,isMobile:size.w<500,hasTouch:size.w<500});
  const page=await context.newPage();const errors=[];page.on("pageerror",e=>errors.push(e.message));
  try{
   const res=await page.goto(url,{waitUntil:"domcontentloaded",timeout:40000});
   await page.waitForTimeout(300);
   const out=await page.evaluate((kind)=>{const container=document.querySelector(kind==="kunden"?"#odpc-customer-ref":"#odpc-partner");const el=document.documentElement;return {title:document.title,visible:!!container&&container.getBoundingClientRect().height>700,width:innerWidth,htmlScroll:el.scrollWidth,bodyScroll:document.body.scrollWidth,bodyWidth:document.body.clientWidth,links:[...container.querySelectorAll("a[href]")].map(a=>a.href).filter(u=>u.includes("odpc.de")),buttons:[...container.querySelectorAll("a")].filter(a=>a.getBoundingClientRect().width>0).length}},kind);
   const overflow=out.htmlScroll>size.w+12||out.bodyScroll>size.w+12;
   const ok=!!res&&res.status()===200&&out.visible&&!overflow&&out.links.length>4&&errors.length===0;
   results.push({label,viewport:size.w,http:res?.status(),containerVisible:out.visible,overflow:overflow?String(out.htmlScroll)+"/"+String(out.bodyScroll):false,linkCount:out.links.length,errors,ok});
   console.log(JSON.stringify(results.at(-1)));
   if(!ok)failures++;
  }catch(e){console.log(JSON.stringify({label,viewport:size.w,error:String(e)}));failures++}
  finally{await context.close()}
 }
}
await browser.close();
console.log("SUMMARY "+JSON.stringify({tests:results.length,failures}));
if(failures)process.exitCode=1;
