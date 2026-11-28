"""Collect a small editorial index. Persist only links, facts and original summaries."""
import argparse
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timedelta
from html import unescape
import json
from pathlib import Path
import re
import time
from urllib.error import HTTPError
from urllib.parse import urlsplit, urlunsplit, parse_qsl, urlencode
from urllib.request import Request, urlopen
from zoneinfo import ZoneInfo
from metadata import fetch_dates

ROOT = Path(__file__).resolve().parent
BERLIN = ZoneInfo("Europe/Berlin")
TERMS = ("Künstliche Intelligenz", "KI", "ChatGPT", "Sprachmodell", "Machine Learning", "Deep Learning",
         "maschinelles Lernen", "Deepfake", "Robotik", "KI-Agenten", "autonome Systeme", "Automatisierung",
         "Artificial Intelligence", "generative KI", "OpenAI")
SIGNAL = re.compile(r"\b(?:KI|(?-i:AI)|künstlich\w*\s+Intelligenz|artificial intelligence|ChatGPT|OpenAI|Sprachmodell\w*|Deepfakes?|maschinell\w*\s+Lernen|machine learning|deep learning|neuronale[ns]? Netz\w*|generativ\w* (?:Modelle?|Systeme?))\b", re.I)
ROBOT = re.compile(r"\b(?:Robotik|Roboters?ystem\w*|autonom\w* Systeme?)\b", re.I)
CONTEXT = re.compile(r"lernend|intelligent|erkenn\w*|autonom|Forsch\w*|Algorithm\w*",re.I)
HOSTS = set("ardmediathek.de zdf.de arte.tv 3sat.de phoenix.de daserste.de swr.de wdr.de ndr.de mdr.de br.de hr.de hr-fernsehen.de rbb24.de rbb-online.de radiobremen.de sr.de kika.de dw.com tagesschau.de srf.ch orf.at".split())
TOPICS = {
 "KI-Sicherheit und Datenschutz": r"Datenschutz|Privatsphäre|Deepfake|Überwachung|Desinformation|Falschinformation|Täuschung|Sicherheitsrisik|Kontrollverlust",
 "Wirtschaft und Arbeitswelt": r"Unternehmen|Arbeitswelt|Betrieb\w*|Arbeitspl[aä]tz|Wirtschaft|Beschäftigte|Berufsleben|Handwerk|Verlag",
 "Robotik und Automatisierung": r"Robot\w*|Automatisierung|autonom\w* (?:System|Fahrzeug)",
 "Gesellschaft und Politik": r"Politik|Gesellschaft|Regulierung|Gesetz|Schule|Bildung|Jugendliche|Demokratie|Urheberrecht",
 "Wissenschaft und Forschung": r"Forschung|Forschende|Universität|Studie|Medizin|Therapie|Diagnos\w*|Wissenschaft",
}
FORMATS = {"Dokumentationen":r"Dokumentation|Dokumentarfilm|\bDoku\b|\bDOK\b","Reportagen":r"Reportage|reporter|Tracks East",
           "Nachrichten":r"nachrichten|tagesschau|heute journal|aktuelle stunde|regionalnachrichten|landesschau|hamburg journal",
           "Interviews":r"Interview|im Gespräch|Gespräch mit|Talkshow|Talkrunde"}
# Editorial angles are assigned only when their cue appears in the source metadata.
ANGLES = [
 (r"Urheberrecht|Copyright|Trainingsdaten","die Herkunft von Trainingsdaten und die Rechte der Urheber"),
 (r"Depression|Psychotherap|Therapeut","den Einsatz von Chatbots bei der Suche nach psychologischer Unterstützung"),
 (r"Desinformation|Falschinformation|Fake News","die Verlässlichkeit digital verbreiteter Informationen"),
 (r"Deepfake","die Frage, wie sich manipulierte Aufnahmen erkennen lassen"),
 (r"Gesichtserkennung|Kennzeichen|Überwachung","die Auswertung von Bilddaten und ihre Folgen für die Privatsphäre"),
 (r"Datenschutz|Nutzerdaten|persönliche Daten","den Umgang mit personenbezogenen Daten"),
 (r"Arbeitspl[aä]tz|Arbeitswelt|Berufsleben","Veränderungen im Berufsalltag"),
 (r"Schule|Schüler|Unterricht","den Einsatz digitaler Werkzeuge im Unterricht"),
 (r"Krankenhaus|Diagnos|Arzt|Ärzt|Medizin","die Rolle digitaler Unterstützung in der medizinischen Praxis"),
 (r"Musik|Künstler|Kunstwerk","das Verhältnis zwischen KI und künstlerischer Arbeit"),
 (r"Roboter|Robotik","die Aufgaben, die Maschinen übernehmen können"),
 (r"ChatGPT|Chatbot|Sprachmodell","die Arbeit mit Systemen, die Sprache verarbeiten und Antworten erzeugen"),
 (r"Regulierung|Gesetz|Verordnung","die Regeln für den Einsatz von KI"),
 (r"Vertrauen|Vertrauens","das Vertrauen in automatisierte Entscheidungen"),
 (r"Forsch|Universität|wissenschaft","Erkenntnisse aus der Forschung"),
]
def clean(v):
 return ' '.join(unescape(re.sub('<[^>]*>', ' ', str(v or ''))).split())

