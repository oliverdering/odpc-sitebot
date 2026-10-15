import { chromium } from "playwright";

const site = "https://ai.odpc.de";
const errors = [];
const browser = await chromium.launch({headless:true});
const context = await browser.newContext({permissions:["clipboard-read","clipboard-write"]});
const page = await context.newPage();
page.on("pageerror", error => errors.push(error.message));
const info = {};
function must(ok,message){if(!ok)throw new Error(message)}
try {
  await page.goto(site+"/sprachen/?lang=vi",{waitUntil:"domcontentloaded",timeout:40000});
  await page.waitForTimeout(1100);
  info.vietnamese_heading = await page.locator("#hm-lang-head").textContent();
  info.vietnamese_selected = await page.locator("#hm-lang-choice").inputValue();
  must(info.vietnamese_selected === "vi", "Language query parameter not applied: "+info.vietnamese_selected);
  must(/ngôn ngữ|của bạn/i.test(info.vietnamese_heading), "Vietnamese page headline not localized: "+info.vietnamese_heading);
  await page.locator("#hm-lang-copy").click();
  await page.waitForTimeout(150);
  info.copied = await page.evaluate(() => navigator.clipboard.readText());
  must(info.copied.includes("Tiếng Việt"),"Clipboard language not Vietnamese");
  await page.goto(site+"/gespraechsuebersetzer/",{waitUntil:"domcontentloaded",timeout:40000});
  await page.waitForTimeout(500);
  info.voice_default_b = await page.locator("#hm-vlang-b").inputValue();
  must(info.voice_default_b === "Tiếng Việt", "Translator default B not Vietnamese");
  await page.locator("#hm-vcopy").click();
  await page.waitForTimeout(150);
  const voice = await page.evaluate(() => navigator.clipboard.readText());
  info.voice_clipboard_ok = voice.includes("Deutsch") && voice.includes("Tiếng Việt");
  must(info.voice_clipboard_ok,"Translator clipboard did not contain language pair");
  await page.goto(site+"/sprachen/",{waitUntil:"domcontentloaded",timeout:40000});
  const links = await page.locator('a[href*="-multilingual-v3.1.zip"]').evaluateAll(els=>els.map(e=>e.href));
  must(links.length===4,"Expected 4 V3.1 ZIP links, found "+links.length);
  info.downloads=[];
  for(const href of links){
    const r=await page.request.get(href,{timeout:35000});
    const b=await r.body();
    const valid=r.ok()&&b.length>1500&&b[0]===0x50&&b[1]===0x4b&&b[2]===0x03&&b[3]===0x04;
    info.downloads.push({url:href.split("/").at(-1),status:r.status(),bytes:b.length,valid_zip_header:valid});
    must(valid,"Bad V3.1 download: "+href);
  }
  await page.goto(site+"/start/",{waitUntil:"domcontentloaded",timeout:40000});
  info.start_has_selector = await page.locator('form[action*="/sprachen/"] select[name="lang"]').count()>0;
  must(info.start_has_selector,"Start page language selector missing");
  info.pageerrors=errors;
  if(errors.length)throw new Error("Browser JS errors: "+errors.join("; "));
  console.log("PASS "+JSON.stringify(info));
} catch(err) {
  console.error("FAIL "+JSON.stringify(info));
  console.error(err.stack || String(err));
  process.exitCode = 1;
} finally {await browser.close();}
