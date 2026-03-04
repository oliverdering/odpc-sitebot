import fs from "fs";
import fetch from "node-fetch";
import cheerio from "cheerio";

const BASE = "https://odpc.de";

async function crawl(url) {
  const res = await fetch(url);
  const html = await res.text();
  const $ = cheerio.load(html);

  const text = $("body").text().replace(/\s+/g, " ").trim();

  return {
    url,
    text
  };
}

(async () => {
  const page = await crawl(BASE);

  const data = {
    built: new Date(),
    pages: [page]
  };

  fs.mkdirSync("public", { recursive: true });
  fs.writeFileSync("public/index.json", JSON.stringify(data));

  console.log("Index built");
})();
