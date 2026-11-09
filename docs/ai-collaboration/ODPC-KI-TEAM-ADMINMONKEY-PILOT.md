# ODPC KI-Team – AdminMonkey Pilot / Teamprotokoll

Status: Architektur und Arbeitsregeln definiert; **keine** automatische Anbindung externer KI-Anbieter hierdurch aktiviert.

## Rollen
- Oliver: Auftraggeber, Freigabe von produktiven Änderungen, Budget und Berechtigungen.
- Oxana (ChatGPT): Orchestrierung, Zusammenführung, GitHub-Integration und Fortschrittskommunikation.
- Claude (Anthropic): Architektur, DevOps, Sicherheits- und Qualitätsprüfung.
- Claire (Google Gemini): Recherche, Microsoft-Learn-Quellenprüfung und Knowledge Engineering.
- Lee (DeepSeek): Implementierungslogik, Code-Review und technische Tests.
- Microsoft Copilot: Microsoft-365-Fachprüfung, Dokumentation und Microsoft-Learn-Validierung.

## Ein gemeinsamer Arbeitskanal
GitHub `oliverdering/odpc-sitebot` ist der verbindliche, versionierte Aufgaben- und Ergebnis-Kanal. `docs/ai-collaboration/` enthält Rollen, Übergaben und Prüfprotokolle. GitHub Actions führt ausschließlich explizit definierte Tests und Deployments aus. Google Drive „ODPC – KI-Agenten“ kann als gemeinsam lesbarer Dokumentenbereich dienen, ist aber kein bestätigter automatischer Modell-zu-Modell-Kanal. Gmail ist ein optionaler Benachrichtigungs- und Rückkanal, **nicht** der Ort für Passwörter oder Datenbankexporte.

## Arbeitsobjekt
Jede Aufgabe erhält: Ticket-ID, Projekt, Priorität P0/P1/P2, Ziel, verantwortliches Modell, benötigte Quellen, Berechtigungsgrenze, erwartetes Artefakt, Testkriterium, Status und nächsten Schritt. Status: QUEUED, IN_PROGRESS, REVIEW, BLOCKED, DONE. Jeder Bericht enthält Datum, Modell/Provider, Quellen, offene Risiken und nachprüfbare Ergebnisse. Modellantworten sind nicht automatisch verifiziert.

## AdminMonkey: priorisierte Pipeline
1. P0: Crawler-Tests und GitHub-Actions-Läufe stabilisieren; Logs und Commit-SHA protokollieren.
2. P0: MariaDB-Zugriff ausschließlich auf `dbs16323373` über IONOS-internen, authentisierten und temporären **lesenden** Test verifizieren. Keine Datenbank-Secrets im Repository oder in öffentlichen Endpunkten.
3. P0: Schema und Backup vor jedem Import prüfen; produktive Schreiboperationen nur mit eindeutig bestätigtem Ziel und kontrollierter Freigabe.
4. P1: Claire bewertet Quellen und Metadaten, Claude prüft Architektur/Sicherheit, Lee testet Importlogik, Copilot prüft Microsoft-Terminologie. Oxana führt Ergebnisse und Evidenz zusammen.
5. P1: Monitor zeigt nur tatsächlich verifizierte Datenbankverbindung und dokumentierte Lernzahlen.

## Verbindungen und Grenzen
- Google-Konto-Verknüpfung mit verschiedenen Chat-Apps ist **kein** automatischer gegenseitiger Zugriff.
- `integrations/gemini-mail-worker/` ist vorhandener **nicht installierter** Google-Apps-Script-Pilot. Die README nennt einmalige OAuth- und API-Einrichtung; nicht als live ausgeben.
- Claude, DeepSeek und Microsoft Copilot sind **nicht** durch dieses Dokument automatisch angeschlossen. Für echte autonome Mitarbeit braucht jeder einen ausdrücklich autorisierten API-/Connector-Worker, einen eigenen beschränkten Token und ein nachvollziehbares Rückkanalprotokoll.
- Niemals gemeinsame Passwörter, Datenbank-Credentials oder unbeschränkte Schreibrechte an mehrere Modelle verteilen. Kundendaten und vertrauliche Akten bleiben aus externen KI-Queues ohne gesonderte Rechts- und Datenschutzprüfung.
- Schreibende GitHub-Aktionen: Branch/PR, Tests, Review und nur freigegebene Deployments; keine stillen Änderungen an fremden ODPC-Projekten.

## Kommunikationsregel
Bei jedem relevanten Übergang: **IN ARBEIT** (konkreter laufender Schritt), **ERLEDIGT** (Beleg/Commit/Testergebnis), **BLOCKIERT** (Ursache und nächster Schritt). Automatisierte Hintergrundarbeit nur, wenn ein tatsächlich eingerichteter Workflow sie ausführt. Keine behauptete Dauerüberwachung durch eine inaktive Chat-Sitzung.
