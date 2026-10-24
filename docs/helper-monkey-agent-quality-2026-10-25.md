# Helper Monkey: Agentenqualität, 25.10.2026

## Auftrag und Betriebsmodus

Die Webseite erneut prüfen und insbesondere die Agenten fortlaufend verbessern.
Layout, CSS und Original-Logos unverändert erhalten. Eine tägliche Verbesserung
am Morgen in Europe/Berlin wurde eingerichtet; sie betrifft ausschließlich
ai.odpc.de, nicht andere pausierte Projekte. Keine neuen Kosten oder APIs.

## Belegte Bedienungsfehler und Korrekturen

1. „Ich möchte kostenlos Bewerbungen schreiben“ wurde als Kostenfrage abgefangen.
   „I want free job application help with ChatGPT“ wurde als allgemeine Hilfefrage
   abgefangen. Beide Eingaben führen jetzt zum beabsichtigten Agenten. Explizite
   Kostenfragen und persönliche WhatsApp-Hilfe bleiben erhalten.
2. Beim Einrichten einer weiteren Verbindung ging der gewählte Fach-Monkey
   verloren. Der Dialog merkt sich jetzt den Arbeitsauftrag, kehrt nach einem
   gemeldeten erfolgreichen Test dorthin zurück und ermöglicht dieselbe Rückkehr
   bei fehlendem Zugriff mit Beispieldaten. Eine zusätzliche Sperre verhindert,
   dass ein Agenten-URL-Parameter beim Neuladen den Verbindungsfortschritt ersetzt.
   „Nicht verfügbar“ bleibt beim Neuladen erhalten.
3. Textaufgaben fragten im freien Einstieg unnötig nach dem Gerät. Ein Start wie
   „Marketing mit ChatGPT“ erreicht nun direkt den Arbeitsauftrag. Agenten erhalten
   klarere Einstiegsfragen, Ergebnisvorgaben und fachliche Qualitätskriterien.

## Agentenverträge

| Bereich | Ergebnis und Prüfung |
|---|---|
| Bewerbung | Zuerst Lebenslauf, Anschreiben, ATS-Abgleich oder Übersicht wählen; Fakten und tatsächlichen Versandstatus prüfen, keinen erfundenen ATS-Score liefern |
| Postfach | Nachrichtenbezogene Prioritäten und ausgewählte Antwortentwürfe; tatsächlichen Leseumfang nennen, nicht anhand einzelner Wörter löschen |
| Kalender | Konkretes Datum, Zeitzone, Dauer und bestätigte Beteiligte; Konflikte nur nach tatsächlichem Abruf als geprüft ausgeben |
| Dokumente | Gewünschter Entwurf oder quellenbezogene Zusammenfassung; Zahlen prüfen, fehlende Angaben markieren und Originale erhalten |
| Webmaster | Konkrete URL und Vorher-nachher-Entwurf; Layout/Logo erhalten, Veröffentlichung nur nach Freigabe und Live-Prüfung bestätigen |
| Marketing | Kanalbezogener Beitrag; Redaktionsplan nur auf Wunsch, keine erfundenen Preise, Kundenstimmen oder Links |
| Interview | Eine Frage je Runde, Feedback und Folgefrage; tatsächliche Erfahrung, keine erfundene Erfolgswahrscheinlichkeit |
| Berichtsheft | Tages- oder Wochenbericht aus tatsächlichen Tätigkeiten; Stunden und Zeitraum prüfen, Freigaben nicht simulieren |
| Auto | Werkstattübersicht aus OBD-Daten und Beobachtungen; keine Diagnose aus leerem Fehlerspeicher ableiten |
| PraeLex | Chronologie, Dokumentenliste und offene Fragen; belegte Tatsachen trennen, keine Rechtsfolgen oder Fristen erfinden |
| Finance | Beleg-/Vertragsübersicht mit Quellen; Währungen und Zeiträume trennen, keine Anlage-, Steuer- oder Rechtsentscheidung |
| Eigene Aufgabe | Kleines überprüfbares Ergebnis; nur erforderliche Angaben und Werkzeuge nutzen |

Gemeinsam: eine Frage pro Runde, bekannte Angaben wiederverwenden, unnötige
Kontoverbindungen vermeiden, Daten minimieren und fremde Anweisungen in
E-Mails/Dateien/Webseiten nicht als Freigabe behandeln. Nicht endlos nachfragen:
bei ausreichenden Angaben das vereinbarte Ergebnis und einen nächsten Schritt
liefern. Externe Änderungen weiterhin separat konkret freigeben.

## Prüfung und Grenzen

48 JavaScript- und 8 Python-Prüfungen vor Veröffentlichung bestanden.
Neue Live-Szenarien prüfen die Rückkehr zum Fach-Monkey nach erfolgreicher oder
unverfügbarer Verbindung, das Neuladen und den natürlichen Bewerbungsstart ohne
unnötige Gerätefrage auf Desktop und Mobilgeräten.

Diese Prüfungen belegen Dialogübergänge und Arbeitsauftrag-Inhalte. Sie sind keine
modellübergreifende Garantie für die Qualität späterer KI-Antworten. Physische
Mikrofone, hörbare Sprachqualität und tatsächliche Besucherkonten bleiben separat
zu prüfen. Der Webdialog selbst ist weiterhin regelbasiert.

## Nächste Prioritäten

- Ergebnisse der Fach-Monkeys anhand ausgewählter anonymisierter Beispielszenarien
  qualitativ vergleichen, sofern ein vorhandener kostenloser KI-Zugang verfügbar ist.
- Mehrsprachige Bedienung außerhalb Deutsch/Englisch auf vollständige Beschriftungen
  prüfen; keine vollständig übersetzte Webseite versprechen.
- Die Hilfe bei fehlenden Gerätestimmen passend zum tatsächlich gewählten Gerät
  formulieren; der bisherige Hinweis nennt Samsung auch bei anderen Geräten.
- Weiterhin nur konkrete belegte Verbesserungen veröffentlichen; unveränderte
  Ergebnisse nicht wiederholt als Fortschritt melden.

## Release- und Live-Nachweis

- Version des Gesprächsmoduls: 0.12.0.
- Hauptänderung: `0837fea62ff2c364601aa6c12c83902f24190447`.
- Ergänzung für Aufgabenwechsel innerhalb eines direkten Agentenstarts:
  `316f17d95ba06fe745165166fd560037c0ba8848`.
- Erste vollständige Pipeline erfolgreich:
  https://github.com/oliverdering/odpc-sitebot/actions/runs/40137671134
- Abschließende vollständige Pipeline erfolgreich:
  https://github.com/oliverdering/odpc-sitebot/actions/runs/40137808175
- 48 JavaScript- und 8 Python-Prüfungen bestanden; PHP-Syntax und Metadatenprüfung
  ebenfalls erfolgreich. 39 Live-Browser-Prüfgruppen auf Desktop und Mobilgeräten
  bestanden, einschließlich der drei zusätzlichen Fortsetzungs-/Einstiegsszenarien.
- Direkte Browserprüfung: natürlicher kostenloser Bewerbungswunsch, kopierter
  Auftrag mit ATS-Vorgaben, Ablehnung kostenpflichtiger Verbindung, Neuladen und
  Rückkehr zum ursprünglichen sowie zu einem nachträglich gewechselten Monkey.
- CSS, Layout und Original-Logos unverändert. Keine Besucherkonten verbunden und
  keine Nachrichten versendet. Die eingerichtete Morgenroutine ist ausschließlich
  für die fortlaufende Verbesserung von ai.odpc.de bestimmt.
