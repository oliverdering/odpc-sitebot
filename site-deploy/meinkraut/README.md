# Mein Kraut · kostenlose Testversion 2.0

Ziel: https://meinkraut.odpc.de/ · IONOS-Webspace `/MeinKraut`.

## Umfang

288 Grundzutaten, 649 Rezepturen und Würzideen einschließlich 24 neuer Trockenmischungen mit je 100 Gramm. 195 Länder im Atlas; 24 vertiefte Länderporträts und 35 Gewürzporträts mit Quellen. Der bestehende Weltgewürz-Atlas auf odpc.de/wga bleibt unverändert.

App ohne Registrierung oder kostenpflichtige APIs. Vorrat, Einkaufsliste, Favoriten und eigene Mischungen liegen im Browser. JSON-Export/Import überträgt und sichert diese Daten. PayPal, Bezahlschranke, Konten und Bestellabwicklung sind bewusst noch nicht implementiert.

## Funktionen

- Rezeptsuche mit Land-, Rezeptart- und Vorratsfilter; Länder auch über Küchenbegriffe wie „chinesisch“ finden.
- Gramm-Mischküche: Gesamtmenge skalieren, Gewichtung ändern, Bestandteile ausschließen und weitere trockene Zutaten hinzufügen.
- TL/EL nur als zutatenspezifische Näherung. Für Zutaten ohne Umrechnungsansatz wird keine Grammpräzision vorgetäuscht.
- Eigene Rezepturen speichern, kopieren, exportieren; druckbare Etiketten mit Rezeptur und Mischdatum.
- Marke: „Mein Kraut. Dein Geschmack.“ / „My spice. Your taste.“
- Geschäftsmodell: Grundgewürze und Nachfüllpackungen, schwarze Gewürzgläser mit passendem Verschluss und Agentur-Beschriftung; später bezahlter Datenbankzugang. Der Shop ist als in Vorbereitung gekennzeichnet. Aktuell nur Gläser-/Beschriftungsanfragen, keine Bestellabwicklung oder Zahlung.
- Transparentes, datiertes Kostenbeispiel für italienische Kräuter gegenüber Ankerkraut. Kosten des Ersteinkaufs und des Verbrauchs getrennt.

## Daten und Qualität

`content/atlas-base.json` stammt aus dem bestehenden Release 1.0. Der Altbestand enthält redaktionelle Hausvarianten ohne vollständigen individuellen Quellen- oder Verkostungsnachweis. `starter-mixes.json` enthält 24 ausdrücklich nicht verkostete eigene Rezepturen. `country-profiles.json` und `ingredient-knowledge.json` enthalten die recherchierten Erweiterungen; Quellen belegen den jeweiligen Kontext, während die Küchentipps redaktionell zusammengestellt sind. Symbole stammen aus dem Altbestand; mehrere Zutaten teilen sich ein Symbolbild. Ein eigenständiges, genaues Foto je Zutat steht noch aus.

Die Zusammenfassung `content/meinkraut-content.json` ist der Laufzeit-Datenstand. `meinkraut-data.js` wird nur für die lokale statische Vorschau generiert; die Live-Version verwendet `data.php`.

## Betrieb

`index.php` liefert die App, `data.php` den öffentlichen Katalog. Zwei eigene MariaDB-Tabellen speichern versionierte Kataloge und ihre Datensätze (`mk_releases`, `mk_catalog_records`). Bestehende Tabellen werden nicht gelöscht. Bei einem Datenbankausfall dient das veröffentlichte JSON als Lesefallback.

Zugangsdaten werden ausschließlich serverseitig außerhalb des Document-Roots in einem geschützten privaten Verzeichnis gespeichert. Keine Zugangsdaten im Repository. Die einmalige Einrichtung erfolgt über einen zufälligen, tokengeprüften HTTPS-Endpunkt; nach erfolgreicher Einrichtung löscht er sich. Im Repository liegt nur der SHA-256-Prüfwert dieses Tokens. `health.php` liefert ausschließlich öffentliche Versions- und Mengendaten.

