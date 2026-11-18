# ODPC – Lokale SEO & KI-Optimierung (Review-Entwurf, 15.11.2026)

**Keine Produktionsdatei. Kein automatischer Deploy.** Manuelle WordPress-Korrektur hat Vorrang. Google Business Profile nicht verändern.

## Bestätigter Ist-Stand
- Startseite: sichtbarer Kopfbereich `Oliver Dering IT-Consulting` korrekt.
- Weiter unten noch `ODPC · Oliver Dering IT Consulting` ohne Bindestrich.
- IT-Betreuung, VMware, Server, Netzwerke, KI, Websites und Kundenreferenzen auf der Startseite bereits beschrieben.
- Sitemap/robots über externes Abrufwerkzeug nicht erreichbar: **nicht als Website-Fehler werten**, HTTP und GSC separat testen.

## Konkrete Metadatenvorschläge (erst nach Abgleich mit RankMath)
Startseite:
- SEO-Title: `IT-Consulting & KI-Beratung in Koblenz | Oliver Dering IT-Consulting`
- Meta-Description: `IT-Betreuung, Windows Server, VMware, Netzwerke, WordPress und KI-Automatisierung für Unternehmen in Polch, Mayen und Koblenz. Persönliche Beratung von ODPC.`
- H1: vorhandene `Oliver Dering – IT-Consulting, KI-Agenten und Digitalisierung` zunächst erhalten; keine zweite H1 ergänzen.

Leistungscluster (vorhandene URLs verwenden, keine künstlichen Ortskopien):
1. IT-Betreuung: `IT-Betreuung für Unternehmen in Koblenz & Mayen | ODPC`
2. KI-Integration: `KI-Beratung & Automatisierung für Unternehmen | ODPC`
3. Webdesign: `WordPress-Webdesign & technische SEO in Koblenz | ODPC`
4. Microsoft/VMware: `Windows Server & VMware Betreuung | ODPC`

## Strukturierte Daten: Prüfvorlage, nicht blind einfügen
Vorhandene RankMath-Entität zuerst auslesen; **nicht doppelt injizieren**. Falls nicht vorhanden, validieren:
```json
{
  "@context": "https://schema.org",
  "@type": "ProfessionalService",
  "@id": "https://odpc.de/#organization",
  "name": "Oliver Dering IT-Consulting",
  "alternateName": "ODPC",
  "url": "https://odpc.de/",
  "telephone": "+49 151 54618104",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Rüberer Str. 25",
    "postalCode": "56751",
    "addressLocality": "Polch",
    "addressRegion": "Rheinland-Pfalz",
    "addressCountry": "DE"
  },
  "areaServed": [
    {"@type":"Place","name":"Polch"},
    {"@type":"Place","name":"Mayen"},
    {"@type":"Place","name":"Koblenz"},
    {"@type":"Place","name":"Westerwald"}
  ]
}
```
Kein `aggregateRating`, `review`, `openingHours` oder `sameAs` ohne nachprüfbare Quelle.

## Content-Maßnahmen
- Auf vorhandenen Leistungsseiten pro Thema eine individuelle lokale Nutzenbeschreibung, klare Leistungspunkte, Kontakt-CTA und 2–3 passende interne Links ergänzen.
- Reale Projekte mit Tätigkeit und nachprüfbarem Ergebnis beschreiben; Kundennamen nur im bereits veröffentlichten Umfang oder mit Freigabe.
- FAQ als sichtbarer redaktioneller Text; FAQ-Schema nur, wenn tatsächlich geeignet.
- Polch/Mayen/Koblenz als tatsächliches Servicegebiet erklären, keine fiktiven Standorte oder Doorway-Pages.

## Release-Gate
1. Vorher WordPress-Export/Backup und live HTML + RankMath-Schema sichern.
2. Alle SFTP-Actions und Zielpfade vergleichen; #373 und #374 nicht blind mergen.
3. Änderungen ausschließlich an geprüften Inhalten, keine vollständigen WP-Dateien zurückspielen.
4. Schema-Validator, Canonical, Robots, Sitemap, Mobiltest und internen Linkcheck durchführen.
5. Danach GSC und Heise als Vorher/Nachher-Messung verwenden.
