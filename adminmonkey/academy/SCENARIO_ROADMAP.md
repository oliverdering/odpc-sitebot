# AdminMonkey Academy – Szenarienausbau

Stand: 2026-11-11

## Ziel
Der Übungsszenario-Bestand wächst von 8 auf 21 Aufgaben für MS-102, AB-650, AZ-104, SC-300 und MD-102. Das sind **Übungsfälle**, keine offiziellen Microsoft-Prüfungsfragen, kein Nachweis bestandener Prüfungen und keine gemessene Agentenkompetenz.

## Bewertung
`evaluate_responses.py` prüft lediglich, ob erwartete Schlüsselbegriffe vorkommen und offensichtliche gefährliche Formulierungen fehlen. Dieses Screening ist kein semantisches oder unabhängiges Fachgutachten. Leere Antworten und leere Rubriken bestehen nicht.

## Evidenz-Gate
`certification_competency.py` verlangt gesonderte `independent_agent_assessment`-Ergebnisse mit Szenario-Domänen-Zuordnung, Grader und HTTPS-Nachweis. Ohne diese bleiben Kompetenzwerte `null` und Status `insufficient_evidence`. Die hinzugefügten MS-102- und AB-650-Domänenzuordnungen sind interne Übungszuordnungen, keine Garantie vollständiger Microsoft-Lernzielabdeckung.

## Nächste Stufe
1. Agentenantworten in einer separaten, versionierten Datei erfassen, ohne sie zu fingieren.
2. Fachlich unabhängige Bewertung mit Prüferkennung, Evidenzlink, Zeitstempel und Sicherheitsbewertung implementieren.
3. Erst verifizierte Ergebnisse im Monitor als Kompetenzindikatoren anzeigen, getrennt von MariaDB-Dokumentzahlen.
4. Sicherheitskritische Administrationsaktionen weiterhin nur in Testumgebungen bzw. nach Freigabe ausführen.
