# SEO-Ausweitung auf ODPC-Subdomains – 18.10.2026

## Tatsächlich veröffentlicht
Tante Lenchen: MU-Plugin `site-deploy/tante-lenchen.odpc.de/odpc-local-search.php` über `scripts/deploy_lenchen_search.py` und `.github/workflows/tante-lenchen-search-deploy.yml`.
Commit: 52e547cd15460be78facb1ae48154f3837f50ed9.
Erfolgreicher produktiver Lauf: https://github.com/oliverdering/odpc-sitebot/actions/runs/39156727578
Titel, Beschreibung, eigener Canonical und JSON-LD (CafeOrCoffeeShop, WebSite, WebPage) für Startseite, Speisekarte, Tischanfrage, Kontakt, Anfahrt, Galerie, Medien, Impressum und Datenschutz.
PHP-Lint, Python-Kompilierung, Upload-Bytevergleich und neun Live-Tests bestanden. Sichtbarer HTML-Body je Seite vor/nach identisch, abgesehen von erlaubter Normalisierung von WordPress-Formularnonces. Separate öffentliche Homepage-Nachprüfung bestätigt neue Ausgabe.
Keine Theme-, CSS-, Inhalts- oder Datenbankänderung. Bei Fehlern automatische Wiederherstellung des bisherigen Moduls. Keine zusätzlichen kostenpflichtigen Dienste.

## Aktuell geprüfter Stand
| Website | Öffentlicher Befund | Google / Maßnahme |
|---|---|---|
| odpc.de | HTTP 200; optimierter Titel, Beschreibung, Canonical und Schema vorhanden | Bestehende Sitemap zuletzt 18:49 UTC ohne Fehler gelesen; keine neue Änderung in diesem Auftrag |
| ai.odpc.de | HTTP 200; optimierte Metadaten und Canonical auf / vorhanden | URL-Prüfung PASS; Googles letzter Crawl ist älter als die aktuelle Canonical-Korrektur |
| tante-lenchen.odpc.de | HTTP 200; neun Seiten aktualisiert und geprüft | Startseite laut URL Inspection „Submitted and indexed“; Sitemap neu eingereicht 19:01:03 UTC, von Google 19:01:10 UTC mit 0 Fehlern/0 Warnungen geladen |
| atrium191-rb.odpc.de | HTTP 200, ausdrücklich noindex/nofollow/noarchive; wp-sitemap.xml HTTP 404 | URL bei Google unbekannt. Theme erzwingt Entwicklungsstatus. Keine Entfernung der bestehenden Entwurfs-/Freigabesperre und keine erneute Einreichung der nicht vorhandenen Sitemap |
| bahnhof-grenzau.odpc.de | Startseite, robots.txt, Sitemap jeweils HTTP 403 | URL bei Google unbekannt; aktueller rein lesender SFTP-Lauf bestätigt fehlende wp-config.php. WordPress-Initialisierung/Datenbankanbindung offen, keine SEO-Veröffentlichung |
| ra-justen.odpc.de | Layoutvorschau, noindex und robots.txt Disallow / | Bestehende Vorschau nicht für Indexierung geöffnet |
| ra-justen.de | Produktive STRATO-Domain HTTP 200, Beschreibung/Canonical/Schema vorhanden | Startseite laut URL Inspection indexiert. Sitemap neu eingereicht 19:01:04 UTC und von Google 19:01:21 UTC fehlerfrei geladen |

## Reproduzierbare Nachweise
- Google Search Console: verifizierte Domain-Property `sc-domain:odpc.de` umfasst Subdomains; produktive Kanzlei über separate Property `https://ra-justen.de/`.
- Bahnhof-Leseprüfung: https://github.com/oliverdering/odpc-sitebot/actions/runs/39058305813 (erneuter Lauf am 18.10., Job 117761603861).
- ATRIUM: `oliverdering/atrium191-odpc-`, `site-theme/atrium191-odpc/functions.php` setzt `wp_robots` unabhängig von der Besucheransicht auf noindex. Die dortige Freigabe-Dokumentation hält Sach-/Bild-/Rechtsprüfung offen.
- Die in der Sitemap-API zurückgegebenen indexed-Zähler von 0 wurden nicht als Beleg einer vollständigen Nichtindexierung ausgelegt: URL Inspection bestätigt Tante Lenchen, ai.odpc.de und ra-justen.de als indexiert.
- Sitemapeinreichung und fehlerfreier Abruf sind kein Versprechen auf Indexierung weiterer URLs oder Rankingsteigerungen.

## Offene Voraussetzungen
ATRIUM: bestehende Projektfreigabe und offene Daten-/Rechteprüfung klären, bevor noindex aufgehoben und eine echte Sitemap veröffentlicht wird.
Bahnhof: fehlende WordPress-Konfiguration und sichere Datenbankanbindung herstellen sowie 403-Ursache beheben; erst den dann tatsächlich fertigen Auftritt indexierbar machen.
