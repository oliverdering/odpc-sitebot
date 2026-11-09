# ODPC – zentraler Administrations- und Zugangsweg (ohne Geheimnisse)

Stand: 2026-11-09. Verbindliche Arbeitsanweisung für ChatGPT/Codex und andere ODPC-Assistenten.

## Grundregel
Vor jeder erneuten Zugangsfrage: dieses Dokument und die vorhandenen Workflows/Deploy-Skripte prüfen. Keine Passwörter, Tokens, private Schlüssel oder Datenbankpasswörter in Git, Chat, Logs oder Memory speichern. Vorhandene GitHub Actions Secrets verwenden; Secret-Werte sind aus GitHub nicht auslesbar. Verfügbarkeit, Rechte und erfolgreichen Lauf stets prüfen, niemals unterstellen.

## Primärer kostenfreier Verwaltungsweg
1. GitHub-Repository: https://github.com/oliverdering/odpc-sitebot ; Standardbranch `main`.
2. Änderungen über verbundene GitHub-Dateiaktionen, möglichst kleine überprüfbare Commits.
3. Ausführung und Veröffentlichung über GitHub Actions; SFTP und Datenbank ausschließlich über vorhandene GitHub-Secrets und geprüfte Skripte.
4. Nach Änderungen Workflow-Lauf, Artefakte und öffentliche URL unabhängig verifizieren. Kein Erfolg ohne Nachweis.
5. WPVibe ist KEIN Standardzugang: quotiert und auf ai.odpc.de zuletzt mit HTTP 404 für WPVibe-Route. Nicht erneut als erste Option nutzen.

## Websites und Projektpfade
- Dachmarke https://odpc.de/ – WordPress/IONOS.
- Helper Monkey https://ai.odpc.de/ – Quellcode/Deploy unter `site-deploy/ai.odpc.de/`.
- AdminMonkey Monitor https://ai.odpc.de/adminmonkey/monitor/ – `site-deploy/ai.odpc.de/adminmonkey/monitor/index.html`; Workflow `.github/workflows/adminmonkey-monitor-deploy.yml`.
- D.R.O.L. https://drol.odpc.de/ – `site-deploy/drol.odpc.de/`; Datenbank-Workflows unter `.github/workflows/drol-*.yml`.
- Sonde https://odpc.de/sonde/ – WordPress-Seite auf odpc.de.
- Tante Lenchen https://tante-lenchen.odpc.de/ – Kundenseite; vor Änderungen eigenes Deployment und Berechtigung prüfen.
- Weitere betreute Auftritte: ra-justen.de, atrium191-rb.odpc.de und weitere ODPC-Projekte. Nicht annehmen, dass alle denselben Host oder dieselben Credentials nutzen; Repository/Workflow pro Ziel verifizieren.

## AdminMonkey – verbindliche Infrastruktur
- Lernworkflow: `.github/workflows/adminmonkey-daily-knowledge.yml`.
- Crawler: `adminmonkey/knowledge/crawl_learn.py`.
- MariaDB-Importer: `adminmonkey/database/migrate_import.py`.
- Additives Schema: `adminmonkey/database/001_knowledge_schema.sql` (`amk_sources`, `amk_documents`, `amk_evaluations`).
- Separater manueller Import: `.github/workflows/adminmonkey-db-import.yml`.
- Benötigte Secrets: `AMK_DB_HOST`, `AMK_DB_PORT` (optional), `AMK_DB_NAME`, `AMK_DB_USER`, `AMK_DB_PASSWORD`. Namen sind keine Zugangsdaten. Workflow-Environment `adminmonkey-production` beim manuellen Import beachten; Secrets können Environment- statt Repository-Scope haben.
- Persistente Nachweise: `adminmonkey/knowledge/document_manifest.json`, `academy_progress.json`, `database_progress.json` (nur nach erfolgreichem Lauf). Privater Volltext-Cache: `crawl_state.json` via Actions Cache.
- Monitor liest GitHub-Raw-Daten; sechs Monitoring-Quellen sind nicht sechs gelernte Artikel.
- Dokumentensammlung ist kein belegtes Agentenwissen; Prüfungen und Zertifizierungen getrennt ausweisen.
- **Offener technischer Prüfpunkt:** Der kontinuierliche Lernworkflow verwendet derzeit Repo-Secrets, während der manuelle Import `environment: adminmonkey-production` nutzt. Vor Produktion prüfen, ob Secrets im richtigen Scope liegen. GitHub-Runner kann bei IONOS durch externe DB-Firewall blockiert sein; vorhandener `drol-db-preflight.yml` beschreibt lokalen PDO-Fallback. Keine erfolgreiche DB-Verbindung behaupten, solange kein Lauf belegt ist.
- PR #66 experimentell: nicht ohne gesonderte Freigabe mergen oder Backend deployen.

## Veröffentlichung
- Monitor: `.github/workflows/adminmonkey-monitor-deploy.yml`, SFTP mit Hostkey-Pinning und HTTP-Smoke-Test.
- Helper Monkey: zuständigen V4/Global-UI-Workflow und `scripts/deploy_helpermonkey_v4.py` prüfen; dieser ist nicht automatisch der Monitor-Deploy.
- D.R.O.L. eigene Workflows und Zielpfade nutzen; keine projektfremden Credentials übertragen.
- Falls GitHub Actions fehlt oder blockiert: tatsächlichen Lauf/Fehler ermitteln, nicht blind neue Zugangsdaten anfordern.

## Betriebs-Checkliste
1. Ziel-Domain, Repository-Pfad und zuständigen Workflow bestimmen.
2. Bestehende Secrets nur anhand ihrer Namen referenzieren; Werte nie ausgeben.
3. Branch, Diff und Sicherheitsgrenzen prüfen; Änderungen committen.
4. Workflow auslösen oder Push-Trigger nutzen; Status/Logs verifizieren.
5. Bei Datenbankarbeiten vorher Verbindung, Backup, Ziel-DB und Tabellennamen prüfen; niemals WordPress-Tabellen überschreiben.
6. Öffentliche URL prüfen und messbare Änderung dokumentieren.
7. Bei Fehlern den exakten technischen Blocker und bereits geprüfte Alternativen festhalten; nicht dieselbe Anmeldung erneut verlangen.

## Grenzen
Dieses Dokument speichert Zugangswege, nicht geheime Daten. Ein Chat kann keinen dauerhaften Session-Token oder ununterbrochenen Zugriff garantieren. Für neue Chat-Sitzungen zuerst diese Datei aus GitHub abrufen und den realen aktuellen Zustand prüfen.
