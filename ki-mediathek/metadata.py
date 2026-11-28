"""Read factual dates on the linked original; never store broadcaster text or images."""
from datetime import datetime
import json
import re
from urllib.parse import urlsplit
from urllib.request import Request,urlopen

def stamp(value):
    if not isinstance(value,str):return 0
    try:
        parsed=datetime.fromisoformat(value.replace('Z','+00:00'))
        return int(parsed.timestamp()) if parsed.tzinfo else 0
    except ValueError:return 0

def dates(url,html):
    parts=urlsplit(url); result={}; broadcasts=[]
    if parts.hostname in ('www.ardmediathek.de','ardmediathek.de'):
        tag=re.search(r'<script id="fetchedContextValue"[^>]*>(.*?)</script>',html,re.S)
        for endpoint,state in json.loads(tag[1]) if tag else []:
            if '/item/' not in endpoint:continue
            for widget in state.get('data',{}).get('widgets',[]):
                if widget.get('type')=='player_ondemand' and widget.get('id')==parts.path.rstrip('/').split('/')[-1]:
                    result={'expires':stamp(widget.get('availableTo')),'published':stamp(widget.get('availableFrom'))}
    if parts.hostname in ('www.arte.tv','arte.tv'):
        program=re.search(r'/videos/([\w-]+)/',parts.path)
        if not program:return {},[]
        for tag in re.findall(r'<script[^>]+type="application/ld\+json"[^>]*>(.*?)</script>',html,re.S):
            data=json.loads(tag)
            if data.get('@type')=='VideoObject' and '/'+program[1]+'/' in data.get('url',''):
                result['published']=stamp(data.get('uploadDate'))
        # Read the programme object in the public German programme detail page.
        flat=html.replace(r'\"','"')
        for pos in re.finditer('"broadcastDates":',flat):
            prefix=flat[max(0,pos.start()-18000):pos.start()]
            if '/'+program[1]+'/' not in prefix and program[1] not in prefix[-3000:]:continue
            # The tracking ID immediately preceding the availability belongs to the same item.
            ids=re.findall(r'trackingPixel":"/ct/\?em=([\w-]+)',prefix)
            if not ids or ids[-1]!=program[1]:continue
            values,_=json.JSONDecoder().raw_decode(flat[pos.end():])
            broadcasts=[stamp(v) for v in values if stamp(v)]
            availability=prefix.rsplit('"availability":',1)
            if len(availability)==2:
                av,_=json.JSONDecoder().raw_decode(availability[1])
                result['expires']=stamp(av.get('end'))
            break
    return {k:v for k,v in result.items() if v},broadcasts

def fetch_dates(url):
    req=Request(url,headers={'User-Agent':'ODPC-KI-Mediathek/2.0 (+https://odpc.de/ki-mediathek)'})
    with urlopen(req,timeout=9) as resp:
        return dates(url,resp.read(2400000).decode('utf8'))
