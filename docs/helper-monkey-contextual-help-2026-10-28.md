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

## Live-Nachweis und Rücksicherung

- Funktionscommit: `dacbdf15d12ede5b4865e372cf403a5fcb3b64fc`,
  Dialogversion `0.14.6`.
- [Release 40652219193](https://github.com/oliverdering/odpc-sitebot/actions/runs/40652219193)
  am 28.10.2026 vollständig erfolgreich: PHP-Syntax, Angebotsprüfung,
  70 JavaScript- und 8 Pythonprüfungen sowie 62 Live-Prüfgruppen.
- Die neun realistischen Monkey-Szenarien wurden auf Desktop und emuliertem
  Mobilgerät erneut geprüft. Der WhatsApp-Link nennt jeweils den Monkey und
  ChatGPT; die jeweilige Beispielanforderung fehlt bewusst im Nachrichtentext.
  Kopieren, manueller Kopierfallback, Wiederaufnahme, Sprachwahl, Fehlerfälle,
  Kontogrenzen sowie die grünen und blauen Wege blieben erfolgreich.
- Eine zusätzliche direkte Browserprüfung bestätigte auf der öffentlichen
  Bewerbungsseite Version `0.14.6` und den Text
  „Eine Bewerbung vorbereiten · ChatGPT“. Eine eingegebene Musteranforderung
  wurde nicht in den Link übernommen. WhatsApp wurde nicht geöffnet.
- [QA- und Rückrollartefakt](https://github.com/oliverdering/odpc-sitebot/actions/runs/40652219193/artifacts/12799679431)
  mit Sicherungsmanifest, vorherigen Dateien, Release-Protokoll und
  Desktop-/Mobilbildern. SHA-256:
  `4c5ac500e44acea31350b9cf2da212bb818246563d008601ed2a8c7e72ca0051`.
  Aufbewahrung bis 27.11.2026.

## Nächste Priorität

Als nächstes sollte nur bei neuem Beleg geprüft werden, ob Besucher nach dem
Kopieren eines Arbeitsauftrags den Wechsel ins eigene KI-Konto und die Rückkehr
zum Dialog auf kleinen Mobilgeräten eindeutig verstehen. Ohne reproduzierbaren
Fehler bleibt der Produktstand unverändert.
