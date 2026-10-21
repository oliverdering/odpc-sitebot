# MeinKraut 3.0 – integrierter Testshop

Die bestehende PHP-/JavaScript-Anwendung und ihr Original-Logo bleiben die Grundlage. Der Atlas enthält weiterhin 288 Zutaten, 649 Rezepturen, 195 Länder, 24 vertiefte Länderporträts und 35 vertiefte Gewürzprofile.

## Architektur

- `server/public.php` liefert öffentliche HTML-Seiten mit eigenen URLs, Metadaten, Canonicals und passenden strukturierten Daten. Grundinformationen sind ohne JavaScript lesbar.
- Die bestehenden Atlasdaten bleiben in der vorhandenen Datenbank mit lokalem JSON-Fallback. Der aktive Datenbankinhalt wird durch diese Erweiterung nicht überschrieben.
- `content/shop-catalog.json` ist die einzige Produktquelle: zehn geplante BASIC-Produkte, neun Premium-Kandidaten und zwei Gläservarianten. Unbestätigte Preise sind `null`.
- `shop.js` verbindet Rezeptmengen, gepflegte Grammvorräte, Packungsvorschläge, Testwarenkorb und persönliche Etiketten. Premiumwaren ersetzen keine Grundgewürze automatisch.
- QR-Codes werden mit dem lokal ausgelieferten Open-Source-Paket `qrcode` erzeugt. Keine externe QR-API.
- `/r/<produkt>` leitet mit HTTP 302 direkt auf die passende Produktseite. Keine personenbezogenen Kennungen im QR-Ziel.
- Das persönliche Regal funktioniert ohne Registrierung. JSON-Export/-Import berücksichtigt auch Shopentwürfe und Etiketten. Freiwillige Testkonten erlauben manuelle Serversicherung und sind auf erfundene Adressen unter `example.invalid` beschränkt.
- Testkonten verwenden die vorhandene MariaDB, Passwort-Hashes, rotierende Sitzungen, CSRF-Schutz, begrenzte Anmeldeversuche und ein Secure/HttpOnly/SameSite-Strict-Cookie. Die vier eigenen Tabellen werden erst beim ersten Kontotest angelegt. Keine E-Mail-Verifizierung oder Passwortwiederherstellung für produktive Konten.

## Verkaufszustand

Echte Bestellungen und Zahlungen sind in der Oberfläche und in `shop-api.php`/`server/commerce.php` gesperrt. Der Testwarenkorb berechnet unbekannte Preise nicht als null Euro. Gespeicherte Testentwürfe sind keine Bestellhistorie.

Alle Freigaben in `releaseGates` bleiben falsch. Die Zahlungssperre ist zusätzlich fest im Server implementiert. Ein Umschalten der JSON-Datei allein aktiviert keinen Verkauf. Die Veröffentlichung von Lebensmittelangeboten mit echter Bestellmöglichkeit erfordert geprüfte Produkt- und Preisdaten, Betreiber-/Lebensmittelpflichten, Verbraucherinformationen, Datenschutz und ausdrückliche Zahlungsfreigabe.

Das Glas zu 2,99 Euro und der Appzugang zu 2,99 Euro sind Zielwerte. Beim Zugang ist auch der Abrechnungszeitraum offen. Gläser sind leer. Tafelfolie, Etikettenmaße, Material und Druck müssen am echten Glas bemustert werden. Dekoretiketten ersetzen keine Lebensmittelpflichtkennzeichnung.

## Betrieb und Wiederherstellung

Vorheriger Quellstand: `6916f4e08ceeeec36f9568268f2904404ba20fed`, zusätzlich gesichert auf `backup/meinkraut-before-platform-20261021`.

Der bestehende GitHub-Workflow veröffentlicht ausschließlich Änderungen auf `main` im vorhandenen `/MeinKraut`-Webspace. SFTP-Zugangsdaten liegen ausschließlich in GitHub Secrets; Datenbankzugangsdaten außerhalb des Webroots. Geänderte öffentliche Dateien werden vor dem Austausch gesichert; das Workflow-Artefakt bleibt 15 Tage erhalten. Der alte Atlas und die WordPress-Installation werden nicht ersetzt.

