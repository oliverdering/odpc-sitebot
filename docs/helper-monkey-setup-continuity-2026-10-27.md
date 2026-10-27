# Helper Monkey: Angaben und widersprüchliche Tests, 27.10.2026

## Ausgangsstand und Belege

Quellstand vor der Änderung: bfcc408127da5e06fd51d845997d0ff52d9d03f5,
Gesprächsmodul 0.14.4. Konzeptaudit vom 24.10. sowie Berichte zu Auftragskontext,
Termindetails und vollständigen Webabläufen bis 26.10. wurden berücksichtigt.

Zwei neue Fehler wurden mit dem aktuellen Dialogkern reproduziert:

- Nach „Bewerbung schreiben“ und „ChatGPT, maximal 120 Wörter.“ enthielt die
  Arbeitsvorlage nur den ersten Satz. Kurze Zusatzangaben zur KI-Auswahl und
  Angaben während Geräte-/Dienstwahl wurden nicht übernommen.
- „Test erfolgreich, aber kein Zugriff auf mein Postfach“ wurde als erfolgreicher
  Test gespeichert. Gleiches galt für englisch „successful but no access“ und
  französisch „réussi mais aucun accès“. Gmail erschien als eingerichtet gemeldet,
  obwohl die Rückmeldung ausdrücklich fehlenden Zugriff nannte.

## Umsetzung 0.14.5

1. Freie kombinierte Antworten in KI-, Geräte- und Dienstwahl behalten ihre
   Zusatzangaben beim aktuellen Auftrag. Reine angebotene Antwortschaltflächen
   werden nicht zusätzlich als Auftragsdetails gespeichert. Bestehende Grenzen
   (sechs Angaben, je 600 Zeichen, erkannte Geheimnisse verwerfen) gelten weiter.
2. Ausdrücklich fehlender Zugriff erhält vor einem beiläufigen Erfolgswort Vorrang.
   Acht Sprachbeispiele sind geprüft. Der Dialog bleibt beim Test mit fehlgeschlagener
   Rückmeldung; er meldet keinen neuen Dienst als eingerichtet. Nach Neuladen und
   beim Einstieg mit Beispieldaten bleibt diese Grenze nachvollziehbar.

## Prüfung und Veröffentlichung

69 JavaScript- und 8 Pythonprüfungen bestanden. Die ergänzten Regressionen prüfen
kurze kombinierte Angaben, Verbindungsumweg, Wiederherstellen, Aufgabenwechsel,
acht widersprüchliche Testrückmeldungen und englisch „not successful“.

Der bestehende vollständige Live-Browserlauf deckt weiterhin Bewerbung, Postfach,
Büro, Webmaster, Marketing, Interview, Berichtsheft, Auto und PraeLex mit Beispieldaten,
Startlinks, Auftragsinhalt, Hilfe, Kopieren, Kopierfehler, Neuladen und Sprachwahl
auf Desktop und Mobil ab. Neue Live-Szenarien prüfen die Zusatzangaben bis zur
Zwischenablage sowie die Rückkehr nach widersprüchlicher Testrückmeldung.
Kontrollierter Release mit Dateisicherung/Rollback und öffentlicher Prüfsumme;
die abschließende Live-Abnahme ist erfolgreich.

Der erste Live-Lauf stoppte im neuen Prüfschritt, weil dieser in der Testphase
eine dort nicht angebotene Beispieldaten-Schaltfläche anklicken wollte. Der
Prüfschritt nutzt nun den vorhandenen Textbefehl „ohne Verbindung starten“.
Die Produktlogik wurde dafür nicht erweitert. Der vollständige Lauf wurde erneut
ausgeführt und erfolgreich abgeschlossen.

## Grenzen und nächste Priorität

Kein Diff an Layoutvorlage, Stylesheets oder Original-Logos. Keine neuen Kosten,
Kontofreigaben, Nachrichten oder anderen Projekte. Web-Einrichtung und kopierbarer
Auftrag sind von tatsächlichen Werkzeugaktionen im eigenen KI-Konto getrennt.
Private Besucherkonten, physische Mikrofone und Hörqualität sind nicht getestet.
Die Auswertung bleibt regelbasiert; widersprüchliche freie Aussagen können nicht
allgemein semantisch verstanden werden. Nächste Priorität: echte Ergebnisqualität
mit anonymisierten Unterlagen über einen tatsächlich verfügbaren kostenlosen
Modellzugang vergleichen, ohne Zugang oder Ausführung zu behaupten.

## Live-Nachweis

- Produktänderung: Commit `6b210709ffd88c56812894d32ca595bf64676718`.
- Korrektur des neuen Prüfschritts: `5e63a40aca9a81dd2d147c3a6cacc863d3b737a4`.
- [Abschließender Release 40469359730](https://github.com/oliverdering/odpc-sitebot/actions/runs/40469359730)
  erfolgreich am 27.10.2026. 69 JavaScript-, 8 Pythonprüfungen, PHP-Syntax,
  Metadaten, SFTP-Prüfsummen und öffentliche Auslieferung bestanden.
- 59 Live-Prüfgruppen bestanden. Alle neun Monkey-Beispielabläufe sowie die neuen
  kombinierten Antworten, Kopierinhalt, widersprüchliche Testrückmeldung und
  Wiederherstellung wurden auf Desktop und Mobil bestätigt.
- [QA- und Rückrollartefakt des abschließenden Laufs](https://github.com/oliverdering/odpc-sitebot/actions/runs/40469359730/artifacts/12718316683)
  enthält Browserbilder einschließlich `desktop-mixed-setup-handoff.png` und
  `mobile-mixed-setup-handoff.png` sowie das Veröffentlichungsprotokoll.
- [Sicherung vor dem ersten Funktionsrelease](https://github.com/oliverdering/odpc-sitebot/actions/runs/40468896656/artifacts/12717457777)
  bewahrt den vorherigen Produktstand 0.14.4 für eine Rücknahme. Der erste Lauf
  stoppte ausschließlich beim oben dokumentierten fehlerhaften Prüfschritt.
