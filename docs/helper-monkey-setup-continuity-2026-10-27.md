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
der konkrete Live-Nachweis wird nach Abschluss ergänzt.

## Grenzen und nächste Priorität

Kein Diff an Layoutvorlage, Stylesheets oder Original-Logos. Keine neuen Kosten,
Kontofreigaben, Nachrichten oder anderen Projekte. Web-Einrichtung und kopierbarer
Auftrag sind von tatsächlichen Werkzeugaktionen im eigenen KI-Konto getrennt.
Private Besucherkonten, physische Mikrofone und Hörqualität sind nicht getestet.
Die Auswertung bleibt regelbasiert; widersprüchliche freie Aussagen können nicht
allgemein semantisch verstanden werden. Nächste Priorität: echte Ergebnisqualität
mit anonymisierten Unterlagen über einen tatsächlich verfügbaren kostenlosen
Modellzugang vergleichen, ohne Zugang oder Ausführung zu behaupten.
