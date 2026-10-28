# Helper Monkey: persönliche Hilfe mit Agentenkontext, 28.10.2026

## Neuer belegter Bedienfehler

Der öffentliche direkte Einstieg in den Bewerbungs-Monkey zeigte im Dialog korrekt
„Eine Bewerbung vorbereiten“. Der schrittbezogene WhatsApp-Link enthielt jedoch
nur „KI-Konto auswählen“. Weder der gewählte Monkey noch die gewählte KI wurden
genannt. Bei neun spezialisierten Arbeitswegen fehlte Oliver dadurch der minimale
Kontext für eine schnelle persönliche Hilfe.

## Verbesserung 0.14.6

Der persönliche Hilfe-Link nennt zusätzlich den lokalisierten Namen des gewählten
Monkeys und, sobald gewählt, ChatGPT, Gemini oder Claude. Konkrete Auftragsangaben,
Beispieldaten, Konten, Dienste und Verbindungsbehauptungen werden nicht in die
WhatsApp-Nachricht übernommen. Der Besucher prüft und versendet die Nachricht
weiterhin selbst.

Die Änderung betrifft ausschließlich den bestehenden Einrichtungsdialog und dessen
schwebenden Hilfe-Link. Layout, Stylesheet, Original-Logo, Markenbild, Kontozugänge,
Hosting und andere Projekte bleiben unverändert. Es entstehen keine neuen Kosten.

## Prüfung und Grenzen

Die neue Unit-Regression prüft Agentenname, Plattform und das Fernhalten einer
konkreten Auftragsangabe. Die vollständige Live-Prüfung kontrolliert denselben
Kontext für Bewerbung, Postfach, Büro, Webmaster, Marketing, Interview,
Berichtsheft, Auto und Rechtsunterlagen auf Desktop und Mobil. Die Links werden
nur gelesen; keine WhatsApp-Nachricht wird versendet. Private Besucherkonten sowie
physische Mikrofon- und Hörqualität werden nicht als getestet ausgegeben.

Veröffentlichungscommit, Release-Lauf, Rückrollartefakt und Live-Ergebnis werden
nach der kontrollierten Veröffentlichung ergänzt.

## Nächste Priorität

Als nächstes sollte nur bei neuem Beleg geprüft werden, ob Besucher nach dem
Kopieren eines Arbeitsauftrags den Wechsel ins eigene KI-Konto und die Rückkehr
zum Dialog auf kleinen Mobilgeräten eindeutig verstehen. Ohne reproduzierbaren
Fehler bleibt der Produktstand unverändert.
