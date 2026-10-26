# Helper Monkey: gewünschtes Ergebnis und Termindetails, 26.10.2026

## Belegte Fehler

Version 0.14.2 ordnete „Ich möchte die E-Mails zur Bewerbung sichten“ dem
Bewerbungs-Monkey zu. „Ich möchte einen Beitrag über Bewerbungstipps schreiben“
hatte denselben Fehlweg. Das Thema verdrängte das ausdrücklich gewünschte
Ergebnis. Ein Terminwunsch wurde als Dokumentenkontext gespeichert; beim
späteren Kalenderauftrag gingen Datum und Uhrzeit verloren. Auch korrekt
gespeicherter Kontext wurde bei der ersten Aufgabenauswahl nach dem Connector
gelöscht, weil vorher noch keine aktive Aufgabe gesetzt war.

## Änderungen (0.14.3)

1. Ausdrückliches Sichten/Lesen von E-Mails sowie Schreiben eines Beitrags über
   ein Thema erhält Vorrang vor beiläufig genannten Fachbegriffen. Diese
   gezielte Ergänzung deckt deutsche und englische Formulierungen ab.
2. Terminangaben werden dem Kalender zugeordnet und bleiben nach Einrichtung,
   Aufgabenauswahl, Einstieg mit Beispieldaten und Neuladen im Prompt erhalten.
   Andere Aufgaben und Agentenwechsel löschen weiterhin fremden Kontext.

## Prüfung und Veröffentlichung

65 JavaScript- und 8 Pythonprüfungen bestanden. Enthalten sind die bisherigen
72 Sprachszenarien für alle neun Monkeys, neue Thema/Ergebnis-Gegenbeispiele
sowie Kalenderkontext über Verbindungsweg und Beispieldaten. Die Live-Prüfung
wurde um drei entsprechende Szenarien auf Desktop und Mobil erweitert.
Der vorhandene Releaseweg sichert Dateien für Rollback und prüft anschließend
die öffentliche Auslieferung. Der endgültige Live-Nachweis folgt nach dem Lauf.

Layout, Stylesheets und Original-Logos bleiben unverändert. Keine neuen Kosten,
Kontofreigaben oder Nachrichten. Geprüft werden Browserdialog und Arbeitsauftrag;
keine erfundenen Ausführungen im KI-Konto, privaten Kontotests oder physischen
Mikrofon-/Hörtests.

## Nächste Priorität

Mehrdeutige Aufträge in den sechs weiteren Sprachen mit konkreten Gegenbeispielen
prüfen. Die zusätzlichen Regeln sind keine allgemeine semantische Spracherkennung.
