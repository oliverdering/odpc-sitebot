/**
 * ODPC + Helper Monkey: independent public merchandise and sitemap audit.
 * Read-only. No mutation of customer sites, hosting or WP data.
 */
const hosts = [
 {label:"ODPC main",url:"https://odpc.de/",need:["https://ai.odpc.de/merchandise/","helper-monkey-rundsiegel-original.webp"]},
 {label:"ODPC references",url:"https://odpc.de/kunden-referenzen",need:["https://ai.odpc.de/merchandise/"]},
 {label:"ODPC partner bonus",url:"https://odpc.de/partnerbonus-helper-monkey",need:["https://ai.odpc.de/merchandise/"]},
 {label:"Helper Monkey store",url:"https://ai.odpc.de/merchandise/",need:["nfc","shirt"]},
 {label:"Helper Monkey home",url:"https://ai.odpc.de/",need:[]},
 {label:"Helper Monkey start",url:"https://ai.odpc.de/start/",need:[]}
];
const report={checkedAt:new Date().toISOString(),checks:[],sitemaps:{},failures:[],warnings:[]};
async function load(url) {
 const c=new AbortController(),timer=setTimeout(()=>c.abort(),16000);
 try{const response=await fetch(url,{redirect:"follow",headers:{"user-agent":"ODPC public sitemap and merchandise QA/1.0"},signal:c.signal});
 return {status:response.status,url:response.url,html:await response.text(),type:response.headers.get("content-type")||""};}finally{clearTimeout(timer)}
}
function locations(xml){return [...xml.matchAll(/<loc>\s*([^<]+?)\s*<\/loc>/gi)].map(x=>x[1].trim().replaceAll("&amp;","&"))}
function findTarget(html,patterns){return patterns.filter(p=>html.toLowerCase().includes(p.toLowerCase()))}
for (const spec of hosts) {
 try {
  const r=await load(spec.url);
  const matched=findTarget(r.html,spec.need);
  const datum={label:spec.label,status:r.status,url:r.url,size:r.html.length,expected:spec.need,matched};
  report.checks.push(datum);
  if(r.status!==200||matched.length!==spec.need.length)report.failures.push(spec.label+" status="+r.status+" missing="+spec.need.filter(p=>!matched.includes(p)).join(","));
  if(spec.label==="Helper Monkey store"){
    datum.title=(r.html.match(/<title[^>]*>([\s\S]*?)<\/title>/i)||[])[1]||"";
    datum.canonical=(r.html.match(/<link[^>]+rel=["']canonical["'][^>]+href=["']([^"']+)/i)||[])[1]||"";
    datum.noindex=/<meta[^>]+robots[^>]+noindex/i.test(r.html);
    datum.brandLogo=r.html.includes("helper")&&r.html.includes("monkey");
    datum.headings=[...r.html.matchAll(/<h[1-3][^>]*>([^<]+)<\/h[1-3]>/gi)].map(x=>x[1].trim()).slice(0,15);
    datum.internalLinks=[...r.html.matchAll(/href=["'](https:\/\/ai\.odpc\.de\/[^"']+)/gi)].map(x=>x[1]).filter((v,i,a)=>a.indexOf(v)===i).slice(0,18);
    if(datum.noindex)report.failures.push("Merch store carries noindex");
  }
 }catch(e){report.failures.push(spec.label+" "+String(e))}
}
for(const [host,urls] of Object.entries({
  "odpc.de":["https://odpc.de/sitemap_index.xml","https://odpc.de/wp-sitemap.xml"],
  "ai.odpc.de":["https://ai.odpc.de/sitemap_index.xml","https://ai.odpc.de/wp-sitemap.xml"]
})){
 let map={site:host,found:false};
 for(const u of urls){try{const r=await load(u);if(r.status===200&&r.html.includes("<loc>")){map={site:host,found:true,url:r.url,status:r.status,locs:locations(r.html)};break}}catch(e){map.lastError=String(e)}}
 if(map.found){
    const children=map.locs.filter(u=>/sitemap/i.test(u)).slice(0,25);
    map.childSitemaps=[];
    let pageUrls=map.locs.filter(x=>!x.includes("sitemap"));
    for(const child of children){
      try{const res=await load(child),locs=locations(res.html);map.childSitemaps.push({url:child,http:res.status,numUrls:locs.length});if(res.status===200)pageUrls.push(...locs)}
      catch(e){map.childSitemaps.push({url:child,error:String(e)})}
    }
    map.targets=host==="odpc.de"?["https://odpc.de/kunden-referenzen","https://odpc.de/partnerbonus-helper-monkey"]:["https://ai.odpc.de/merchandise/"];
    map.targetFind=map.targets.map(url=>({url,listed:pageUrls.some(u=>u.replace(/\/$/,"")===url.replace(/\/$/,""))}));
    map.urlCount=pageUrls.length;
    for(const t of map.targetFind)if(!t.listed)report.warnings.push(host+" missing sitemap entry: "+t.url);
 }else report.warnings.push(host+" sitemap not accessible");
 report.sitemaps[host]=map;
}
console.log(JSON.stringify(report,null,2));
if(report.failures.length)process.exitCode=1;