Bei einem Fehler kann der vorherige Quellstand über einen normalen geprüften Wiederherstellungscommit auf `main` erneut veröffentlicht werden. Während eines fehlgeschlagenen Datei-Uploads stellt das Deployment geänderte Dateien automatisch wieder her. Vorhandene lokale Browserdaten und Atlas-Datenbankdaten bleiben erhalten. Neue Testkonto-Tabellen werden bei einer Wiederherstellung nicht automatisch gelöscht.

## Prüfungen

```sh
node --check site-deploy/meinkraut/meinkraut.js
node --check site-deploy/meinkraut/shop.js
node scripts/test_meinkraut.cjs
php scripts/test_meinkraut_php.php
python3 scripts/build_meinkraut_seo.py
git diff --exit-code -- site-deploy/meinkraut/sitemap.xml
```

Die PHP-Prüfung kontrolliert alle 990 Sitemap-Ziele, Canonicals, Indexierungsregeln, unbekannte URLs, Testkonto-Isolation, Passwort-Hashing und die Zahlungssperre. Die JavaScript-Prüfung schützt vorhandene Daten, Mengenskalierung und Speicherformate und prüft Nachfülllogik, Packungsvorschläge und Etiketten gegen ungültige Eingaben.

Nach jeder Veröffentlichung zusätzlich echte HTTPS-Antworten, Redirects, Datenbankstatus, öffentliche HTML-Ausgabe und die Oberfläche auf Desktop und Smartphone prüfen. Eine gültige Sitemap und lesbares HTML beweisen keine tatsächliche Google-Indexierung. Dafür ist eine autorisierte Google-Search-Console-Prüfung erforderlich.

## Bewusste Restpunkte

Keine produktiven Kundenkonten, Adressdaten, E-Mails, Erinnerungen, Zahlungsanbieter, Bestellabwicklung, echten Bestellhistorien oder automatisch ausgelösten Nachkäufe. Keine fertig abgefüllten individuellen Mischungen. Lieferantenangebote, Chargendokumente, Endpreise und sämtliche Verkaufsvoraussetzungen werden vor der Aktivierung separat abgenommen. Pro Gläserposition gilt im Testkorb ein Etikettenentwurf für die gesamte Stückzahl.

## Datenqualität und freigegebene Marke

Der übernommene Atlasbestand enthält redaktionelle Hausvarianten ohne vollständige individuelle Quellen- oder Verkostungsnachweise. Die 24 neueren Trockenmischungen sind als nicht verkostet gekennzeichnet. 24 Länderporträts und 35 Gewürzporträts sind vertieft recherchiert; daraus folgt keine Vollständigkeit für alle 195 Länder. Mehrere Zutaten teilen sich weiterhin ein Symbolbild. Für Zutaten ohne belegten Löffel-Umrechnungswert wird keine Grammpräzision vorgetäuscht. Die ursprünglichen Datenquelldateien bleiben erhalten.

Das freigegebene Logo stammt aus `https://odpc.de/wp-content/uploads/2026/10/file_000000009f5081f4864e23c5e7a7e7f7.png`, SHA-256 `8b848e559c99c43f9aaff48fe54a5d3857f0f475d8fd9fa66ad1e1b4aa2725a5`. Die bestehenden WebP-Ableitungen bleiben unverändert.

Das italienische Kräuterbeispiel vergleicht den Verbrauch von Zutaten aus sechs fremden 500-g-Packungen mit einer fertigen Markenmischung im Glas: 0,27035 Euro Zutaten für 25 g gegenüber 5,49 Euro. Der erste Einkauf der sechs Grundpackungen kostet 38,95 Euro zuzüglich Versand. Die Prozentdifferenz schließt eigenes Glas, Etikett, Versand, Arbeitszeit, Restvorräte und späteren Zugang aus. Fremdhändlerpreise sind keine zukünftigen MeinKraut-Shoppreise; Mengenverhältnisse sind eine eigene Hausrezeptur. Die Beispielrechnung ist keine Aussage über interne Herstellkosten eines Wettbewerbers.
