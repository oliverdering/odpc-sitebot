# ODPC & Rechtsanwaltskanzlei Justen – GitHub statt limitiertem WPVibe

Stand: 14.10.2026. Dieser Plan verwendet nur vorhandene kostenlose GitHub-/WordPress-Werkzeuge; er richtet **keine Abonnements und keine zusätzlichen kostenpflichtigen Dienste** ein.

## Bestehender und verifizierter Ausgangspunkt

- ATRIUM: privates Repository [oliverdering/atrium191-odpc-](https://github.com/oliverdering/atrium191-odpc-), Theme-Pipeline mit gezieltem SFTP-Upload, vorherigem Dateisnapshot, Smoke-Test und Rollback.
- ATRIUM-SFTP-Konto ist auf **/Atrium** eingeschränkt. **Nicht für ODPC oder Kanzlei Justen verwenden.**
- ODPC: vorhandenes **öffentliches** Repository [oliverdering/odpc-sitebot](https://github.com/oliverdering/odpc-sitebot), bestehender automatischer Seitenindex in `build.yml`; noch **kein bestätigtes produktives ODPC-Deployment**.
- Rechtsanwaltskanzlei Justen: `https://ra-justen.de/`, WordPress mit Theme `akj-kanzlei-v1`; kein dediziertes GitHub-SFTP-Deployment verifiziert.

## Phase 1 – ODPC und ai.odpc.de: ein IONOS-Login, getrennte Theme-Zielpfade

Auf ausdrücklichen Wunsch sollen **odpc.de und ai.odpc.de denselben dedizierten IONOS-SFTP-Benutzer** nutzen. Das funktioniert nur, wenn beide WordPress-Installationen im **gleichen IONOS-Webspace/Zugriffsbereich** dieses Benutzers liegen. Das Home-Verzeichnis des SFTP-Benutzers muss beide Document-Roots erreichbar machen; keinesfalls den auf `/Atrium` beschränkten ATRIUM-Benutzer verwenden.

Im Workflow `ODPC / WordPress SFTP connection check` gibt es jetzt eine Auswahl `odpc.de` oder `ai.odpc.de`. Host, Benutzer, Passwort und geprüfter Fingerprint sind gemeinsam; nur der freigegebene Theme-Pfad ist je Website unterschiedlich. Beide Pfade erst **nach Prüfung der tatsächlichen Verzeichnisstruktur** hinterlegen. Ein SFTP-Login allein vermittelt keinen WordPress-Administratorzugang oder Datenbankzugriff.

## Vorbereiteter, nur lesender Verbindungstest

Neu in diesem Repository:

- `scripts/check_wordpress_sftp.py` – verifiziert den unabhängig geprüften SSH-Serverfingerprint und kann anschließend **nur lesend** prüfen, ob das gewünschte Theme-Verzeichnis erreichbar ist.
- `.github/workflows/odpc-sftp-check.yml` – manueller GitHub-Actions-Aufruf, keine automatische Ausführung bei Push, **kein Upload und keine Löschung**.

Einmalig auf der [GitHub-Actions-Secrets-Seite](https://github.com/oliverdering/odpc-sitebot/settings/secrets/actions) (Repository Secrets) hinterlegen:

| Secret | Beschreibung |
|---|---|
| `ODPC_SFTP_HOST` | Vom **ODPC-Webhosting** bestätigter SFTP-Hostname, nicht automatisch vom ATRIUM-Hosting übernehmen |
| `ODPC_SFTP_USER` | Dedizierter SFTP-Benutzer mit möglichst beschränkten Dateirechten |
| `ODPC_SFTP_PASSWORD` | Dessen SFTP-Passwort, nur als verschlüsseltes Secret; **niemals in Code, Chats oder README kopieren** |
| `ODPC_SFTP_HOSTKEY_SHA256` | Vom Hoster oder anderweitig **unabhängig** bestätigter SSH-Host-Key-Fingerprint |
| `ODPC_THEME_PATH` | Exakter absoluter Theme-Pfad der Hauptwebsite **odpc.de**, einschließlich `/wp-content/themes/<themename>` |
| `AI_ODPC_THEME_PATH` | Separater absoluter Theme-Pfad für **ai.odpc.de**; bisher verwendeter Theme-Slug `helpermonkey-business-v34-child` – aktuellen Stand vor Einsatz kontrollieren |

`ODPC_SFTP_PORT` ist im Workflow auf Standardport 22 eingestellt. Falls der Hoster einen anderen Port zuweist, Workflow **vor** dem Check anpassen.

Im Tab **Actions** den Workflow **ODPC / WordPress SFTP connection check** starten: zuerst `fingerprint` (nur Server-Key auslesen, **keine Authentifizierung**), extern beim Hoster verifizieren, dann `check` für **odpc.de** und separat `check` für **ai.odpc.de** (geprüfter Host-Key, derselbe Login, unterschiedliche Theme-Verzeichnisse). Erst wenn **beide Tests PASS** melden, dürfen wir separate, allowlist-gebundene Deploy-Workflows vorbereiten. Die GitHub-Secrets sind im Repository derzeit nicht unabhängig auf Vollständigkeit prüfbar; fehlende Werte müssen durch berechtigte Personen hinterlegt werden.

Der später zu erstellende Deploy-Workflow muss WordPress-Kern, Medien, Plugins, Datenbank und andere Verzeichnisse unangetastet lassen. Er soll ausschließlich bestätigte Theme-Dateien atomar schreiben, vorher einen gesicherten Snapshot ausführen, die Website testen und bei Fehlern den Ausgangszustand wiederherstellen.

**Wichtig:** Dieses Repository ist **öffentlich**. In Commits, Workflows, Issues, Logs und GitHub-Actions-Artefakten dürfen niemals Passwörter, personenbezogene Mandantendaten oder Zugangstokens landen.

## Phase 2 – Kanzlei Justen: rechtlich und technisch getrennt

Für `ra-justen.de` ein **eigenes privates GitHub-Repository** anlegen (z. B. `oliverdering/ra-justen-website`). In dieser GitHub-Verbindung ist das automatische Anlegen neuer Repositories nicht verfügbar; das Repository muss einmalig über GitHub erstellt werden.

Kanzleispezifische Verbindungsdaten getrennt verwalten: Hosting-Anbieter STRATO, dessen SFTP-Zugang, Host-Key-Fingerprint und **korrekter WordPress-Theme-Pfad**. Keine ATRIUM-, ODPC- oder privaten Zugangsdaten wiederverwenden. Anwaltliche Verschwiegenheit und Mandatsdaten bleiben von dem öffentlichen Webdesign-Deployment strikt getrennt.

Die produktive Kanzlei-Website hat das Theme `akj-kanzlei-v1`. Ein ODPC-Partnerhinweis wurde bereits im WPVibe-Theme-Entwurf v1.4.2 vorbereitet; vor einem alternativen GitHub-Upload erst prüfen, ob er mittlerweile veröffentlicht oder per WordPress-Plugin doppelt aktiv wurde. Die rechtlichen Pflichtseiten dürfen nicht durch Theme-Uploads überschrieben werden.

## Grenzen und tatsächlicher Status

GitHub Actions + SFTP kann **WordPress-Theme-Dateien** ohne WPVibe bereitstellen. WordPress-Seiten, Medien, Nutzer, Menüs oder Pluginkonfigurationen liegen in der **Datenbank** und benötigen eine separate authentifizierte WordPress-REST-/WP-CLI- oder Datenbank-Strategie. Ein SFTP-Deploy ist kein universeller WordPress-Adminzugang.

**Status:** GitHub-Vorbereitung für beide ODPC-Domains erstellt; **gemeinsamer SFTP-Benutzer noch nicht für beide Document-Roots authentifiziert**. Für Kanzlei Justen noch kein separates privates Repository und kein SFTP-Test. Kein aktives ODPC-/Kanzlei-Produktivdeployment über diese neuen Workflows.
