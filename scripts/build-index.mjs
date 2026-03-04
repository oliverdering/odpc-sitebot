import fs from "fs";
import * as cheerio from "cheerio";

const BASE = "https://odpc.de";
const USER_AGENT = "odpc-sitebot/1.0 (+https://odpc.de)";
const OUTFILE = "index.json";

// Performance/Robustheit
const CONCURRENCY = 5;
const TIMEOUT_MS = 20000;
const RETRIES = 2;

// Filter (damit wir keinen Müll indexieren)
const SKIP_PATTERNS = [
  /\/wp-json\/?/i,
  /\/wp-admin\/?/i,
  /\/xmlrpc\.php$/i,
  /\/wp-content\/uploads\/?/i,
  /\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|rar|7z|mp4|mov|avi|mp3|wav)$/i,
  /#/
];

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

async function fetchWithTimeout(url) {
  const controller = new AbortController();
  const t = setTimeout(() => controller.abort(), TIMEOUT_MS);
  try {
    const res = await fetch(url, {
      signal: controller.signal,
      headers: { "user-agent": USER_AGENT }
    });
    return res;
  } finally {
    clearTimeout(t);
  }
}

async function getText(url) {
  let lastErr;
  for (let i = 0; i <= RETRIES; i++) {
    try {
      const res = await fetchWithTimeout(url);
      if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText}`);
      return await res.text();
    } catch (e) {
      lastErr = e;
      if (i < RETRIES) await sleep(500 * (i + 1));
    }
  }
  throw new Error(`Fetch failed after retries: ${url} -> ${lastErr?.message || lastErr}`);
}

function normalizeUrl(u) {
  try {
    const url = new URL(u, BASE);
    url.hash = "";
    // Nur odpc.de
    if (url.origin !== new URL(BASE).origin) return null;
    return url.toString();
  } catch {
    return null;
  }
}

function shouldSkip(url) {
  return SKIP_PATTERNS.some((rx) => rx.test(url));
}

async function loadSitemapUrls() {
  // WordPress: meist sitemap_index.xml, fallback sitemap.xml
  const candidates = [`${BASE}/sitemap_index.xml`, `${BASE}/sitemap.xml`];

  let xml = null;
  let used = null;

  for (const sm of candidates) {
    try {
      xml = await getText(sm);
      used = sm;
      break;
    } catch {
      // next
    }
  }
  if (!xml) throw new Error("No sitemap found (sitemap_index.xml / sitemap.xml)");

  const $ = cheerio.load(xml, { xmlMode: true });

  // sitemap index?
  const sitemapLocs = $("sitemap > loc")
    .map((_, el) => $(el).text().trim())
    .get()
    .map(normalizeUrl)
    .filter(Boolean);

  // direkte urlset?
  const urlLocsDirect = $("url > loc")
    .map((_, el) => $(el).text().trim())
    .get()
    .map(normalizeUrl)
    .filter(Boolean);

  let urls = [];

  if (sitemapLocs.length > 0) {
    // Index -> alle Sub-Sitemaps laden
    console.log(`Sitemap index found: ${used} (${sitemapLocs.length} sub-sitemaps)`);
    for (const smUrl of sitemapLocs) {
      try {
        const subXml = await getText(smUrl);
        const $$ = cheerio.load(subXml, { xmlMode: true });
        const locs = $$("url > loc")
          .map((_, el) => $$(el).text().trim())
          .get()
          .map(normalizeUrl)
          .filter(Boolean);
        urls.push(...locs);
      } catch (e) {
        console.log(`WARN: sub-sitemap failed: ${smUrl} -> ${e.message}`);
      }
    }
  } else {
    console.log(`Direct sitemap found: ${used}`);
    urls = urlLocsDirect;
  }

  // Dedupe + Filter
  const dedup = Array.from(new Set(urls))
    .filter((u) => u.startsWith(BASE))
    .filter((u) => !shouldSkip(u));

  if (dedup.length === 0) throw new Error("Sitemap parsed but no URLs found after filtering.");

  console.log(`URLs from sitemap (filtered): ${dedup.length}`);
  return dedup;
}

function extractTextFromHtml(html) {
  const $ = cheerio.load(html);

  // Weg mit typischem Noise
  $("script, style, noscript, iframe").remove();
  $("header, nav, footer").remove();

  const text = $("body").text().replace(/\s+/g, " ").trim();
  return text;
}

async function crawlPage(url) {
  const html = await getText(url);
  const text = extractTextFromHtml(html);
  return { url, text };
}

async function runPool(items, worker, concurrency) {
  const results = [];
  let idx = 0;

  async function next() {
    while (idx < items.length) {
      const current = idx++;
      const url = items[current];
      try {
        const r = await worker(url, current);
        results.push({ ok: true, ...r });
      } catch (e) {
        results.push({ ok: false, url, error: e.message });
      }
    }
  }

  const workers = Array.from({ length: concurrency }, () => next());
  await Promise.all(workers);
  return results;
}

(async () => {
  const urls = await loadSitemapUrls();

  console.log(`Crawling with concurrency=${CONCURRENCY} ...`);

  const pages = await runPool(
    urls,
    async (url, i) => {
      if ((i + 1) % 20 === 0) console.log(`Progress: ${i + 1}/${urls.length}`);
      const page = await crawlPage(url);
      return page;
    },
    CONCURRENCY
  );

  const okPages = pages.filter((p) => p.ok).map(({ ok, ...rest }) => rest);
  const errors = pages.filter((p) => !p.ok).map(({ ok, ...rest }) => rest);

  const data = {
    built: new Date().toISOString(),
    base: BASE,
    count: okPages.length,
    errors: errors.length,
    pages: okPages,
    errorLog: errors
  };

  fs.writeFileSync(OUTFILE, JSON.stringify(data, null, 2), "utf8");
  console.log(`Index built -> ${OUTFILE} (pages=${okPages.length}, errors=${errors.length})`);
})();