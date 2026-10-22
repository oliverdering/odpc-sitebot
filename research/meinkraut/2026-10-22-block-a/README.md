# MeinKraut – Recherchetranche A, 22.10.2026

Drei eigenständig recherchierte Kandidaten: irische Spice-Bag-Würze, kanadische Ahorn-Montréal-Würze und geröstetes Sri-Lanka-Currypulver. Die Originalseiten wurden gelesen; Zutaten und Mengen bleiben nachvollziehbar. Deutsche Kurztexte sind eigenständig formuliert. Es wurden keine Bilder oder vollständigen fremden Beschreibungstexte übernommen. Dieser Block ist nicht das angekündigte externe 30er-Paket.

## Status

`candidates.json` ist ein eigenständiges Rechercheschema und **kein unmittelbar importierbares `extra.mixes`-Array**. Alle drei Kandidaten bleiben gesperrt, solange die dort dokumentierten Fragen offen sind. `validation.json` bestätigt ausschließlich Struktur, Referenzen und Rechenwerte, keine Verkostung oder abgeschlossene Veröffentlichung. Da einzelne Allergene im übernommenen Atlas nicht vollständig gepflegt sind, prüft der Validator bekannte Senf-, Sesam-, Erdnuss- und Sellerie-Zutaten zusätzlich anhand ihrer Identität.

- Irland: zusammengesetztes Fünfgewürzepulver, unspezifiziertes Chilipulver und MSG sind nicht korrekt als Atlaszutat aufgelöst. Bestehende Fünfgewürze-Rezept-IDs dürfen nicht als Zutaten-IDs eingesetzt werden.
- Kanada: Zwiebelflocken, Dillsamen, Ahornzucker und Knoblauchpulver benötigen formgenaue Zuordnungen. Dillblatt, Ahornsirup und Granulate sind keine identischen Mengenersatzwerte. Enthält Senf.
- Sri Lanka: Basmati fehlt; vorhandener Jasminreis ist nicht gleichzusetzen. Kardamom wird als Samengewicht nach dem Auslösen erfasst. 119 g sind die ausgewählten Einwaagen, nicht die vom Autor ungefähr angegebene Endausbeute. Die 100-g-Vorschau normalisiert diese Einwaagen. Enthält Senf in der dokumentierten Auswahl.

## Belegte Ausgangslage

Live-Daten wurden über `https://meinkraut.odpc.de/data.php` gelesen; der Server meldete die Datenquelle `database`: 288 Zutaten, 649 Rezepturen, 195 auswählbare Länder. Jedes auswählbare Land besitzt mindestens drei Zuordnungen. Bei 115 Ländern fehlt hingegen die Rezeptart `Trockenmischung`. Irland besitzt drei Saucen-/Dressing-Einträge und keine Trockenmischung. `coverage.json` enthält die prüfbare Länderübersicht.

Leere Ansichten deshalb zusätzlich nach Rezeptart, Suchbegriff, Vorratsfilter und Linkziel prüfen. Aus vorhandenen Rohdaten folgt weder eine vollständige kulinarische Abdeckung noch ein nachgewiesener UI-Fehler.

## Weitere geprüfte Quellen

`source-review.json` protokolliert zurückgestellte Quellen: widersprüchliche Fenchelmengen, doppelte Knoblauchzeilen und ausdrücklich gewünschte reine Linkweitergabe. Nicht durch eigene Vermutungen korrigieren oder ungeprüft importieren.

## Nächster Integrationsschritt

1. Offene Zutatenspezifikationen und Zusammensetzungen klären und geprüfte Zutaten ergänzen.
2. Zutatenreferenzen, Allergene und Dubletten gegen den dann aktuellen Bestand erneut prüfen. Ursprüngliche Volumeneinheiten bewahren; keine pauschalen Grammumrechnungen.
3. Recherchedaten in das aktive Rezeptformat überführen. Das bisherige `extra.mixes`-Testformat verlangt 100-g-Rezepte und akzeptiert diese gemischten Originaleinheiten nicht unverändert.
4. Datenbankfreigabe als eigenen, gesicherten Schritt umsetzen: Der aktuelle Deployment-Prozess aktualisiert Dateien, nicht den aktiven `mk_releases`-Datensatz. Kein Abbruch der Datenbankverbindung, um den JSON-Fallback zu erzwingen.
5. JavaScript-/PHP-/Routenprüfungen durchführen und erst danach die konkreten Länder- und Zutatenlinks mit der neuen aktiven Freigabe prüfen.

## Validierung

```sh
python3 scripts/validate_meinkraut_research.py research/meinkraut/2026-10-22-block-a/candidates.json
node scripts/test_meinkraut.cjs
```

Mit `--require-ready` liefert der Prüfer bei offenen Kandidaten Exitcode 2. Das ist eine beabsichtigte Importsperre. Alle Recherchedaten liegen außerhalb des ausgelieferten Webverzeichnisses. Website, Shopzustand, Nutzerregale und aktive Rezeptdaten bleiben unverändert.
