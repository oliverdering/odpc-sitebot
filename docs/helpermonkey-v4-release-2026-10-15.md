# Helper Monkey V4.0 – Release- und Betriebsdokumentation
Stand: 15.10.2026 | Verantwortlich: ODPC

## Ziel
Ein kostenloser, leicht verständlicher Einstieg für Menschen ohne IT-Kenntnisse, mit klaren Rot-Gelb-Grün-Schritten, sechs unterstützten Interviewsprachen, optionaler Spracheingabe und sofort erreichbarer WhatsApp-Business-Hilfe. Das bestehende Layout, die Agentenpakete und die WordPress-Inhalte bleiben erhalten.

## Veröffentlichte Komponenten
- `https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/`: persönliche Erstbefragung, Android/iOS/Windows/Mac, Mehrfachauswahl vorhandener Konten, sechs Sprachvarianten, Browser-Sprachfunktion mit Text-Fallback, FAQ, individuelle Handlungsempfehlung.
- `https://ai.odpc.de/helper-monkey-fuer-unternehmen-ki-kollegen/`: Produkt- und Beratungsseite für Mitarbeiterrollen, explizite Trennung von verfügbaren und geplanten Funktionen.
- `https://odpc.de/l-i-f-e-mpu-orientierung-und-vorbereitung-im-aufbau/`: MPU-Orientierung mit Fachgrenzen.
- `https://odpc.de/l-i-f-e-mpu-selbstlernkurs-neun-lernmodule/`: kostenloser allgemeiner Selbstlern-/Reflexionsbereich, keine verkehrspsychologische Einzelfallberatung.
- `site-deploy/ai.odpc.de/odpc-helpermonkey-v4.php`: zentraler MU-Plugin-Baustein für Spracheinstieg, WhatsApp, Hilfe, Ampelabschnitte, FAQ, suchbasierten Wissenshelfer und Voice-Fallback.
- `site-deploy/odpc.de/odpc-helpermonkey-v4-bridge.php`: sprachabhängiger Einstieg und Hilfekomponenten auf der ODPC-Dachseite, ohne Änderung des ursprünglichen Startseiten-WhatsApp-Buttons.

## Wiederherstellung / Deployment
- GitHub Action `helpermonkey-v4-deploy.yml` deployt nur ein MU-Plugin via verifizierter SFTP-Verbindung, sichert eine vorhandene Version und prüft anschließend öffentliche HTML-Marker. Bei fehlgeschlagener Prüfung stellt sie die vorige Datei wieder her.
- `odpc-helpermonkey-v4-bridge.yml` nutzt dasselbe Ein-Datei-und-Rollback-Prinzip für odpc.de.
- Zugangsdaten bleiben in GitHub Actions Secrets; nicht in Git gespeichert.
- Vor Theme- oder Core-Deployments ist weiterhin ein vollständiges IONOS-Datenbank- und Dateibackup erforderlich.
- Nicht zu verwechseln: Das Sicherungsartefakt der MU-Plugins ist KEIN vollständiges WordPress-Backup.

## Bekannte Grenzen
- Sprachmenüs führen zum lokalisierten Interview. Die gesamten bisherigen WordPress-Seiten sind noch nicht vollständig professionell in andere Sprachen übersetzt.
- Browser-Spracherkennung variiert je nach Gerät und kann Daten an den jeweiligen Erkennungsanbieter senden. Sie ist kein dauerhaft lauschender Agent.
- Der Hilfe-Assistent sucht dokumentierte Antworten und öffentliche WordPress-Suchergebnisse. Er nutzt keine generative KI und ist nicht identisch mit einem Live-Agenten.
- Unternehmenslizenzen, Mitarbeitermanagement und Bezahlung sind nicht automatisch verfügbar. Der B2B-Auftritt ist zurzeit eine Angebots-/Anfrageseite.
- Die ChatGPT–Gemini-Koordination ist konzipiert. Der Apps-Script-Worker muss auf dem konkreten Google-Konto fertig autorisiert und Ende-zu-Ende getestet werden. Die Ablage von Quellcode in GitHub alleine aktiviert den Worker nicht.
- Ein eigenes Gmail-Konto für jeden Monkey ist nicht nötig und nicht eingerichtet. Funktionale Identität kann durch Alias, Rolle, Label und separate Zugriffsrechte entstehen.
- Die L.I.F.E.-MPU-Materialien sind nicht medizinisch/verkehrspsychologisch geprüft oder offiziell zertifiziert; für betreute kostenpflichtige Angebote ist Fach-/Rechtsprüfung nötig.
- Offener Sicherheitsbefund odpc.de: Core-Checksums weichen ab; mögliche IONOS-PHP-Konfiguration, Ursprung noch nicht geklärt.
- Offene SEO-/Performance-Aufgaben: ODPC robots.txt, allgemeine Ladezeit, Kontraste, Alttexte und teils vorhandene Headerstruktur.

## Qualitätsnachweise
- PHP- und Python-Syntaxprüfungen werden vor GitHub-SFTP-Deployment ausgeführt.
- Rollback bei negativem öffentlichem Rauchtest.
- `scripts/qa_helpermonkey_v4_live.py`: öffentliche Seiten, globale Bedienelemente, Kurslinks, Meta-Texte und WordPress-Suche.
- `scripts/helpermonkey_v4_browser_qa.mjs`: mobile Chrome-Simulation, Interview bis zum Ergebnis, Mehrfachkonten, Sprachumschalter, FAQ-Suche, Crosssite-Navigation.
- Realgeräteabnahme Android/iPhone sowie manuelle mehrsprachige Lektoratsprüfung sind separat erforderlich.

## Produktarchitektur (HM-006/007/008/009/010/011/012)
1. Anonymer Erstcheck mit minimaler Datenerfassung.
2. Rollen- und gerätespezifische Anweisungen.
3. Technischer Connector prüft nach Autorisierung verfügbare Dienste; keine Passwortübergabe an Websites.
4. Agentenrollen teilen Aufträge über ein kontrolliertes Auftragsjournal, nicht durch unkontrollierte Weiterleitung privater E-Mails.
5. Externe Aktionen (Versand, Veröffentlichung, Zahlungen, Löschungen) benötigen passende Berechtigungen und definierte Freigaben.
6. Mandanten-, Mitarbeiter- und Kanzleidaten bleiben logisch und technisch getrennt.
7. Für jede Produktstufe muss klar zwischen Promptpaket, assistiertem Workflow und tatsächlich laufender Automation unterschieden werden.

## Operativer nächster Release
Ein eindeutiges Produktregister, Entdopplung aller Alttexte, professionelle Übersetzungen der priorisierten Unterseiten, echte Datenschutzprüfung, kostenfreie Self-Service-Lernpfade, Rollen-/Lizenzverwaltung nach Preis- und Vertragsentscheidung sowie gesonderte Live-Inbetriebnahme des Google/Gemini-Workers.
