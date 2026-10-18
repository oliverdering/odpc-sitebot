# Sprach-Monkey: Live-Prüfung am 18.10.2026

Ausgangspunkt: Version 0.5.1. Veröffentlicht wurde die zusammengeführte Fehlerkorrektur als 0.5.2.
Release-Commit: cad7a5e824f0c27f83c9f28627434190aebdbe66
Deployment: https://github.com/oliverdering/odpc-sitebot/actions/runs/39150433353
Ergebnis: erfolgreich. Die vorherige Plugin-Datei ist als Workflow-Artefakt gesichert.

## Korrekturen

- Falsch maskierte Wortgrenzen in der Erkennung der Stimmnamen korrigiert; weitere bekannte Namen übernommen.
- Bei männlich/weiblich wird eine tatsächlich passend erkannte Stimme ausgewählt. Keine unbemerkte Systemstimme außerhalb des Filters.
- Fehlende Stimmen werden ausdrücklich angezeigt; Stimmprofil und Hörprobe sind dann deaktiviert. Texteingabe bleibt verfügbar.
- Verzögertes Laden und Änderungen des Stimmenkatalogs werden berücksichtigt.
- Hörproben überschreiben die Interviewfrage für Wiederholen nicht mehr.
- Ausstehende Spracheingaben und Sprachausgabe werden bei Antworten, Filterwechsel, Neustart und Schließen beendet.
- Sprachwechsel und Zurück entfernen veraltete Eingaben.
- Breitenberechnung des Dialogs und Tastaturfokus bei deaktivierter Stimmenliste korrigiert.

## Automatische Prüfung

PHP-Syntaxprüfung bestanden, acht Python-Prüfungen und sechs ausführbare JavaScript-Testgruppen bestanden.
Die JavaScript-Tests prüfen unter anderem 16 Kombinationen aus acht Sprachen und zwei Stimmtypen mit simulierten Stimmen, fehlende Stimmen, verzögerte Kataloge, Profilerhalt und Hörproben.
Upload-Prüfsumme und exakter JavaScript-/Bedienelement-Code auf der Live-Startseite stimmen mit dem Release überein.
Der Workflow veröffentlicht bei Push nur von main und führt diese Prüfungen vor jeder Veröffentlichung aus.

## Direkte Live-Prüfung im Chrome-Testbrowser

| Prüfung | Ergebnis |
| --- | --- |
| Deutsch, Englisch, Spanisch, Französisch, Italienisch, Portugiesisch, Polnisch, Türkisch | Jeweils vier Fragen beantwortet und Interview abgeschlossen; lokalisierte Fragen und Buttons sichtbar |
| Stimmtypen alle/männlich/weiblich | In allen acht Sprachen bedienbar; bei leerem Gerätekatalog ausdrückliche Meldung und deaktivierte Hörprobe |
| Leere Antwort | Kein Fortschritt |
| Enter, Zurück, Neustart | Erfolgreich |
| Stopp | Audio beendet; Texteingabe verfügbar |
| Escape, oberes X, Schließen | Dialog geschlossen, aria-expanded=false |
| Tab und Shift+Tab an Dialoggrenzen | Fokus bleibt im Dialog |
| Mikrofon nicht erlaubt | Verständliche Fehlermeldung mit Texteingabe als Alternative |
| Einbindung Startseite und Sprachassistent-Einstiegsseite | Version 0.5.2 vorhanden |
| Dialogbreite im Testfenster | Kein horizontaler Überlauf; genau ein Modul; acht Spracheinträge |
| Vier mögliche Zielseiten | HTTP 200 |
| Von ai.odpc.de gemeldete Browserfehler | Keine beobachtet |

## Grenzen der Abnahme

Der entfernte Testbrowser stellt keine Synthesestimmen bereit; die Mikrofonaufnahme wird mit not-allowed abgelehnt. Daher ist die echte hörbare Ausgabe und Erkennung auf Android/iOS/Windows nicht abgenommen. Die positiven Stimmzuordnungen sind automatisiert mit simulierten Stimmen geprüft, die Behandlung fehlender Stimmen direkt live. Auch ein physischer Mobilgerätetest ist nicht erfolgt. Eine universelle Zusage für männliche und weibliche Stimmen auf jedem Gerät ist nicht möglich; maßgeblich ist der vom Gerät bereitgestellte Katalog.

Es wurden keine kostenpflichtigen Dienste, Abonnements oder kostenpflichtigen Sprach-APIs ergänzt.
