#!/usr/bin/env python3
"""Build translated-on-demand Helper Monkey starter archives from the public original ZIPs.
Preserves original file payloads, except prepending a tested locale layer to UTF-8 masterprompts.
Does not modify the paid Agent product files or existing published original ZIPs.
"""
from __future__ import annotations
import hashlib
import io
import json
import re
import urllib.request
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "downloads"
LAYER = (OUT / "helpermonkey-sprachmodul-v1.0.txt").read_text(encoding="utf-8")
SOURCES = {
  "connector": "helpermonkey-connector-3.0.0.zip",
  "lebenslauf": "helpermonkey-lebenslauf-3.0.0.zip",
  "bewerbung": "helpermonkey-bewerbung-3.0.0.zip",
  "postfach": "helpermonkey-postfach-demo-3.0.0.zip",
}
BASE = "https://ai.odpc.de/wp-content/themes/helpermonkey-business-v3/assets/downloads/"
INTRO = """HELPER MONKEY · SPRACHWAHL / LANGUAGE SELECTION
1. Open the MASTERPROMPT in your preferred supported AI chat.
2. Choose your working language at the beginning, e.g. German, Turkish, Arabic, Russian, Vietnamese, French or any language the model can handle.
3. Everything in the workflow (not only the first greeting) should be in the selected language; names, addresses, URLs and technical code stay unchanged.
4. Change language whenever needed: Sprache wechseln: Türkçe.
5. This package is NOT a standalone app or API connector. Permissions and human approval remain necessary for real account actions.
"""
OUT.mkdir(parents=True, exist_ok=True)
manifest = []
for slug, name in SOURCES.items():
    src = BASE + name
    request = urllib.request.Request(src, headers={"User-Agent": "HelperMonkey-LanguageBuilder/1.0"})
    with urllib.request.urlopen(request, timeout=35) as response:
        data = response.read(12_000_000)
        if len(data) >= 12_000_000:
            raise RuntimeError("Source too large: " + name)
    original_hash = hashlib.sha256(data).hexdigest()
    with zipfile.ZipFile(io.BytesIO(data)) as z:
        assert z.testzip() is None, name + ": damaged source"
        file_names = [i.filename for i in z.infolist()]
        if any(n.startswith("/") or ".." in Path(n).parts for n in file_names):
            raise RuntimeError(name + ": unsafe ZIP member")
        target = OUT / f"helpermonkey-{slug}-multilingual-v3.1.zip"
        patched = 0
        with zipfile.ZipFile(target, "w") as dst:
            for member in z.infolist():
                payload = z.read(member.filename)
                basename = Path(member.filename).name.lower()
                if (re.search(r"master[-_ ]?prompt", basename) and basename.endswith((".txt", ".md", ".markdown"))):
                    try:
                        prompt = payload.decode("utf-8-sig")
                        if "HELPER MONKEY · MULTILINGUAL LANGUAGE LAYER v1.0" not in prompt:
                            payload = (LAYER + "\n\n" + prompt).encode("utf-8")
                            patched += 1
                    except UnicodeError:
                        pass
                dst.writestr(member, payload)
            dst.writestr("00-SPRACHE-WAEHLEN.txt", INTRO)
            dst.writestr("SPRACHMODUL-UNIVERSAL.txt", LAYER)
        with zipfile.ZipFile(target) as zcheck:
            assert zcheck.testzip() is None, name + ": bad output archive"
            preserved = [n for n in file_names if n in zcheck.namelist()]
            assert len(preserved) == len(file_names)
        manifest.append({
            "agent": slug, "source": src, "source_sha256": original_hash,
            "source_file_count": len(file_names), "masterprompts_patched": patched,
            "output": target.name, "output_sha256": hashlib.sha256(target.read_bytes()).hexdigest(),
            "bytes": target.stat().st_size,
        })
(OUT / "multilingual-v3.1-build-manifest.json").write_text(
    json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
)
for row in manifest:
    print(row["agent"], row["source_file_count"], "source files preserved,",
          row["masterprompts_patched"], "prompts updated,", row["bytes"], "bytes")
print("SUCCESS:", len(manifest), "multilingual V3.1 archives built, existing downloads untouched")
