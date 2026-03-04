import fs from "fs";
import cheerio from "cheerio";

const BASE = "https://odpc.de";

async function crawl(url) {
  const res = await fetch(url, {
    headers: { "user-agent": "odpc-sitebot/1.0 (+https://odpc.de)" }
  });

  if (!res.ok) {
    throw new Error(`Fetch failed: ${res.status} ${res.statusText} (${url})`);
  }

  const html = await res.text();
  const $ = cheerio.load(html);

  const text = $("body").text().replace(/\s+/g, " ").trim();

  return { url, text };
}

(async () => {
  const page = await crawl(BASE);

  const data = {
    built: new Date().toISOString(),
    pages: [page]
  };

  fs.writeFileSync("index.json", JSON.stringify(data, null, 2), "utf8");
  console.log("Index built -> index.json");
})();