def norm(v):
 return re.sub(r'[^\w]+',' ',clean(v).casefold()).strip()

def canonical(url):
 try:
  parts=urlsplit(url)
  if parts.scheme not in ('https','http') or parts.username or parts.password or parts.port not in (None,443,80):return ''
  host=(parts.hostname or '').lower()
  if not any(host==h or host.endswith('.'+h) for h in HOSTS) or parts.path in ('','/'):return ''
  return urlunsplit(('https',host,parts.path.rstrip('/'),urlencode([(k,v) for k,v in parse_qsl(parts.query) if not k.startswith('utm_')]),''))
 except ValueError:return ''

def relevant(title,description):
 text=title+' '+description
 return bool(SIGNAL.search(text) or (ROBOT.search(title) and CONTEXT.search(description)))

def read(url, body=None):
 for attempt in range(2):
  try:
   req=Request(url,data=json.dumps(body).encode() if body else None,headers={'User-Agent':'ODPC-KI-Mediathek/2.0 (+https://odpc.de/ki-mediathek)','Content-Type':'application/json'})
   with urlopen(req,timeout=15) as reply:return json.load(reply)
  except (OSError,ValueError) as error:
   if attempt or isinstance(error,HTTPError) and error.code not in (429,500,502,503,504):raise
   time.sleep(2)

def classify(patterns,s):
 return [k for k,v in patterns.items() if re.search(v,s,re.I)]

def station(name):
 return {'3sat':'3sat','ard alpha':'ARD-alpha','ard-alpha':'ARD-alpha','alpha':'ARD-alpha','hr':'hr','rbb':'rbb','phoenix':'phoenix','sr fernsehen':'SR','das erste':'Das Erste','tagesschau24':'tagesschau24','zdfinfo':'ZDFinfo','zdfneo':'ZDFneo','arte.de':'ARTE','arte':'ARTE','srf':'SRF','orf':'ORF','kika':'KiKA','dw':'Deutsche Welle','funk.net':'funk','rb':'Radio Bremen','rbtv':'Radio Bremen','zdf-tivi':'ZDFtivi'}.get(name.lower(),name)

def enrich(title,show,description,sender):
 context=' '.join((title,show,description))
 angles=[phrase for pattern,phrase in ANGLES if re.search(pattern,description,re.I)]
 matches=list(dict.fromkeys(m.group(0) for m in SIGNAL.finditer(context)))
 topic=next(iter(matches),'Robotik')
 opening=f'In „{show}“ greift {sender} das Thema {topic} auf.' if show and norm(show)!=norm(title) else f'{sender} behandelt in diesem Beitrag das Thema {topic}.'
 if angles:opening+=' Im Mittelpunkt steht '+angles[0]+('.' if len(angles)==1 else '. Ein weiterer Aspekt ist '+angles[1]+'.')
 else:opening+=' Weitere inhaltliche Einzelheiten findest du beim Sender.'
 return {'summary':opening,'topics':classify(TOPICS,context) or ['KI im Alltag'],'formats':classify(FORMATS,show+' '+title+' '+description[:160])}

def german_tv(title,show):
 return not (any(re.match(r'^(?:Ö[13]\b|Radio |FM4\b)',v,re.I) for v in (title,show)) or re.search(r'\((?:englisch|französisch|italienisch)\)',title,re.I))

