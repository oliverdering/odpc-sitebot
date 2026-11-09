"""Static D.R.O.L. site audit: links, language controls, page structure, design risk."""
from pathlib import Path
from html.parser import HTMLParser
from urllib.parse import urlsplit,unquote
import json,re
ROOT=Path("site-deploy/drol.odpc.de")
class Page(HTMLParser):
 def __init__(self):
  super().__init__();self.links=[];self.ids=set();self.languages=set();self.title=False;self.description=False;self.main=False;self.images=[]
 def handle_starttag(self,tag,attrs):
  a=dict(attrs)
  if tag=="a" and a.get("href"):self.links.append(a["href"])
  if a.get("id"):self.ids.add(a["id"])
  if a.get("data-drol-language"):self.languages.add(a["data-drol-language"])
  if tag=="title":self.title=True
  if tag=="meta" and a.get("name")=="description":self.description=True
  if tag=="main":self.main=True
  if tag=="img":self.images.append(a)
def main():
 pages={}
 for f in sorted(ROOT.glob("*.html")):
  p=Page();p.feed(f.read_text(encoding="utf-8"));pages[f.name]=p
 problems=[];warnings=[]
 for name,p in pages.items():
  raw=(ROOT/name).read_text(encoding="utf-8")
  if not p.title:problems.append(f"{name}: missing title")
  if not p.description:warnings.append(f"{name}: missing meta description")
  if not p.main:warnings.append(f"{name}: missing semantic main")
  if len(p.languages)<2:warnings.append(f"{name}: no integrated multilingual selector ({len(p.languages)} options)")
  if re.search(r'background(?:-color)?\s*:\s*(?:white|#fff(?:fff)?)(?:[;}]|\s*!important)',raw,re.I):
   warnings.append(f"{name}: legacy white CSS background (check theme contrast)")
  for img in p.images:
   if "alt" not in img:warnings.append(f"{name}: image without alt attribute")
  for href in p.links:
   if href.startswith(("mailto:","tel:","javascript:","data:")):continue
   u=urlsplit(href)
   if u.scheme or u.netloc:continue
   path=unquote(u.path)
   if not path and u.fragment:
    if u.fragment not in p.ids:warnings.append(f"{name}: unresolved local anchor #{u.fragment}")
    continue
   if not path:continue
   target=(ROOT/path.lstrip("/")) if path.startswith("/") else (ROOT/name).parent/path
   if target.is_dir():target=target/"index.html"
   if not target.exists() and path.lstrip("/") not in ("data/models.json","data/models-db.json"):
    problems.append(f"{name}: broken local link {href}")
   elif u.fragment and target.suffix==".html":
    dest=pages.get(target.name)
    if dest and u.fragment not in dest.ids:warnings.append(f"{name}: target anchor missing {href}")
 report={"pages":len(pages),"errors":sorted(set(problems)),"warnings":sorted(set(warnings)),
         "language_options_by_page":{k:len(v.languages) for k,v in pages.items()},
         "policy":"Static audit only; not a substitute for browser/mobile/RTL/runtime tests"}
 Path("drol-site-audit.json").write_text(json.dumps(report,indent=2,ensure_ascii=False)+"\n",encoding="utf-8")
 print(f"Audited {len(pages)} pages; {len(report['errors'])} errors; {len(report['warnings'])} warnings")
 for x in report["errors"]:print("ERROR:",x)
 for x in report["warnings"]:print("WARN:",x)
 if problems:raise SystemExit(1)
if __name__=="__main__":main()
