# Helper Monkey: natürliche Aufgabenwahl in acht Sprachen, 26.10.2026

## Befund

Die sichtbaren Bedienelemente waren in Deutsch, Englisch, Spanisch, Französisch,
Italienisch, Portugiesisch, Polnisch und Türkisch übersetzt. Freie Sätze wurden
jedoch nicht gleichwertig verstanden. Typische Formulierungen für ein
Vorstellungsgespräch oder Berichtsheft blieben in mehreren Sprachen bei der
Startfrage hängen. Formulierungen wie „Dokumente für einen Anwalt vorbereiten“
wurden wegen des Wortes „Dokumente“ dem allgemeinen Büro-Monkey statt PraeLex
zugeordnet.

## Umsetzung

Version 0.14.2 ergänzt eine gemeinsame, priorisierte Aufgabenerkennung für alle
acht angebotenen Sprachen. Bewerbung, Interview, Berichtsheft, PraeLex und Auto
haben bei eindeutigen Fachbegriffen Vorrang vor beiläufig genannten Bereichen.
Postfach, Büro/Dokumente, Webmaster, Marketing und Finance werden ebenfalls aus
natürlichen Formulierungen erkannt. Sprachtypische Wortformen, darunter
polnische und türkische Beugungen, sind ausdrücklich abgedeckt.

Eine eindeutige Textaufgabe führt weiterhin nach der Wahl von ChatGPT, Gemini
oder Claude direkt zum Arbeitsauftrag. Postfach-, Kalender-, Dokument- und
Webseitenaufgaben fragen weiterhin nach Gerät und benötigter Verbindung. Die
Webseite behauptet dadurch keinen Kontozugriff und führt keine externe Aktion
aus.

## Prüfung

- 63 JavaScript- und 8 Pythonprüfungen bestanden.
- 72 realistische freie Formulierungen prüfen neun Monkey-Arten in acht
  Sprachen.
- Mehrdeutige Beispiele prüfen ausdrücklich PraeLex gegen Dokumente sowie
  Interview gegen Marketing.
- Der Live-Browserlauf prüft zusätzlich sechs nichtdeutsche freie
  Formulierungen auf Desktop und Mobilgerät, einschließlich des übernommenen
  Originalauftrags im kopierbaren Prompt.

Layout, Stylesheets, Original-Logo, Kostenmodell, Kontoberechtigungen und
WhatsApp-Hilfe bleiben unverändert. Physische Mikrofon-/Hörqualität und private
Besucherkonten werden weiterhin nicht als real geprüft ausgegeben.

