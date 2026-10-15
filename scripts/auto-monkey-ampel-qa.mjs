import { chromium } from "playwright";
const url="https://odpc.de/obd-fahrzeugdiagnose-und-programmierung-fuer-alle-fahrzeuge";
const browser=await chromium.launch({headless:true});
let bad=0;
for(const viewport of [{width:390,height:844},{width:1440,height:900}]){
 const ctx=await browser.newContext({viewport,permissions:["clipboard-read","clipboard-write"],isMobile:viewport.width===390,hasTouch:viewport.width===390});
 const page=await ctx.newPage(),errs=[];
 page.on("pageerror",e=>errs.push(e.message));
 try{
 await page.goto(url,{waitUntil:"domcontentloaded",timeout:45000});
 await page.locator("#am-regphoto").waitFor({state:"attached",timeout:15000});
 const initial=await page.evaluate(()=>{
 const root=document.querySelector("#odpc-obd-v2");let out={};
 for(const [key,id] of [["red","am-rot"],["orange","am-orange"],["green","am-gruen"]]){
 const el=document.getElementById(id);out[key]={exists:!!el,border:getComputedStyle(el).borderTopColor,visible:el.getBoundingClientRect().width>0};
 }
 out.camera=document.getElementById("am-regphoto").getAttribute("capture");
 out.scrollWidth=document.documentElement.scrollWidth;
 out.viewport=innerWidth;
 out.hasQualification=!!root.querySelector('a[href*="Gesellenbrief.pdf"]');
 out.hasAdapter=!!root.querySelector('a[href*="obdlink.com/products/obdlink-mxp"]');
 out.hasWizard=!!root.querySelector("#obd-copy");
 return out;
 });
 if(!initial.red.visible||!initial.orange.visible||!initial.green.visible)throw Error("Missing visible traffic-light stage");
 if(initial.scrollWidth>initial.viewport+12)throw Error("Horizontal viewport overflow "+JSON.stringify(initial));
 if(!initial.hasQualification||!initial.hasAdapter||!initial.hasWizard||initial.camera!=="environment")throw Error("Prerequisite missing "+JSON.stringify(initial));
 await page.locator("#am-brand").selectOption({label:"BMW / MINI"});
 await page.locator("#obd-os").selectOption("android");
 let reco=await page.locator("#am-app-result").textContent();
 if(!/BimmerLink|BimmerCode/i.test(reco))throw Error("BMW software recommendation missing "+reco);
 const img=Buffer.from("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jIn8AAAAASUVORK5CYII=","base64");
 await page.locator("#am-regphoto").setInputFiles({name:"fahrzeugschein-test.png",mimeType:"image/png",buffer:img});
 if(await page.locator("#am-ocr").isDisabled())throw Error("Scan button not enabled");
 await page.evaluate(()=>{window.Tesseract={createWorker:async()=>({recognize:async()=>({data:{text:"2.1 0603 2.2 BPG00000 VOLKSWAGEN"}}),terminate:async()=>{}})}};});
 await page.locator("#am-ocr").click();
 await page.waitForFunction(()=>document.querySelector("#am-hsn").value==="0603",{timeout:20000});
 const hsn=await page.locator("#am-hsn").inputValue();
 const tsn=await page.locator("#am-tsn").inputValue();
 const make=await page.locator("#am-brand").inputValue();
 if(hsn!=="0603"||tsn!=="BPG00000"||!make.includes("Volkswagen"))throw Error("Simulated OCR extraction failed: "+JSON.stringify({hsn,tsn,make}));
 await page.locator("#obd-copy").click();
 const copied=await page.evaluate(()=>navigator.clipboard.readText());
 if(!copied.includes("HSN 2.1: 0603")||!copied.includes("TSN 2.2: BPG00000")||!copied.includes("ORANGE"))throw Error("AI prompt missing vehicle scan context");
 if(errs.length)throw Error("JS errors "+errs.join(";"));
 console.log("PASS "+JSON.stringify({viewport:viewport.width,initial,ocrMock:{hsn,tsn,make},clipboardChars:copied.length,pageErrors:errs}));
 }catch(e){bad++;console.log("FAIL "+viewport.width+" "+String(e.stack||e));}
 finally{await ctx.close();}
}
await browser.close();
if(bad)process.exitCode=1;