'use strict';
const fs=require('fs'),path=require('path'),assert=require('assert'),vm=require('vm');
const root=path.resolve(__dirname,'..');
const names=['index.html','drol-wissen.html','drol-mobile.html','engineering-studio.html'];
for(const name of names){const data=fs.readFileSync(path.join(root,name),'utf8');assert(/<meta name="viewport"/.test(data),name+': viewport');for(const m of data.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/gi)){new vm.Script(m[1],{filename:name})}const ids=new Set([...data.matchAll(/\sid=["']([^"']+)["']/g)].map(m=>m[1]));for(const m of data.matchAll(/href=["']#([^"']+)["']/g))assert(ids.has(m[1]),name+': missing fragment '+m[1]);}
const home=fs.readFileSync(path.join(root,'index.html'),'utf8');for(const id of ['intelligence','kontakt','knowledge','engineering'])assert(home.includes('id="'+id+'"'),'missing historical anchor '+id);
assert(home.includes('Wir machen Ihre Drohnendaten nutzbar.'),'deployment precondition');
for(const name of names){const data=fs.readFileSync(path.join(root,name),'utf8');for(const m of data.matchAll(/(?:href|src)=["']([^"'#?:/]+\.html)(?:#[^"']*)?["']/g))assert(fs.existsSync(path.join(root,m[1])),name+': missing link '+m[1]);}
console.log('PASS: release HTML syntax, viewport, historical anchors and local HTML links');
