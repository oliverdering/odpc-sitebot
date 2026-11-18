# Malta: Aljoli, Rechercheblock A vom 24.10.2026

Status: **Forschungsentwurf, nicht importfreigegeben, nicht live**.

Die eigene Veröffentlichung von MC / Maltese Cuisine liefert neun bezifferte Zutaten sowie Petersilie und Chiliflocken ohne Mengen. Alle elf Zutaten sind erhalten. Die Variante ist ein frischer Fisch-Tomaten-Dip, keine Trockenmischung. Mengen und Markenbezeichnungen wurden sachlich erfasst; deutsche Hinweise sind eigenständig, keine Bilder oder Artikeltexte übernommen.

## Vor einem Import zu klären

- Vier Zutatenidentitäten fehlen: Thunfisch, Sardellenfilets, nicht spezifizierte Brotbrösel, Galletti. Geröstete Semmelbrösel sind kein belegter Ersatz.
- Zwei Quellmengen fehlen. `qualitativeIngredients` speichert sie ohne Zahlen; die produktive Unterstützung ist noch ungeprüft. Das Forschungsschema entspricht dem Vorschlag in Entwurf 17.
- Zwei Zehen und drei Filets dürfen nicht als ganze Knollen/Fische erscheinen. `Stück` ist hier ausschließlich ein Staging-Container.
- Fisch ist sicher enthalten. Glutenrelevanz der Brotprodukte und vollständige Produktallergene müssen vor Freigabe geklärt werden. Keine Allergenfrei-Aussage.
- Nach Zutatenfreigabe Dublettenprüfung wiederholen. Keine Gewichts-, Ausbeute- oder Haltbarkeitsschätzung.

`validation.json` dokumentiert die Prüfung gegen 663 aktuelle Rezepte und 299 Zutaten. Zusätzlich zum Hauptbranch-Validator wurde die noch ungemergte Erweiterung aus Entwurf 17 für qualitative Mengen verwendet; vier Fehlereingaben werden dort abgewiesen. **Keine Änderung am Validator oder produktiven Code in diesem Branch.** Bestandene Staging-Prüfungen erlauben keinen Import.

`live-baseline.json` hält den tatsächlichen Datenbank- und Länderstand fest. Es ist ausdrücklich ein Ausgangsstand und kein Importnachweis.

Ein neuer Kandidat am 24.10.2026. Reine Quellensichtung in `source-screening.json` zählt nicht als zusätzliche Rezeptanlage. Die offenen SI-/AL-/LU-Entwürfe des Vortags bleiben unverändert gesperrt.

## Dateien und nächste Schritte

`candidates.json`: vollständige sachliche Quellmengen, Referenzen, Länderbeleg und Importsperren. `validation.json`: fachliche/strukturelle Prüfung. `validation-report.json`: Ausgabe des Hauptbranch-Validators. `source-screening.json`: gelesene Alternativquellen und offene Punkte.

Zunächst Produktidentitäten und vollständige Deklarationen prüfen. Luxemburg und Malta bleiben Länder mit drei Live-Treffern; weitere passende Primärquellen gezielt suchen. Keine Veröffentlichung dieses Entwurfs, bevor die genannten Fragen und die produktive Mengendarstellung geklärt sind.