Der GitHub-Workflow `meinkraut-deploy.yml` prüft Datenreferenzen, Rezeptmengen und JavaScript/PHP-Syntax und überträgt ausschließlich den ausgewählten Webspace. Die SFTP-Hostkennung wird gegen den bestehenden Fingerabdruck geprüft. Öffentliche Vorgängerdateien werden gesichert. `assets-bundle.zip` enthält nur die WebP-Logoableitungen und vorhandenen Gewürzsymbole.

## Prüfung

`node scripts/test_meinkraut.cjs` prüft Referenzen, 100-g-Summen, Länder-/Gewürzprofile, proportionale Mengen, Ausschlüsse, leere Mischungen und Importvalidierung. Zusätzlich manuell im Browser geprüft: Startseite, Rezeptöffnung, 200-g-Skalierung, salzfreie Variante, lokales Speichern sowie Smartphone-Layout.

## Nächste Produktstufen

Weitere Länderporträts und Einzelgewürze recherchieren, Rezepturen mit Testköchen verkosten, Bilder exakt zuordnen, Feedback aus Pilotbetrieb auswerten. Anschließend Konten und serverseitig gespeicherte persönliche Mischungen planen. PayPal erst nach einem eigenständigen Zugangs- und Zahlungsmodell integrieren; ein öffentlich ausgelieferter JSON-Katalog ist keine geschützte Bezahldatenbank.

## Gläserauswahl · Stand 21.10.2026

Kandidat: ETIVERA FACTUM 192 ml, schwarz, TO-58DE, Artikel FAC-SW192#K180Z. Lieferantenseite: https://www.etivera.de/factum-schraubglas-192ml-schwarz-mdg.-to58de-karton-180stk.html

180 Stück kosten laut Angebot 174,57 EUR inklusive 19 % MwSt., zuzüglich Versand, ohne Deckel (rund 0,97 EUR je Glas). Das ist ein Beschaffungshinweis und kein Mein-Kraut-Verkaufspreis. Passenden Deep-Twist-Off-Deckel separat kalkulieren. Vor der Sortimentsfreigabe Eignung der Glas-/Deckelkombination, Dichtheit und Lichtschutz bestätigen; die schwarze Farbe allein belegt keine vollständige Lichtundurchlässigkeit. Konformitätsdownload war im Recherchezugriff nicht abrufbar. Füllgewicht pro Gewürz praktisch messen; 192 ml sind kein einheitliches Grammgewicht. Kalkulation später inklusive Deckel, Etikett/Druck, Verpackung, Versand, Zahlungsgebühren und Zugangskosten betrachten.

## Marketing und Vergleich

Startseite erklärt das Selbstmischen sofort, zeigt den 25-g-Vergleich direkt nach dem Einstieg und ergänzt Familien-, Geschenk- und Grillanlässe. Preisvergleich in der Hauptnavigation. Preisstammdaten liegen zentral in `priceRows` mit sechs Händlerquellen; Startseite und Detailrechnung rechnen daraus. Stand 21.10.2026: 1,0814 EUR Zutaten/100 g, 0,27035 EUR/25 g; Vergleichsglas 5,49 EUR/25 g. Differenz 5,21965 EUR, rund 95 %. Erster Einkauf der sechs 500-g-Packungen: 38,95 EUR plus Versand. Eigene Gläser, Etiketten, Versand, Arbeitszeit, Restvorräte und späterer Zugang sind nicht im Prozentvergleich enthalten. Fremdhändlerpreise sind keine zukünftigen Mein-Kraut-Shoppreise. Kein Vergleich identischer Originalrezepturen oder interner Herstellkosten. Familienaktionen mit Begleitung Erwachsener und milden Kräutern. Wintermischungen werden als eigene Entwicklungsideen beschrieben, keine noch nicht vorhandenen fertigen Winterrezepte versprochen.
