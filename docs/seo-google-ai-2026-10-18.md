# SEO und Google-Index: odpc.de / ai.odpc.de
Stand: 18.10.2026, 20:50 Uhr Europe/Berlin.

## Veröffentlicht
- odpc.de: Meta-Beschreibung der Startseite regional auf Koblenz, Mayen-Koblenz und Westerwald ausgerichtet. SEO-Titel und Beschreibungen der Branchenübersicht (435) und Kundenreferenzen (3344) verbessert. Startseiten-ID: 3085.
- Alternativtexte der Medien 837 (initIQ), 1824 (PraeDoc), 424 (KI-Illustration) und 1264 (PraeLex) gespeichert. Vorher visuell geprüft.
- ai.odpc.de: neues MU-Plugin `odpc-search-metadata.php`, Version 1.0.1. Individuelle Titel und Beschreibungen für Startseite und zwölf Seiten; serverseitige Canonicals, Open Graph, Twitter-Metadaten und JSON-LD für Organization, Person, WebSite, WebPage und untergeordnete BreadcrumbList.
- Die öffentliche Startseite `/` und der Download-/Angebotskatalog `/start/` haben unterschiedliche Inhalte und jeweils einen eigenen Canonical. Keine Weiterleitung oder Inhaltszusammenlegung.
- Publisher verweist auf die auf odpc.de bestehende Entität `https://odpc.de/#person`, einschließlich des tatsächlich verwendeten ODPC-Logos.

## Deployment und Abnahme
- Erfolgreiches finales Metadaten-Deployment: https://github.com/oliverdering/odpc-sitebot/actions/runs/39155379880
- PHP-Syntax und Regressionstests bestanden. Tests prüfen unter anderem eindeutige Metadaten, JSON- und HTML-Escaping, Erhalt vorhandener Robots-Anweisungen sowie unveränderten Body.
- Vorher/Nachher-Vergleich des vollständigen HTML-Bodys für Startseite, /start/, /werkstatt/ und Sprachassistenten bestanden. Dadurch wurden die bestehenden Sprachbedienelemente beim SEO-Deployment erhalten. Kein erneuter Hörtest im Rahmen dieser SEO-Arbeit.
- Abschließender GSC-Wizard-Live-Audit aller 13 Helper-Monkey-Zielseiten: HTTP 200, indexierbar, selbstreferenzierender Canonical, strukturierte Daten; keine gemeldeten Audit-Probleme.
- Bei ODPC bestehen kleinere Restpunkte wie doppelte H1 auf einzelnen Seiten und fehlende Alternativtexte in älteren, fest eingebetteten Inhaltsbildern. Die Medienbibliothek-Angaben ersetzen solche fest gespeicherten HTML-Attribute nicht automatisch.
- Keine kostenpflichtigen Dienste, KI-API-Aufrufe oder Abos für diese Umsetzung.

## Cache
Auf odpc.de wurden veraltete Ausgaben über die vorhandenen WP-Fastest-Cache- und WP-Optimize-Cache-Verzeichnisse identifiziert. Nur alte HTML-Dateien mit exakt passenden öffentlichen Canonicals wurden nach Sicherung entfernt. Prüfung auf aktualisierte Inhalte über die normalen URLs, ohne Cache-Busting-Parameter.
Erfolgreiche Abnahme für sechs betroffene URLs:
https://github.com/oliverdering/odpc-sitebot/actions/runs/39155910959
Workflow: `.github/workflows/odpc-home-search-cache.yml`; Skript: `scripts/refresh_odpc_home_search_cache.py`.

## Google Search Console
Verwendete, autorisierte Domain-Property: `sc-domain:odpc.de`.
URL Inspection bestätigte bereits bestehende Indexierung für odpc.de/, ai.odpc.de/, ai.odpc.de/start/, die ODPC-Kundenreferenzen, /webdesign und den Helper-Monkey-Sprachassistenten.
Die historischen Inspection-Daten der AI-Startseite zeigen den früheren vom Nutzer deklarierten /start/-Canonical. Die jetzt live korrigierten Angaben werden erst beim erneuten Google-Crawl in der Inspection erscheinen.

Sitemaps am 18.10.2026 um 20:49:44 MESZ erneut eingereicht und von Google erneut heruntergeladen:
- https://odpc.de/sitemap_index.xml — 95 Web-URLs; 0 Fehler, 0 Warnungen.
- https://ai.odpc.de/wp-sitemap.xml — 17 Web-URLs; 0 Fehler, 0 Warnungen.

Sitemap-Zähler sind kein Nachweis, dass sämtliche URLs indexiert sind. Keine Einzelindexierung über eine ungeeignete Google Indexing API ausgelöst. Keine Ranking- oder KI-Zitierungsgarantie.

## Crawler
robots.txt beider Domains erlaubt die öffentlichen Startseiten für Googlebot, Bingbot, OAI-SearchBot, PerplexityBot und Claude-SearchBot; /wp-admin/ bleibt ausgeschlossen. Bestehende Regeln wurden erhalten.
Google bestätigt, dass für AI Overviews und AI Mode die normalen SEO-Grundlagen gelten und keine spezielle KI-Datei vorausgesetzt wird:
https://developers.google.com/search/docs/appearance/ai-features
