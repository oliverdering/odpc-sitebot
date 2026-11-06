# D.R.O.L. Relaunch – Bestandsaufnahme und Prüfstand
Stand: 06.11.2026. Entwicklungsbranch: drol/visual-relaunch-preview-20261106. Produktions-Basiscommit: c63319e5614be748276310ee3c6df9e522116fe4. Die unveränderte Git-Historie und der Basiscommit ermöglichen eine Wiederherstellung; eine separate Datensicherung des Servers wurde nicht erstellt.

## Inventar
- `anwendungen.html` — Anwendungsfälle und Workflows | D.R.O.L.
- `daten.html` — Drohnendaten sicher verarbeiten | D.R.O.L.
- `datenverarbeitung.html` — Datenverarbeitung und KI – D.R.O.L. Intelligence | D.R.O.L.
- `engineering-studio.html` — D.R.O.L. Engineering Studio; 1 Skriptblock/-blöcke
- `faq.html` — Häufige Fragen zu D.R.O.L. | FAQ; 1 Skriptblock/-blöcke
- `index.html` — D.R.O.L. | Wir machen Ihre Drohnendaten nutzbar – ODPC
- `katalog.html` — Drohnenkatalog | D.R.O.L.; 1 Skriptblock/-blöcke
- `knowledge-base.md` — Dokument
- `lab.html` — D.R.O.L. Lab – offene Schnittstellen und Entwicklung | D.R.O.L.
- `lab/index.html` — D.R.O.L. Lab | Offline-Demo; 1 Skriptblock/-blöcke
- `leitfaden.html` — D.R.O.L. Pilot – Praxisleitfaden | D.R.O.L.
- `modellprofile.html` — Modellprofile & Schnittstellen | D.R.O.L.; 1 Skriptblock/-blöcke
- `pilot.html` — D.R.O.L. Pilot – Praxiswissen für gute Aufnahmen | D.R.O.L.
- `praxis.html` — Praxisdossiers – Drohnendaten professionell auswerten | D.R.O.L.
- `software.html` — Software, Firmware und Datenimport | D.R.O.L.
- `vorhandene-drohne.html` — Mit vorhandener Drohne starten | D.R.O.L.
- `werkstatt.html` — Entwickler-Werkstatt | D.R.O.L.
- `wissen.html` — Drohnen und KI: Wissensportal, Modelle und Entwicklung | D.R.O.L.

Dies ist eine Datei- und Quelltextinventur, kein vollständiger Funktionstest. Katalog und Modellprofile enthalten Skripte für interaktive Funktionen, FAQ und lab/index.html ebenfalls. engineering-studio.html enthielt lokale Speicherung und JSON-Export; das Alpha ist kein elektrischer Validator.

## Neue Arbeiten
- Freigegebener visueller Entwurf in relaunch-preview/ mit responsiven Komponenten, Tag-/Nachtmodus, Menüs und geplanten Sprachen.
- relaunch-preview/knowledge.html: acht durchsuchbare Einträge, drei Referenztexte zu MAVLink, Pixhawk 6C und optischer Sensorik, Quellen und Bestand-Verknüpfungen. Die Suche ist auf diese Einträge begrenzt.
- engineering-studio.html: begrenzter JSON-Import mit Format-/Typ-/Größenprüfung, Anzeige einer generischen Stückliste und CSV-Export. Keine geprüften elektrischen Daten oder Pinbelegungen.
- Primärquellen: ArduPilot MAVLink Basics, PX4/Holybro Pixhawk 6C, FLIR und Axis. Neu überprüfte Links für FLIR und Axis verwenden aktuelle Quellseiten.

## Tests und Grenzen
- JavaScript-Syntax des erweiterten Studios geprüft.
- Validierungsfunktion separat geprüft: gültige Daten angenommen; falsches Schema, unbekannte Komponente und mehr als 24 Komponenten abgewiesen.
- Dateiinventur mit vollständigem Git-Tree durchgeführt.
- Kein End-to-End-Import oder Export, keine Browser-, Screenshot-, Smartphone-, Tastatur-, Screenreader- oder Performanceprüfung. Frühere Browserprüfung einer lokalen HTML-Datei war durch Browser-Sicherheitsrichtlinie blockiert.
- Die neue Startseite liegt weiterhin in relaunch-preview/index.html; sie ersetzt die produktive index.html nicht. Ein Merge allein ist kein funktionsfertiger Relaunch und würde den produktiven Deployment-Workflow auslösen.
- README des Design-Prototyps beschreibt den frühen Stand; dieser Bericht ergänzt ihn.

