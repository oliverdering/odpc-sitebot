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

## Prüfstand vor Veröffentlichung

- Dialogversion `0.14.8`.
- 70 JavaScript-Prüfungen und 9 Pythonprüfungen lokal erfolgreich.
- JavaScript-Syntax und Python-Kompilierung erfolgreich. PHP-Syntax,
  Veröffentlichung, Rücksicherung sowie die vollständige Desktop-/Mobilprüfung
  folgen im bestehenden Release-Workflow.

## Nächste Priorität

Nach dem Live-Nachweis sollte nur bei einem neuen Beleg geprüft werden, ob der
Wechsel zum Zielkonto nach einem manuellen Kopiervorgang auf iOS und Android
weitere gerätespezifische Hinweise benötigt. Ohne reproduzierbaren Fehler bleibt
der Produktstand unverändert.
