# ODPC – Konsolidierte Sichtbarkeitsprüfung (15.11.2026)

## Verbindliche Rahmenbedingungen
- Offizieller Name: **Oliver Dering IT-Consulting**, ODPC als Kurzmarke.
- Google Business Profile **nicht** ändern.
- **0 € zusätzliche Software-/API-/Abokosten**; Open Source, bestehende WordPress-/GitHub-Infrastruktur und kostenlose Webmaster-Angebote.
- Kein blindes Deployment, keine Rücksetzung manueller WordPress-Änderungen. Vor Merge Live-Stand, SFTP-Workflows, Zielpfade und Backups abgleichen.

## Quellenprüfung (öffentliche Suchindex-Treffer, nicht vollständiger Live-HTML-/GSC-Audit)
- https://odpc.de/impressum : korrekter Name und Adresse Rüberer Straße 25, 56751 Polch-Kaan.
- https://odpc.de/about-me : klare drei Angebote (WordPress/Webdesign, Betrieb, KI) und echte Referenzen.
- https://odpc.de/preise-angebote : 59 €/h Privat, 89 €/h Geschäftskunden; aktuelle Preisangaben erst vor Übernahme live prüfen.
- https://odpc.de/ihre-it-mein-auftrag-ein-fester-ansprechpartner : im indizierten Seitentext **Oliver Dering Professional Consulting**; redaktionelle Konsistenzabweichung.
- https://odpc.de/it-dienstleistungen-ki-integration : offizieller Name korrekt und IT-Leistungen vorhanden.
- https://odpc.de/ : Suchtreffer zeigt Leistungscluster und Regionen; dieser Suchtreffer ist keine Bestätigung des aktuellen Header-HTML.
- Keine bestätigten GSC-/Bing-Indexzahlen oder aktiven IndexNow-Übermittlungen; Suchtreffer beweisen keine vollständige Indexierung.

## Externe KI-Unternehmensanalyse (vom Inhaber bereitgestellt, nicht unabhängig verifiziert)
Positiv: Name/Standort/Portfolio/Referenzen/Preise werden erkannt. Genannt werden 5,0 Sterne/15 Rezensionen (vor Verwendung unabhängig prüfen). Kritik: zu breites Portfolio, uneinheitlicher Produktreifegrad, wenig unabhängige Fallstudien, geringe Kapazitäts-/Prozessnachweise. **Nicht als unabhängigen Auditbefund darstellen.**

## Priorisierte technische Maßnahmen
1. Read-only Indexability-Audit aus tools/seo/read_only_index_audit.py tatsächlich ausführen, Ergebnisse mit Datum protokollieren.
2. Vorhandene WordPress-/RankMath-Sitemap, Canonical, Robots, noindex und JSON-LD prüfen. Keine doppelte Schema-Injektion.
3. Bing Webmaster Tools (kostenlos) und Google Search Console (kostenlos) mit bestehender Domain-Property prüfen; fehlende Verifizierung nicht als vorhanden behaupten.
4. IndexNow nur aktivieren, falls nicht bereits durch RankMath/WordPress erledigt; Domain-Key prüfen, Änderungen deduplizieren und Rate-Limits respektieren. IndexNow ist **keine Google-Webseiten-Indexierung**.
5. Echte servicebezogene Landingpages und interne Links statt duplizierter Ortsseiten; Primärcluster IT-Betreuung, KI/Automatisierung, WordPress/Webdesign.
6. Referenzen mit belegten Projektleistungen und Statuskennzeichnung (live / Pilot / Konzept); keine erfundenen Erfolgszahlen.
7. Prüfen, ob Google/Bing einzelne URLs kennen; Status 'eingereicht' von 'indexiert' und 'rankt' trennen.
8. Erst nach sicherem Live-Abgleich gezielte WordPress-Änderungen, Tests und GSC/Bing-Messung.

## Offene Abnahmen
- IndexNow-Aktivierung: **nicht bestätigt**.
- Sitemap-Einreichung: **nicht bestätigt**.
- Google-/Bing-Indexierungszahlen: **nicht erhoben**.
- Änderungen an produktiver Website: **nicht erfolgt**.

## GitHub
Issue #388, Draft PR #389; bestehende Namens-/Deploy-PRs #373/#374 nicht blind mergen.
