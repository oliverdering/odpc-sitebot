# ODPC KI-Team – Claude: nativer Google-Sheets-Lese-/Schreibtest

**Stand:** 2026-11-13 · **Owner:** Oxana / ODPC · **Tracking:** [Issue #296](https://github.com/oliverdering/odpc-sitebot/issues/296)  
**Implementierungsphase:** Claude zuerst; Claire, Lee, Copilot und Vivian danach einzeln.  
**Status:** Lesezugriff durch Claude im ersten Pilot laut Claude-Review belegt; Schreibzugriff **noch nicht** belegt; **kein** autonomer Worker aktiv.

## Entscheidung: offiziellen Claude-Google-Sheets-Connector vor Eigenentwicklung testen

Anthropic dokumentiert inzwischen die separate, native Google-Sheets-Integration mit **Live-Lesen und Live-Bearbeiten**, unabhängig vom allgemeinen Google-Drive-Connector. Die Verbindung nutzt die bestehenden Rechte des authentifizierten Google-Kontos. Dadurch kann der erste **interaktive** Ende-zu-Ende-Test ohne zusätzlichen selbst gehosteten MCP-Server, Apps-Script-Deployment oder Claude-API-Schlüssel stattfinden.

Offizielle Quellen:
- [Anthropic: Google Workspace connectors / Live editing](https://support.claude.com/en/articles/10166901-use-google-workspace-connectors)
- [Anthropic: Claude in Google Docs, Sheets, Slides](https://support.claude.com/en/articles/16951679-use-claude-in-google-docs-sheets-and-slides)

**Wichtig:** Ein Chat-Connector ist **nicht** gleichbedeutend mit unbeaufsichtigtem Betrieb. Für automatische wiederkehrende Auftragsbearbeitung ist später ein autorisierter Dispatcher/Worker mit kontrollierter Laufzeit und ggf. Provider-API erforderlich. Ohne explicit gestattete API-Abrechnung und Datenschutzprüfung keine Aktivierung.

## Einmaliges Setup im Claude-Konto (wird vom Konto-Inhaber autorisiert)

1. In Claude Web (Chrome) oder Claude Desktop das betreffende Claude-Konto öffnen.
2. Im Chat auf `+` > **Connectors** gehen und **Google Sheets** aktivieren (nicht bloß Google Drive).
3. Falls verlangt, mit dem Google-Konto autorisieren, das Zugriff auf das private ODPC-Sheet besitzt. **Keine öffentliche Freigabe** der Tabelle.
4. ODPC KI-Team-Zentrale V1 als Link in Claude einfügen; Claude soll die **bestehende** Tabelle bearbeiten, keine neue Kopie erstellen.
5. Den vorbereiteten Auftrag `TASK-20261113-002` aus dem Tabellenregister `Auftraege` ausführen lassen. Den eigentlichen Testprompt und die einmalige Testkennung ausschließlich aus dem privaten Sheet, Tab `Startprompts`, übernehmen.

Falls das native Bearbeiten nicht angeboten wird, die genaue Fehlermeldung sichern. Alternativ kann nach Kosten-/Berechtigungsprüfung Googles **Google Sheets MCP Server** getestet werden; dieser ist ein **Developer Preview** und erfordert Cloud-Projekt, OAuth-Konfiguration und Freischaltung. Keine Geheimnisse in öffentlichen GitHub-Issues dokumentieren.

## Testplan – TASK-20261113-002

**Vorher:** Tabelle ist privat; Task hat status `NEU`; Nachrichten enthält eine Auftragsnachricht und die Zielantwortzeile ist leer.

**Test:** Claude soll mit seinem eigenen Google-Sheets-Schreibwerkzeug die mit `TASK-20261113-002` verknüpfte neue Nachricht (`MSG-20261113-004`) erzeugen und den vorhandenen Auftrag erst nach erfolgreicher Antwort auf `ERLEDIGT` umstellen. Schreibbereich ausschließlich:
- `Nachrichten!A5:J5`: `message_id`, `task_id`, `von=CLAUDE`, `an=OXANA`, `art=ERGEBNIS`, Zeit, Inhalt mit Testkennung, Notiz; alte Nachrichten unberührt.
- `Auftraege!G3`, `K3`, `L3`: `ERLEDIGT`, Aktualisierungszeit, Ergebnisreferenz.

**Akzeptanzkriterien:**
- [ ] OAuth-Autorisierung durch Kontoinhaber abgeschlossen und Google-Sheets-Connector in Claude aktiv.
- [ ] Claude **liest** genau den vorhandenen Testauftrag aus dem privaten Sheet.
- [ ] Claude **schreibt selbst** eine neue Nachrichtenzeile mit der geforderten Testkennung; kein manuelles Einfügen aus Chat.
- [ ] Claude setzt danach den Task auf `ERLEDIGT` und die korrekte `ergebnis_ref`.
- [ ] Oxana liest und validiert die Werte **live** über den separaten Google-Connector.
- [ ] Der Google-Versionsverlauf bzw. Claudes Toolausführung wird soweit möglich als Provenienzbeleg überprüft. Ein Tabellenwert `von=CLAUDE` **allein** beweist nicht, dass Claude ihn selbst geschrieben hat.
- [ ] Keine Änderung an anderen Zeilen, keine Berechtigungsänderung, keine öffentliche Freigabe, keine Deployments.

**Pass:** `Agenten`-Register aktualisieren auf **READ_WRITE_VERIFIED (interaktiv)**; mit Datum und Nachweis. **Nicht** als `AUTONOM`/Hintergrundworker kennzeichnen.

**Fail:** konkrete fehlende Berechtigung/Toolname/Fehlermeldung protokollieren; Status `SCHREIBTEST OFFEN` oder `BLOCKIERT` statt Erfolg behaupten. Bestehenden Gmail-Pilot nicht beschädigen.

## Nach bestandenem Claude-Schreibtest

1. Datenschema, Statusrechte und Freigabeprüfungen stabilisieren. Für weitere Agenten nur minimal notwendige Berechtigungen und getrennte Identitäten.
2. CLAUDE → CLAIRE → LEE → COPILOT → VIVIAN: für **jeden** Agenten eigener Lese-, Schreib-, Provenienz- und Rechte-Test. Nur bestätigte Fähigkeiten als verfügbar anzeigen.
3. Erst nach Sicherheits-/Kostenentscheidung: GitHub Actions als CI/CD- und QA-Schicht für Dispatcher/Worker; Apps Script LockService/Claim, idempotente IDs, Audit und getrennte Secrets. Kein automatischer Provider-Aufruf durch das Vorhandensein eines Sheets.
4. Freigabegrenze: Keine produktiven WordPress- oder GitHub-Deployments durch Agenten ohne explizite projektbezogene Zustimmung.

## Datenschutz und Zugriff

- Öffentliches Repository: **keine** Google-Sheet-ID, privaten Drive-Links, Kundeninhalte, Testdaten mit Personenbezug, OAuth-Client-Secrets oder API-Tokens.
- Für diesen Test ausschließlich synthetische, nicht vertrauliche Daten.
- Claude-Connector kann mit den Rechten des autorisierten Google-Kontos arbeiten; deshalb **bewusst eingeschränkte** Aufgaben formulieren und Änderungen kontrollieren.
- Der Google-Sheets-Connector benötigt eine manuell gestartete Claude-Konversation. Für unsichtbare Hintergrundverarbeitung ist ein separater Worker erforderlich.
