# Helper Monkey: manueller Kopierweg mit Zielkonto, 30.10.2026

## Neuer belegter Bedienfehler

Wenn ein Browser den Zugriff auf die Zwischenablage verweigerte, öffnete und
markierte der Dialog den vollständigen Arbeitsauftrag korrekt. Die lokale
Rückmeldung lautete jedoch nur „Bitte den markierten Auftrag kopieren.“ Anders
als beim erfolgreichen Kopieren nannte sie ChatGPT, Gemini oder Claude nicht.
Durch die aufgeklappte Auftragsvorschau oberhalb der Schaltfläche konnte die
Rückmeldung auf kleinen Displays außerdem außerhalb des sichtbaren Bereichs
liegen.

## Verbesserung 0.14.8

Der manuelle Fehlerweg nennt jetzt in allen acht Dialogsprachen das ausgewählte
Zielkonto. Auf Deutsch lautet der Hinweis beispielsweise: „Kopiere den
markierten Auftrag und öffne danach ChatGPT.“ Nach dem Markieren wird diese
Rückmeldung in den sichtbaren Bereich des vorhandenen Dialogs gebracht. Der
Arbeitsauftrag bleibt vollständig ausgewählt und das Textfeld behält den Fokus.

Die Änderung betrifft ausschließlich den vorhandenen Kopierfehlerfall.
Stylesheets, Layoutregeln, Original-Logo, Markenbild, Kontozugänge, Hosting und
andere Projekte bleiben unverändert. Es entstehen keine neuen Kosten.

## Prüfung und Grenzen

Die Regression verlangt den lokalisierten Zielkontohinweis, die sichtbare
Rückmeldung im Mobil-Viewport sowie den weiterhin vollständig markierten
Arbeitsauftrag. Der vollständige Live-Lauf prüft Bewerbung, Postfach, Büro,
Webmaster, Marketing, Interview, Berichtsheft, Auto und PraeLex mit
Beispieldaten auf Desktop und emuliertem Mobilgerät.

Die Webseite bereitet nur den Arbeitsauftrag vor. Das Einfügen und Absenden
geschieht im eigenen KI-Konto; Werkzeugaktionen benötigen dort tatsächlich
vorhandene Freigaben. Private Besucherkonten, echte Kontoaktionen sowie
physische Mikrofon- und Hörqualität werden nicht als getestet ausgegeben. Es
werden keine WhatsApp-Nachrichten versendet.

## Live-Nachweis und Rücksicherung

- Funktionscommit `96d1117aa4d10ab50431081b08dd79ae09ad01b0`,
  abschließender Prüfstand `489685393d277908e2c298903bc3f15413f5318f`,
  Dialogversion `0.14.8`.
- [Release 41023905478](https://github.com/oliverdering/odpc-sitebot/actions/runs/41023905478)
  am 30.10.2026 vollständig erfolgreich: PHP-Syntax, 70 JavaScript- und
  9 Pythonprüfungen sowie 62 Live-Prüfgruppen.
- Der erste Mobil-Lauf belegte, dass ein sofortiges Scrollen noch vor Abschluss
  der Vorschauerweiterung zu früh erfolgte. Die Rückmeldung wird deshalb im
  nächsten Layout-Zyklus positioniert. Der abschließende Lauf bestätigte den
  Hinweis vollständig im 390 × 844-Viewport, den weiterhin markierten Auftrag
  und den Fokus im Textfeld.
- Bewerbung, Postfach, Büro, Webmaster, Marketing, Interview, Berichtsheft,
  Auto und PraeLex wurden mit realistischen Beispieldaten erneut auf Desktop
  und Mobil geprüft. Kopieren, manueller Fehlerweg, Wiederaufnahme, acht
  Sprachen, Kontogrenzen und WhatsApp-Hilfe blieben erfolgreich.
- Eine zusätzliche direkte Browserprüfung bestätigte auf der öffentlichen
  Bewerbungsseite Version `0.14.8`, den unveränderten normalen Kopierweg und
  den offiziellen Ziel-Link `https://chatgpt.com/`. Ein KI-Konto wurde nicht
  geöffnet.
- [QA- und Rückrollartefakt](https://github.com/oliverdering/odpc-sitebot/actions/runs/41023905478/artifacts/12963161693)
  mit 48 Sicherungs- und Prüfdateien, Release-Protokoll sowie Desktop-/Mobilbildern.
  SHA-256:
  `5e8a2e98fb5acb172b6cb83d444927734b9bdd903789acedf87a5404a6e3b567`.
  Aufbewahrung bis 29.11.2026.

## Nächste Priorität

Nach dem Live-Nachweis sollte nur bei einem neuen Beleg geprüft werden, ob der
Wechsel zum Zielkonto nach einem manuellen Kopiervorgang auf iOS und Android
weitere gerätespezifische Hinweise benötigt. Ohne reproduzierbaren Fehler bleibt
der Produktstand unverändert.
