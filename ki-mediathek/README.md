# ODPC KI-Mediathek

WordPress-Seite 3852 ruft unverändert den Shortcode `[odpc_ki_mediathek]` auf. Die Laufzeiterweiterung verwendet den Banner aus WPCode 3851 über `odpc_media_render(false)`. WPCode 3854 gestaltet dieselben Karten auf beiden Seiten. Auf der Mediathek wird das bereits gestaltete Originalelement unter der Einleitung positioniert.

## Betrieb und Quellen

Ein gemeinsamer GitHub-Workflow aktualisiert um 06:00 Uhr in Europe/Berlin, bei einer ausdrücklich veröffentlichten Softwareänderung sowie bei manuellem Start. Alle Aufrufe teilen eine Produktionssperre. Der Veröffentlichungsjob prüft die bekannte IONOS-Hostsignatur und verwendet das bestehende SFTP-Secret. Das neue Portal liest einen atomar ersetzten Katalog. Der alte WordPress-Optionswert bleibt zur Wiederherstellung erhalten. Erst nach Prüfung beider öffentlichen Seiten wird das bisherige WordPress-Ereignis stillgelegt.

MediathekViewWeb liefert die Beitragskandidaten. Treffer müssen einen fachlichen Bezug und einen Originallink haben. ARTE-Sendezeiten werden vom verknüpften Originalprogramm übernommen. TVmaze (CC BY-SA, Quellenhinweis auf der Seite) durchsucht deutsche Tagesprogramme. Automatische TVmaze-Termine müssen mit Titel, Sender und Sendezeit des offiziellen ARD-Programms übereinstimmen. Die Abdeckung variiert je Sender. Redaktionell bestätigte Einzeltermine und eigene Zusammenfassungen stehen in editorial.json. Ablaufdaten werden nur übernommen, wenn das Original sie ausweist. Ein rotierender Linkcheck schließt bei HTTP 404 und 410 aus. Erreichbarkeitsprobleme und Teilabrufe allein löschen keine Einträge.

Der Katalog hält bis zu 160 verfügbare Einträge und archiviert weitere Datensätze im selben Snapshot. Metadaten zu fremdsprachigen ARTE-Fassungen und ausdrücklich gekennzeichneten Radioformaten werden gefiltert. Die Senderkennzeichen werden aus geprüften Wikimedia-Commons-Dateien lokal ausgeliefert. Quelle, Urheber und Lizenz stehen je Datei in logos/sources.json und in den Bildnachweisen auf der Seite. Die Sendernamen dienen bei fehlendem Bild als Ersatz. Pressefotos und Videos werden nicht übernommen.

## Rollback

Im Workflow **ODPC KI-Mediathek** auf main den manuellen Lauf mit **legacy** starten. Dieser pausiert das Veröffentlichen, entfernt den Einstiegspunkt der Erweiterung und aktiviert beim nächsten WordPress-Aufruf wieder den vorherigen Shortcode mit seinem ursprünglichen Zeitplan. WordPress-Seiteninhalt, beide aktiven WPCode-Snippets und ihr Datenstand bleiben dafür erhalten. **resume** führt einen neuen Abruf mit Prüfung aus und setzt den Betrieb fort. Bei einem Fehler während des Deployments werden die gerade ersetzten Dateien automatisch auf die vorherige Fassung zurückgesetzt. Der vorherige reine Inhaltsstand steht zusätzlich als previous.json im isolierten Medienverzeichnis, sobald mindestens ein Vorgänger vorliegt.

## Prüfung

`python ki-mediathek/test_catalogue.py`, `php -l ki-mediathek/portal.php` und `node --check ki-mediathek/portal.js` prüfen die lokalen Grundlagen. `python ki-mediathek/collect.py --out /tmp/ki-mediathek.json` führt die Quellrecherche ohne Veröffentlichung aus. Der Workflow kontrolliert die öffentliche WordPress-Ausgabe und den Banner auf der Startseite vor dem Umschalten des Zeitplans.