def mv(term,now):
 query={'queries':[{'fields':['title','topic','description'],'query':term}],'sortBy':'timestamp','sortOrder':'desc','future':False,'offset':0,'size':30}
 result=read('https://mediathekviewweb.de/api/query',query)['result']['results']
 if not isinstance(result,list):raise ValueError('unexpected MediathekViewWeb response')
 rows=[]
 for item in result:
  title,desc,show=map(clean,[item.get('title'),item.get('description'),item.get('topic')])
  sender=station(clean(item.get('channel')))
  url=canonical(item.get('url_website',''))
  if sender.startswith('ARTE.') or (sender=='ARTE' and '/de/' not in url) or not german_tv(title,show) or 'Ö3-KI-Check' in desc:continue
  if '/play/radio/' in url or urlsplit(url).hostname in {'sound.orf.at','oe1.orf.at'}:continue
  date=int(item.get('timestamp') or 0)
  if not(sender and title and url and 0<date<=now.timestamp() and relevant(title+' '+show,desc)):continue
  rows.append(dict(url=url,title=title,sender=sender,show=show,date=date,duration=item.get('duration') or 0,
                   id=str(item.get('id') or ''),**enrich(title,show,desc,sender),_desc=desc))
 return rows

def official_match(ep,day):
 # Resolve a TVmaze candidate against the broadcaster's own programme for this date.
 url='https://programm-api.ard.de/program/api/program?day='+day.isoformat()
 schedule=read(url)
 start=datetime.fromisoformat(ep['airstamp']).timestamp()
 for channel in schedule['channels']:
  for slot in channel['timeSlots']:
   for program in slot:
    when=datetime.fromisoformat(program['broadcastedOn']).timestamp()
    title=clean(program['title']); sender=station(program['channel']['name'])
    if abs(start-when)>60 or norm(sender)!=norm(station(ep['show']['network']['name'])):continue
    if norm(title) not in (norm(ep['name']),norm(ep['show']['name'])):continue
    return {'title':title,'sender':sender,'date':int(when),'duration':program.get('duration',0),
            'url':'https://www.ardmediathek.de/tv-programm/'+program['id'],**enrich(title,ep['show']['name'],clean(ep.get('summary')),sender)}
 return None

def tv_day(day,now):
 episodes=read('https://api.tvmaze.com/schedule?country=DE&date='+day.isoformat())
 if not isinstance(episodes,list):raise ValueError('unexpected TVmaze response')
 rows=[]
 for ep in episodes:
  show=ep.get('show') or {}
  if not(show.get('network') and ep.get('airstamp') and ep.get('name')):continue
  if datetime.fromisoformat(ep['airstamp'])<=now or not relevant(ep['name']+' '+show['name'],clean(ep.get('summary'))):continue
  match=official_match(ep,day)
  if match:rows.append(match)
 return rows

def link_check(item):
 try:
  req=Request(item['url'],method='HEAD',headers={'User-Agent':'ODPC-Linkcheck/2.0 (+https://odpc.de/ki-mediathek)'})
  with urlopen(req,timeout=7) as r:
   return 'ok' if canonical(r.url) else 'unknown'
 except HTTPError as e:return 'gone' if e.code in (404,410) else 'unknown'
 except OSError:return 'unknown'

