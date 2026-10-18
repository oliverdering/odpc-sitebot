import {readFileSync} from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
import {test} from 'node:test';
const source=readFileSync('site-deploy/ai.odpc.de/odpc-monkey-voice-interview.php','utf8');
const script=source.split('<script>')[1].split('</script>')[0];
new vm.Script(script);
const helpers=script.slice(script.indexOf(' function voiceGender('),script.indexOf(" $('odpc-voice-preview').addEventListener"));
function setup(voices,locale='de-DE',gender='male'){
 const controls=Object.fromEntries(['language','gender','preview','voicehelp','audio'].map(id=>['odpc-voice-'+id,{value:'',disabled:false,textContent:'',checked:true,options:[],replaceChildren(){this.options=[]},add(o){this.options.push(o)}}]));
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
  assert.equal(s.controls['odpc-voice-voicehelp'].hidden,true);
 }
});
test('unrecognized gender uses a device voice with a visible fallback notice',()=>{
 const s=setup([voice('Anna')],'de-DE','male');s.run('refreshVoices();say("Test")');assert.equal(s.spoken.length,1);assert.equal(s.controls['odpc-voice-preview'].disabled,false);assert.equal(s.controls['odpc-voice-voicehelp'].hidden,false);
 s.controls['odpc-voice-gender'].value='female';s.run('refreshVoices();say("Test")');assert.equal(s.spoken[1].voice.name,'Anna');assert.equal(s.controls['odpc-voice-voicehelp'].hidden,true);
});
test('delayed voice catalogs are refreshed at speech time',()=>{
 const s=setup([]);s.run('refreshVoices()');assert.equal(s.controls['odpc-voice-preview'].disabled,false);
 s.setVoices([voice('Stefan')]);s.run('say("Test")');assert.equal(s.spoken[0].voice.name,'Stefan');
 s.setVoices([]);s.run('say("Default")');assert.equal(s.spoken[1].voice,undefined);
});
test('voice is chosen automatically for the selected language and gender',()=>{
 const s=setup([voice('David','en-GB'),voice('Mark','en-US'),voice('Anna','en-US')],'en-US','male');s.run('refreshVoices();say("Test")');assert.equal(s.spoken[0].voice.name,'Mark');
 s.controls['odpc-voice-gender'].value='female';s.run('refreshVoices();say("Again")');assert.equal(s.spoken[1].voice.name,'Anna');
});
test('preview does not replace current question; muted output stays silent',()=>{
 const s=setup([voice('Anna')],'de-DE','female');s.run('refreshVoices();say("Question");say("Preview",false)');assert.equal(s.run('speaking'),'Question');
 s.controls['odpc-voice-audio'].checked=false;s.run('say("Muted")');assert.equal(s.spoken.length,2);
});

test('menu contains exactly two selectors, male/female and eight languages',()=>{
 const markup=source.split('<style>')[0];
 const selects=[...markup.matchAll(/<select id="([^"]+)">([\s\S]*?)<\/select>/g)];
 assert.deepEqual(selects.map(s=>s[1]),['odpc-voice-gender','odpc-voice-language']);
 const labels=[...selects[0][2].matchAll(/<option value="([^"]+)">([^<]+)<\/option>/g)].map(m=>[m[1],m[2]]);
 assert.deepEqual(labels,[['male','Männlich'],['female','Weiblich']]);
 assert.equal((selects[1][2].match(/<option /g)||[]).length,8);
 assert.ok(!source.includes('odpc-voice-persona'));
 assert.ok(!markup.includes('soweit erkennbar'));
});

test('generic Android voice names remain speakable for both menu choices',()=>{
 for(const gender of ['male','female']){
  const s=setup([voice('Google Deutsch')],'de-DE',gender);s.run('refreshVoices();say("Hallo")');
  assert.equal(s.controls['odpc-voice-preview'].disabled,false);
  assert.equal(s.spoken.length,1);assert.equal(s.spoken[0].voice.name,'Google Deutsch');
 }
});
test('temporarily empty voice catalog still invokes the browser default engine',()=>{
 const s=setup([]);s.run('refreshVoices();say("Hallo")');assert.equal(s.controls['odpc-voice-preview'].disabled,false);assert.equal(s.spoken.length,1);assert.equal(s.spoken[0].lang,'de-DE');assert.equal(s.spoken[0].voice,undefined);
});

test('explicit preview plays even when automatic speech is unchecked',()=>{
 const s=setup([voice('Stefan')]);s.controls['odpc-voice-audio'].checked=false;s.run('say("Preview",false,true)');assert.equal(s.spoken.length,1);
});
test('a voice in a different language is not forced onto the selected language',()=>{
 const s=setup([voice('David','en-US')],'de-DE','male');s.run('say("Hallo")');assert.equal(s.spoken[0].voice,undefined);assert.equal(s.spoken[0].lang,'de-DE');
});
