# Helper Monkey: Aufgaben und Bedienung, 24.10.2026

## Ziel

Ein klarer Weg vom ausgewählten Monkey zum ersten Ergebnis. Bestehendes Layout,
Stylesheets und Original-Logos bleiben unverändert. Die Webseite führt durch die
Einrichtung und erzeugt Arbeitsaufträge. Die eigentliche KI-Arbeit findet im
eigenen KI-Konto mit dessen Werkzeugen statt.

## Festgestellte Brüche

- Bewerbung, Postfach, Marketing, Interview und Berichtsheft führten zur gleichen
  allgemeinen Downloadsektion. Eigene Startaufträge fehlten teilweise vollständig.
- Auto-Monkey führte ins allgemeine Praxismagazin; PraeLex wurde mit einer
  Konfliktlotsen-Seite gleichgesetzt.
- Textaufgaben verlangten einen unnötigen Verbindungstest. Fehlende Verbindungen
  boten keinen direkten Einstieg mit Beispieldaten.
- „Aufgabe wählen / Test später“ meldete Dienste fälschlich als verbunden.
- Der Sprachwechsel blieb beim Wechsel zwischen Seiten nicht erhalten.
- Ältere Unterseiten vermischten Textvorlagen, produktive Integrationen,
  persönliche ODPC-Leistungen und Premium-Bestelltexte.
- Zwei Prüfroutinen verwendeten veraltete Erwartungen: alter Wizard-Marker und
  ZIP-Signatur auch für TXT-Downloads.

## Produktlogik

1. Connector: Verbindung erklären, offiziell autorisieren, im KI-Konto testen.
2. Monkey: eine konkrete Aufgabe auswählen, KI-Konto wählen, Auftrag übernehmen.
3. Erster Entwurf: ohne Verbindung mit eigenen ausgewählten Texten oder Beispielen.
4. Echte Kontodaten und Änderungen: nur mit vorhandenen Werkzeugen und Freigaben.
5. Persönliche Hilfe: schrittbezogener WhatsApp-Link, Nachricht wird vom Besucher gesendet.

| Monkey | Ergebnis des Arbeitsauftrags | Grenze |
|---|---|---|
| Bewerbung | Lebenslauf oder Anschreiben aus tatsächlichen Angaben | Kein erfundener Werdegang, kein automatischer Versand |
| Postfach | Übersicht und Antwortentwürfe | Echtes Postfach nur mit freigegebenem Zugriff |
| Webmaster | Inhalts-, SEO- oder WordPress-Entwurf | Publikation nur mit geprüftem Zugang und konkreter Freigabe |
| Büro | Dokument oder Zusammenfassung; Termine als weitere Aufgabe | Ablage und Kalender separat verbinden |
| Marketing | Beitrag oder Redaktionsplan | Keine erfundenen Produktversprechen, kein automatisches Posting |
| Interview | Eine Frage, Antwort, Feedback | Sprache abhängig vom verwendeten KI-Konto |
| Berichtsheft | Bericht aus tatsächlichen Tätigkeiten | Betrieb prüft und gibt frei; keine Anerkennung zugesichert |
| Auto | Geordnete OBD-Angaben für die Werkstatt | Kein Fahrzeugzugriff, keine gesicherte Diagnose |
| PraeLex | Chronologie, Dokumentenliste und offene Fragen | Keine Rechtsberatung oder Kanzleisystem-Freigabe |
| Finance (bestehende Unterseite) | Beleg- und Vertragsübersicht | Keine Finanzentscheidung, Zahlung oder Kündigung |

## Umsetzung und Prüfung

Koordinierter, bestehender SFTP-Release mit Dateisicherung und Rückrollfunktion.
Keine Änderungen an Benutzerrechten, Kontoverbindungen, Drittanbieter-Abos,
geplanten Agenten oder Stylesheets. Bestehende Downloadadressen bleiben erhalten.

Vor Veröffentlichung: 41 JavaScript-Prüfungen und 8 Python-Prüfungen bestanden.
Die Release-Pipeline prüft zusätzlich PHP-Syntax und Live-Verhalten auf Desktop
und Mobilgeräten, einschließlich der neun direkten Agentenstarts.

Physisches Mikrofon, akustische Qualität auf Samsung/iPhone und tatsächliche
Kontozugriffe von Besuchern können durch diese Prüfungen nicht bestätigt werden.
Der Browserdialog bleibt regelbasiert; er wird nicht als eigener frei sprechender
KI-Dienst oder autonomes Agentensystem ausgegeben.

## Live-Nachweis

- Funktionsrelease: `482755b0a3a86a50b23ca697e896244aa4445ba3`.
- Erfolgreiche vollständige Pipeline: https://github.com/oliverdering/odpc-sitebot/actions/runs/40136603958
- 41 JavaScript- und 8 Python-Prüfungen sowie PHP-Syntax und Metadatenprüfung bestanden.
- 33 Browser-Prüfgruppen bestanden: alle veröffentlichten Seiten, Downloads auf
  eigener Domain, Desktop/Mobil, Scrollen, Dialogschließen, Fortschritt,
  Verbindungstests, Kopieren und alle neun direkten Agentenstarts.
- Produkt-/FAQ-Audit: https://github.com/oliverdering/odpc-sitebot/actions/runs/40136603990 erfolgreich.
- Zusätzliche direkte Browserprüfung: Interview-Start und kopierter Auftrag,
  Dokumentenvorschau, Bewerbungsregister, Kalenderexport und Übersetzer-Prompt
  mit dem ausgewählten Sprachpaar funktionieren mit Beispieldaten.
- Begleittext für direkte Aufgabenstarts anschließend in
  `86719d6f2350381deb923bccc84a713ca9339b02` präzisiert und live visuell geprüft.
- Original-Logos und CSS unverändert; keine Besucher-Konten verbunden oder
  Nachrichten versendet. Keine neuen Abos, kostenpflichtigen APIs oder
  Hintergrundaufgaben eingerichtet.
