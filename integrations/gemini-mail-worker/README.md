# ODPC KI-Team – Gemini-Mail-Worker

Status: installierbarer Pilot (Google Apps Script) | 15.10.2026 – direkter Thread-Rückkanal und Nachrecherche korrigiert

ChatGPT ↔ autorisierte Gmail-Konten ↔ Gemini Developer API ↔ gemeinsames Google-Dokument.

**Achtung:** Diese Dateien sind in GitHub veröffentlicht, aber noch nicht in Google Apps Script installiert. Ohne einmalige Google-OAuth-Freigabe und einen Gemini-API-Schlüssel findet KEINE automatische Gemini-Ausführung statt. Das vorhandene ChatGPT-Monitoring des Rückkanals ist ein separater Prozess.

## Inbetriebnahme im richtigen Google-Konto

1. Melde dich beim Google-Konto an, in dessen Gmail-Posteingang die E-Mail mit dem Betreff **[ODPC-KI-TEAM][BRAND-QA-20261015][AN-GEMINI]** eingegangen ist.
2. Öffne https://script.google.com/home/projects/create – erstelle ein Projekt **ODPC KI-Team Gemini Worker**.
3. Öffne die Quellcodedatei [Code.gs](Code.gs), kopiere ihren kompletten Inhalt in die Datei Code.gs im Apps-Script-Editor.
4. Öffne in Apps Script **Projekteinstellungen** und aktiviere **Manifestdatei im Editor anzeigen**. Übernimm die Inhalte von [appsscript.json](appsscript.json). So sind die benötigten Berechtigungen transparent.
5. Erstelle über https://aistudio.google.com/apikey einen Gemini Developer API Key für das Free Tier. Der Schlüssel kommt ausschließlich nach **Projekteinstellungen → Script Properties**. **Nicht in den Chat, eine Mail oder GitHub kopieren!** Ein kostenpflichtiger Google-Cloud-Abrechnungsaccount ist für diesen Prototyp nicht vorgesehen.
6. Lege folgende Script Properties an:
   - GEMINI_API_KEY = persönlicher API-Schlüssel (geheim)
   - ODPC_DOC_ID = ID des bereits vorhandenen ODPC-KI-Team-Google-Dokuments
   - ODPC_ALLOWED_SENDER = E-Mail-Adresse des ChatGPT-Absenderpostfachs
   - ODPC_WORKER_ACCOUNT = E-Mail-Adresse, in deren Konto das Script ausgeführt wird
   - Optional: GEMINI_MODEL = gemini-2.5-flash-lite
   - Optional: ODPC_SEARCH_GROUNDING = true (Google-Suche für beleggestützte Recherche; vorab Free-Tier-Quoten und Abrechnung prüfen, standardmäßig AUS)
7. Wähle die Funktion **testOdpcKiTeam** aus und klicke **Ausführen**. Beim ersten Start die Google-OAuth-Berechtigungen ausdrücklich bestätigen. Das Testprotokoll nennt Modell und Docs-Erreichbarkeit; niemals den Schlüssel.
8. Erst wenn der Test erfolgreich ist, **setupOdpcKiTeam** einmal ausführen. Damit wird **ein** zeitgesteuerter Trigger eingerichtet, der die Queue alle 15 Minuten überprüft. Du musst den Chat danach nicht ständig öffnen.
9. Sende einen ersten freigegebenen Arbeitsauftrag vom definierten ChatGPT-Postfach an dieses Gemini-Worker-Postfach. Der Betreff muss mindestens **[ODPC-KI-TEAM][TEST-202610][AN-GEMINI]** enthalten. Für Folgerecherchen akzeptiert der Worker auch **[NACHRECHERCHE]**. Der Worker markiert den Auftrag, erstellt über das Gemini-API-Modell einen Bericht, ergänzt das Google-Dokument und antwortet im bestehenden Gmail-Thread; der Text enthält **[GEMINI-FERTIG]**. Der Absender bleibt das Google-Konto des Workers.

