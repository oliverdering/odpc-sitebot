# ODPC / D.R.O.L. – Deployment und Betriebsübergabe

Stand: 2026-11-07. Dieses Dokument beschreibt verifizierte Wege und verhindert, dass der nächste Bearbeitungslauf bei null beginnt.

## Zwei getrennte Veröffentlichungswege

### ODPC-Dachseite
- URL: https://odpc.de/
- CMS: WordPress 7.1.4, Theme Astra 4.14.2 (Stand 2026-11-07)
- Connected App: WPVibe, site_url https://odpc.de, Adminzugang via WPVibe-Connector, keine Passwörter im Chat oder Repository.
- WPVibe site_info verifiziert Verbindung am 2026-11-07; Wordfence hatte WordPress Application Passwords blockiert, Nutzer hat die WPVibe-Ausnahme aktiviert.
- WordPress REST API: /wp/v2/pages; bei großen bestehenden Seiten ausschließlich selektive serverseitige Änderungen über POST /wpvibe/v1/content/search und /wpvibe/v1/content/edit. Keine Komplettüberschreibung großer HTML-Inhalte.
- Wesentliche Seiten: Dienstleistungen & Produkte (ID 2609), Kunden & Live-Referenzen (3344), Webdesign (1185), ältere D.R.O.L.-WordPress-Seite (2790), neue D.R.O.L.-Fallstudie (3653, https://odpc.de/referenz-drol-webplattform).
- Layout/Astra/Startseite unverändert lassen; Freigaben für gefährliche Änderungen nicht pauschal umgehen.

### D.R.O.L. Subdomain
- URL: https://drol.odpc.de/
- Kein WordPress-Runtime: statisches HTML/CSS/JavaScript auf IONOS.
- GitHub Repository: oliverdering/odpc-sitebot, branch main.
- Quellpfad: site-deploy/drol.odpc.de/
- GitHub Workflow: .github/workflows/drol-deploy.yml (Name D.R.O.L. / Isolated website deploy), getriggert durch Push auf main mit Änderungen an site-deploy/drol.odpc.de/**, scripts/deploy_drol.py oder Workflow selbst; auch workflow_dispatch.
- Deploy: Python scripts/deploy_drol.py via SFTP, ausschließlich IONOS-Verzeichnis /drol; GitHub Secret ODPC_SFTP_PASSWORD. Nicht in andere ODPC-Subdomains deployen.
- Datenbank: IONOS MariaDB; SQL-Migrationen 001_catalog.sql, 002_evidence_model.sql, 003_knowledge_core.sql. Secrets nur in GitHub Actions.
- Datenfluss: Automatisierte Quellen-/Release-Überwachung -> ungeprüfte Kandidaten (E0) -> MariaDB -> allowlist-basierter JSON-Export -> öffentlicher Katalog. Kein direkter Browser-Zugriff auf MariaDB. Export via scripts/drol_ionos_db_export.py und .github/workflows/drol-db-export.yml.
- Weitere Workflows: drol-knowledge-discovery.yml, drol-candidate-ingest.yml, drol-source-monitor.yml, drol-site-audit.yml.
- Projekt-Referenz: https://drol.odpc.de/odpc-case-study.html und auf ODPC https://odpc.de/referenz-drol-webplattform.

## Sicherheits- und Qualitätsschranken
- Niemals Zugangsdaten, DB-Passwörter, API-Schlüssel oder Application Passwords in Dateien, Issues oder Antworten veröffentlichen.
- Keine Änderung des freigegebenen D.R.O.L.-Designs oder der ODPC-Startseite ohne Auftrag.
- GitHub-Commit ist nicht gleich Live-Deployment. GitHub Actions auf Erfolg prüfen und Live-URL separat abrufen.
- Externe Links und Anker auf tatsächliche Zielseiten testen; Datenkandidaten nicht als verifizierte Fakten ausgeben.
- WPVibe-Änderungserfolg ist nicht gleich visuell geprüfte öffentliche Seite; bei Cache/Rendering gesondert testen.

## Geschäftsmodell und Dokumentation
- ODPC-Leistung: Websites, Hosting & intelligente Webplattformen (Migration, Datenbanken, KI-Integration, APIs, CI/CD, Wartung).
- D.R.O.L. dokumentiert Entwicklung von persönlicher Sondel-/Drohnenidee über WordPress bis zu eigenständiger Datenbankplattform. Zwischen realisierten und geplanten Funktionen trennen.
- Relevante WordPress-Seiten bei Projektmeilensteinen aktualisieren; keine unbelegten Leistungsversprechen.
