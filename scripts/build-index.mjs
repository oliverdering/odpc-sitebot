import fs from "fs";
import * as cheerio from "cheerio";

const BASE = "https://odpc.de";

async function crawl(url) {

  const res = await fetch(url);

  if (!res.ok) {
    throw new Error("HTTP Fehler: " + res.status);
  }

  const html = await res.text();

  const $ = cheerio.load(html);

  const text = $("body")
    .text()
    .replace(/\s+/g, " ")
    .trim();

  return {
    url,
    text
  };
}

async function run() {

  const page = await crawl(BASE);

  const data = {
    built: new Date().toISOString(),
    pages: [page]
  };

  fs.writeFileSync(
    "index.json",
    JSON.stringify(data, null, 2)
  );

  console.log("Index created");
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});