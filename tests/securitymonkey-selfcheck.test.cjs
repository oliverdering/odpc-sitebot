const fs=require("node:fs"),vm=require("node:vm"),assert=require("node:assert");
const html=fs.readFileSync("site-deploy/ai.odpc.de/securitymonkey/index.html","utf8");
const match=html.match(/<script>([\s\S]*?)<\/script>/);
assert(match,"Missing script");
function setup(){
 class E{constructor(){this.children=[];this.textContent="";this.hidden=false} appendChild(e){this.children.push(e)} replaceChildren(){this.children=[]}}
 const elements={content:new E(),progress:new E(),back:new E(),restart:new E()};
 const ctx=vm.createContext({document:{getElementById:id=>elements[id],createElement:()=>new E()}});
 vm.runInContext(match[1],ctx);
 const choose=text=>{const e=elements.content.children.find(x=>x.textContent===text&&x.onclick);assert(e,"Missing choice: "+text);e.onclick()};
 const strings=element=>[element.textContent,...element.children.flatMap(strings)];
 return {ctx,elements,choose,strings};
}
{
 const {elements,choose,strings}=setup();
 choose("Einen Computer");choose("Auf dem Gerät");choose("Nein");
 choose("Nein");choose("Nein");choose("Ich bin die einzige Person");
 const result=strings(elements.content).join(" ");
 assert(result.includes("Ersatzgerät"),"Single-computer recovery must mention substitute hardware");
 assert(result.includes("zusätzliche, getrennte Sicherung"),"Missing backup action");
 assert(!result.includes("technischer Sicherheitscheck hat stattgefunden"),"Must not claim a technical scan");
}
{
 const {elements,choose,strings}=setup();
 choose("Mehrere Computer");choose("Auf dem Gerät und online");choose("Ja");
 choose("Nur in einem synchronisierten Cloud-Ordner");choose("Nein");choose("Ja");choose("Ja");choose("Ja");
 const result=strings(elements.content).join(" ");
 assert(result.includes("synchronisierter Cloud-Ordner"),"Sync is not automatically a backup");
 assert(result.includes("unwichtige Testdatei"),"Restore test guidance missing");
}
{
 const {elements,choose,strings}=setup();
 choose("Einen Computer");choose("Auf dem Gerät");choose("Ja");choose("Nur auf demselben Gerät");choose("Nein");
 elements.back.onclick(); elements.back.onclick(); elements.back.onclick();
 choose("Nein"); choose("Ja"); choose("Ja"); choose("Ich bin die einzige Person");
 const result=strings(elements.content).join(" ");
 assert(!result.includes("Eine Sicherung nur auf demselben Gerät"),"Stale skipped backup answer leaked into report");
}
console.log("PASS: three questionnaire paths and back-navigation");
