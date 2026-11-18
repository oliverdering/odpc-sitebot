# ODPC Deployment-Inventur – verifizierter Ausschnitt

Stand 2026-11-16. Nur aus GitHub-Dateien gelesen; **keine vollständige Inventur aller 109 Workflows**. Remote-Roots müssen gegen Skripte und Live-Konfiguration verifiziert werden.

| Workflow | Trigger | Concurrency | Schreibart / Risiko | Maßnahme |
|---|---|---|---|---|
| `odpc-remote-deploy.yml` | workflow_dispatch, push paths ohne branch-filter | odpc-remote-deploy | SFTP via `deploy_odpc_remote.py` | Branch-Filter und Remote-Ziel prüfen |
| `odpc-prices-deploy.yml` | workflow_dispatch, push paths ohne branch-filter | odpc-prices-release | SFTP via `deploy_odpc_prices.py`, Backup-Artifact 15 Tage | Branch-Filter, SHA-Sperre und Zielkonflikte prüfen |
| `deploy-odpc-tv.yml` | workflow_dispatch | **keine** | SFTP via `deploy_odpc_tv.py`, Root `/clickandbuilds/Dering` | Ziel-Lock und SHA-Check erforderlich |
| `odpc-regional-seo.yml` | workflow_dispatch, push main mit paths | odpc-regional-seo | SFTP, Live-Schema-Verifikation, Rollback-Artifact | Fehlerursache vor neuem Release beheben |
| `shared-footer-deploy.yml` | workflow_dispatch, push main mit paths | odpc-shared-footer | SFTP via `deploy_shared_footer.py`, mehrere Site-Backups laut Workflow | Alle Sites/Roots ermitteln |
| `odpc-about-2026.yml` | pull_request (Preflight), push main, workflow_dispatch | odpc-about-2026 | SFTP bei Nicht-PR, danach Cache-Refresh | Zwei Schreibphasen und Zielkonflikte erfassen |
| `adminmonkey-db-import.yml` | workflow_dispatch, `apply=false` als Default | adminmonkey-db-import | MariaDB nur bei `apply=true`, Environment adminmonkey-production | Nicht mit anderem 5-Minuten-Importer verwechseln; DB-Backup prüfen |

## Wichtige Differenzierung
Das in #414 genannte Fünf-Minuten-Intervall betrifft einen anderen AdminMonkey-Wissensimport, **nicht** die hier geprüfte Datei `adminmonkey-db-import.yml`, die nur manuell gestartet wird.

## Freigabesperren
- Ohne vollständige Run-/Deployment-Übersicht keine Änderungen an produktiven Workflow-Dateien.
- Kein `main`-Merge und kein Live-Deploy aus diesem Dokument.
- Die Zielpfad-Matrix ist unvollständig; Secrets-Namen sind kein Beweis für gleiche Remote-Roots.
