# Helper Monkey: vollständige Webabläufe, 26.10.2026

## Neue belegte Fehler

In Version 0.14.3 wurden „Ich heiße Oliver und möchte ein Anschreiben erstellen“,
„Meine Bewerbung ist für Oliver Wyman“ und ein Beitrag über WhatsApp fälschlich
als persönliche Hilfe behandelt. Der Auftrag blieb stehen. Außerdem erkannte
der Eingabeschutz deklarierte Passwörter etwa mit französisch „est“, spanisch
„es“, italienisch „è“, portugiesisch „é“ und polnisch „to“ nicht. Im Arbeitsmodus
konnten diese Angaben dadurch in Verlauf und Arbeitsauftrag gelangen.

## Umsetzung 0.14.4

1. Namen und WhatsApp als Thema lassen konkrete Aufgaben weiterlaufen. Eine
   ausdrückliche Bitte um Olivers Hilfe und alleinige Hilfe-Stichwörter behalten
   den persönlichen Hilfeweg, ohne den bestehenden Auftrag zu verwerfen.
2. Der vorhandene Eingabeschutz erkennt die geprüften Passwortdeklarationen in
   allen acht angebotenen Sprachen. Sie werden vor Verlauf, Speicherung und
   Übergabe verworfen; Wiederherstellen alter Auftragsangaben filtert sie ebenso.
   Fragen zum Ändern eines Passworts bleiben möglich. Dies ist ein regelbasierter
   Schutz für erkennbare Deklarationen, keine Garantie, beliebige Geheimnisse
   oder unbeschriftete Kennwörter zu erkennen.

## Prüfumfang

67 JavaScript- und 8 Pythonprüfungen bestanden. Für Bewerbung, Postfach, Büro,
Webmaster, Marketing, Interview, Berichtsheft, Auto und PraeLex sind realistische
Beispieldaten im Live-Prüfskript hinterlegt. Je Monkey werden direkter Einstieg,
Auftragsergänzung, korrekte Zuordnung, Hilfe ohne Datenverlust, exaktes Kopieren
und Wiederherstellen nach Neuladen auf Desktop und Mobil geprüft. Eine simulierte
Clipboard-Verweigerung prüft zusätzlich das Öffnen und vollständige Markieren
des Auftrags für manuelles Kopieren. Acht Sprachbeispiele prüfen die Ablehnung
von Passwortangaben vor Prompt, Verlauf und Sitzungsspeicher.

Die bisherigen Regressionen decken weiterhin Verbindungsaufbau, Nichtverfügbarkeit,
fehlgeschlagene und gemeldete erfolgreiche Tests, Aufgabenwechsel, Sprachwahl,
Scrollen, Dialogsteuerung und schrittbezogene WhatsApp-Links ab. WhatsApp-Nachrichten
werden nicht versendet. Der kontrollierte Release sichert Dateien für Rollback.
Der Live-Nachweis wird nach Abschluss ergänzt.

## Grenzen und nächste Priorität

Geprüft werden der vollständige Webweg und die Übergabe an das eigene KI-Konto.
Tatsächliche KI-Ergebnisse oder Werkzeugaktionen in privaten Besucherkonten sind
damit nicht abgenommen. Physische Mikrofon- und Hörtests bleiben offen. Layout,
Stylesheets und Original-Logos sind unverändert; keine neuen Kosten oder Rechte.
Nächster sinnvoller Schritt ist ein qualitativer Ergebnisvergleich anhand dieser
anonymisierten Aufträge, sobald ein vorhandener kostenloser Modellzugang dafür
tatsächlich verfügbar ist.
