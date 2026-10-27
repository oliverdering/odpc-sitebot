# Grün selbst starten, Blau persönlich einrichten – 27.10.2026

## Freigegebene Neuausrichtung

Oliver hat nach der Konzeptbesprechung die sichtbare Umsetzung auf ai.odpc.de
und der Dachseite odpc.de ausdrücklich beauftragt. Die bisherige Beschränkung
auf kostenlose Monkey-Arbeitsaufträge wird um eine klar getrennte Premium-Sektion
mit Rückverweisen auf bestehende Fachprodukte ergänzt. Andere Projekte und
Automationen bleiben unberührt. Es werden keine Preise erfunden oder Abos gekauft.

## Umsetzung

- Zwei sichtbare Wege: Grün für kostenlose Anleitungen und eigenständige erste
  Aufträge; Blau für die kostenpflichtige persönliche Einrichtung durch Oliver,
  vor Ort oder online. Kosten- und Leistungsgrenze steht ausgeschrieben am Angebot.
- Premium-Rollen führen zu vorhandenen Produkten: Anwalt → PraeLex.ai,
  Buchhaltung/Lohn → PraeTax, Immobilien → PRAEDIUM, Unternehmensaufbau → initIQ,
  Webdesigner/Webmaster → Webdesign, Marketing/Büro → KI-Dienstleistungen,
  Dokumente/Wissen → PraeDoc.ai.
- Auf ODPC-Startseite, Portfolio, sieben Fachseiten und zwei Lernseiten sind
  Gegenverbindungen und die Einordnung als helper · monkey Premium ergänzt.
  Die vorhandenen Fachinhalte und Produktmarken bleiben erhalten. Ausgewählte
  Fachseiten zeigen nur das passende Premium-Paket statt des ganzen Katalogs.
- Links verbinden den kostenlosen Einstieg mit Prompt Engineering und KI nach
  Branche. Früher entfernte persönliche Business-Verweise werden wieder auf die
  passenden Dachseiten geführt. Der kostenlose Rechtsstarter heißt nun
  Rechtsunterlagen-Monkey; PraeLex ist die persönlich eingerichtete Business-Lösung.
- Das Originalmotiv bleibt dieselbe unveränderte PNG-Datei. Ausschließlich bei
  Premium wird über einen SVG-Farbfilter der grüne Akzent blau dargestellt;
  Gesicht, Form und Originaldatei bleiben erhalten. Keine generierten Logos.
  Bestehendes Layout und Stylesheet bleiben erhalten; neue Bereiche verwenden
  eigene, eng begrenzte Gestaltungsregeln.

## Technischer Weg und Prüfung

Ein gemeinsames, versioniertes Angebotsmodul wird auf beiden bereits bestehenden
WordPress-Installationen als mu-plugin bereitgestellt. Es ergänzt Inhalte über
einen auf ODPC-Fachseiten begrenzten Filter; keine Datenbank-, Rechte-, Hosting-
oder Kundenseitenänderungen. Der vorhandene Release sichert alle betroffenen
Dateien vorab und stellt bei fehlgeschlagener öffentlicher Prüfung beide Ziele
wieder her. Neue PHP-Prüfung erfolgt vor SFTP-Schreibzugriff.

69 JavaScript- und 8 Pythonprüfungen vor Veröffentlichung bestanden. Zusätzliche
PHP-Prüfung kontrolliert Inhaltserhalt, Fachzuordnung, Einmaligkeit und Seitengrenzen.
Die Live-Abnahme prüft alle neun grünen Arbeitsabläufe sowie die sieben blauen
Produktziele einschließlich PraeDoc.ai, Rücklinks, Bildungslinks, geladene Originalbilder, Filter und mobile
Karten. Desktop-/Mobil-Screenshots werden im Release-Artefakt abgelegt.
Die abschließende Live-Abnahme ist erfolgreich; Nachweise folgen unten.

Die direkte Browserprüfung zeigte einen zusätzlichen Auslieferungsunterschied:
Prüf-URLs enthielten die neuen Bereiche, normale ODPC-Produktadressen teilweise
noch alte HTML-Caches. Der Release erneuert deshalb nur gesicherte HTML-Cachedateien,
deren Canonical exakt einer der elf betroffenen ODPC-Seiten entspricht. Bei
Fehlern werden auch diese Cachedateien zurückgespielt. Andere Cacheinhalte bleiben
unangetastet. Die Live-Prüfung verwendet nun normale Adressen ohne Prüfparameter.
Lazy-Loading-Bilder werden erst nach Scrollen geprüft; vorhandene Analytics-Parameter
werden bei der Navigation nicht als fehlerhaftes Linkziel behandelt.

