"""D.R.O.L. daily source health monitor; never promotes claims automatically."""
import datetime
import json
import urllib.request
import urllib.error
from pathlib import Path
from urllib.parse import urlparse

root=Path("site-deploy/drol.odpc.de/database/model-schema.json")
data=json.loads(root.read_text(encoding="utf-8"))
urls=sorted({url for model in data["models"] for url in model.get("sources",[]) if isinstance(url,str) and url.startswith("https://")})
results=[]
for url in urls:
    parsed=urlparse(url)
    if not parsed.hostname:
        continue
    req=urllib.request.Request(url,headers={"User-Agent":"DROL-SourceMonitor/1.0 (+https://drol.odpc.de/; documentation health check)","Accept":"text/html,application/json;q=0.9,*/*;q=0.5"},method="HEAD")
    try:
        with urllib.request.urlopen(req,timeout=12) as response:
            status=response.status
            result="reachable" if status<400 else "review"
    except urllib.error.HTTPError as exc:
        status=exc.code
        result="restricted_or_missing" if status in (403,404,405,429) else "review"
    except (urllib.error.URLError,TimeoutError,OSError) as exc:
        status=None
        result="network_unavailable"
    results.append({"url":url,"status":status,"result":result})
report={"checked_utc":datetime.datetime.now(datetime.timezone.utc).isoformat(),"source_count":len(results),"sources":results,"note":"HTTP status is not a factual verification. No technical claims are automatically approved."}
Path("drol-source-health.json").write_text(json.dumps(report,ensure_ascii=False,indent=2)+"\n",encoding="utf-8")
print("D.R.O.L. source health checked:",len(results))
for item in results:
    print(item["result"],item["status"],item["url"])
