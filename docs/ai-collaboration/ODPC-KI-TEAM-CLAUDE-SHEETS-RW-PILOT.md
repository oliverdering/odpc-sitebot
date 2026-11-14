# ODPC KI-Team – Claude: nativer Google-Sheets-Lese-/Schreibtest

**Stand:** 2026-11-13 · **Owner:** Oxana / ODPC · **Tracking:** [Issue #296](https://github.com/oliverdering/odpc-sitebot/issues/296)  
**Implementierungsphase:** Claude zuerst; Claire, Lee, Copilot und Vivian danach einzeln.  
**Status:** Erstes Lesen laut Claude-Review belegt. **Aktueller Chat-Test vom 13.11.2026 gescheitert:** kein Google-Sheets-Schreibtool und nun auch kein Google-Drive-Lesetool in dieser Claude-Sitzung. Im privaten Sheet: `TASK-20261113-002` = `BLOCKIERT`, `MSG-20261113-004` fehlt. Kein autonomer Worker aktiv.

## Entscheidung: offiziellen Claude-Google-Sheets-Connector vor Eigenentwicklung testen

Anthropic dokumentiert inzwischen die separate, native Google-Sheets-Integration mit **Live-Lesen und Live-Bearbeiten**, unabhängig vom allgemeinen Google-Drive-Connector. Die Verbindung nutzt die bestehenden Rechte des authentifizierten Google-Kontos. Dadurch kann der erste **interaktive** Ende-zu-Ende-Test ohne zusätzlichen selbst gehosteten MCP-Server, Apps-Script-Deployment oder Claude-API-Schlüssel stattfinden.

Offizielle Quellen:
- [Anthropic: Google Workspace connectors / Live editing](https://support.claude.com/en/articles/10166901-use-google-workspace-connectors)
- [Anthropic: Claude in Google Docs, Sheets, Slides](https://support.claude.com/en/articles/16951679-use-claude-in-google-docs-sheets-and-slides)

**Wichtig:** Ein Chat-Connector ist **nicht** gleichbedeutend mit unbeaufsichtigtem Betrieb. Für automatische wiederkehrende Auftragsbearbeitung ist später ein autorisierter Dispatcher/Worker mit kontrollierter Laufzeit und ggf. Provider-API erforderlich. Ohne explicit gestattete API-Abrechnung und Datenschutzprüfung keine Aktivierung.

## Nach gescheitertem Chat-Test: zwei offizielle Integrationswege

**Priorität 1 – Offizielles Anthropic-Workspace-Add-on direkt in Google Sheets (interaktiv):**

1. Auf einem Rechner mit **Chrome, Edge oder Safari** und einem **berechtigten kostenpflichtigen Claude-Plan** das offizielle Anthropic-Add-on aus dem [Google Workspace Marketplace](https://workspace.google.com/marketplace/app/claude/12459801340) installieren. Ein Workspace-Administrator muss die Installation ggf. erlauben.
2. Mit dem Google-Konto anmelden, das das private ODPC-Sheet bearbeiten darf; Zugriffsberechtigungen sorgfältig prüfen. Kein öffentliches Teilen.
3. Das **bestehende** Google Sheet öffnen; **Erweiterungen > Claude > Open Claude**; in der Seitenleiste mit Claude anmelden und Bearbeitungsbestätigungen freigeben.
4. Der konkrete Prüfprompt für `TASK-20261113-002` steht im **privaten** Sheet, Tab `Startprompts`. Claude darf ausschließlich die vorgesehenen Zellen bearbeiten. Nach Prüfung der Antwort Status setzen.
5. Die Seitenleiste geöffnet lassen; sie führt **keine** Hintergrundjobs aus. Der Google-Versionsverlauf zeigt Änderungen unter der authentifizierten Google-Person, nicht als technisch separate Claude-Identität. Prozess-/Toolnachweis daher zusätzlich dokumentieren.

**Priorität 2 – Alternativ native Claude-Chat-Connectors:**
- In `claude.ai` > `+` > **Connectors** die **separaten** Google Sheets/Docs/Slides Connectoren prüfen. Bei Team/Enterprise muss ggf. der Owner diese aktivieren. Fehlende Tools können nicht durch einen Prompt nachträglich erzeugt werden.
- Bei OAuth-Problemen Claude > **Customize > Connectors** auf Verbindungsstatus und ggf. Neuautorisierung prüfen; nicht ohne Not bestehende Google-Zugriffe entfernen.

**Nicht mit einer vollautonomen Agenten-Bridge verwechseln:** Beide Wege sind vorerst nutzergetriggerte interaktive Bearbeitungen. Für autonomes Polling später verifizierbaren Worker mit separaten Credentials, Idempotenz, Audit, Quoten- und Kostenkontrolle entwickeln. Drittanbieter-API-Zahlungen nicht ohne Budgetfreigabe aktivieren.

Bei nicht verfügbarem Add-on: konkrete Konto-/Plan-/Administrator-Beschränkung protokollieren. Kein sinnloser erneuter derselbe Chat-Test. Keine Geheimnisse in öffentlichen GitHub-Issues dokumentieren.

## Testplan – TASK-20261113-002

**Aktuell:** Tabelle ist privat; Task steht nach fehlgeschlagenem Chat-Test auf `BLOCKIERT`, die Ergebniszeile ist noch leer. Nach Installation des Add-ons den Test wiederaufnehmen. Kein Erfolgseintrag bis der tatsächliche Write nachgewiesen ist.

**Test:** Claude soll mit seinem eigenen Google-Sheets-Schreibwerkzeug die mit `TASK-20261113-002` verknüpfte neue Nachricht (`MSG-20261113-004`) erzeugen und den vorhandenen Auftrag erst nach erfolgreicher Antwort auf `ERLEDIGT` umstellen. Schreibbereich ausschließlich:
- `Nachrichten!A5:J5`: `message_id`, `task_id`, `von=CLAUDE`, `an=OXANA`, `art=ERGEBNIS`, Zeit, Inhalt mit Testkennung, Notiz; alte Nachrichten unberührt.
- `Auftraege!G3`, `K3`, `L3`: `ERLEDIGT`, Aktualisierungszeit, Ergebnisreferenz.

**Akzeptanzkriterien:**
- [ ] Offizielles Claude-for-Google-Workspace-Add-on oder nativer Google-Sheets-Chat-Connector nachweislich **mit Bearbeitungsrecht** autorisiert.
- [ ] Claude **liest** genau den vorhandenen Testauftrag aus dem privaten Sheet.
- [ ] Claude **schreibt selbst** eine neue Nachrichtenzeile mit der geforderten Testkennung; kein manuelles Einfügen aus Chat.
- [ ] Claude setzt danach den Task auf `ERLEDIGT` und die korrekte `ergebnis_ref`.
- [ ] Oxana liest und validiert die Werte **live** über den separaten Google-Connector.
- [ ] Der Google-Versionsverlauf bzw. Claudes Toolausführung wird soweit möglich als Provenienzbeleg überprüft. Ein Tabellenwert `von=CLAUDE` **allein** beweist nicht, dass Claude ihn selbst geschrieben hat.
- [ ] Keine Änderung an anderen Zeilen, keine Berechtigungsänderung, keine öffentliche Freigabe, keine Deployments.

**Pass:** `Agenten`-Register aktualisieren auf **READ_WRITE_VERIFIED (interaktiv)**; mit Datum und Nachweis. **Nicht** als `AUTONOM`/Hintergrundworker kennzeichnen.

**Fail (bereits eingetreten):** Claude meldet keinen Google-Sheets-Schreibconnector; Google-Drive-Tools in dieser Sitzung ebenfalls fehlend. Oxana prüfte die Live-Tabelle: Ergebniszeile leer, Task unverändert. Blocker unter `BLOCKIERT` protokolliert. Add-on-Weg ist noch ungetestet. Bei weiteren Fehlern genaue Meldung erfassen. Bestehenden Gmail-Pilot nicht beschädigen.

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
