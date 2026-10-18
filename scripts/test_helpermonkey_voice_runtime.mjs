import {readFileSync} from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
import {test} from 'node:test';
const source=readFileSync('site-deploy/ai.odpc.de/odpc-monkey-voice-interview.php','utf8');
const script=source.split('<script>')[1].split('</script>')[0];new vm.Script(script);
const helpers=script.slice(script.indexOf(' function voiceGender('),script.indexOf(" $('odpc-voice-preview').addEventListener"));
function setup(voices,locale='de-DE',gender='male'){
 const controls=Object.fromEntries(['language','gender','preview','voicehelp','audio'].map(id=>['odpc-voice-'+id,{value:'',disabled:false,textContent:'',checked:true}]));
 controls['odpc-voice-language'].value=locale;controls['odpc-voice-gender'].value=gender;
 const spoken=[],fallback=[],status={textContent:''};let available=voices;const panel={hidden:false,dataset:{}};
 const synth={getVoices:()=>available,cancel(){},speak:u=>spoken.push(u)};
 const ctx=vm.createContext({$:id=>controls[id],status,panel,fallback,window:{speechSynthesis:synth,SpeechSynthesisUtterance:class{},AudioContext:class{},Worker:class{}},speechSynthesis:synth,SpeechSynthesisUtterance:class{constructor(t){this.text=t}},setTimeout,clearTimeout});
 vm.runInContext('let voiceCatalog=[],speechTicket=0,speaking="";'+helpers,ctx);
 vm.runInContext('localSay=(text,ticket,locale,gender)=>fallback.push({text,ticket,locale,gender,voice:localLanguage[locale]+"+"+(gender==="male"?"m3":"f2")})',ctx);
 return{controls,spoken,fallback,status,panel,run:code=>vm.runInContext(code,ctx),setVoices:v=>available=v};
}
const voice=(name,lang='de-DE')=>({name,lang,voiceURI:name});
test('named native voices are assigned to the requested gender',()=>{
 const s=setup([]);for(const name of ['Microsoft Stefan - German','Google UK English Male'])assert.equal(s.run(`voiceGender(${JSON.stringify(voice(name))})`),'male');for(const name of ['Microsoft Hedda - German','Google UK English Female'])assert.equal(s.run(`voiceGender(${JSON.stringify(voice(name))})`),'female');assert.equal(s.run('voiceGender({name:"Google Deutsch"})'),'unknown');
});
test('eight locales and two genders select the correct named native voice',()=>{
 for(const lang of ['de-DE','en-US','es-ES','fr-FR','it-IT','pt-PT','pl-PL','tr-TR'])for(const gender of ['male','female']){const s=setup([voice('Male',lang),voice('Female',lang)],lang,gender);s.run('say("Test")');assert.equal(s.spoken[0].voice.name,gender==='male'?'Male':'Female');assert.equal(s.fallback.length,0);}
});
test('a single generic Android voice is never silently reused for both genders',()=>{
 const s=setup([voice('Google Deutsch')]);s.run('say("Test")');s.controls['odpc-voice-gender'].value='female';s.run('say("Test")');assert.equal(s.spoken.length,0);assert.deepEqual(s.fallback.map(x=>x.voice),['gmw/de+m3','gmw/de+f2']);assert.equal(s.controls['odpc-voice-preview'].disabled,false);
});
test('missing male voice uses local male, not the known female voice',()=>{
 const s=setup([voice('Anna')]);s.run('say("Test")');assert.equal(s.spoken.length,0);assert.equal(s.fallback[0].gender,'male');s.controls['odpc-voice-gender'].value='female';s.run('say("Test")');assert.equal(s.spoken[0].voice.name,'Anna');
});
test('empty native catalog still has two distinct local variants in every language',()=>{
 for(const lang of ['de-DE','en-US','es-ES','fr-FR','it-IT','pt-PT','pl-PL','tr-TR']){const s=setup([],lang);s.run('say("Test")');s.controls['odpc-voice-gender'].value='female';s.run('say("Test")');assert.notEqual(s.fallback[0].voice,s.fallback[1].voice);assert.equal(s.controls['odpc-voice-preview'].disabled,false);}
});
test('delayed catalog is refreshed at speech time',()=>{const s=setup([]);s.setVoices([voice('Stefan')]);s.run('say("Test")');assert.equal(s.spoken[0].voice.name,'Stefan');});
test('failed native synthesis invokes the corresponding local gender',()=>{const s=setup([voice('Stefan')]);s.run('say("Test")');s.spoken[0].onerror({error:'synthesis-failed'});assert.equal(s.fallback[0].voice,'gmw/de+m3');});
test('preview keeps the question and explicitly overrides muted automatic speech',()=>{const s=setup([voice('Stefan')]);s.run('say("Question");say("Preview",false,true)');assert.equal(s.run('speaking'),'Question');s.controls['odpc-voice-audio'].checked=false;s.run('say("Muted");say("Explicit",false,true)');assert.equal(s.spoken.length,3);});
test('no voice of an unrelated language is forced onto the requested language',()=>{const s=setup([voice('David','en-US')]);s.run('say("Test")');assert.equal(s.spoken.length,0);assert.equal(s.fallback[0].locale,'de-DE');});
test('menu retains exactly male/female and eight languages',()=>{const markup=source.split('<style>')[0],selects=[...markup.matchAll(/<select id="([^"]+)">([\s\S]*?)<\/select>/g)];assert.deepEqual(selects.map(s=>s[1]),['odpc-voice-gender','odpc-voice-language']);assert.deepEqual([...selects[0][2].matchAll(/<option value="([^"]+)">([^<]+)<\/option>/g)].map(m=>[m[1],m[2]]),[['male','Männlich'],['female','Weiblich']]);assert.equal((selects[1][2].match(/<option /g)||[]).length,8);});