## Offene Veröffentlichungsvoraussetzungen
1. Vollständige Navigation und Inhalte auf allen bestehenden Seiten übertragen; URL- und Linkmigration prüfen.
2. Suche auf tatsächlich indexierte Inhalte erweitern; Hardware- und Quellenmodell ausarbeiten.
3. Studio im Browser testen, JSON-Roundtrip, CSV, Speicherung und Fehlerfälle prüfen; visuelles Design angleichen.
4. Inhaltsprüfung, Recherche, Glossar, FAQ, Mobile-App-Architektur und Communityprozess weiter ausarbeiten.
5. Realistische Internationalisierung, geprüfte Übersetzungen und RTL erst aktivieren, wenn Inhalte vorliegen.
6. Desktop- und Smartphone-Browser- und Barrierefreiheitstests durchführen, fehlende Infrastruktur dokumentieren.
7. Deployment-Vorbedingungen, Serverbackup und Rollback verifizieren. Der D.R.O.L.-Workflow auf main erwartet unter anderem den bisherigen Startseitentext; einen Umbau nicht ungeprüft zusammenführen.
8. Kein Merge oder Deployment, solange die vom Auftraggeber verlangten erforderlichen Tests nicht erfolgreich sind.

## Status
Implementiert, funktional nur teilweise separat geprüft: Studio-Importvalidierung und Stücklisten-Code.
Implementiert, nicht im Browser geprüft: Wissenseinstieg, Suche, Studio-Import/CSV, Entwurf und mobile Navigation.
Geplant: vollständig migrierte Plattform, vollständige Hardware-Datenbank, native Mobile-App, Community-Backend, KI-gestützter Editor, tatsächlich lokalisierte Sprachen und Live-Veröffentlichung.

## Fortschreibung: Suche, Studio-Test und Mobile
- Der Suchindex enthält den extrahierten Text von 17 Bestands-HTML-Dateien. Dynamisch erzeugte Inhalte und externe Inhalte sind nicht vollständig abgedeckt. Der Index ist eine Momentaufnahme des Git-Basisstands und muss bei Änderungen neu erzeugt werden.
- Suche mit simulierten DOM-Objekten geprüft: 17 indizierte Einträge, Treffer für Pixhawk, kein Treffer bei Fantasiebegriff. Keine Browserprüfung.
- Reproduzierbarer Node-Test unter tests/studio.test.cjs bestanden: JSON-Export und -Import, fehlerhaftes JSON, unbekannte Komponente, Größen-/Anzahlgrenzen, lokale Speicherung und CSV-BOM/Zeilen. DOM und Browser-APIs wurden simuliert; es ist kein echter Browser-End-to-End-Test.
- mobile.html dokumentiert die geplante Adapterarchitektur und enthält einen kleinen lokalen Text-/CSV-Inspektor ohne Flugfunktion. Tests mit simulierten Eingaben: gültige Datei und Überschreiten von 2 MB. Keine native App.
- Vor Veröffentlichung: automatischen Index-Neuaufbau einrichten, Browser- und echte Gerätetests ausführen und Ergebnisse von Claude/Claire mit Primärquellen abgleichen.

## Vorbereitung der Veröffentlichung – 06.11.2026
Der vorhandene Deployment-Code überträgt ausschließlich HTML-Dateien im Wurzelverzeichnis und lab/index.html; Dateien im Relaunch-Vorschauordner und externe CSS-/JS-Dateien würden nicht mit übertragen. Daher wurden die neue Startseite (index.html), drol-wissen.html und drol-mobile.html als eigenständige HTML-Dateien vorbereitet; Design, Skripte und Suchindex sind darin eingebettet. Der frühere Startseitenstand liegt zusätzlich als bestand-start.html vor und bleibt auch über den Basiscommit wiederherstellbar. Dies ist eine Quellsicherung, kein Serverbackup.

Statische Prüfung: Inline-JavaScript aller vier neu vorbereiteten bzw. geänderten Hauptdateien parsesicher; interne Anker und erkannte relative HTML-Links vorhanden; die bestehende automatische Deploy-Prüfung auf den alten Text wird erfüllt. Diese Prüfungen ersetzen keinen Browser- und Gerätetest. Der Entwicklungsbranch wurde nicht zusammengeführt oder bereitgestellt.

Die früheren Aussagen oben, dass sich die neue Startseite ausschließlich in relaunch-preview befindet und der Wissensindex nur acht Einträge enthält, beschreiben ältere Zwischenstände. Die Veröffentlichungsvoraussetzungen bleiben bestehen.
