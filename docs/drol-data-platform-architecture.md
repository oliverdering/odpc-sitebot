# D.R.O.L. Data Platform — architecture decision record (2026-11-07)

Status: **approved design, implementation in progress; NOT a claim of live DB integration**.

## Scope and non-negotiables
- Keep existing visual design, public URLs, and SFTP deployment isolated to `site-deploy/drol.odpc.de`.
- Existing MariaDB tables: `drol_manufacturers`, `drol_sources`, `drol_hardware`, `drol_hardware_sources`; proposed V002 evidence extension.
- Migration `003_knowledge_core.sql` is additive, not executed automatically. Verify V001/V002 actual production state, DB version and backups before applying.
- Do not publish credentials in repository, browser, logs or GitHub artifacts.
- SONDE is a separate project. Only contextual crosslinks permitted.

## Architecture
Official sources -> scheduled GitHub Actions (fetch, hash, structured parse, identity resolution) -> **authenticated** IONOS PHP ingest -> MariaDB staging -> human review -> approved canonical facts -> authenticated read-only export -> validated static build -> SFTP staging/smoke test -> production.

Public pages use prebuilt HTML, JSON and search index. They never connect to MariaDB. PHP is reserved for authenticated ingest/export/admin and optional protected corrections. No public writable DB endpoint before abuse controls, privacy notice and rate limiting.

## Canonical model
- Identity: manufacturer + model + variant (SKU, regional codes and FCC ID stored as aliases; never infer interchangeability).
- Attribute registry defines datatype, canonical unit, freshness TTL and filter eligibility.
- Fact: one active value per hardware, attribute, region, qualifier and firmware context. Preserve superseded versions and original source snapshot hashes.
- Capability/compatibility claims are **versioned claims**: model, SDK line/version, firmware, controller, platform, function, polarity (YES/PARTIAL/NO/UNKNOWN), evidence E0-E3, source, checked_at, expires_at, tester and optional lab log.
- `drol_model_facts` and `drol_capability_evidence` are the current V002 starting point. Do not create a second competing public facts source. Before a final typed-facts migration, write migration adapters and assert parity.
- Staging candidates must have exact quote and source locator. AI proposals are E0 until a documented validation rule or human reviewer assigns a stronger level. Positive compatibility cannot be auto-approved.

## Release gates
1. Snapshot existing DB and current site release; verify restoration path.
2. Run SQL on a staging schema/prefixed tables first; inspect migration order, foreign keys and MariaDB compatibility.
3. Import current catalog/profile/SDK records as *unverified* seeds. Merge by manufacturer/model/variant, not fuzzy string alone.
4. Validate each source-backed value and prohibit fabricated measurements or release dates.
5. Generate all three public views from one canonical JSON export, compare with golden-master HTML and screenshots.
6. Preserve old routes and test 200/301 responses; no automatic rewrite until proven.
7. Deploy only after QA, with a saved previous release for rollback.
8. Record batch ID, Git SHA, export checksum, fact count and source freshness.

## Initial deliverables
- V003 additive migration (repository only; NOT applied to live DB).
- Canonical JSON contract and offline consistency checker.
- Importer for existing editorial seeds, followed by review queue.
- Static modelfinder and comparison generated from approved data.
- Official-release watcher (PX4, ArduPilot etc.) with source terms and rate limiting.
- DE/EN static pages with reviewed translations, hreflang, canonical and indexability thresholds.

## Important corrections to external audits
- A static browser search (MiniSearch/Pagefind) is **not** vector search; only implement embeddings if measured search quality requires it.
- A source's official status is not itself proof that an SDK function works for every controller/firmware combination.
- GitHub Actions runners should not be assumed to connect directly to a private IONOS MariaDB. Use server-side authenticated HTTPS export/ingest with replay prevention, or a validated IONOS-hosted job.
- Existing shared repository deploy workflows require isolation tests; do not move repository without a dependency audit.
- Zero additional cost and guaranteed zero downtime are not proven. Hosting quotas, Actions limits and API fees require measurement.
- A SQL file in Git is not proof of a live migration. Record the deployment and verify with a read-only query.

## Security checklist before live API
- Server-side credentials outside webroot; separate DB roles; prepared statements.
- HMAC signatures with timestamps, nonce replay store, strict JSON schema, body-size limits, TLS.
- Admin login and MFA, CSRF defenses, request throttling and error logging without secrets.
- Historical secret scan and rotation if exposed; verify restoration from backups.
- Respect robots.txt, licensing, source rights and privacy requirements.

## Acceptance criteria for DB-master cutover
- 100% of published nontrivial specifications and compatibility claims carry source, context, evidence and review date.
- Catalog, model profile and SDK matrix agree for each canonical model ID.
- No live visitor path requires DB connectivity.
- An offline build from approved export is deterministic; rollbacks tested.
- No SONDE personal project data appears as a drone manufacturer technical claim.
