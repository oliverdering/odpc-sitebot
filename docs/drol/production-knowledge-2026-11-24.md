# Produktivabnahme vom 24. November 2026

Die Bestandsaufnahme um 19:43 MEZ erfasste 15 Tabellen auf MariaDB 11.8.10. Die Referenz enthielt 74 Hardwaremodelle, 10 Hersteller, 67 Hardware-Quellenverknüpfungen, 49 Quellen, 16 Snapshots, 16 Kandidaten, drei Firmwarestände und drei Protokolleinträge. Eine Attributdefinition war vorhanden.

## Sicherung und erster Import

Die verschlüsselte Sicherung liegt außerhalb des D.R.O.L.-Webverzeichnisses auf dem Hostingkonto. Der Runner hat das dort gespeicherte Archiv erneut gelesen, entschlüsselt und auf einer separaten MariaDB 11.8.9 wiederhergestellt. Tabellenstruktur und sämtliche Zeilen stimmten überein. Für die spätere Entschlüsselung müssen die zum Sicherungszeitpunkt gültigen Versionen beider Zugangsschlüssel erhalten bleiben.

- Sicherungscode [dbed49d](https://github.com/oliverdering/odpc-sitebot/commit/dbed49df0b5d15551fe9436d480c543e0e276d02), [Wiederherstellungsprotokoll](https://github.com/oliverdering/odpc-sitebot/actions/runs/45463465158)
- Importcode [1ba03ff](https://github.com/oliverdering/odpc-sitebot/commit/1ba03ff8e4ae4066d185426dcc8baf0d3c61c4b8), [Import und Wiederholungsprüfung](https://github.com/oliverdering/odpc-sitebot/actions/runs/45463850119)
- 0 → 29 Modell-Fakten, 3 → 25 Firmwarestände, 31 E0-Identitäten in drei neuen Graphtabellen. Die Testdatenbank bestätigte Rollback, konkurrierende Schreibsperre, Ablehnung widersprüchlicher Fakten und Wiederholbarkeit.
- [Discovery](https://github.com/oliverdering/odpc-sitebot/actions/runs/45463850003) löste [die anschließende Messung](https://github.com/oliverdering/odpc-sitebot/actions/runs/45463934471) aus. Nach dem kuratierten Import meldete [die zweite Folgemessung](https://github.com/oliverdering/odpc-sitebot/actions/runs/45464054947) die neuen Zahlen.
- [Quellenimport](https://github.com/oliverdering/odpc-sitebot/actions/runs/45463850151) fügte 27 offene Dokumentverweise und zwei tatsächlich überwachte Indizes hinzu. [Öffentliche Messdaten](https://drol.odpc.de/data/knowledge-metrics.json)

## Historische Bestände und ursprünglich leere Tabellen

Über die lokal abrufbaren Git-Referenzen hinweg wurden SQL-Migrationen, frühere Modellregister, Wissensdateien und Sicherungspfade geprüft. Die untersuchten historischen Fassungen des Modellregisters enthalten leere Faktenobjekte. Das frühere Verzeichnis knowledge-base.md beschreibt Plattformen und Rechercheaufgaben. Das Backup unter backups/drol.odpc.de enthält eine frühere HTML-Seite. Die 23 historischen Katalogmessungen in data/drol-growth-history.json belegen Modellzahlen, aber keine früheren Firmware- oder Faktenbestände. Die graphischen Identitäten und der geprüfte erste Faktenstapel wurden übernommen. Der IONOS-Wiederherstellungskatalog des Providers ist von den geprüften SFTP-Archiven zu unterscheiden. Ob dort weitere ältere Sicherungsstände liegen, ist offen.

- Modell-Fakten speichern typisierte Sachverhalte mit Herstellerquelle und Prüftag. Die frühere Seed-Routine importierte nur Identitäten und Links. Der neue Importer übernimmt geprüfte Faktenstapel.
- Metadateneingang sammelt offene Dokumentverweise. Der neue Quellenlauf trägt Original-URLs aus zulässigen Dokumentationsindizes ein.
- Quellenüberwachungen steuern konkrete Indexprüfungen. Aktiv sind die geprüften Dokumentationsindizes von GDAL und PDAL.
- Fähigkeitsnachweise erfordern eine konkrete Funktion, Modellzuordnung und Quelle. Allgemeine Angaben im älteren Katalog wie „UNKNOWN“ sind kein positiver Nachweis.
- Modell-Aliasse erfassen nachweisbare Produktcodes. Modellnamen und die internen Katalogschlüssel sind keine zusätzlichen Aliasse.
- Veröffentlichungsstände dokumentieren reale redaktionelle Ausgaben. Ein unverändert wiederholter Import erzeugt keinen neuen Stand.
- Graphquellen und Beziehungen verlangen eigene Belege. Eine gemeinsame Zugehörigkeit zu den 31 Seed-Identitäten begründet keine technische Kompatibilität.

## Noch offene Quellen

Historische GitHub-Datenbankartefakte wurden nicht in das öffentliche Repository übernommen. Vor der Entfernung vorhandener Altartefakte ist ihre Aufbewahrung gesondert zu klären. Ungeklärte Varianten sowie widersprüchliche Firmwaredaten bleiben von der automatischen Bestätigung ausgeschlossen. E2 und E3 erfordern Testprotokolle.

## Wiedergefundene Bestandsmessungen

25 ältere SQL-Bestandsmessungen aus erfolgreichen Inventur-, Seed- und Migrationsjobs wurden mit UTC-Messzeit und GitHub-Lauf-ID in data/drol-verified-inventory-history.json gesichert. Vom 7. bis 23. November enthalten alle 25 ausgewerteten Messungen jeweils null Modell-Fakten und null Firmwarestände. Der Hardwarebestand stieg von einem Modell bei der ersten Messung auf 39 sowie später auf 74. Diese Messungen bilden die historische Vergleichsbasis des Monitors.

Der Quellenabgleich in f333c26 und der korrigierte Import in 89e77b2 ergänzten 31 Modell-Fakten, zwei von DJI dokumentierte Controllerbeziehungen, drei zugehörige Quellenverknüpfungen, drei Modellcodes und zwei Parrot-API-Nachweise. Die Quellenangaben kennzeichnen Modellvarianten und die Reichweite der Aussagen. Der erste Versuch des Beziehungsimports brach im Dry-Run ab. Die Live-Inventur nach der Korrektur belegte 60 Fakten, zwei Wissensbeziehungen, drei Aliasse, zwei Fähigkeitsnachweise und einen Veröffentlichungsstand. Die sonstigen Importbestände blieben erhalten.
