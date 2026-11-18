# ODPC Universal Podcast – controlled rollout

## Non-negotiable UI contract
Existing floating dock is the single source of truth. Preserve current visual geometry, language selection, Home link, WhatsApp link and all unrelated handlers. Replace ONLY the legacy full-page read-aloud Play/Stop controls. Do not add a second floating dock or a second player inside article content. No visual changes without mobile acceptance tests.

## Phase 0: current state
- PR #220 includes separate plugin and GitHub validation/package workflow.
- CI success means syntax/package only. NOT a live deployment or audio quality acceptance.
- Existing WPCode pilots #3718 and #3719 must remain OFF when universal module is tested, to avoid competing controls.
- Current plugin requires ODPC_PODCAST_GEMINI_KEY and ODPC_PODCAST_GOOGLE_TTS_KEY, securely configured server-side.
- Current API endpoint is public and needs production-grade abuse/cost protection, rate limits, response size controls and security review before activation.
- Current implementation is WordPress-only and supports published pages/posts, not static HTML subdomains.
- Current script injects an additional Stop control into the existing dock; verify geometry and stop placement on narrow phones before release.
- Current fallback when APIs are unavailable is an error; the legacy reader is not automatically restored once the new control is installed. Fix this before deployment.
- Translation language preference must be synchronized with GTranslate and the podcast service, including Italian and Portuguese where supported.

## Phase 1: isolated pilot
Target: https://odpc.de/prompt-engineering-digitale-mitarbeiter-ki-sinnvoll-nutzen
1. Enable server-side APIs with restrictive quotas; never place API keys in browser or GitHub.
2. Add opt-in/feature flag limited to pilot URL and admins/testers; maintain original reader until podcast is ready.
3. Generate cached DE/TR/EN MP3s; test semantic fidelity against article and natural pronunciation.
4. Test Play/Stop, language switching, page navigation, mobile 360/393px, accessibility, WhatsApp and Home.
5. Confirm existing language and translation functionality is unaffected.
6. Run real browser checks and record screenshots + API results as GitHub Actions artifacts.
7. Document rollback: disable podcast feature flag, restore original reader without changing dock.

## Phase 2: odpc.de
Roll out progressively: 1 article -> 5 representative pages -> all public pages/posts with sufficient editorial content. For thin/interactive pages, use curated per-page scripts instead of unreliable extraction. Cache by page revision and language. Provide manual invalidation and monitoring.

## Phase 3: ODPC network
Sites: odpc.de, ai.odpc.de, drol.odpc.de, tante-lenchen.odpc.de, meinkraut.odpc.de and other owned projects, subject to domain-specific owner authorization.
Build a central authenticated summary/audio backend at ODPC and a lightweight adapter per CMS/static site. Never assume WordPress plugins run on static sites. The adapter sends only the current site's permitted public editorial content; allowlist origins, constrain size, respect privacy and CORS. The Home link stays domain-local; WhatsApp destination can be configured per brand/site. Use site-specific branding and voice scripts. Use centralized observability, quota budgets, cache and rollback switches.

## Release gates
- CI PHP/JS syntax and package checks pass.
- Real HTTP API returns valid MP3 for DE/TR/EN and content-specific summaries for at least 2 different pages.
- Automated browser test verifies Play and Stop functionality and language choice.
- Screenshot regression verifies dock geometry and WhatsApp/Home for mobile and desktop.
- Production keys and cost controls approved.
- Feature flag can be disabled immediately without redeploying old dock.
- Only then merge and deploy.
