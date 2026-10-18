import vm from 'node:vm';
import {readFileSync} from 'node:fs';
import {createHash} from 'node:crypto';
import assert from 'node:assert/strict';
const dir=process.env.HM_ASSET_DIR||'voice-assets-cache';
const messages=[];let ready;const initialized=new Promise(r=>ready=r);
class XHR{open(method,url){assert.equal(url,'espeakng.worker.data');}send(){const b=readFileSync(dir+'/espeakng.worker.data');this.response=b.buffer.slice(b.byteOffset,b.byteOffset+b.byteLength);this.status=200;setTimeout(()=>this.onload({}),0);}}
const ctx=vm.createContext({Module:{ENVIRONMENT:'WORKER',printErr:console.error},XMLHttpRequest:XHR,location:{pathname:'/espeakng.worker.js'},importScripts(){},WorkerGlobalScope:function(){},postMessage(m){if(m==='ready')ready();else messages.push(m)},console,setTimeout,clearTimeout,ArrayBuffer,Uint8Array,Uint16Array,Uint32Array,Int8Array,Int16Array,Int32Array,Float32Array,Float64Array,TextDecoder,TextEncoder});ctx.self=ctx;
vm.runInContext(readFileSync(dir+'/espeakng.worker.js','utf8'),ctx,{timeout:10000});
let timer;await Promise.race([initialized,new Promise((_,rej)=>timer=setTimeout(()=>rej(Error('Speech worker initialization timeout')),15000))]);clearTimeout(timer);
function call(method,args=[]){messages.length=0;ctx.onmessage({data:{method,args,callback:'cb'}});return messages.slice();}
const identifiers=call('list_voices')[0].result[0].map(v=>v.identifier);
const cases=[['de-DE','gmw/de','Hallo, dies ist die ausgewählte Stimme.'],['en-US','gmw/en-US','Hello, this is the selected voice.'],['es-ES','roa/es','Hola, esta es la voz seleccionada.'],['fr-FR','roa/fr','Bonjour, voici la voix sélectionnée.'],['it-IT','roa/it','Ciao, questa è la voce selezionata.'],['pt-PT','roa/pt-PT','Olá, esta é a voz selecionada.'],['pl-PL','zls/pl','Cześć, to jest wybrany głos.'],['tr-TR','trk/tr','Merhaba, bu seçilen ses.']];
call('set_rate',[175]);
for(const[locale,id,text]of cases){
 assert.ok(identifiers.includes(id),'Missing language voice '+id);const results=[];
 for(const variant of ['m3','f2']){
  assert.equal(call('set_voice',[id+'+'+variant])[0].result[0],0,'Voice selection failed: '+id+'+'+variant);
  const chunks=call('synthesize',[text]).filter(m=>m.result?.[0] instanceof ArrayBuffer).map(m=>Buffer.from(m.result[0]));const bytes=Buffer.concat(chunks),pcm=new Float32Array(bytes.buffer.slice(bytes.byteOffset,bytes.byteOffset+bytes.byteLength));
  assert.ok(pcm.length>4410,'No meaningful audio produced');assert.ok(pcm.every(Number.isFinite),'Invalid samples');assert.ok(pcm.reduce((s,v)=>s+v*v,0)>1,'Silent audio');
  results.push({variant,samples:pcm.length,hash:createHash('sha256').update(bytes).digest('hex')});
 }
 assert.notEqual(results[0].hash,results[1].hash,'Male and female audio must differ: '+locale);
 console.log('PASS real synthesis:',locale,results.map(r=>r.variant+' '+(r.samples/44100).toFixed(2)+'s '+r.hash.slice(0,12)).join(' / '));
}
console.log('PASS: 16 real audio syntheses across eight languages, distinct male/female waveforms');
