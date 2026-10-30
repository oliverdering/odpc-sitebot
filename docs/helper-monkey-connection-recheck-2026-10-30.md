# Helper Monkey: spätere fehlgeschlagene Verbindungstests berücksichtigen

## Belegter Fehler

Auf der öffentlichen Seite mit Dialogversion 0.14.8 reproduziert:

1. Gmail, Kalender und Drive auswählen; Verbindung und erfolgreichen Test melden.
2. Postfach-Auftrag starten und erneut eine Gmail-Verbindung einrichten.
3. Gmail als „nicht verfügbar“ melden und ohne Verbindung weiterarbeiten.
4. Im Arbeitsauftrag steht weiterhin „Ich habe folgende Dienste als eingerichtet
   gemeldet: Gmail, Google Kalender, Google Drive.“

Dasselbe geschah bei einem ausdrücklich fehlgeschlagenen erneuten Test.
Die jüngere negative Rückmeldung entfernte die ältere Erfolgsmeldung nicht.
Es wurden ausschließlich Meldungen im Webdialog nachgestellt; kein echtes
Google-Konto wurde autorisiert oder getestet.

## Verbesserung 0.14.9

Eine Meldung „nicht verfügbar“ oder ein fehlgeschlagener Test zieht jetzt die
frühere Erfolgsmeldung für genau die aktuell ausgewählten Dienste zurück.
Andere gemeldete Dienste bleiben erhalten. Im Beispiel nennt der nächste
Arbeitsauftrag nur noch Google Kalender und Google Drive. Die gewählte
Aufgabe und ihre Angaben bleiben bestehen. Erst ein später erneut als
erfolgreich gemeldeter Test nimmt Gmail wieder in die Liste auf.

Beim Wiederherstellen einer gespeicherten Einrichtung wird dieselbe Regel
angewendet, wenn die fehlgeschlagenen oder nicht verfügbaren Dienste noch
nachvollziehbar gespeichert sind. Bei alten, bereits abgeschlossenen Aufträgen
ohne gespeicherte Zuordnung lässt sich nicht rückwirkend erkennen, welche
frühere Meldung falsch war; diese wird nicht geraten.

Die Verbesserung gilt im gemeinsamen Einrichtungsweg aller Monkeys und
ändert weder Anbieterrechte noch echte Kontoverbindungen. Layout, Stylesheets,
Original-Logos, Markenbild und die erweiterten FAQ bleiben unverändert.
Keine neuen Kosten und keine Aktionen in fremden Konten.

## Prüfungen

- 71 JavaScript- und 9 Pythonprüfungen lokal erfolgreich.
- Die neue Regression behandelt alle acht Sprachen, nicht verfügbare Dienste,
  fehlgeschlagene Einzel- und Mehrdiensttests, Wiederaufnahme und erneute
  Erfolgsmeldung. Sie prüft, dass unbetroffene Dienste erhalten bleiben.
- Die Live-Prüfung ergänzt auf Desktop und 390 × 844 Mobil beide Fehlerwege,
  Neuladen, erhaltene Auftragsangaben, exakt kopierten korrigierten Auftrag und
  eine neue Erfolgsmeldung.
- Der vorhandene kontrollierte Release bleibt auf ai.odpc.de beschränkt und
  sichert die vorherigen Dateien für einen Rollback. Private Kontozugriffe,
  physische Mikrofon- und Hörtests sind nicht Gegenstand dieser Prüfung.

## Live-Nachweis und Rücksicherung

- Quellstand `8e596bdd7e8388af77abd0507ce244c44a983e5b`, Dialogversion `0.14.9`.
- [Release 41035183481](https://github.com/oliverdering/odpc-sitebot/actions/runs/41035183481)
  am 30.10.2026 vollständig erfolgreich: PHP-Syntax, 71 JavaScript- und
  9 Pythonprüfungen sowie 65 Live-Prüfgruppen im Browser-Prüfschritt.
- Neue Desktop- und Mobilprüfungen bestanden für beide negativen Rückmeldungen:
  alter Gmail-Bericht entfernt, Kalender und Drive erhalten, Auftragsangaben
  nach Neuladen erhalten, korrigierter Auftrag exakt kopiert und spätere
  erfolgreiche Rückmeldung wieder aufgenommen. Alle neun Monkey-Szenarien,
  acht Dialogsprachen sowie die 45 FAQ-Antworten blieben erfolgreich.
- Zusätzliche direkte Browserprüfung auf der öffentlichen Connector-Seite:
  Version `0.14.9` und nach „nicht verfügbar“ die Auftragszeile
  „Ich habe folgende Dienste als eingerichtet gemeldet: Google Kalender,
  Google Drive.“ Kein echtes Konto wurde dabei geprüft.
- [QA- und Rückrollartefakt](https://github.com/oliverdering/odpc-sitebot/actions/runs/41035183481/artifacts/12967274001)
  mit 49 Sicherungs- und Prüfdateien. SHA-256:
  `34fdcd5bb07eff50140d68d2cc0c13c9b8ec24547dab7d8d4c1b1ea43837d7f6`.
  Aufbewahrung bis 29.11.2026.

## Nächste Priorität

Bei einem weiteren belegten Fall prüfen, ob ein Plattformwechsel innerhalb
einer Einrichtung ebenfalls ältere Dienstmeldungen unpassend übernimmt.
Es wird keine Verbindung allein aus einer früheren Chat-Meldung als technisch
geprüft ausgegeben.
