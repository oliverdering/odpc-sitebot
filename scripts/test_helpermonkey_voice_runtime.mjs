import {readFileSync} from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
import {test} from 'node:test';
const source=readFileSync('site-deploy/ai.odpc.de/odpc-monkey-voice-interview.php','utf8');
const script=source.split('<script>')[1].split('</script>')[0];new vm.Script(script);
const helpers=script.slice(script.indexOf(' function voiceGender('),script.indexOf(" $('odpc-voice-preview').addEventListener"));
function setup(voices,locale='de-DE',gender='female'){
 const controls=Object.fromEntries(['language','gender','profile','preview','voicehelp','audio'].map(id=>['odpc-voice-'+id,{value:'',disabled:false,textContent:'',checked:true,children:[],replaceChildren(){this.children=[]},append(x){this.children.push(x)}}]));
 controls['odpc-voice-language'].value=locale;controls['odpc-voice-gender'].value=gender;
 const spoken=[],timers=[],status={textContent:''};let available=voices;const panel={hidden:false,dataset:{}};
 const synth={getVoices:()=>available,cancel(){},speak:u=>spoken.push(u)};
 const ctx=vm.createContext({$:id=>controls[id],document:{createElement:()=>({value:'',textContent:''})},status,panel,window:{speechSynthesis:synth,SpeechSynthesisUtterance:class{}},speechSynthesis:synth,SpeechSynthesisUtterance:class{constructor(t){this.text=t}},setTimeout(fn){timers.push(fn);return timers.length},clearTimeout(){}});
 vm.runInContext('let voiceCatalog=[],speechTicket=0,speaking="",voiceWaitTimer=null;'+helpers,ctx);
 return{controls,spoken,timers,status,panel,run:code=>vm.runInContext(code,ctx),setVoices:v=>available=v};
}
const voice=(name,lang='de-DE',extra={})=>({name,lang,voiceURI:name,...extra});
test('female is the default, male remains an explicit option',()=>{const markup=source.split('<style>')[0];assert.match(markup,/<option value="female" selected>Weiblich<\/option>/);assert.match(markup,/<option value="male">Männlich<\/option>/);assert.match(markup,/id="odpc-voice-profile"/);assert.equal((markup.match(/<option value="(?:de-DE|en-US|es-ES|fr-FR|it-IT|pt-PT|pl-PL|tr-TR)"/g)||[]).length,8);});
test('natural female voice wins over classic female and male voices',()=>{const s=setup([voice('Microsoft Hedda'),voice('Microsoft Conrad Online (Natural)'),voice('Microsoft Katja Online (Natural)')]);s.run('say("Guten Tag.")');assert.equal(s.spoken[0].voice.name,'Microsoft Katja Online (Natural)');assert.equal(s.spoken[0].rate,.94);assert.equal(s.spoken[0].pitch,1);});
test('Google Deutsch is used instead of being discarded for absent gender metadata',()=>{const s=setup([voice('Google Deutsch')]);s.run('say("Hallo.")');assert.equal(s.spoken[0].voice.name,'Google Deutsch');assert.match(s.controls['odpc-voice-voicehelp'].textContent,/kein Geschlecht/);assert.equal(s.panel.dataset.audioEngine,'device');});
test('enhanced Apple voice is preferred over compact counterpart',()=>{const s=setup([voice('Anna (Compact)'),voice('Anna (Enhanced)')]);s.run('say("Hallo.")');assert.equal(s.spoken[0].voice.name,'Anna (Enhanced)');});
test('explicit profile choice overrides automatic quality ranking',()=>{const s=setup([voice('Anna'),voice('Microsoft Katja Online (Natural)')]);s.controls['odpc-voice-profile'].value='Anna';s.run('say("Hallo.")');assert.equal(s.spoken[0].voice.name,'Anna');});
test('known male voice is never the female fallback',()=>{const s=setup([voice('Microsoft Stefan')]);s.run('say("Hallo.")');s.timers.shift()();assert.equal(s.spoken.length,0);assert.equal(s.panel.dataset.audioState,'voice-unavailable');});
test('missing voices wait for the device and then show an honest limitation',()=>{const s=setup([]);s.run('say("Hallo.")');assert.equal(s.panel.dataset.audioState,'waiting-for-device-voice');s.timers.shift()();assert.equal(s.panel.dataset.audioState,'voice-unavailable');assert.equal(s.controls['odpc-voice-preview'].disabled,true);assert.equal(s.spoken.length,0);});
test('late device catalog becomes usable without eSpeak',()=>{const s=setup([]);s.run('say("Hallo.")');s.setVoices([voice('Google Deutsch')]);s.timers.shift()();assert.equal(s.spoken[0].voice.name,'Google Deutsch');});
test('device errors do not activate a robot voice',()=>{const s=setup([voice('Anna')]);s.run('say("Hallo.")');s.spoken[0].onerror();assert.equal(s.panel.dataset.audioState,'error');assert.match(s.status.textContent,/keine künstliche Ersatzstimme/);assert(!source.includes('localSay('));assert(!source.includes('new window.eSpeakNG'));});
test('all eight languages use matching device voices',()=>{for(const lang of ['de-DE','en-US','es-ES','fr-FR','it-IT','pt-PT','pl-PL','tr-TR']){const s=setup([voice('Female Natural',lang)],lang);s.run('say("Test.")');assert.equal(s.spoken[0].voice.lang,lang);}});
test('sentence pauses and cancellation prevent queued continuation after stop',()=>{const s=setup([voice('Anna')]);s.run('say("Ein Satz. Noch ein Satz.")');assert.equal(s.spoken.length,1);s.spoken[0].onend();assert.equal(s.timers.length,1);s.run('speechTicket++;stopLocalAudio()');s.timers.shift()();assert.equal(s.spoken.length,1);});
test('preview does not overwrite the question and can be requested while muted',()=>{const s=setup([voice('Anna')]);s.controls['odpc-voice-audio'].checked=false;s.run('say("Frage.");say("Hörprobe.",false,true)');assert.equal(s.run('speaking'),'Frage.');assert.equal(s.spoken.length,1);});
