#!/usr/bin/env python3
"""Read-only Helper Monkey product, FAQ and download quality gate."""
import json, pathlib, re, urllib.request, urllib.error, zipfile, sys
from html.parser import HTMLParser

BASE = "https://ai.odpc.de"
PAGES = {
 "home": BASE + "/",
 "start": BASE + "/start/",
 "interview": BASE + "/helper-monkey-einfach-starten-sprachassistent/",
 "business": BASE + "/helper-monkey-fuer-unternehmen-ki-kollegen/",
 "connector": BASE + "/connector-einrichten/",
 "praxis": BASE + "/praxis/",
}
class Inspector(HTMLParser):
 def __init__(self):
  super().__init__(); self.links=[]; self.faq=0; self.title=False
 def handle_starttag(self,tag,attrs):
  a=dict(attrs)
  if tag=="a" and a.get("href"): self.links.append(a["href"])
  if tag=="details": self.faq+=1
  if tag=="title": self.title=True

def fetch(url):
 req=urllib.request.Request(url,headers={"User-Agent":"ODPC-HelperMonkey-QA/4.1","Cache-Control":"no-cache"})
 with urllib.request.urlopen(req,timeout=25) as r: return r.status,r.read(1200000).decode("utf-8","replace")

results=[]
def check(label,passed,detail="",severity="FAIL"):
 results.append((label,bool(passed),str(detail)[:180],severity))

for name,url in PAGES.items():
 try:
  status,html=fetch(url); p=Inspector();p.feed(html)
  check(name+" HTTP 200",status==200,status)
  check(name+" title",p.title,"HTML title")
  check(name+" navigation links",len(p.links)>=3,str(len(p.links)))
  if name in ("start","interview","business","connector","praxis"):
   check(name+" FAQ present",p.faq>0,"details: "+str(p.faq),"WARN")
  if name in ("home","start","business"):
   check(name+" interview link",any("helper-monkey-einfach-starten-sprachassistent" in x for x in p.links),"interview discoverability","WARN")
  if name=="interview":
   check("interview wizard marker",'id="odpc-voice"' in html and 'id="hm-connector-mount"' in html,"wizard DOM marker")
 except Exception as e:
  check(name+" fetch",False,type(e).__name__+": "+str(e))
for file in sorted(pathlib.Path("downloads").glob("*.zip")):
 try:
  with zipfile.ZipFile(file) as z:
   bad=z.testzip()
   check("ZIP "+file.name,bad is None and len(z.namelist())>0,"entries="+str(len(z.namelist()))+"; first bad="+str(bad))
 except Exception as e: check("ZIP "+file.name,False,str(e))
manifest=pathlib.Path("downloads/multilingual-v3.1-build-manifest.json")
check("versioned build manifest",manifest.exists(),"v3.1")
if manifest.exists():
 try:
  data=json.loads(manifest.read_text(encoding="utf-8"))
  check("manifest valid JSON",isinstance(data,(list,dict)),type(data).__name__)
 except Exception as e:check("manifest valid JSON",False,str(e))
fails=sum(not ok and severity=="FAIL" for _,ok,_,severity in results)
warns=sum(not ok and severity=="WARN" for _,ok,_,severity in results)
lines=["# Helper Monkey V4.1 automated read-only audit","",f"Checks: {len(results)} · failures: {fails} · warnings: {warns}","",
"| Check | Result | Detail |","|---|---|---|"]
for label,ok,detail,severity in results:
 lines.append("| "+label.replace("|","/")+" | "+("PASS" if ok else severity)+" | "+detail.replace("|","/").replace("\n"," ")+" |")
report="\n".join(lines)+"\n"
pathlib.Path("helpermonkey-v41-audit.md").write_text(report,encoding="utf-8")
print(report)
if __name__=="__main__":sys.exit(1 if fails else 0)
