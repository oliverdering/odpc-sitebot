# ODPC GitHub Arbeits-Pipeline – priorisiert am 15.11.2026

## Gate 0 – Produktionsschutz (P0, blockierend)
- #372: alle SFTP-Deploy-Workflows, Zielpfade, SHA und Backups prüfen; Schutz der bereits manuell in WordPress korrigierten Firmenbezeichnung `Oliver Dering IT-Consulting`.
- PR #373 nur nach vollständiger Pfad-/Concurrency-Prüfung mergen.
- PR #374 **nicht unverändert mergen** (alte Schreibweise ohne Bindestrich).
- Bis dahin: keine automatischen Produktiv-Deployments aus neuen SEO-Branches.

## Gate 1 – SEO/Indexing Hub (P1)
- #388 / Draft PR #389: read-only Indexierungs-Audit und wöchentlicher GitHub-Action-Workflow.
- Der Workflow auf dem PR-Branch kann durch `pull_request` geprüft werden; `schedule` wird erst auf dem Default-Branch aktiv.
- Nach positivem CI und P0-Freigabe: nur isolierte Audit-Workflow-Dateien übernehmen; kein WordPress-Overwrite.
- Live-Audit-Berichte prüfen und RankMath/IndexNow-Duplikate ausschließen.
- Danach Google Search Console/Bing Webmaster, Sitemap und indexierte URLs tatsächlich validieren. Keine kostenpflichtigen Dienste.

## Gate 2 – Content / KI-Sichtbarkeit (P2)
- #375: About-Me/Kennenlernen auf Kernangebote und nachvollziehbare Referenzen ausrichten.
- PR #389: Metadaten, LocalBusiness-Entität und regionale Leistungscluster sind derzeit nur Review-Vorlagen, nicht live.
- Keine fiktiven Standorte, Bewertungen, Referenzen oder Indexierungszusagen.

## Separate Workstreams, nicht mit Website-Deploy verketten
- PR #371: KI-Orchester Resource Monitor; eigener Review-/Testpfad.
- PR #297: Teamkommunikation/Google Sheets; kein SEO-Blocker.
- PR #271 / #220: Voice/Mehrsprachigkeit; auf Kompatibilität prüfen.
- PR #206 / #66: AdminMonkey; isoliert halten.
- PR #65: D.R.O.L.-Hardware; isoliert halten.
- Viele D.R.O.L.-Review-Issues sind wiederkehrende Versionsmeldungen; **nicht massenhaft automatisch schließen**. Erst deduplizieren, neue Versionen und offene Reviews verifizieren.

## Automationsregel
- Automatisches **Prüfen, Testen und Protokollieren** ist zulässig.
- Automatisches **Mergen, Live-Deployen, kostenpflichtige APIs aktivieren oder Kundendaten ändern** bleibt gesperrt, bis entsprechende Schutzmechanismen und konkrete Freigaben nachweislich vorliegen.
- Ein GitHub-Issue/PR alleine führt keine Aufgaben automatisch aus. Der SEO-Audit ist als echter Workflow vorbereitet; laufende Ausführung erst nach Aktivierung/CI nachgewiesen.
