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

## Veröffentlichung

Live-Prüfung und Rücksicherungsnachweis folgen nach dem kontrollierten Release.

## Nächste Priorität

Die Qualität der erzeugten Ergebnisse anhand anonymisierter Quellen in einem
tatsächlich verfügbaren KI-Konto prüfen, sobald ein geeigneter autorisierter
Testzugang vorliegt. Die bisherigen Nachweise betreffen die Webabläufe und
Arbeitsaufträge.
