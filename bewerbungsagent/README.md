# ODPC Bewerbungsagent – Entwicklungszweig

Status: experimenteller ATS-Basiskern; **nicht produktiv**.

## Test
```bash
cd bewerbungsagent
python -m unittest discover -p 'test_*.py' -v
```

## Aktuell
- Deterministische, nachvollziehbare Schlagwort-/Phrasenprüfung.
- Gewichtete Kriterien und gesonderte Pflichtanforderungen.
- Keine KI-Inferenz, keine Dokumentextraktion, keine Gmail- oder OAuth-Verbindung.
- Keine automatischen Bewerbungen und kein Versand.

## Nächste Arbeitspakete
1. Datenmodell für Bewerbungen, Nachrichten und Interview-Sitzungen.
2. CV-/Stellenimport mit expliziten Nachweisstellen.
3. Google OAuth, Gmail/Sheets/Calendar mit minimalen Scopes und sicherem Token-Storage.
4. Cockpit und sprachbasierte Interviews mit Text-Fallback.
5. Datenschutz, End-to-End-Tests und kontrolliertes Deployment.

Kein Secret und keine echten Bewerberdaten in dieses öffentliche Repository committen.
