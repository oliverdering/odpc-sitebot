"""Discover upstream UAV software releases and open deduplicated review issues.

A release is a *candidate*, not evidence of model compatibility. Never writes
unreviewed technical claims to the public database.
"""
import json,os,urllib.request,urllib.error
from datetime import datetime,timezone
from pathlib import Path

REPOS={
 "ArduPilot":"ArduPilot/ardupilot",
 "PX4":"PX4/PX4-Autopilot",
 "MAVSDK":"mavlink/MAVSDK",
 "QGroundControl":"mavlink/qgroundcontrol",
 "OpenDroneMap":"OpenDroneMap/ODM",
 "ROS 2":"ros2/ros2",
 "OpenCV":"opencv/opencv",
 "COLMAP":"colmap/colmap",
 "MAVLink protocol":"mavlink/mavlink",
}
API="https://api.github.com"
TOKEN=os.environ.get("GH_TOKEN","")
def request(url,method="GET",payload=None):
    headers={"Accept":"application/vnd.github+json","User-Agent":"DROL-Knowledge-Watch/1.0"}
    if TOKEN:headers["Authorization"]="Bearer "+TOKEN
    body=json.dumps(payload).encode() if payload is not None else None
    if body is not None:headers["Content-Type"]="application/json"
    with urllib.request.urlopen(urllib.request.Request(url,headers=headers,data=body,method=method),timeout=25) as r:
        return json.load(r)
def main():
    out=[]
    for product,repo in REPOS.items():
        try:
            release=request(f"{API}/repos/{repo}/releases/latest")
            tag=release.get("tag_name")
            url=release.get("html_url")
            if not tag or not url or not url.startswith("https://github.com/"):raise ValueError("invalid release")
            item={"product":product,"repository":repo,"tag":tag,"url":url,
                  "published_at":release.get("published_at"),"status":"UNREVIEWED",
                  "evidence_level":"E0","model_compatibility":"UNKNOWN"}
            out.append(item)
            if TOKEN and os.environ.get("GITHUB_REPOSITORY"):
                target=os.environ["GITHUB_REPOSITORY"]
                marker=f"drol-release:{repo}:{tag}"
                title=f"[D.R.O.L. Review] {product} {tag}"
                issues=request(f"{API}/repos/{target}/issues?state=all&per_page=100")
                if not any(marker in (i.get("body") or "") for i in issues if "pull_request" not in i):
                    body=(f"Automatisch entdeckter Software-Release-Kandidat.\\n\\n"
                          f"- Projekt: {product}\\n- Upstream: {repo}\\n- Version: {tag}\\n"
                          f"- Primärquelle: {url}\\n- Veröffentlicht: {release.get('published_at') or 'unbekannt'}\\n"
                          f"- Evidenz: E0 / UNREVIEWED\\n- Modell- und Firmware-Kompatibilität: UNKNOWN\\n\\n"
                          "Prüfauftrag: offizielle Release Notes lesen; sicherheitsrelevante Änderungen, "
                          "API-Brüche und betroffene Versionen prüfen. Erst nach redaktioneller Prüfung "
                          "als Fakten in die D.R.O.L.-Datenbank übernehmen.\\n\\n"
                          f"<!-- {marker} -->")
                    request(f"{API}/repos/{target}/issues","POST",{"title":title,"body":body})
                    print("CREATED review issue:",title)
                else:print("KNOWN candidate:",title)
        except (urllib.error.URLError,ValueError,KeyError,TimeoutError) as exc:
            print("WARN: upstream unavailable",repo,type(exc).__name__)
    report={"checked_utc":datetime.now(timezone.utc).isoformat(),
            "candidates":out,"policy":"All releases are E0 until reviewed; no automatic compatibility assertions."}
    Path("drol-release-candidates.json").write_text(json.dumps(report,indent=2,ensure_ascii=False)+"\n",encoding="utf-8")
    if not out:raise RuntimeError("No upstream releases accessible")
    print("PASS: release candidates checked:",len(out))
if __name__=="__main__":main()
