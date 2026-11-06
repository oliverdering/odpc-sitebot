'use strict';
const fs=require('fs'),path=require('path'),vm=require('vm'),assert=require('assert');
const html=fs.readFileSync(path.join(__dirname,'../engineering-studio.html'),'utf8');
const code=html.match(/<script>([\s\S]*?)<\/script>/)[1];
const els={}, blobs=[], memory={};
class Element{constructor(){this.value='';this.textContent='';this.children=[];this.files=[]}add(o){this.children.push(o);if(!this.value)this.value=o.value}append(...a){this.children.push(...a)}replaceChildren(){this.children=[]}setAttribute(){}click(){}}
const document={getElementById:id=>els[id]||(els[id]=new Element()),createElement:()=>new Element()};
class FakeBlob{constructor(parts,options){this.parts=parts;this.type=options.type;blobs.push(this)}}
const context={document,localStorage:{getItem:k=>memory[k]||null,setItem:(k,v)=>memory[k]=v},Option:class{constructor(text,value){this.text=text;this.value=value}},Blob:FakeBlob,URL:{createObjectURL:()=> 'blob:test',revokeObjectURL(){}},navigator:{clipboard:{writeText:async()=>{}}},setTimeout(){},confirm:()=>true};
(async()=>{vm.runInNewContext(code,context);els.preset.onclick();els.export.onclick();const project=JSON.parse(blobs.at(-1).parts[0]);assert.equal(project.schema,'drol-engineering-studio/1');assert.equal(project.parts.length,5);
async function load(value,size=100){const target={files:[{size,text:async()=>value}],value:'chosen'};await els['import-file'].onchange({target});return els['import-status'].textContent}
assert.match(await load(JSON.stringify(project)),/Projekt importiert/);const before=memory['drol-studio-v1'];assert.match(await load('{bad'),/Ungültiges JSON/);assert.equal(memory['drol-studio-v1'],before);assert.match(await load(JSON.stringify({...project,parts:['__proto__']})),/unbekannte Komponenten/);assert.match(await load(JSON.stringify({...project,parts:Array(25).fill('pi')})),/Komponenten/);assert.match(await load('{}',100001),/zu groß/);els.bom.onclick();const csv=blobs.at(-1).parts[0];assert(csv.startsWith('\ufeff'));assert(csv.includes('\r\n'));assert.match(csv,/Raspberry Pi/);assert(memory['drol-studio-v1']);console.log('PASS: Studio JSON roundtrip, invalid inputs, local persistence, CSV contents');})().catch(e=>{console.error(e);process.exitCode=1});
