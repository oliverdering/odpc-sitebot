# AdminMonkey Microsoft Learn FastTrack — API decision (2026-11-09)

Status: P0 technical decision; no production database migration authorized.

## Verified Microsoft documentation
- Legacy Catalog API: https://learn.microsoft.com/en-us/training/support/catalog-api
- Replacement Platform API: https://learn.microsoft.com/en-us/training/support/integrations-learn-platform-api-catalog

Microsoft documents that the legacy unauthenticated Catalog API was supported through June 2026, with deprecation planned. As of November 2026 it MUST NOT be assumed available. The replacement Platform API requires authentication **and application onboarding**. It provides catalog metadata for learning paths, modules, units, certifications, exams, Applied Skills, and instructor-led courses. It does **not** provide documentation, code samples, shows, or events.

## Implementation requirements
1. Probe catalog availability in a read-only diagnostic step; log HTTP status and schema keys, never access tokens or full response.
2. Prefer authenticated Platform API once onboarding and securely scoped credentials are verified. No credential guessing.
3. If legacy endpoint still responds, treat as temporary compatibility source, not durable primary.
4. Persist catalog discovery state and metadata separately from article text; preserve existing amk_sources/amk_documents/amk_evaluations tables.
5. Never mark downloaded content as verified or claim certification.
6. Log: endpoint mode, response status, catalog item counts by type, timestamp, import totals, error class.
7. Avoid bulk copying Microsoft course content without verifying applicable licensing and API terms.
8. Do not invent 500-module throughput or 80% reduction; benchmark actual runs.

## Review of Claire / Gemini report
- Valuable: catalog-first discovery, separate metadata from content and measured competence.
- Unverified: blanket licensing permission, 80% fewer requests, 500 modules without timeout, asserted existing amk_modules/amk_units tables, 100+ sources (only a sample delivered).
- Engineering owner: Claude; source owner: Claire; integrator: Oxana.
- P0 tracking: issues #83, #84; P1 dashboard #85.

## Acceptance
A successful GitHub Actions run must publish a valid, non-synthetic snapshot, report catalog source mode and counts, and preserve prior progress across runner restarts. Database import success requires verified logs and real row counts.
