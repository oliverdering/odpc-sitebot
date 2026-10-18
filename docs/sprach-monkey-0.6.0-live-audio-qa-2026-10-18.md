# Sprach-Monkey 0.6.0 – Stimmenwechsel und Live-Abnahme, 18.10.2026

Release-Commit: b155ddc66ac966ac598e43cf42a5a690abf1c9e6
Erfolgreicher Deployment-Lauf: https://github.com/oliverdering/odpc-sitebot/actions/runs/39153579172
Live: https://ai.odpc.de/helper-monkey-einfach-starten-sprachassistent/?hm_voice_release=0.6.0

## Problem und Verhalten

0.5.4 konnte bei allgemeinen oder nicht passenden Stimmnamen dieselbe Gerätestimme für beide Einstellungen verwenden. Damit war die Auswahl männlich/weiblich auf solchen Geräten wirkungslos.

0.6.0 wählt eine passend benannte native Stimme, falls vorhanden. Andernfalls erzeugt die lokal im Browser ausgeführte eSpeak-NG-Engine getrennte männliche und weibliche Varianten (m3 und f2). Diese unterscheiden sich in Grundtonbereich und Formantparametern. Eine weibliche Gerätestimme wird nicht mehr als männlicher Fallback verwendet.

Das Menü enthält weiterhin nur Stimme (Männlich/Weiblich) und Sprache (acht Sprachen). Bei eingeschalteter Ausgabe spielt ein Stimmenwechsel automatisch eine kurze Hörprobe. Stopp, Neustart, Schließen, Antwort senden und Abschalten der Ausgabe beenden laufende lokale Audionodes.

## Auslieferung

Die ca. 3,3 MB große Engine wird bei Bedarf von ai.odpc.de geladen. Es wurde keine kostenpflichtige Sprach-API oder ein Abonnement hinzugefügt. Die lokale Ersatzsynthese verarbeitet den Text im Browser. Sie klingt synthetischer als hochwertige native Stimmen; dieser Hinweis steht auch im Dialog.

Die Distribution ist auf den Upstream-Commit c023eaca5609b4523613f674d0ee67bd761502f1 festgelegt. SHA-256-Prüfungen sichern Download, SFTP-Dateien und öffentliche HTTPS-Dateien ab. Lizenz (GPL-3.0-or-later), Herkunft und Source-Verweise werden mit ausgeliefert. Das Plugin wird erst nach erfolgreicher Veröffentlichung aller benötigten Dateien aktiviert. Die vorherige Plugin-Datei bleibt als Workflow-Artefakt gesichert.

## Automatische Prüfungen

- PHP-Syntaxprüfung bestanden.
- Acht bestehende Python-Prüfungen und zehn JavaScript-Testgruppen bestanden.
- Die Fälle eines einzigen allgemeinen Android-Stimmnamens, fehlender männlicher Stimme, leerer/verzögerter Kataloge und fehlgeschlagener nativer Synthese werden geprüft.
- 16 echte Synthesen im unveränderten eSpeak-Worker erzeugten nicht-stumme Audiodaten: acht Sprachen mal zwei Varianten. Stimmkennung/Sprachdatei muss vorhanden sein, set_voice muss erfolgreich zurückkehren, Samples müssen gültig sein und männlich/weiblich unterschiedliche Wellenformen ergeben.
- Upload und exakter Live-Code geprüft.

## Direkte Live-Prüfung

Chrome-Testbrowser, ohne native Stimmen. Damit wurde der tatsächliche lokale Ersatzpfad geprüft. Alle folgenden Wiedergaben erreichten den Zustand playing und anschließend ended. Die Tabelle zeigt die protokollierten Kennungen und PCM-Samplezahlen der Live-Hörproben (44.100 Hz).

| Sprache | Männliche Kennung | Samples | Weibliche Kennung | Samples |
| --- | --- | ---: | --- | ---: |
| Deutsch | gmw/de+m3 | 181720 | gmw/de+f2 | 182730 |
| Englisch | gmw/en-US+m3 | 166600 | gmw/en-US+f2 | 173308 |
| Spanisch | roa/es+m3 | 135002 | roa/es+f2 | 143040 |
| Französisch | roa/fr+m3 | 135090 | roa/fr+f2 | 140394 |
| Italienisch | roa/it+m3 | 171714 | roa/it+f2 | 175836 |
| Portugiesisch | roa/pt-PT+m3 | 163750 | roa/pt-PT+f2 | 167148 |
| Polnisch | zls/pl+m3 | 162714 | zls/pl+f2 | 166560 |
| Türkisch | trk/tr+m3 | 206420 | trk/tr+f2 | 207910 |

Zusätzlich bestanden: direkter Wechsel während laufender männlicher Ausgabe auf weiblich (Kennung wechselt und neuer Audionode spielt), Stopp mit Zustand stopped, vollständiges deutsches Interview mit vier Antworten und funktionierendem Ziel /webmaster-agent/, Neustart, Hörprobe bei ausgeschalteter automatischer Ausgabe. Keine von ai.odpc.de gemeldeten Konsolenfehler beobachtet.

## Grenze

Die Wiedergabe wurde mit echten im Browser erzeugten Audiodaten bis zum Wiedergabeende geprüft. Eine physische Hörprüfung auf dem S24 des Nutzers oder ein Test sämtlicher nativer Betriebssystemstimmen wurde nicht durchgeführt.
