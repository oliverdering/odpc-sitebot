# ODPC GitHub 2.0 — kontrollierte Restrukturierung

Status: Entwurf / keine Produktionsfreigabe. Stand: 2026-11-16.

## Sicherheitsregeln
- `main` und Live-Websites unverändert lassen, bis Workflow-Trigger, aktive Runs, Deployments, Backups und Rollback geprüft sind.
- Keine Secrets ausgeben, keine Branches oder Workflows pauschal löschen, keine DB-Migrationen auslösen.
- Änderungen nur in isolierten PRs; deploy-relevante Pfade erst nach Trigger- und Zielpfadprüfung anfassen.
- Verbindlicher Firmenname laut Issues #372/#374: **Oliver Dering IT-Consulting**. PR #374 nicht unverändert mergen.

## Zielstruktur (Vorschlag, noch keine Verschiebung)
- `platform/`: gemeinsame Standards, Konfiguration, Freigabegates
- `sites/odpc/`, `sites/ai/`, `sites/drol/`: eigene Releases und Tests
- `customers/<projekt>/`: streng getrennte Kunden-Releases
- `agents/`: AdminMonkey, helper·monkey, weitere Agenten
- `research/`: D.R.O.L.-Experimente und Prototypen
- `operations/`: Runbooks, Rollback, Deploy-Zielmatrix, Inventur

Die vorhandenen Pfade bleiben bis zur geprüften Migration bestehen. Ziel ist keine unkontrollierte Umbenennung.

## Bestätigte Befunde
- 109 Workflow-Dateien laut Issue #414, vollständige Einzelinventur ausstehend.
- `odpc-remote-deploy.yml` und `odpc-prices-deploy.yml` haben Push-Trigger ohne explizite main-Branch-Beschränkung.
- `deploy-odpc-tv.yml` hat keinen Concurrency-Block.
- Mehrere SFTP-Concurrency-Gruppen, teils überlappende Remote-Roots (Issues #372, #415).
- PR #373 deckt nur Teilmenge der Schreiber ab.
- Doppelte D.R.O.L.-Review-Issues; vor Schließung Inhalte und Verweise abgleichen.

## Abnahme-Gates
1. Vollständige Inventur: Workflow, Trigger, Permissions, Schedule, Concurrency, Secrets-NAMEN, Skript, Schreibziel, Domain, Root, Dateien, Rollback.
2. GitHub Actions: queued/in_progress/completed und Deployment-Historie unmittelbar vor jeder Änderung verifizieren.
3. Backup-Snapshot jeder produktiven Root separat wiederherstellbar nachweisen.
4. Konfliktgraph aus tatsächlichen Schreibzielen erstellen; Cross-Root-Workflows gesondert behandeln.
5. Stale-SHA-Check, gemeinsame zielbasierte Locks, sichere temporäre Uploads, Verifikation und Rollback implementieren.
6. Erst Dry-Run und CI, dann Review und explizite Produktionsfreigabe; kontrollierte schrittweise Migration.
7. Nach jeder Migration Live-Smoke-Test, dokumentierte Rückfallprobe und erst danach alten Schreiber deaktivieren.

## Arbeitspakete
- P0: #372, #414, #415 – Inventur und Deployment-Sicherheit.
- P0: #373 – unvollständigen Locking-Entwurf überarbeiten.
- P1: #374 – Firmennamen-Patch korrigieren, ohne Live-WordPress zu überschreiben.
- P1: #388/#389 – SEO nach abgesichertem Releaseprozess.
- P2: PR-Triage und D.R.O.L.-Issue-Deduplizierung ohne Datenverlust.

**Keine der obigen Abnahmen ist durch dieses Dokument bereits erfüllt.**
