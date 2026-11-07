# D.R.O.L. – Gemeinsames Modell- und Evidenzschema

Stand: 2026-11-07. Architekturentwurf; **noch nicht produktiv migriert oder mit der Website verbunden**.

## Eine Identität pro Gerät

`drol_manufacturers` + `drol_hardware` aus `001_catalog.sql` bilden den kanonischen Datensatz. Katalog, Modellprofil und SDK-Matrix sollen künftig **Ansichten desselben Geräts** sein, keine getrennten manuell gepflegten Listen. Komponenten (Flugcontroller) sind explizit von Komplettdrohnen zu unterscheiden.

## Quellenbasierte technische Fakten

`drol_model_facts` aus `002_evidence_model.sql` speichert pro Modell und Eigenschaft einen Wert mit Einheit, Quell-ID, genauer Fundstelle, Prüfdatum, Prüfer und optionalem Firmwarebezug. Beispiel-Feldschlüssel: `mass_g`, `flight_time_min`, `camera_sensor_format`, `camera_photo_mp`, `thermal_resolution_px`, `eu_class`, `remote_id`. Werte bleiben leer, wenn keine belastbare Quelle vorliegt. Hersteller-Marketingwerte sind als solche zu kennzeichnen, nicht als eigene Messergebnisse.

## Technische Fähigkeiten separat bewerten

`drol_capability_evidence` hält je Modell und Funktion die Bewertung `YES`, `PARTIAL`, `NO` oder `UNKNOWN` fest. Bei SDK-Behauptungen sind SDK-Linie, Hostplattform, Fernsteuerung, Firmware und API-Funktion nach Möglichkeit explizit zu nennen. `UNKNOWN` bedeutet **nicht** `NO`.

Die Evidenzstufe ist unabhängig vom Bewertungsergebnis:
- **E0** – unbekannt oder nicht belegt
- **E1** – konkrete Hersteller-/Projekt-Primärquelle mit nachvollziehbarer Fundstelle
- **E2** – dokumentierter, reproduzierbarer Labortest mit Versionsstand und Logs
- **E3** – unabhängig wiederholter Test mit eigenem Nachweis

Ein positives `YES` darf nicht ohne verknüpfte Quelle, Datum und dokumentierten Geltungsbereich veröffentlicht werden. Ein `NO` erfordert ebenfalls einen belastbaren Nachweis, sonst `UNKNOWN`.

## Redaktions- und Veröffentlichungsregeln

1. Daten aus Primärquellen erfassen, mit URL **und genauer Fundstelle**; sekundäre Quellen ergänzend markieren.
2. Werte mit `checked_by`, `checked_on`, Firmware-/Hardwareversion versehen; Quellen- und Lizenzbedingungen beachten.
3. Änderungen als Revisionen nachvollziehbar halten; alte Firmwarestände nicht stillschweigend überschreiben.
4. Nach sechs Monaten ohne Prüfung **„erneute Prüfung fällig“** anzeigen, nicht automatisch „falsch“.
5. Katalog, Profil, Vergleich und SDK-Matrix aus demselben Datenbestand erzeugen.
6. Hardwaretests klar von Dokumentenrecherche trennen. Keine vorgetäuschten Messungen.
7. SONDE bleibt ein getrenntes privates Sondelforschungsprojekt; auf D.R.O.L. höchstens ein Crosslink.
8. Vor produktiver Migration: MariaDB-Version, Constraints, Backup, Rollback und bestehende Tabellen prüfen. Danach Integration und automatisierte Konsistenztests implementieren.

## Umsetzungsstand

- [x] Bestehende Hersteller-, Hardware- und Quellentabellen im Schema
- [x] SQL-Migration für technische Fakten und Fähigkeitsnachweise
- [ ] Datenbank-Backup, Migration und Funktionstest
- [ ] Verifizierte Modell-Datensätze aus Primärquellen
- [ ] API oder statischer Build für alle drei Oberflächen
- [ ] Vergleichsansicht, Filter und verlässliche Quellenanzeige
- [ ] Automatischer Frischehinweis und Review-Prozess