## Funktionsumfang

- Arbeitet auf **genau einem freigegebenen Aufgaben-Dokument**.
- Durchsucht Gmail nur nach Nachrichten mit der Auftragskennung und dem zugelassenen Absender.
- Begrenzt Aufträge auf maximal **2 pro Durchlauf** und die Nachrichtengröße.
- Liest zum Faktenabgleich nur drei **fest hinterlegte öffentliche** ODPC-Seiten, keine fremden privaten Websites.
- Übergibt **keine Gmail-Anhänge** und erkennt typische Geheimnismuster, um solche Nachrichten auszusondern.
- Kennzeichnet abgeschlossene, wartende und fehlerhafte Aufträge mit Gmail-Labels. Ein neuer Auftrag im selben Thread wird anhand seiner einzelnen Nachrichten-ID erneut berücksichtigt. Fehlerhafte Nachrichten können erst nach manueller Prüfung/Zurücksetzung der entsprechenden Script Property erneut verarbeitet werden.
- Kein eigenmächtiger WordPress-Zugriff, keine beliebigen Änderungen an GitHub, kein Zugriff auf fremde Adressen, keine Kundendaten-Exports.
- Die Rückantwort stammt von der **Gemini Developer API**, nicht automatisch vom laufenden Gemini-Chat beziehungsweise der Persona Claire. Beide können auf dasselbe Dokument zurückgreifen.

## Free-Tier und Datenschutz

Apps Script ist für kleine Anwendungsfälle innerhalb der Google-Quoten ohne zusätzlichen Hostingvertrag nutzbar. Gemini 2.5 Flash-Lite ist nach den derzeitigen Google Developer API-Preisen im Free Tier nutzbar, sofern für Region und Konto zugelassen. Limits können sich ändern; der Worker hält bei API-429-Kontingentfehlern zunächst eine Stunde an.

**Achtung:** Im kostenlosen Gemini-API-Tier können Daten zur Verbesserung von Google-Diensten verwendet werden. Deshalb im Pilot nur ODPC-eigene, öffentliche oder bewusst zur externen KI-Verarbeitung freigegebene Inhalte verwenden. Kanzleiakten, Bewerbungen, persönliche Daten, volle FIN/VIN, private Gmail-Nachrichten und Kundenzugangsdaten gehören nicht in diese Queue.

Der Gmail-Dienst in Apps Script verlangt relativ breite OAuth-Rechte, obwohl dieser Worker funktional nur nach markierten ODPC-Aufträgen sucht. Eine umfassende OAuth-Freigabe bedeutet **nicht**, dass der Worker den restlichen Posteingang an Gemini weitergibt.

## Abschalten und Wiederherstellen

In Google Apps Script unter **Trigger** den Eintrag für runOdpcKiTeam löschen. Das beendet den automatischen Lauf. Bereits markierte E-Mails und die Dokumentrückmeldungen bleiben erhalten. Bei unklarer Fehlerlage zuerst die Ausführungsprotokolle prüfen, bevor Nachrichten erneut freigegeben werden.

## Erweiterungen nach erfolgreichem Live-Test

- Google Sheets als nachvollziehbares Projekt-Cockpit mit Status und Bearbeiter
- GitHub Versionskontrolle für Agentenprompts und Templates
- Microsoft Outlook/Teams/SharePoint als optionale Kanäle nach separater Kontoverbindung; Word, Excel und Copilot sind nicht automatisch freie Backend-APIs
- Sicherheits- und Fachfreigaben vor WordPress/Repository-Änderungen
- Mehrsprachige Antwortvarianten und sichere Agenten-Templates

**Offizielle Dokumentation**

- https://ai.google.dev/gemini-api/docs/pricing
- https://ai.google.dev/gemini-api/docs/text-generation
- https://ai.google.dev/gemini-api/docs/google-search/
- https://developers.google.com/apps-script/guides/triggers/installable
- https://developers.google.com/apps-script/guides/services/quotas
