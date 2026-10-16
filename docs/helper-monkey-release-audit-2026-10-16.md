# Helper Monkey – Live-Audit und Release-Stand (16.10.2026)

## Zweck und Änderungsschutz
Freigegeben sind inhaltliche Korrekturen und Prüfung der Agenten sowie der bestehenden Links und Downloads. **Keine Änderungen an Corporate Design, Logos, WhatsApp-Button, Farben, Typografie, Menüstruktur oder Bildgestaltung ohne ausdrückliche Freigabe.** Bestehende Slugs und ZIP-Downloads dürfen nicht ungeprüft verändert, gelöscht oder verschoben werden.

## Verifizierter WordPress-Bestand
Verbindung `ai.odpc.de` über WPVibe, am 16.10.2026 mit Administratorrolle `odpc_master`. WordPress 7.1.3, PHP 8.4.26, WPVibe 1.20.3. Aktives Theme: `helpermonkey-business-v34-child`, Version 3.4.0. Die Seite nutzt eigene Plugins für Agenten-Katalog, Premium Guard, Werkstatt sowie Fluent SMTP und Redirection. Ein WPVibe-Draft-Theme ist separat vorhanden, aber NICHT als aktiv gemeldet.

### Verifizierte veröffentlichte Seiten (WP REST)
- `/` – vom aktiven Theme gerenderte Startseite (nicht durch `wp/v2/pages` zurückgegeben)
- `/start/` (ID 24)
- `/praxis/` (ID 21)
- `/connector-einrichten/` (ID 26)
- `/webmaster-agent/` (ID 31)
- `/finanzen-vertragscheck/` (ID 32)
- `/konfliktlotse-anwaltsvorarbeit/` (ID 33)
- `/werkstatt/` (ID 74)
- `/merchandise/` (ID 92)
- `/sprachen/` (ID 94)
- `/gespraechsuebersetzer/` (ID 95)
- `/helper-monkey-einfach-starten-sprachassistent/` (ID 136)
- `/helper-monkey-fuer-unternehmen-ki-kollegen/` (ID 140)

Veröffentlicht sind ferner zwei Praxis-Posts (IDs 19 und 20). Das sagt nur etwas über den WordPress-Veröffentlichungsstatus, nicht über funktionierende Links, Downloads oder alle JavaScript-Funktionen.

### Beobachtete Inhaltshinweise
- `/start/` verwendet weiterhin die Zeichenfolge „12 ZIP“, die mit dem kommunizierten Angebot „vier Gratis-Agenten“ verwechselt werden kann; verbindliche Zählregel anhand ZIP-Inventar prüfen, nicht blind ersetzen.
- Die Seiten `/start/` und `/helper-monkey-einfach-starten-sprachassistent/` enthalten bereits eigene umfangreiche V4-Gestaltung/Onboarding-Elemente; KEIN blindes Ersetzen.
- Thema Titel `Helper Monkey fuer Unternehmen – KI-Kollegen` ist eine Schreibweisen-Korrektur im sichtbaren Titel, Slug `...-fuer-...` ausdrücklich erhalten.
- Der Claude-Report „Release-Check V4.1“ vom 15.10.2026 (19 Seiten) wurde als Prüfliste ausgewertet; seine numerische Bewertung 53/100 ist eine subjektive Einschätzung, keine technische Messung.

## Bereits live umgesetzt, getrennte ODPC-WordPress-Installation
- Neuer Einsteigerbeitrag `https://odpc.de/smartphone-ki-helfer-connector-einfach-erklaert` ist veröffentlicht (Post ID 3454). Er erklärt Connector-Starter, sichere Kontenverwendung, Ampel und zusätzliche Voraussetzungen für autonome Agenten; SEO-Titel und Meta-Beschreibung sind gespeichert.
- Bestehender Beitrag `odpc.de`, Post ID 3053: Die unzutreffend pauschale Formulierung „zwölf Starterpakete“ in der FAQ wurde präzisiert (vier Einstiegspakete, weitere Sprach-/Zusatzdownloads gesondert). Keine Layoutänderung.
- Das grüne, vom Inhaber bestätigte Helper-Monkey-Smartphone-Bild liegt als ChatGPT-Datei vor, konnte aber noch nicht auf den WordPress-Medienserver übertragen werden. Es ist weder im Artikel noch als Beitragsbild eingebunden: NICHT als fertig kennzeichnen.

## Ausstehende technische Release-Prüfungen
1. WordPress-Inventar zu Download-ZIP-Dateien, Datei-Existenz, HTTP 200, Größe, Prüfsumme, Versionsnummer, Referenzen im Theme, Artikeln und GitHub.
2. Agenten-Logik, Masterprompts und reale kontoberechtigte Aktionen getrennt prüfen. Prompt = Anleitung, keine heimlich installierte Automatisierung.
3. V4-Links und veraltete Produktzählungen auf `/start/`, Startseite und Fachseiten gegen das tatsächliche Inventar abgleichen.
4. Konsistenz- und Datenschutz-Review der Kanzlei-Referenz nur mit sachlich freigegebenen Angaben.
5. Das freigegebene grüne Bild originalgetreu in Medienbibliothek hochladen, demselben Beitrag als featured image und innerhalb des Artikels zuordnen.
6. Staging/Backup und Regressionstests vor einem Theme-Deployment: Desktop und mobile Größen, WhatsApp, bestehende Logos und ZIPs; danach nur getestete Änderungen veröffentlichen.

## Blocker
WPVibe Free erreichte am 16.10.2026 erneut sein rollierendes 24-Stunden-Limit. Aufeinanderfolgende Detailabfragen wurden blockiert; es wurden **keine Theme-, Download- oder Plugin-Dateien live überschrieben**. Die Plugin-Meldung nannte für weitere Kapazität ungefähr **16.10.2026 11:20 UTC** (dynamische Quote; keine Verfügbarkeitsgarantie). GitHub-Verbindung funktioniert. Keine kostenpflichtige Erweiterung ohne Benutzerfreigabe.

## Veröffentlichungsgrundsatz
Nur tatsächlich durchgeführte und überprüfte Maßnahmen als „GRÜN“ behandeln. Ungeprüfte Konzeptpunkte bleiben „ORANGE“. Jede visuelle Veränderung bleibt von vorheriger Zustimmung abhängig.
