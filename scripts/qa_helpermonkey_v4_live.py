#!/usr/bin/env python3
"""External read-only acceptance tests for ODPC Helper Monkey V4 sites."""
from urllib.request import Request,urlopen
from urllib.error import HTTPError
import json,sys,time,os
BASES={
 "ai-home":"https://ai.odpc.de/",
 "ai-onboarding":"https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/",
 "ai-connector":"https://ai.odpc.de/connector-einrichten/",
 "ai-business":"https://ai.odpc.de/helper-monkey-fuer-unternehmen-ki-kollegen/",
 "odpc-home":"https://odpc.de/",
 "odpc-initiQ":"https://odpc.de/initiq",
 "life-orientation":"https://odpc.de/l-i-f-e-mpu-orientierung-und-vorbereitung-im-aufbau/",
 "life-lessons":"https://odpc.de/l-i-f-e-mpu-selbstlernkurs-neun-lernmodule/"
}
def fetch(url):
    req=Request(url+("?v4q="+str(int(time.time())) if "?" not in url else "&v4q="+str(int(time.time()))),headers={"User-Agent":"ODPC-QA/1.0","Cache-Control":"no-cache"})
    with urlopen(req,timeout=35) as h:
        return h.status,h.read(650000).decode("utf-8","replace")
def run():
    report=[]
    pages={}
    for key,url in BASES.items():
        try:
            status,data=fetch(url)
            pages[key]=data
            report.append((key+" HTTP",status==200,str(status)))
        except Exception as error:
            report.append((key+" HTTP",False,str(error)))
    expected={
        "ai-home":["hm-v4-global-js","hm-v4-helpbtn","hm-v4-wa","hm-v4-ribbon"],
        "ai-onboarding":["hmv4-wizard","hmv4-voice","hmv4-faqlist","hmv4-wa","Mehrere Antworten"],
        "ai-connector":["hm-v4-steps","hm-v4-global-js"],
        "ai-business":["hm-business","Finance Monkey","hm-v4-global-js"],
        "odpc-home":["odpc-hm-v4-js","odpc-hm-v4-helpbtn","odpc-hm-v4-flags"],
        "odpc-initiQ":["odpc-hm-v4-helpbtn","odpc-hm-v4-flags"],
        "life-orientation":["Selbstlernkurs","odpc-hm-v4-helpbtn"],
        "life-lessons":["Modul 1","Modul 9","Keine","odpc-hm-v4-helpbtn"],
    }
    for key,markers in expected.items():
        data=pages.get(key,"")
        for marker in markers:
            report.append((key+" contains "+marker,marker in data, "verified" if marker in data else "missing"))
    # Confirm SEO metadata only on public AI pages. No auth is required.
    for key in ["ai-home","ai-onboarding","ai-business"]:
        data=pages.get(key,"")
        report.append((key+" meta description",'name="description"' in data or "name='description'" in data,"present/absent"))
    try:
        req=Request("https://ai.odpc.de/wp-json/wp/v2/search?search=connector&per_page=5",headers={"User-Agent":"ODPC-QA/1.0"})
        with urlopen(req,timeout=30) as h:
            data=json.load(h)
        report.append(("Public WordPress knowledge-search endpoint",isinstance(data,list),"JSON results"))
    except Exception as error:
        report.append(("Public WordPress knowledge-search endpoint",False,str(error)))
    failed=[x for x in report if not x[1]]
    lines=["# ODPC Helper Monkey V4 acceptance report","",f"Checks: {len(report)}; passed: {len(report)-len(failed)}; failed: {len(failed)}","","| Test | Result | Detail |","|---|---|---|"]
    for label,passed,detail in report:
        lines.append("| "+label.replace("|","/")+" | "+("PASS" if passed else "FAIL")+" | "+str(detail).replace("|","/")[:150]+" |")
    summary="\n".join(lines)+"\n"
    print(summary)
    if os.getenv("GITHUB_STEP_SUMMARY"):
        with open(os.environ["GITHUB_STEP_SUMMARY"],"a",encoding="utf-8") as f:f.write(summary)
    if failed:sys.exit(1)
if __name__=="__main__":run()
