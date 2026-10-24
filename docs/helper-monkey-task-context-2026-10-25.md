# Helper Monkey: Auftragsdetails und Agenten-Zuordnung, 25.10.2026

## Ergebnis

Version 0.14.0 übernimmt kurze, selbst eingegebene Auftragsdetails in die
KI-Arbeitsvorlage. Ergänzungen und Korrekturen bleiben in zeitlicher Reihenfolge
bei der gewählten Aufgabe, auch beim Neuladen und nach einer Verbindungseinrichtung.
Eine Bewerbung für Marketing oder eine Kanzlei wird vorrangig als Bewerbung
zugeordnet; ein Vorstellungsgespräch als Interview, OBD-Codes als Auto-Aufgabe.
Reine Textaufgaben verlangen auch bei beiläufig erwähnten Diensten keine Verbindung.

## Begrenzung und Bedienung

- Maximal die letzten sechs Angaben mit jeweils höchstens 600 Zeichen bleiben
  lokal in der laufenden Browsersitzung. Für längere Unterlagen dient das eigene
  KI-Konto. FAQ und Dialoghinweis erklären die lokale Speicherung.
- Eine ausdrückliche andere Aufgabenwahl beginnt mit leerem Auftragskontext;
  die Aufgabenauswahl bleibt nach Neuladen sichtbar. „Neu starten“ löscht die Sitzung.
- Erkannte Zugangsdaten werden vor einer Übernahme verworfen; dieselbe Prüfung
  erfolgt beim Wiederherstellen. Dies ist keine vollständige Erkennung aller
  sensiblen Daten. Nur nötige, möglichst anonymisierte Angaben eintragen.
- Ergänzungen erhalten in allen acht Sprachen eine verständliche Bestätigung.
  Die Webseite erstellt eine Vorlage; sie erzeugt keine KI-Antworten und führt
  keine Aktionen in fremden Konten aus. Keine zusätzlichen Abos oder APIs.

## Prüfung

60 JavaScript- und 8 Python-Prüfungen lokal bestanden. Neue Fälle umfassen alle
zwölf Aufgaben in acht Sprachen, Auftragsübergabe, mehrdeutige Fachwörter,
Korrekturen, Neuladen, Verbindungsumweg, Aufgabenwechsel, begrenzte Speicherung
und verworfene Zugangsdaten. Ergänzende Live-Prüfungen auf Desktop und Mobilgerät
prüfen die sichtbare Aufgabenwahl, Prompt, Zwischenablage und Sitzungsfortsetzung.
Live-Ergebnis wird nach Abschluss des Releases dokumentiert.

Layout, CSS und Original-Logo bleiben unverändert. Nur ein bestehender FAQ-Text
und der vorhandene Übergabehinweis erläutern die neue Speicherfunktion.
