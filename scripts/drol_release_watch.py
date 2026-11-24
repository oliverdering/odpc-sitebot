"""Discover upstream UAV software releases without creating public issues.

A release is a *candidate*, not evidence of model compatibility. Never writes
unreviewed technical claims to the public database.
"""
import json,os,re,hashlib,urllib.request,urllib.error
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
 "Betaflight":"betaflight/betaflight",
 "INAV":"iNavFlight/inav",
 "ExpressLRS":"ExpressLRS/ExpressLRS",
 "Paparazzi UAV":"paparazzi/paparazzi",
}
API="https://api.github.com"
TOKEN=os.environ.get("GH_TOKEN","")
def request(url,method="GET",payload=None):
    if method != "GET" or payload is not None: raise ValueError("Discovery is read-only")
    headers={"Accept":"application/vnd.github+json","User-Agent":"DROL-Knowledge-Watch/1.0"}
    if TOKEN:headers["Authorization"]="Bearer "+TOKEN
    body=json.dumps(payload).encode() if payload is not None else None
    if body is not None:headers["Content-Type"]="application/json"
    with urllib.request.urlopen(urllib.request.Request(url,headers=headers,data=body,method=method),timeout=25) as r:
        return json.load(r)
def summarize_release_notes(raw):
    """Bounded upstream note extraction; E0 only, never inferred compatibility."""
    if not isinstance(raw,str):return {"excerpt":"","sha256":None,"topics":[]}
    clean=re.sub(r"<[^>]{0,200}>"," ",raw)
    clean=re.sub(r"\s+"," ",clean).strip()
    keywords={"security":r"\b(security|vulnerabilit|cve-|authentication)\b",
              "api_change":r"\b(api|deprecated|breaking change|migration)\b",
              "firmware":r"\b(firmware|bootloader|flight controller)\b",
              "sensor":r"\b(lidar|camera|imu|gps|gnss|sensor)\b",
              "navigation":r"\b(navigation|mission|waypoint|geofence)\b"}
    topics=[name for name,pattern in keywords.items() if re.search(pattern,clean,re.I)]
    return {"excerpt":clean[:1200],"sha256":hashlib.sha256(raw.encode()).hexdigest(),"topics":topics}

def release_markers(issues):
    """Dedup markers (drol-release:owner/repo:tag) already present in issue bodies."""
    return {marker for issue in issues
            for marker in re.findall(r"drol-release:[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+:[^\s<>]+",issue.get("body") or "")}
def no_notifications(text):
    """Imported upstream text must not notify anyone. GitHub sends e-mails for @mentions and adds
    cross-references for #123 / owner/repo#123, also inside backticks that the text itself
    unbalances. Replace the characters instead of quoting: every @ (also written as an HTML
    entity) becomes a full-width at sign and # before a digit a full-width number sign."""
    text=re.sub(r"&(?:#0*64|#x0*40|commat);?","@",str(text),flags=re.I)
    text=text.replace("@","\uff20")
    return re.sub(r"#(?=\s*\d)","\uff03",text)
def main():
    out=[]
    for product,repo in REPOS.items():
        try:
            release=request(f"{API}/repos/{repo}/releases/latest")
            tag=release.get("tag_name")
            url=release.get("html_url")
            if not tag or not url or not url.startswith("https://github.com/"):raise ValueError("invalid release")
            notes=summarize_release_notes(release.get("body",""))
            item={"product":product,"repository":repo,"tag":tag,"url":url,"release_notes":notes,
                  "published_at":release.get("published_at"),"status":"UNREVIEWED",
                  "evidence_level":"E0","model_compatibility":"UNKNOWN"}
            out.append(item)
        except (urllib.error.URLError,ValueError,KeyError,TimeoutError) as exc:
            print("WARN: upstream unavailable",repo,type(exc).__name__)
    report={"checked_utc":datetime.now(timezone.utc).isoformat(),
            "candidates":out,"policy":"All releases are E0 until reviewed; no automatic compatibility assertions."}
    Path("drol-release-candidates.json").write_text(json.dumps(report,indent=2,ensure_ascii=False)+"\n",encoding="utf-8")
    if not out:raise RuntimeError("No upstream releases accessible")
    print("PASS: release candidates checked:",len(out))
if __name__=="__main__":main()
