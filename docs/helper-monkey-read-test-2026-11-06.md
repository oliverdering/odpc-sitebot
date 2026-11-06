# Helper Monkey – Lesetest pro Dienst

## Befund am 6. November 2026

Auf Basis des Konzeptaudits vom 24. Oktober und der Prüfberichte zu
Verbindungstests und KI-Wechsel wurde Version 0.14.10 erneut geprüft. Mit Gmail,
Google Kalender und Drive im Testschritt führte „Test teilweise erfolgreich“
im Dialogkern zu einer Erfolgsmeldung für alle drei Dienste. Außerdem erlaubte
der kopierbare Auftrag einen Lesetest mit eingefügten Beispieldaten. Daraus
lässt sich kein Zugriff auf einen verbundenen Dienst ableiten. Die FAQ
beschreibt bereits den aussagekräftigeren Test mit einem benannten Eintrag.

## Änderungen in 0.14.11

- Der Testauftrag fragt für jeden ausgewählten Dienst nach genau einem
  unkritischen Testeintrag. Die KI soll über das freigegebene Werkzeug nur
  diesen Eintrag lesen und Werkzeug und überprüfbares Detail nennen. Sie soll
  offene Dienste separat nennen. Ein Erfolg für die Gruppe setzt einen
  tatsächlichen Lesezugriff in jedem ausgewählten Dienst voraus.
- Eine ausdrücklich teilweise erfolgreiche Testrückmeldung bleibt im
  Testschritt und bestätigt keinen Dienst der ausgewählten Gruppe. Bei einem
  erneuten Test gelten die bestehenden Regeln zum Widerruf älterer Meldungen
  für die ausgewählte Gruppe. Unbeteiligte Dienste bleiben erhalten. Ein
  später als vollständig erfolgreich gemeldeter Test bleibt möglich.

## Prüfung und Grenzen

Lokal bestanden 73 JavaScript- und neun Pythonprüfungen. Die neue Regression
deckt acht Sprachen, Wiederherstellung nach teilweiser Rückmeldung und
anschließenden vollständigen Erfolg ab. Die öffentliche Browserprüfung
überprüft den kopierten Lesetest und den simulierten Teilerfolg auf Desktop und
Mobil. Sie enthält alle neun Monkey-Starts mit Beispieldaten, Sprachwahl,
Aufträge, Kopierfehler, WhatsApp-Hilfelink und Verbindungswege.

Der Webdialog erzeugt Anweisungen und verarbeitet Rückmeldungen. Die KI muss
im eigenen Konto tatsächlich über ein autorisiertes Werkzeug lesen können.
Die Prüfung umfasst simulierte Rückmeldungen und keine privaten Konten oder
physischen Mikrofon- und Hörtests. Layout, CSS, Logos und Markenbild blieben
unverändert. Der Release ist auf ai.odpc.de begrenzt.

## Veröffentlichung

Am 6. November 2026 wurde Version 0.14.11 mit Quellstand
`8c0cacc429743c70c67876dcadbc8a505e29c4b4` veröffentlicht. PHP-Syntax, Metadaten, 73
JavaScript-, neun Python- und 69 Live-Browserprüfgruppen bestanden.
Auf Desktop (1440 × 1000) und Mobil (390 × 844) wurde der neue Testauftrag
exakt kopiert. Die teilweise Erfolgsmeldung bestätigte in beiden Prüfungen
keinen ausgewählten Dienst. Ein anschließend vollständig gemeldeter Erfolg
und die neun Monkey-Beispielabläufe bestanden ebenfalls.
[Release und Prüfprotokoll](https://github.com/oliverdering/odpc-sitebot/actions/runs/42180356437).

Die Sicherung und die Prüfbilder enthalten zusammen 49 Dateien.
[QA- und Rückrollartefakt](https://github.com/oliverdering/odpc-sitebot/actions/runs/42180356437/artifacts/13484704054).
SHA-256 `4db3afba361612d7e632ccbf70d30607e22496a19199039cb2615655f55231e5`.
Aufbewahrung bis 6. Dezember 2026. Der vorhandene Release stellt die
vorherigen Dateien bei einem Upload- oder öffentlichen Abgleichfehler
wieder her.

## Nächste Priorität

Bei einem belegten Bedienungsproblem prüfen, ob die Rückkehr vom gemischten
Testergebnis zum Einzeltest verständlich genug ist. Der Dialog kann freie
Rückmeldungen nicht zuverlässig einzelnen Diensten zuordnen.
