# D.R.O.L. – visueller Relaunch / isolierter Arbeitsstand

Dieser Prototyp liegt ausschließlich auf einem Entwicklungsbranch. Keine produktive Veröffentlichung. Die bestehenden Seiten bleiben unverändert.

## Sitemap
- Start / Ökosystem
- Wissen & Hardware Atlas: Wissensübersicht, Drohnenkatalog, Modellprofile, Software, FAQ
- Engineering Studio: Werkstatt; Mobile, Komponenten, Schaltplan, Simulation und Projekte als geplant
- AI Research: Datenverarbeitung; Computer Vision, Sensorfusion und autonome Systeme als Forschungsfelder
- Guides & Repair: Praxisdossiers, Leitfaden, vorhandene Drohne; illustrierte Reparaturanleitungen geplant
- Global Community: Repository; Profile und Diskussionen geplant
- About: Lab, ODPC, Daten und Rechtliches

Hardware Atlas ist unter Wissen, Mobile unter Engineering eingeordnet; Guides bleibt eigenständiger Einstieg. Die sieben Hauptbereiche sind im Prototyp als verlinkte Bereiche auf einer Seite angelegt, nicht als fertig ausgebaute Einzelseiten.

## Designsystem
Original-Logo (bestehende ODPC-Medienadresse); dunkles Marineblau, Mint/Teal als technischer Akzent; helle Palette für Tageslicht. CSS-Variablen definieren Oberflächen, Text, Akzente und Linien. Systemschrift mit internationalen Fallbacks, Monospace nur für Kennzeichnungen. Komponenten: Navigation, mobile Navigation, Sprachhinweis, Buttons, Status, Karten, Module und Listen. Fokus sichtbar; Bedienziele mindestens 44px; Bewegung bei prefers-reduced-motion reduziert. Responsive Raster und physisch wirkende Buttonzustände.

## Bestandsinventar und Schutz
Die Wurzel des D.R.O.L.-Verzeichnisses enthält index, anwendungen, daten, datenverarbeitung, engineering-studio, faq, katalog, lab, leitfaden, modellprofile, pilot, praxis, software, vorhandene-drohne, werkstatt und wissen als HTML sowie knowledge-base.md und lab/; bestehende Dateien wurden nicht verändert. Prototyp verlinkt auf vorhandene Inhalte. Eine vollständige Prüfung aller Skripte, Unterverzeichnisse, Assets und Funktionen steht noch aus; Erhalt funktionaler Abläufe ist nicht verifiziert.

## Sprachen
UI derzeit Deutsch mit englischen Markennamen; 15 Zielsprachen als Planung. Keine Sprachumschaltung vorgetäuscht. HTML lang und dir vorhanden; CSS verwendet teilweise logische Eigenschaften. Vollständige RTL-, Schrift- und Übersetzungsprüfung steht aus.

## Referenzen
iFixit: aufgabenorientierte Anleitung und Community-Struktur. Schematik: projektorientierter Einstieg in Engineering. Keine Inhalte oder Medien kopiert.

## Offene Punkte vor Freigabe
- Vollständige Bestands- und Abhängigkeitsprüfung inklusive Unterseiten, Suche, Filtern, Export und Lab.
- Seitenlayouts zu eigenständigen Vorlagen ausbauen und Inhalte migrieren.
- Echte geprüfte Übersetzungen und RTL implementieren.
- Logo lokal/rechtegeprüft bereitstellen und Offline-Verhalten klären.
- Browser-, Geräte-, Tastatur-, Screenreader-, Kontrast- und Performance-Tests durchführen.
- Links zu Impressum/Datenschutz auf Ziel und Zuständigkeit prüfen.
- Kein Backend für Studio, Mobile oder Community vorhanden.
- Ergebnisse von Claude und Claire später prüfen und integrieren.
- Produktionsworkflow prüft derzeit bestehende Startseiten-Inhalte; vor möglicher Veröffentlichung Deployment und Tests bewusst anpassen.
