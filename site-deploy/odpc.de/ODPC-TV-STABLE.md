# ODPC TV – Stable baseline

**Frozen version:** 0.9.8
**Baseline commit:** `6a13d493e7202f332a791a69edb2474d9fd7aa99`
**Immutable recovery branch (do not commit here):** `release/odpc-tv-v0.9.8-stable`
**Production source:** `site-deploy/odpc.de/odpc-tv-lab.php`

## Rules for future changes

1. Preserve existing 15 channels, their URLs, order, logos, and playback behavior.
2. Only append new channels after independently validating HLS playlist and media segments. Do not claim browser playback is proven by HTTP checks alone.
3. Never modify player UI, existing logo rendering, CSS, or playback logic for channel additions.
4. Parse and validate the entire PHP `$channels` JSON before committing. Reject invalid JSON and duplicate channel names.
5. Test new candidates on a separate branch first; deploy only after explicit validation and rollback readiness.
6. Roll back by restoring the stable baseline commit or branch if regressions occur.

The stable branch preserves source code, not an external server snapshot.
