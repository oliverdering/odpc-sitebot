"""Load documented aircraft firmware releases. Titles, dates and page locators only."""
import argparse
import hashlib
import json
import re
import subprocess
from datetime import date
from pathlib import Path
from urllib.parse import urlsplit

DATABASE = Path(__file__).resolve().parents[1] / "site-deploy/drol.odpc.de/database"
MANIFEST = DATABASE / "reviewed-firmware.json"


def load_documents(path=MANIFEST):
    data = json.loads(Path(path).read_text(encoding="utf-8"))
    catalog = json.loads((DATABASE / "model-schema.json").read_text(encoding="utf-8"))
    models = {(r["manufacturer"], r["name"]) for r in catalog["models"]}
    documents = data["documents"]
    if data["schema_version"] != 1 or not documents:
        raise ValueError("Invalid firmware document manifest")
    seen = set()
    for doc in documents:
        model = doc["model"]
        if (doc["manufacturer"], model) not in models or doc["manufacturer"] != "DJI":
            raise ValueError("Unknown manufacturer/model")
        if doc["kind"] != "aircraft_firmware" or doc["evidence"] != "E1":
            raise ValueError("Unreviewed or unsupported release class")
        url = urlsplit(doc["source_url"])
        prefix = "DJI_" + model.replace(" ", "_")
        pattern = rf"/downloads/{re.escape(prefix)}/(?:RN/)?[0-9]{{8}}/{re.escape(prefix)}_Release_Notes_EN\.pdf"
        if (url.scheme, url.netloc, url.query, url.fragment) != ("https", "dl.djicdn.com", "", "") or not re.fullmatch(pattern, url.path):
            raise ValueError("Invalid model-specific manufacturer PDF")
        if not re.fullmatch(r"[a-f0-9]{64}", doc["source_sha256"]):
            raise ValueError("Invalid reviewed source digest")
        checked = date.fromisoformat(doc["checked_on"])
        if checked > date.today() or not doc["releases"]:
            raise ValueError("Invalid document review date or empty document")
        for release in doc["releases"]:
            version = release["version"]
            if not re.fullmatch(r"v\d{2}\.\d{2}\.\d{4}", version) or (model, version) in seen:
                raise ValueError("Invalid or duplicate aircraft version")
            if date.fromisoformat(release["released_on"]) > checked or type(release["page"]) != int or release["page"] < 1:
                raise ValueError("Invalid release date or page")
            seen.add((model, version))
    return documents


def extract_pages(model, pdf):
    """Extract the aircraft field for just one model. Keep same-version/date conflicts visible."""
    text = subprocess.run(["pdftotext", "-layout", str(pdf), "-"], check=True,
                          capture_output=True, timeout=30).stdout.decode("utf-8")
    records = []
    for number, page in enumerate(text.split("\f"), start=1):
        if not page.strip():
            continue
        title = "DJI " + model + " Release Notes"
        dates = re.findall(r"^\s*Date:\s*(\d{4})[./](\d{1,2})[./](\d{1,2})\s*$", page, re.M | re.I)
        versions = re.findall(r"^\s*Aircraft Firmware:\s*(v\d{2}\.\d{2}\.\d{4})\s*$", page, re.M | re.I)
        if title not in " ".join(page.split()) or len(dates) != 1 or len(versions) != 1:
            continue
        records.append({"version": versions[0], "released_on": date(*map(int, dates[0])).isoformat(), "page": number})
    if not records:
        raise ValueError(f"No document pages for {model} could be parsed")
    return records


def verify_pdf(document, pdf):
    """Match reviewed fields to the named manufacturer's page. Ignore RC and app rows."""
    if hashlib.sha256(Path(pdf).read_bytes()).hexdigest() != document["source_sha256"]:
        raise ValueError("PDF differs from reviewed source")
    rows = extract_pages(document["model"], pdf)
    for record in document["releases"]:
        if record not in rows:
            raise ValueError(f"{document['model']}, page {record['page']}: manufacturer fields differ")
        if any(other["version"] == record["version"] and other["released_on"] != record["released_on"] for other in rows):
            raise ValueError(f"{document['model']} {record['version']}: ambiguous release date")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--pdf-dir", type=Path, help="Verify downloaded primary PDFs in this directory")
    parser.add_argument("--extract-pdf", type=Path, help="Extract new document fields for E0 review")
    parser.add_argument("--model", help="Exact model in the manufacturer's title, for --extract-pdf")
    args = parser.parse_args()
    if args.extract_pdf:
        if not args.model:
            parser.error("--extract-pdf requires --model")
        print(json.dumps({"evidence": "E0", "model": args.model, "releases": extract_pages(args.model, args.extract_pdf)}, indent=2))
        raise SystemExit(0)
    documents = load_documents()
    if args.pdf_dir:
        for doc in documents:
            verify_pdf(doc, args.pdf_dir / Path(urlsplit(doc["source_url"]).path).name)
    print(sum(len(doc["releases"]) for doc in documents), "reviewed aircraft releases")
