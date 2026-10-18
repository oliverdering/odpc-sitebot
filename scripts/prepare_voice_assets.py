#!/usr/bin/env python3
"""Download the pinned, hash-verified eSpeak distribution for self-hosting."""
import hashlib,json,shutil
from pathlib import Path
from urllib.request import Request,urlopen
MANIFEST=Path('site-deploy/ai.odpc.de/voice-assets/manifest.json')
CACHE=Path('voice-assets-cache')
def prepare():
    manifest=json.loads(MANIFEST.read_text())
    CACHE.mkdir(exist_ok=True)
    for item in manifest['files']:
        name=item['name']
        if name not in {'espeakng.js','espeakng.worker.js','espeakng.worker.data','LICENSE'}: raise ValueError('Unexpected asset')
        if not item['url'].startswith('https://raw.githubusercontent.com/pettarin/espeakng.js-cdn/'+manifest['commit']+'/'): raise ValueError('Unpinned asset URL')
        target=CACHE/name
        if not target.exists() or hashlib.sha256(target.read_bytes()).hexdigest()!=item['sha256']:
            with urlopen(Request(item['url'],headers={'User-Agent':'ODPC-voice-assets/1'}),timeout=45) as r:
                data=r.read(item['size']+1)
            if len(data)!=item['size'] or hashlib.sha256(data).hexdigest()!=item['sha256']: raise ValueError('Asset checksum failed: '+name)
            target.write_bytes(data)
        if target.stat().st_size!=item['size']: raise ValueError('Asset size failed: '+name)
    shutil.copyfile(MANIFEST,CACHE/'manifest.json')
    shutil.copyfile(MANIFEST.parent/'SOURCES.txt',CACHE/'SOURCES.txt')
    return CACHE
if __name__=='__main__':
    print('PASS: pinned local speech assets verified:',prepare())
