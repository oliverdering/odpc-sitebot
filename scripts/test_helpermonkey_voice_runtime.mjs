import {readFileSync} from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
import {test} from 'node:test';
const source=readFileSync('site-deploy/ai.odpc.de/odpc-monkey-voice-interview.php','utf8');
const script=source.split('<script>')[1].split('</script>')[0];
new vm.Script(script);
const helpers=script.slice(script.indexOf(' function voiceGender('),script.indexOf(" $('odpc-voice-preview').addEventListener"));
function setup(voices,locale='de-DE',gender='all'){
 const controls=Object.fromEntries(['persona','language','gender','preview','voicehelp','audio'].map(id=>['odpc-voice-'+id,{value:'',disabled:false,textContent:'',checked:true,options:[],replaceChildren(){this.options=[]},add(o){this.options.push(o)}}]));
 controls['odpc-voice-language'].value=locale;controls['odpc-voice-gender'].value=gender;
 const spoken=[],status={textContent:''};let available=voices;
 const synth={getVoices:()=>available,cancel(){},speak:u=>spoken.push(u)};
 const ctx=vm.createContext({$:id=>controls[id],status,window:{speechSynthesis:synth,SpeechSynthesisUtterance:class{}},speechSynthesis:synth,SpeechSynthesisUtterance:class{constructor(t){this.text=t}},Option:class{constructor(text,value){this.text=text;this.value=value}}});
 vm.runInContext('let voiceCatalog=[],speechTicket=0,speaking="";'+helpers,ctx);
 return {controls,spoken,status,run:code=>vm.runInContext(code,ctx),setVoices:v=>available=v};
}
const voice=(name,lang='de-DE')=>({name,lang,voiceURI:name});
test('actual JS detects named voices and keeps unknown voices unclassified',()=>{
 const s=setup([]);
 for(const name of ['Microsoft Stefan - German','Google UK English Male','Microsoft Conrad Online'])assert.equal(s.run(`voiceGender(${JSON.stringify(voice(name))})`),'male');
 for(const name of ['Microsoft Hedda - German','Google UK English Female','Anna','Microsoft Katja Online'])assert.equal(s.run(`voiceGender(${JSON.stringify(voice(name))})`),'female');
 assert.equal(s.run('voiceGender({name:"Google Deutsch"})'),'unknown');
});
test('eight locales, two genders: selected voice has matching language and type',()=>{
 for(const lang of ['de-DE','en-US','es-ES','fr-FR','it-IT','pt-PT','pl-PL','tr-TR'])for(const gender of ['male','female']){
  const s=setup([voice('Male',lang),voice('Female',lang),voice('Other','ja-JP')],lang,gender);s.run('refreshVoices();say("Test")');
  assert.equal(s.spoken.length,1);assert.equal(s.spoken[0].voice.name,gender==='male'?'Male':'Female');assert.equal(s.spoken[0].lang,lang);
  assert.ok(!s.controls['odpc-voice-persona'].options.some(o=>o.value==='auto'));
 }
});
test('missing gender never silently falls back to a different system voice',()=>{
 const s=setup([voice('Anna')],'de-DE','male');s.run('refreshVoices();say("Test")');assert.equal(s.spoken.length,0);assert.equal(s.controls['odpc-voice-preview'].disabled,true);
 s.controls['odpc-voice-gender'].value='all';s.run('refreshVoices();say("Test")');assert.equal(s.spoken[0].voice.name,'Anna');
});
test('empty and delayed voice catalogs update without fake options',()=>{
 const s=setup([]);s.run('refreshVoices()');assert.equal(s.controls['odpc-voice-persona'].disabled,true);
 s.setVoices([voice('Stefan')]);s.run('refreshVoices()');assert.equal(s.controls['odpc-voice-preview'].disabled,false);
 s.setVoices([]);s.run('refreshVoices();say("Test")');assert.equal(s.spoken.length,0);
});
test('exact locale preferred; selected profile survives a reordered catalog',()=>{
 const s=setup([voice('David','en-GB'),voice('Mark','en-US')],'en-US','male');s.run('refreshVoices();say("Test")');assert.equal(s.spoken[0].voice.name,'Mark');
 s.controls['odpc-voice-persona'].value='1';s.setVoices([voice('Mark','en-US'),voice('David','en-GB')]);s.run('refreshVoices();say("Again")');assert.equal(s.spoken[1].voice.name,'David');
});
test('preview does not replace current question; muted output stays silent',()=>{
 const s=setup([voice('Anna')]);s.run('refreshVoices();say("Question");say("Preview",false)');assert.equal(s.run('speaking'),'Question');
 s.controls['odpc-voice-audio'].checked=false;s.run('say("Muted")');assert.equal(s.spoken.length,2);
});
