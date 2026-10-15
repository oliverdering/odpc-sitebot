// Read-only public website and ODPC customer-reference crosslink audit.
// Checks public HTTP delivery and important editorial links. Does not infer customer approval.
const cases = [
  {label:"ODPC homepage", url:"https://odpc.de/", required:["kunden-referenzen","partnerbonus-helper-monkey","Tante Lenchen","Campingplatz Runkel"]},
  {label:"ODPC customer references",url:"https://odpc.de/kunden-referenzen",required:["Anwaltskanzlei Anne-Kathrin Justen","ATRIUM 191","Tante Lenchen","Campingplatz Runkel","Bahnhof Grenzau","https://ai.odpc.de/","partnerbonus-helper-monkey"]},
  {label:"Helper Monkey partner bonus",url:"https://odpc.de/partnerbonus-helper-monkey",required:["T-Shirt","NFC","Bewertungen","sponsored","kunden-referenzen"]},
  {label:"ODPC portfolio",url:"https://odpc.de/struktur-portfolio",required:["kunden-referenzen","partnerbonus-helper-monkey"]},
  {label:"ODPC Webdesign",url:"https://odpc.de/webdesign",required:["kunden-referenzen","partnerbonus-helper-monkey"]},
  {label:"ODPC Justen customer report",url:"https://odpc.de/referenz-ra-justen-helper-monkey",required:["Kundenauftrag","ra-justen.de","kunden-referenzen"]},
  {label:"Tante Lenchen",url:"https://odpc.de/tante-lenchen",required:["kunden-referenzen","camping-runkel-lahnstein"]},
  {label:"Campingplatz Runkel",url:"https://odpc.de/camping-runkel-lahnstein",required:["kunden-referenzen","tante-lenchen"]},
  {label:"Kanzlei operator domain",url:"https://ra-justen.de/",required:[],external:true},
  {label:"ATRIUM live ODPC project",url:"https://atrium191-rb.odpc.de/",required:[],external:true},
  {label:"Helper Monkey platform",url:"https://ai.odpc.de/",required:[],external:true}
];
const results=[];let failures=0;
for(const item of cases){
  const ctl=new AbortController();const timer=setTimeout(()=>ctl.abort(),16000);
  try{
    const r=await fetch(item.url,{redirect:"follow",signal:ctl.signal,headers:{"User-Agent":"ODPC-Reference-QA/1.0 (public read-only audit)"}});
    const html=await r.text();
    const missing=item.required.filter(x=>!html.includes(x));
    const row={name:item.label,status:r.status,final_url:r.url,bytes:html.length,missing,external:item.external||false,odpc_mentioned:/odpc\.de/i.test(html)};
    results.push(row);
    console.log(JSON.stringify(row));
    if(!item.external&&(!r.ok||missing.length))failures++;
  }catch(e){const row={name:item.label,error:String(e),external:item.external||false};results.push(row);console.log(JSON.stringify(row));if(!item.external)failures++;}
  finally{clearTimeout(timer)}
}
console.log("SUMMARY "+JSON.stringify({checked:results.length,odpc_failed:failures,externals:results.filter(x=>x.external),date:new Date().toISOString()}));
if(failures)process.exitCode=1;