def collect(old, now):
 config=json.loads((ROOT/'editorial.json').read_text())
 rows={i['url']:dict(i) for i in old.get('archived',[])+old.get('items',[])}
 errors=[]; successes=0; debug={}
 for term in TERMS:
  try:
   found=mv(term,now)
   for i in found:
    desc=i.pop('_desc')
    prior=rows.get(i['url'],{})
    rows[i['url']]={**prior,**i}
    debug[i['url']]=[i['sender'],i['title'],desc]
   successes+=1
  except (OSError,ValueError,KeyError) as e:errors.append('MediathekViewWeb '+term+': '+type(e).__name__)
  time.sleep(.35)
 if not successes or not rows:raise RuntimeError('No usable catalogue. Keep previous snapshot.')
 if not debug:raise RuntimeError('No matching media returned. Keep previous snapshot.')
 for item in rows.values():
  item.update(config['media'].get(item['url'],{}))
 ordered=sorted((i for i in rows.values() if german_tv(i['title'],i.get('show',''))),key=lambda i:(-i['date'],i['url']))
 unique=[];seen=set()
 for item in ordered:
  keys=[('url',item['url']),('title',norm(item['sender']),norm(item['title']),item['date'])]
  if item.get('id'):keys.append(('id',item['id'],norm(item['sender']),item['date']))
  if any(k in seen for k in keys):continue
  seen.update(keys)
  if not item.get('expires') or item['expires']>now.timestamp():unique.append(item)
 # ARTE publishes the broadcast times on the original programme pages.
 detail_inputs=[i for i in unique if i['sender']=='ARTE'][:20]
 detail_inputs += sorted((i for i in unique if 'ardmediathek.de/' in i['url']),key=lambda i:i.get('detail_checked',0))[:12]
 def inspect(item):
  try:return fetch_dates(item['url'])
  except (OSError,KeyError,ValueError):return None
 arte_ok=False
 with ThreadPoolExecutor(max_workers=3) as pool:
  for item,extra in zip(detail_inputs,pool.map(inspect,detail_inputs)):
   if extra is None:continue
   meta,slots=extra
   item.update(meta);item['detail_checked']=int(now.timestamp())
   if item['sender']=='ARTE' and meta:
    arte_ok=True;item['broadcasts']=slots
 unique=[i for i in unique if not i.get('expires') or i['expires']>now.timestamp()]
 # Rotate link checks, with a bounded per-host workload and reversible exclusion.
 sample=sorted(unique,key=lambda i:i.get('checked',0))[:32]
 with ThreadPoolExecutor(max_workers=3) as pool:
  for item,status in zip(sample,pool.map(link_check,sample)):
   item['checked']=int(now.timestamp())
   if status!='unknown':item['gone']=(status=='gone')
 active=[i for i in unique if not i.get('gone')][:160]
 if not active:raise RuntimeError('No usable media after link checks.')
 end=(now+timedelta(days=14)).replace(hour=0,minute=0,second=0,microsecond=0)
 upcoming={i['url']+'@'+str(i['date']):i for i in old.get('upcoming',[]) if now.timestamp()<i['date']<end.timestamp()}
 days_ok=0
 for n in range(14):
  day=now.date()+timedelta(days=n)
  try:
   batch=tv_day(day,now)
   upcoming={k:i for k,i in upcoming.items() if i.get('source')!='TVmaze' or datetime.fromtimestamp(i['date'],BERLIN).date()!=day}
   upcoming.update({i['url']+'@'+str(i['date']):{**i,'source':'TVmaze'} for i in batch})
   days_ok+=1
  except (OSError,ValueError,KeyError) as e:errors.append('TVmaze '+str(day)+': '+type(e).__name__)
  time.sleep(.3)
 upcoming={k:v for k,v in upcoming.items() if v.get('source')!='ARTE'}
 for item in unique:
  for date in item.get('broadcasts',[]):
   if now.timestamp()<date<end.timestamp():
    key=item['url']+'@'+str(date)
    upcoming[key]={**{k:item[k] for k in ('title','url','sender','duration','summary','formats','topics')},'date':date,'source':'ARTE'}
 for item in config['upcoming']:
  date=int(datetime.fromisoformat(item['date']).timestamp())
  if now.timestamp()<date<end.timestamp():
   if not any(v['url'].rstrip('/')==item['url'].rstrip('/') and v['date']==date for v in upcoming.values()):
    upcoming[item['url']]={**item,'date':date,'source':'Redaktion'}
 if now.date().isoformat()==config.get('reviewed_date'):
  print('Editorially checked programme links included.')
 result={'version':2,'updated':int(now.timestamp()),'complete':successes==len(TERMS) and days_ok==14,
         'items':active,'upcoming':sorted(upcoming.values(),key=lambda i:i['date']),'days_ok':days_ok,
         'queries_ok':successes,'errors':errors,'sources':['MediathekViewWeb']+(['TVmaze'] if days_ok else [])+(['ARTE'] if arte_ok else []),
         'archived':[i for i in unique if i.get('gone') or i not in active][:200]}
 print(f"Queries {successes}/{len(TERMS)}, programme days {days_ok}/14, media {len(active)}, broadcasts {len(upcoming)}",flush=True)
 for e in errors:print(e)
 return result,debug

if __name__=='__main__':
 parser=argparse.ArgumentParser();parser.add_argument('--previous',type=Path);parser.add_argument('--out',required=True,type=Path);parser.add_argument('--research',type=Path)
 args=parser.parse_args()
 old=json.loads(args.previous.read_text()) if args.previous and args.previous.exists() else {}
 data,debug=collect(old,datetime.now(BERLIN))
 args.out.write_text(json.dumps(data,ensure_ascii=False))
 if args.research:args.research.write_text(json.dumps(debug,ensure_ascii=False))
