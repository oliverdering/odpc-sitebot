# Helper Monkey – Einzeltest nach einem Verbindungsproblem

## Befund am 7. November 2026

Konzeptaudit vom 24. Oktober sowie die aktuellen Berichte zum KI-Wechsel und
Lesetest wurden gegen den Quellstand geprüft. In Version 0.14.11 bleibt nach
„Test teilweise erfolgreich“ die Dienstgruppe Gmail, Kalender und Drive
ausgewählt. Die angebotenen Aktionen sind Erfolg, Fehlschlag und Aufgabe
wählen. Die Eingabe „Gmail“ wird im Testschritt abgewiesen. Ein gezielter
Einzeltest verlangt bislang das Zurückgehen durch mehrere vorherige Schritte.

## Verbesserung in 0.14.12

Nach fehlgeschlagenem Test, Teilerfolg oder nicht verfügbarer Verbindung bietet
der Dialog „Dienste neu wählen“ an. Diese Aktion öffnet unmittelbar die
bestehende Dienstauswahl und leert die bisherige Auswahl. Besucher können
beispielsweise Gmail wählen und erhalten danach einen Testauftrag nur für
Gmail. Die aktuelle Aufgabe, ihre Angaben, KI-Plattform, Gerät und
unbeteiligte Dienstmeldungen bleiben erhalten. Die Aktion ist in allen acht
Dialogsprachen beschriftet und funktioniert auch als eingegebener Text.

Die bestehenden Regeln für erfolglose Testmeldungen gelten weiterhin. Eine
neue Erfolgsmeldung betrifft nur die neu gewählte Dienstgruppe. Der Browser
verarbeitet Rückmeldungen und erzeugt den Testauftrag für das eigene KI-Konto.
Dort müssen tatsächliche Zugriffe mit autorisierten Werkzeugen geprüft werden.

## Regressionen und Umfang

74 JavaScript- und neun Pythonprüfungen lokal bestanden. Die neue Regression
behandelt acht Sprachen und die drei negativen Rückmeldungen einschließlich
Wiederherstellung, erhaltenem Berichtsheft-Auftrag, vorheriger GitHub-Meldung,
eingegrenztem Gmail-Test und anschließender Rückkehr zur Aufgabe.

Die öffentliche Browserprüfung ergänzt den vorhandenen vollständigen Lauf
auf Desktop und Mobil um Teilerfolg und fehlende Verbindung. Sie nutzt die
echte neue Schaltfläche, lädt neu, wählt Gmail einzeln und prüft den exakt
kopierten Testauftrag und die erhaltenen Auftragsangaben. Der vorhandene Lauf
enthält außerdem alle neun Monkeys mit Beispieldaten, Sprachwahl, Kopierfehler,
Kontoverbindungsdialog, WhatsApp-Kontext und 45 FAQ-Antworten.

Layoutvorlage, Stylesheets, Original-Logos und Markenbild bleiben unverändert.
Der bestehende kontrollierte Release ist auf ai.odpc.de begrenzt und sichert
die vorherigen Dateien. Physische Mikrofone, Hörqualität, private
Besucherkonten und die tatsächlichen Ergebnisse externer KI-Modelle sind
durch diese Webprüfungen nicht bestätigt.

## Korrektur des neuen Prüfschritts

Der erste Browserlauf stoppte bei der neuen Schaltfläche. Die vorausgegangene
Sprachprüfung hatte Türkisch eingestellt, der neue Prüfschritt suchte den
sichtbaren deutschen Namen. Die Testvoraussetzung wird jetzt ausdrücklich
auf Deutsch gesetzt und die Auswahl verwendet den sprachunabhängigen
Aktionswert. Der vollständige Lauf wurde anschließend erfolgreich wiederholt.
[Erster Lauf](https://github.com/oliverdering/odpc-sitebot/actions/runs/42352585283).

## Veröffentlichung

Am 7. November 2026 wurde Version 0.14.12 vollständig geprüft veröffentlicht.
Produktänderung `615db2ecc7d5e3f31d7a3997e99d20940a97cf9c`, abschließender
Prüfstand `5ce82ef559cc68ce1c57fc80751d5019c9c60194`.
[Abschließender Release](https://github.com/oliverdering/odpc-sitebot/actions/runs/42352882949).
PHP-Syntax, Metadaten, 74 JavaScript-, neun Python- und 71 Live-Browserprüfgruppen
bestanden. Der neue Ablauf wurde auf Desktop (1440 × 1000) und Mobil
(390 × 844) für Teilerfolg und nicht verfügbare Dienste bestätigt. Die
ursprüngliche Aufgabe und die unbeteiligte GitHub-Meldung blieben erhalten.
Der eingegrenzte Gmail-Test wurde exakt kopiert und seine anschließende
Erfolgsmeldung kehrte zum Berichtsheft-Auftrag zurück. Alle neun bestehenden
Monkey-Szenarien, acht Dialogsprachen und 45 FAQ-Antworten bestanden ebenfalls.

[Abschließendes QA- und Rückrollartefakt mit 51 Dateien](https://github.com/oliverdering/odpc-sitebot/actions/runs/42352882949/artifacts/13565974982)
enthält auch die neuen Desktop- und Mobilbilder zur Einzeltest-Rückkehr.
SHA-256 `318fbe43f733186609491d91e3555946323194224ccf638cdd13c75543bd5ef3`.
Aufbewahrung bis 7. Dezember 2026.

[Sicherung vor der Produktänderung](https://github.com/oliverdering/odpc-sitebot/actions/runs/42352585283/artifacts/13566500953)
bewahrt den vorherigen Produktstand 0.14.11. SHA-256
`97948f0ec098b78c8dec5beaa3a9be4a7bcb0316085c646572b550c5421d9705`.
Der bestehende Veröffentlichungsweg sichert Dateien vor der Übertragung und
stellt sie bei einem Upload- oder öffentlichen Abgleichfehler wieder her.

## Nächste Priorität

Die Qualität der erzeugten Ergebnisse anhand anonymisierter Quellen in einem
tatsächlich verfügbaren KI-Konto prüfen, sobald ein geeigneter autorisierter
Testzugang vorliegt. Die bisherigen Nachweise betreffen die Webabläufe und
Arbeitsaufträge.
