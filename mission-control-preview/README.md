# ODPC Mission Control – Arbeitsstand 14.11.2026

## Verifiziert
- Öffentliches Repository oliverdering/odpc-sitebot, Standardbranch main, Connector meldet Schreibrechte. Monitor-Deployment 43596363893 am 14.11. erfolgreich. YourBusiness-Preflight 43098475420 wartet; GitHub-Statusabfrage meldete zwei laufende Vorgänge, lieferte aber keine zugehörigen Datensätze. Kein neues Deployment ausgelöst.
- Google-Sheet KI-Team-Zentrale mit sechs bekannten Registern lesbar. TASK-20261114-005 offen; keine Antwort im geprüften Nachrichtenbereich. Vorherige Einträge dokumentieren manuelle Claude- und Claire-Schreibtests; Herkunft allein anhand der Zellwerte nicht kryptografisch nachweisbar.
- Drive und Kalender-Connector: oliver.dering@gmail.com. Geprüftes ODPC-Gmail: oliver.dering1971@gmail.com; Labels NEU und VERARBEITET vorhanden. Verbindungskonten sorgfältig getrennt halten.
- Repository enthält SFTP-Deployment für AdminMonkey mit Host-Key-Pinning, Sicherung und Smoke-Test; ein separater Gemini-API-Pilot ist auf GitHub Issue 219 begrenzt. Schlüsselnutzung und Kosten nicht geprüft; Pilot nicht gestartet.
- Öffentliches ODPC und D.R.O.L. bei Webabfrage erreichbar; Monitor aus Suchcache, kein aktueller Live-Test. /team/ konnte über die Webabfrage nicht geprüft werden; Belegung unbekannt.

## Lokaler Testworker
Python 3, Standardbibliothek, synthetische Daten. Beispiel:

    python3 worker.py test.sqlite enqueue TEST-001 synthetic
    python3 worker.py test.sqlite run
    python3 worker.py test.sqlite show
    python3 -m unittest -v test_worker.py

Test: vier Threads konkurrieren um einen Auftrag; genau ein Claim, Audit QUEUED/CLAIMED/DONE und doppelte ID abgewiesen. Ausführung nur lokal, kein Scheduler, kein Google-Sheets-Adapter, keine Modell-API, kein Deployment. SQLite-Transaktionen sichern Claims nur für dieselbe lokale Datenbank. Ein Absturz nach CLAIMED bleibt RUNNING: Lease, Recovery, Fehlerbehandlung und Retry fehlen. Nicht produktiv einsetzen. Testdatenbank nicht öffentlich bereitstellen.

## Architektur und Grenzen
Sheets als Bedienoberfläche, dedizierter Worker mit kontrolliertem atomarem Claim und Audit; externe Schreibzugriffe auf Sheets umgehen Apps-Script-Sperren und sind deshalb nicht automatisch synchronisiert. Vor Produktion zentralen Claim-Dienst oder transaktionale Datenbank als maßgebliche Queue vorsehen. Agenten nur über ausdrücklich autorisierte Adapter mit überprüfbarer Herkunft; Empfänger pro Aufgabe getrennt, keine Antworten erfinden. Trennung von öffentlicher Statusansicht und authentifizierter Betriebsansicht. Keine Kundendaten ohne Freigabe. Quoten und Kosten vor Modellaktivierung prüfen. Wissen sammeln, Retrieval, Training und Kompetenzbewertung getrennt behandeln.

## Offen
IONOS-Kontrollzentrum, DNS, tatsächliche Datenbankschemas, Backups und Restore sind nicht unabhängig geprüft. Keine automatisierte Claude-/Claire-Zustellung oder Antwort nachgewiesen. Kein End-to-End-Test mit Sheets, kein produktives UI, kein Commit, keine Veröffentlichung. Nächster Schritt: autorisierten Apps-Script-Ausführungsweg oder separat eingerichteten Worker mit begrenzten Berechtigungen bereitstellen, synthetischen Sheet-Durchlauf testen, dann UI und Deployment nach Prüfung der Pipeline und Zielpfadbelegung.
