# Helper Monkey: Dienstmeldungen beim KI-Wechsel

## Belegter Fehler

Am 05.11.2026 auf der öffentlichen Connector-Seite (0.14.9) nachgestellt:
Gmail, Kalender und Drive für ChatGPT auswählen, Verbindung und erfolgreichen
Test melden, „Weitere Verbindung einrichten“, „Gemini Gmail“ eingeben und
„Ohne Verbindung starten“. Der für Gemini erzeugte Auftrag nennt weiterhin
alle drei früher für ChatGPT gemeldeten Dienste. Es handelt sich um simulierte
Besucherrückmeldungen, keine echte Kontoautorisierung oder Zugriffstests.

## Korrektur 0.14.10

Bei einem im Einrichtungsdialog erkannten Plattformwechsel werden frühere
Dienstmeldungen und deren Erfolgsstatus zurückgesetzt. Die Aufgabe und ihre
Angaben bleiben erhalten. Dieselbe Plattform erneut zu nennen erhält deren
Meldungen. Neue Erfolgsmeldungen gelten nur für die neu ausgewählten Dienste.
Dies verhindert einen Widerspruch zur FAQ: Verbindungen werden nicht zwischen
KI-Anbietern übertragen. Keine Änderung an echten Kontoverbindungen, CSS,
Layout, Logos oder Markenbild; keine Kosten oder Drittkommunikation.

## Regressionen und Grenzen

72 JavaScript- und neun Pythonprüfungen lokal bestanden. Neue Regression:
alle acht Sprachen, alle neun Kombinationen aus ChatGPT/Gemini/Claude,
Neuladen, erhaltene Auftragsangaben und anschließende neue Erfolgsmeldung.
Die Live-Prüfung ergänzt beide Wechselziele auf Desktop und Mobil einschließlich
Kopierfunktion, Plattformlink, datensparsamem WhatsApp-Kontext und erneutem Test.
Die bestehende Suite prüft alle neun Monkey-Szenarien mit Beispieldaten,
45 FAQ-Antworten, Sprachwahl, Fehlerfälle und alternative Kopierwege.

Physische Mikrofone, akustische Qualität und private Besucherkonten werden
nicht als geprüft ausgegeben. Bereits vor dieser Korrektur gespeicherte
plattformfremde Meldungen ohne Herkunftsnachweis lassen sich nicht sicher
rückwirkend unterscheiden. Der Auftrag fordert weiterhin die Prüfung der im
jeweiligen KI-Gespräch tatsächlich verfügbaren Werkzeuge und Konten.

## Veröffentlichung

Ausstehend: kontrollierter ai.odpc.de-Release mit Dateisicherung/Rollback und
anschließender öffentlicher Desktop-/Mobilprüfung. Erfolg erst danach melden.

## Nächste Priorität

Bei konkretem Nachweis prüfen, ob ein Wechsel zwischen zwei Konten desselben
KI-Anbieters im Dialog verständlich genug behandelt wird. Die Webseite kann
das aktive Besucherkonto nicht selbst erkennen.