## Grenzen

Ein Premium-Angebot ist keine pauschale Freischaltung sämtlicher beworbener
Integrationen. Umfang, Daten, Kosten und tatsächliche Zugänge werden je Projekt
vereinbart und geprüft. Keine Buchung, Nachricht oder Zahlung ausgelöst. Keine
Rechts- oder Steuerentscheidung durch die KI zugesagt. Private Besucherkonten und
physische Mikrofon-/Hörqualität sind nicht getestet.

## Veröffentlichungsnachweis und Rücksicherung

- Veröffentlichter Quellstand: `3f935db3a25a78ab90de6702afd55f96b590e116`,
  Angebotsmodul `1.1.0`, bestehender Dialogstand `0.14.5`.
- [Release 40474178382](https://github.com/oliverdering/odpc-sitebot/actions/runs/40474178382)
  am 27.10.2026 erfolgreich abgeschlossen: PHP-Syntax, Angebotsprüfung,
  69 JavaScript- und 8 Pythonprüfungen sowie **61 bestandene Live-Prüfgruppen**.
- Desktop und emuliertes Mobilgerät prüfen beide Domains an normalen öffentlichen
  Adressen, alle neun Arbeitsaufträge, acht Dialogsprachen, Clipboard-Fallback,
  Wiederaufnahme, Fehlerfälle und die Grün-Blau-Wege. Alle sieben Fachseiten
  zeigen jeweils nur das passende Premium-Paket. Ein echter Browserklick führt
  vom blauen Anwalt-Monkey zu PraeLex und über den grünen Rücklink wieder zum
  kostenlosen Katalog. WhatsApp wurde als Link geprüft; keine Nachricht versandt.
- Zusätzlich im interaktiven Browser bestätigt: ODPC-Portfolio liefert Version
  `1.1.0` mit sieben Karten; AI-Startseite zeigt beide Wege. Screenshot:
  [Grün-Blau-Einstieg](evidence/odpc-green-blue-live-2026-10-27.jpg).
- [Abschlussartefakt mit Dateisicherung, Release-JSON und Desktop-/Mobil-Screenshots](https://github.com/oliverdering/odpc-sitebot/actions/runs/40474178382/artifacts/12720970589).
  SHA-256: `b541055f4556495c94701c88a105e4f62a6f63d5fa73d98a21e38285a47e17ae`.
  Die Artefaktaufbewahrung beträgt 30 Tage.
- Für die vollständige Rückkehr zum Stand vor dieser Neuausrichtung liegt die
  [erste Dateisicherung](https://github.com/oliverdering/odpc-sitebot/actions/runs/40472550279/artifacts/12718792677)
  vor. Beim Wiederherstellen müssen die beiden gemeinsamen mu-plugin-Dateien
  und die gesicherten AI-Dateien zusammen behandelt werden. Ein vorher nicht
  vorhandenes Modul wird anhand des Sicherungsmanifests entfernt. Gesicherte
  ODPC-Cachedateien und die genaue Zuordnung stehen im jeweiligen Release-Artefakt.
- Zwei anfängliche Browserprüfungen wurden korrigiert: Lazy-Loading-Bilder vor
  dem Laden prüfen und Analytics-Parameter als falsches Ziel bewerten. Der
  zusätzliche reale Cachefehler wurde durch die oben beschriebene begrenzte
  Cacheerneuerung behoben. Ein überholter Release mit langsamer Browserinstallation
  wurde beendet; ausschließlich die abschließende Fassung gilt als abgenommen.

## Nächste Priorität

Die bestehenden Fachprodukttexte bei einer Folgeprüfung auf einheitliche Angaben
zu Einrichtung, Übergabe, benötigten Zugängen und vereinbartem Leistungsumfang
prüfen. Bestehende Preise oder Zusagen werden dabei nicht erfunden. Für diesen
Durchlauf bleibt es bei Angebotszuordnung, Rückverknüpfungen und belastbarer
öffentlicher Auslieferung.
