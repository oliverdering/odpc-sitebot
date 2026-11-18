# AdminMonkey – Microsoft Learn Evidence Pilot (zero-cost)

Status: proposal only. No production deployment, no credentials, no paid services.

## Goal
Separate source collection from demonstrated competence. Microsoft Learn is the canonical catalog for training metadata; Microsoft Ignite is supplementary and never the sole evidence for a technical claim.

## Confirmed baseline (2026-11-11 07:49:39 UTC)
Source: `adminmonkey/knowledge/academy_progress.json` on main.
- 1,500 collected and internally approved source documents
- 1,495 retrieval-staged documents / 7,709 prepared chunks
- 18 practice scenarios across MS-102 (6), AZ-104 (4), SC-300 (4), MD-102 (4)
- 0 independently graded agent responses; 0 verified passes
- Retrieval quality unverified; no searchable production index evidenced

These are source counts, NOT learning-objective coverage or official certifications.

## Microsoft Learn catalog integration
Official docs: https://learn.microsoft.com/en-us/training/support/integrations-learn-platform-api-catalog
Developer reference: https://learn.microsoft.com/en-us/training/support/integrations-learn-platform-api-catalog-endpoints-developer-reference

- New Platform API is free to use, but requires Microsoft Entra authentication and application onboarding. An existing personal Microsoft login does NOT imply API access.
- Catalog metadata only: modules, units, learning paths, applied skills, certifications, exams, instructor-led courses. It does NOT supply general documentation, code examples, events or Ignite session media.
- Do not invoke paid Azure resources, enable trials, store tokens in Git, or scrape attendee-only sessions.
- Before onboarding, use official public Learn pages as link-only source references; do not claim API connectivity.
- Respect Microsoft terms, content licenses, attribution and rate limits.

## Pilot acceptance gates
1. Connectivity: document API onboarding prerequisites; if not onboarded, report `blocked_on_onboarding` instead of silently falling back.
2. Provenance: each source record has canonical URL, Microsoft publisher, retrieval timestamp, source type, and content/version fingerprint where permitted.
3. Source change detection: report new/changed/retired catalog records, not just a growing total.
4. Competency: run at least 20 scenario evaluations with frozen prompts, evidence links, independent grader, rubric, pass/fail and regression results; no self-scored passes.
5. Security: read-only; no Graph write scopes, tenant access, personal tokens, secret logging or deployment.
6. Costs: zero additional charges. Abort and request explicit approval before any billing, trial, subscription, paid lab or certification exam.
7. Reporting: distinguish scheduled cycle, successful ingestion, indexed knowledge, independently verified answers and official credentials.

## Initial engineering tasks
- Inspect existing academy collector, monitor, assessment tests and workflow schedules before code changes.
- Add a **dry-run** catalog adapter contract and unit tests using local fixtures only.
- Make unsupported authentication and HTTP failures explicit, with no fake success.
- Design a versioned `source_evidence` record and an `assessment_evidence` record; no migrations until reviewed.
- Verify a 20-scenario test suite on CI before proposing any merge.

## Hard safety gate
PR #66 is an isolated draft prototype and is not authorized for merge or production deployment. All new code must be reviewed and CI-validated separately.
