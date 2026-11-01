#!/usr/bin/env python3
"""Read-only HLS candidate audit. No automatic publication or rights inference."""
import argparse, concurrent.futures, datetime, json, re, urllib.parse, urllib.request
from pathlib import Path

UA = {"User-Agent": "ODPC-TV-Research/1.0"}
def fetch(url, limit=1024*1024):
    req=urllib.request.Request(url,headers=UA)
    with urllib.request.urlopen(req,timeout=12) as resp:
        data=resp.read(limit)
        return resp.status, dict(resp.headers), data,resp.geturl()
def parse_m3u(text):
    name=None
    for line in text.splitlines():
        line=line.strip()
        if line.startswith("#EXTINF:"):
            name=line.rsplit(",",1)[-1].strip()
        elif line and not line.startswith("#") and name:
            yield {"name":name,"url":line}
            name=None
def audit(item):
    out=dict(name=item["name"],url=item["url"],available=None,quality=None,logo=None,
        tested_at=datetime.datetime.now(datetime.timezone.utc).isoformat(),
        http_status=None,hls_manifest_valid=False,segments_accessible=False,
        browser_playback_verified=None,cors_verified=None,drm=None,
        authentication_required=None,embedding_permission="unknown",notes="")
    try:
        status,headers,body,final=fetch(item["url"])
        out["http_status"]=status
        out["cors_verified"]="*" in headers.get("Access-Control-Allow-Origin","") or "https://odpc.de" in headers.get("Access-Control-Allow-Origin","")
        manifest=body.decode("utf-8-sig","replace")
        if "#EXTM3U" not in manifest[:100]:raise ValueError("Not an HLS manifest")
        out["hls_manifest_valid"]=True
        out["drm"]=bool(re.search(r"#EXT-X-KEY:.*(?:KEYFORMAT=|METHOD=SAMPLE-AES)",manifest))
        variants=re.findall(r"RESOLUTION=\\d+x(\\d+)",manifest)
        if variants:out["quality"]=str(max(map(int,variants)))+"p"
        links=[line.strip() for line in manifest.splitlines() if line.strip() and not line.startswith("#")]
        if not links:raise ValueError("No media references")
        first=urllib.parse.urljoin(final,links[0])
        _,_,data,child=fetch(first,limit=65536)
        if b"#EXTM3U" in data[:100]:
            childtext=data.decode("utf-8-sig","replace")
            out["drm"]=out["drm"] or bool(re.search(r"#EXT-X-KEY:.*(?:KEYFORMAT=|METHOD=SAMPLE-AES)",childtext))
            segs=[x.strip() for x in childtext.splitlines() if x.strip() and not x.startswith("#")]
            if segs:
                _,_,segment,_=fetch(urllib.parse.urljoin(child,segs[0]),limit=4096)
                out["segments_accessible"]=bool(segment)
        else:out["segments_accessible"]=bool(data)
        out["notes"]="HTTP/manifest/segment probe only; browser and licensing not verified"
    except Exception as exc:out["notes"]=str(exc)[:240]
    return out

def main():
    p=argparse.ArgumentParser()
    p.add_argument("--playlist",default="https://iptv-org.github.io/iptv/countries/de.m3u")
    p.add_argument("--output",default="tv-audit-results.json")
    p.add_argument("--limit",type=int,default=60)
    p.add_argument("--workers",type=int,default=6)
    args=p.parse_args()
    if args.playlist.startswith("https://"):
        _,_,data,_=fetch(args.playlist,limit=8*1024*1024)
        text=data.decode("utf-8-sig","replace")
    else:text=Path(args.playlist).read_text(encoding="utf-8-sig")
    items=list(parse_m3u(text))[:max(0,args.limit)]
    with concurrent.futures.ThreadPoolExecutor(max_workers=max(1,min(args.workers,12))) as pool:
        results=list(pool.map(audit,items))
    Path(args.output).write_text(json.dumps(results,ensure_ascii=False,indent=2)+"\n",encoding="utf-8")
    print(f"Audited {len(results)} candidates; none marked browser-verified or licensed.")

if __name__=="__main__":main()
