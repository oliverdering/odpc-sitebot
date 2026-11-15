# Nachgewiesene Pipeline-Prüfung – 15.11.2026

## Bestätigter SEO-Workflow
- GitHub Actions Run https://github.com/oliverdering/odpc-sitebot/actions/runs/43768326901
- Workflow `ODPC SEO indexability audit (read-only)`, run_number 2, **completed / success** (Connectorabfrage).
- Dies bestätigt den Job-Abschluss, **nicht** Google/Bing-Indexierung. Bericht/Artifact noch auswerten.
- `schedule` ist nur aktiv, wenn Workflow im Default-Branch liegt; PR #389 weiterhin als Draft prüfen.

## Nachgewiesene Deployment-Risiken (Default-Branch)
- `.github/workflows/odpc-remote-deploy.yml`: `concurrency.group: odpc-remote-deploy`; `workflow_dispatch` und Push-Pfade können SFTP-Deploy auslösen.
- `.github/workflows/odpc-premium-header-deploy.yml`: `concurrency.group: odpc-premium-header`; manueller oder Trigger-Datei-Deploy.
- `.github/workflows/shared-footer-deploy.yml`: `concurrency.group: odpc-shared-footer`; eigener SFTP-Deploy.
- Diese Gruppen sind **nicht gemeinsam serialisiert**. Der Scope der SFTP-Zielpfade und übrige Deploy-Workflows muss vor PR #373 geprüft werden.
- Die aktuelle SEO-PR #389 enthält nur docs, ein read-only Audit-Skript und eine Audit-Workflowdatei; keine WordPress-Datei-Deploys. Nicht mit #374 vermischen.

## Nächste Gates
1. CI-Artefakt aus Run #2 lesen, 200/301/404/robots/noindex/canonical getrennt bewerten.
2. Alle SFTP-Workflows und Skriptzielpfade auf potenzielle Überschneidungen prüfen.
3. Schutz-PR #373 korrigieren/vervollständigen, mit WordPress-Backup und Commit-SHA-Preflight testen.
4. Nur den sicheren SEO-Audit-Workflow auf main aktivieren; dessen Schedule dann live verifizieren.
5. IndexNow/RankMath und GSC/Bing separat prüfen; keine unbelegten Indexierungszusagen.
