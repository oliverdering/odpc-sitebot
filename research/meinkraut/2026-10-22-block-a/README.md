# MeinKraut – Recherchetranche A, 22.10.2026

## Aktueller Stand

Zwei veröffentlichte Autorenvarianten sind in der aktiven Datenbank und auf ihren öffentlichen Rezeptseiten geprüft:

- `mk-source-ca-maple-montreal`: Montréal-Steakwürze mit Ahorn, Quelle Maple from Canada / Quebec Maple Syrup Producers.
- `mk-source-lk-roasted-curry`: geröstetes Sri-Lanka-Currypulver, Quelle Dini Kodippili / The Flavor Bender.

Die sechs formgenauen Zutaten Basmati, ausgelöste grüne Kardamomsamen, Zwiebelflocken, Dillsamen, Ahornzucker und Knoblauchpulver sind vorhanden. Live-Bestand: **294 Zutaten, 651 Rezepte**, Datenquelle `database`. Kanada und Sri Lanka haben jetzt je vier Rezeptzuordnungen, davon eine Trockenmischung. Irland hat weiterhin drei Saucen-/Dressing-Rezepte und keine Trockenmischung. Die Zahl auswählbarer Länder ohne Trockenmischung ist von 115 auf 113 gesunken. Dies bedeutet keine vollständige kulinarische Länderabdeckung.

Der irische Spice-Bag-Kandidat bleibt gesperrt: Fünfgewürzepulver ist zusammengesetzt, Chilipulver unspezifiziert und MSG noch nicht als Zutat aufgelöst. Keine unbekannten Zusammensetzungen oder Grammwerte ergänzen.

## Herkunft und Mengen

Die Originalseiten wurden gelesen. Sachliche Mengenangaben bleiben in Originaleinheiten erhalten; deutsche Kurztexte wurden eigenständig formuliert. Keine fremden Bilder oder vollständigen Beschreibungstexte übernommen. Beide Live-Rezepturen sind als veröffentlichte Autorenvarianten gekennzeichnet und noch nicht von MeinKraut verkostet.

- https://maplefromcanada.ca/recipes/maple-montreal-steak-spice/
- https://www.theflavorbender.com/sri-lankan-roasted-curry-powder/
- https://finnishinireland.com/recipes/spice-bag-spice-mix

Kanada: Löffelangaben und 30 g Ahornzucker bleiben getrennte Gewichts-/Volumeneinheiten. Keine eigene 100-g-Umrechnung. Enthält Senf.

Sri Lanka: 5 g beziehen sich auf ausgelöste Kardamomsamen ohne Kapselhülle. Der dokumentierte Ansatz enthält optionalen schwarzen Senf und keine optionalen Curryblätter. 119 g sind die Einwaagen vor dem Rösten; die ungefähre Endausbeute der Quelle wird nicht garantiert. Die Mischküche skaliert ausschließlich diese gewogenen Einwaagen. Enthält Senf.

## Prüfnachweise und Import

`../2026-10-22-live-import.json` protokolliert den tatsächlichen Datenbankstand, beide neuen öffentlichen Rezeptseiten, sechs Zutatenrouten samt vorhandenen Symbolen, Länder-Treffer und den erfolgreichen Deployment-Lauf. Quellen und Autoren bleiben in SSR, Rezeptdialog, Mischküche und kopiertem Rezept erhalten.

`candidates.json` bleibt ein Rechercheschema, kein direktes `extra.mixes`-Importarray. Bereits übernommene Datensätze tragen `importStatus: live_verified` und die tatsächliche Rezept-ID. `readyForImport: false` verhindert eine erneute Übernahme. Zutatenvorschläge mit `status: bundled` beschreiben die jetzt im Katalog enthaltenen Formen.

`coverage.json` und `integration-review.json` enthalten den historischen Ausgangsstand und die Prüfung vor dem Import. `source-review.json` hält widersprüchliche oder ausdrücklich nur zur Verlinkung vorgesehene Quellen zurück. Diese Probleme wurden nicht durch erfundene Werte aufgelöst.

Der Datenbankimport prüft den aktiven Bestand gegen die vorherige Revision, erlaubt nur ausdrücklich aufgeführte Ergänzungen, schaltet transaktional um und behält die vorherige Freigabe für einen Rückfall. Große Katalogdaten werden über den vorhandenen SFTP-Zugang bereitgestellt; kleine authentifizierte HTTP-Aufrufe steuern die Freigabe. Temporäre Importdateien werden entfernt. Nutzerregale und Shopdaten werden nicht verändert.

## Weiterarbeit

Vier neue Kandidaten wurden heute insgesamt recherchiert: drei in diesem Block und ein weiterer im getrennten Entwurf https://github.com/oliverdering/odpc-sitebot/pull/3 . Der neue Mixed-Spice-Kandidat für IE/GB hat belegten Länderbezug und Mengen, benötigt aber gemahlene Muskatblüte als eigene Zutat. Erst diese Zuordnung und die anschließende Integration abschließen; danach Baltikum, Karibik und Zentralasien nach tatsächlichen Lücken priorisieren. Tagesobergrenze insgesamt zehn neue Kandidaten.

```sh
python3 scripts/validate_meinkraut_research.py research/meinkraut/2026-10-22-block-a/candidates.json
node scripts/test_meinkraut.cjs
php scripts/test_meinkraut_php.php
php scripts/test_meinkraut_catalog_release.php
```

Mit `--require-ready` bleibt der Rechercheimport gesperrt: offene Kandidaten dürfen nicht ungeprüft übernommen und bereits importierte Kandidaten nicht dupliziert werden.
