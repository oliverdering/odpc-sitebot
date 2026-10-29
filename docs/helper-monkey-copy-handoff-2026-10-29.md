# Helper Monkey: sichtbare Übergabe nach dem Kopieren, 29.10.2026

## Neuer belegter Bedienfehler

Der Arbeitsauftrag wurde korrekt kopiert, aber unmittelbar an der Schaltfläche
blieb die Oberfläche unverändert. Die Bestätigung erschien ausschließlich im
allgemeinen Dialogstatus. Damit war der entscheidende Übergang von der
Web-Einrichtung zum eigenen KI-Konto nicht direkt an der ausgelösten Aktion
erkennbar. Der Auftrag selbst, der Zielanbieter und die Kontogrenzen waren
inhaltlich korrekt.

## Verbesserung 0.14.7

Nach erfolgreichem Kopieren erscheint die vorhandene lokalisierte Bestätigung
jetzt zusätzlich direkt unter „Auftrag kopieren“ und nennt ChatGPT, Gemini oder
Claude als Ziel. Verweigert der Browser den Zugriff auf die Zwischenablage,
erscheint dort stattdessen die vorhandene Anleitung zum manuellen Kopieren; der
vollständige Arbeitsauftrag wird weiterhin geöffnet, markiert und fokussiert.
Der lokale Hinweis ist als Statusmeldung ausgezeichnet und mit der
Kopierschaltfläche verknüpft.

Die Änderung betrifft ausschließlich den funktionalen Arbeitsübergang des
bestehenden Dialogs. Stylesheets, Layoutregeln, Original-Logo, Markenbild,
Kontozugänge, Hosting und andere Projekte bleiben unverändert. Es entstehen
keine neuen Kosten.

## Prüfung und Grenzen

Die neue Regression verlangt die lokale Statusmeldung und ihre barrierearme
Verknüpfung. Die vollständige Live-Prüfung kontrolliert den Hinweis nach dem
Kopieren für Bewerbung, Postfach, Büro, Webmaster, Marketing, Interview,
Berichtsheft, Auto und PraeLex auf Desktop und emuliertem Mobilgerät. Im kleinen
Viewport muss die Meldung nach dem Klick vollständig sichtbar sein. Auch der
synthetisch verweigerte Zwischenablagezugriff wird geprüft.

Die Web-Einrichtung, der kopierte Arbeitsauftrag für das eigene KI-Konto und
tatsächlich dort autorisierte Werkzeuge bleiben ausdrücklich getrennt. Private
Besucherkonten, echte Kontoaktionen sowie physische Mikrofon- und Hörqualität
werden nicht als getestet ausgegeben. Es wurden keine Nachrichten versendet.

## Live-Nachweis und Rücksicherung

- Funktionscommit `4b09457642d7489e62a5b1142d6b97d092f96578`,
  abschließender Prüfstand `5dd8188c0cb9a86b1d66ddb72e26f757918fffc1`,
  Dialogversion `0.14.7`.
- [Release 40839365239](https://github.com/oliverdering/odpc-sitebot/actions/runs/40839365239)
  am 29.10.2026 vollständig erfolgreich: PHP-Syntax, 70 JavaScript- und
  9 Pythonprüfungen sowie 62 Live-Prüfgruppen.
- Bewerbung, Postfach, Büro, Webmaster, Marketing, Interview, Berichtsheft,
  Auto und PraeLex wurden mit realistischen Beispieldaten auf Desktop und im
  emulierten Mobil-Viewport 390 × 844 geprüft. Der kopierte Auftrag entsprach
  jeweils exakt der sichtbaren Fassung; die lokale Bestätigung nannte das
  Zielkonto. Auch verweigerte Zwischenablage, Wiederaufnahme, acht Sprachen,
  Kontogrenzen und WhatsApp-Hilfe blieben erfolgreich.
- Eine zusätzliche direkte Browserprüfung bestätigte auf der öffentlichen
  Bewerbungsseite Version `0.14.7`, die Verknüpfung der Kopierschaltfläche mit
  der lokalen Statusmeldung und den Text „Auftrag kopiert. Öffne jetzt ChatGPT
  und füge ihn dort ein.“ Der ChatGPT-Link zeigte auf den Originalanbieter;
  das Konto wurde nicht geöffnet.
- Der Browserlauf bleibt gemäß Auftrag auf `ai.odpc.de`. Externe Produktziele
  werden dort als Linkadressen geprüft, aber nicht aufgerufen. Es wurden keine
  Nachrichten an Dritte gesendet.
- [QA- und Rückrollartefakt](https://github.com/oliverdering/odpc-sitebot/actions/runs/40839365239/artifacts/12881744391)
  mit 48 Sicherungs- und Prüfdateien, Release-Protokoll sowie Desktop-/Mobilbildern.
  SHA-256:
  `f46f0988449b21c76c0bb1f0951bd3cf1e059caa6d00280bf50cb5e1c87b51c5`.
  Aufbewahrung bis 28.11.2026.

## Nächste Priorität

Nach dem Live-Nachweis sollte nur bei einem neuen Beleg geprüft werden, ob der
Zielkonto-Link nach einem fehlgeschlagenen oder manuellen Kopiervorgang noch
eindeutiger geführt werden muss. Ohne reproduzierbaren Fehler bleibt der
Produktstand unverändert.
