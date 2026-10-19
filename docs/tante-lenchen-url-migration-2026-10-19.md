# Tante Lenchen – dauerhafte URL-Umstellung, 19.10.2026

Produktiv umgesetzt und unabhängig per HTTP nachgeprüft:

- https://odpc.de/tante-lenchen und https://odpc.de/tante-lenchen/ liefern direkt HTTP 301 nach https://tante-lenchen.odpc.de/.
- Query-Parameter bleiben erhalten; geprüfter UTM-Link liefert direkt 301 zum neuen Ziel.
- Ziel liefert HTTP 200, eigenen Canonical und kein noindex. robots.txt erlaubt Crawling und nennt die erreichbare wp-sitemap.xml. Startseite ist im Seiten-Sitemap enthalten.
- Alte Seite (ID 2352) ist aus ODPCs Rank-Math-Seiten-Sitemap ausgeschlossen. Alte Seiteninhalte sind nicht gelöscht.
- WordPress-interne Seitenlinks für ID 2352 verweisen auf die neue Website. Alternative öffentliche ID-Aufrufe werden ebenfalls durch das MU-Plugin weitergeleitet.
- Bestehende .htaccess wurde gesichert, um einen separaten eng begrenzten Rewrite-Block ergänzt und bytegenau hochgeladen. MU-Plugin ebenfalls gesichert; automatische Rücknahme bei Fehlern.
- ODPC-Startseite und Kundenreferenzen nach der Veröffentlichung HTTP 200.
- Strukturiertes Café-Telefon korrigiert auf +49 2621 6298644; unabhängiger Abruf bestätigt Übereinstimmung mit tel:+4926216298644.

## Nachweise

Migration: Commit 174a35c36e36caf5f9e7f679824ec4c6883f12f1; erfolgreicher Lauf https://github.com/oliverdering/odpc-sitebot/actions/runs/39261937267 (Job 118091558134).
Metadaten: erfolgreicher Lauf https://github.com/oliverdering/odpc-sitebot/actions/runs/39262043982 (Job 118091934021), nach Commit ecdffa457a992e6adfc0c47361ce41e8457156b4. Acht aktuell veröffentlichte Seiten geprüft; Galerie ist aktuell nicht veröffentlicht und nicht im Seiten-Sitemap. Der erste Metadatenlauf stoppte deshalb vor dem Upload mit 404; Prüfliste danach auf den tatsächlichen Veröffentlichungsstand korrigiert.

Deployment: .github/workflows/tante-lenchen-migration.yml, scripts/deploy_lenchen_migration.py, site-deploy/odpc.de/odpc-lenchen-migration.php. IONOS-Root /clickandbuilds/Dering; SFTP über bestehenden gepinnten Host und Secret ODPC_SFTP_PASSWORD. Kein Secret im Repository. Vorherige Konfiguration als Actions-Artefakt lenchen-migration-previous-configuration für 30 Tage gesichert.

## Google-Status und Grenzen

Aktuelle URL-Inspection und erneute Sitemap-Einreichung in dieser Sitzung nicht möglich: GSC Wizard verweigert sämtliche Aufrufe mit payment_required/subscription_required. Kein Abo abgeschlossen. Die frühere erfolgreiche Indexprüfung und Sitemapeinreichung vom 18.10.2026 sind in docs/seo-subdomains-2026-10-18.md dokumentiert; dies ist kein frisch erhobener Google-Status.

301 ist jetzt das dauerhafte Umzugssignal. Die Umstellung des angezeigten Suchtreffers und Rankings hängt von Googles erneutem Crawling und Verarbeitung ab; keine sofortige Platzierung zugesagt. Weiterleitung dauerhaft beibehalten.
