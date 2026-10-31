# D.R.O.L. Local Control Lab

This is an **offline-only prototype**. No drone transport is implemented and no physical aircraft is controlled.

## Start

From repository root:

```bash
python research/drol/local_dashboard.py
```

Open `http://127.0.0.1:8765` in the browser on the **same computer**.

Python 3.11+ recommended. The web dashboard uses only the standard library. It imports `bridge_core.py` and operates on an in-memory state machine.

## Offline test suite

```bash
python -m unittest discover -s research/drol -p 'test_*.py' -v
```

## Scope and limitations

- Four displayed profiles: simulator, FLOW-UFO, LSRC-S1S, Potensic Atom.
- Only the simulator supports UI actions; no UDP, radio, USB or serial transmission.
- Simulated takeoff and landing are **not** claims about any real aircraft.
- The 30-second heartbeat timeout is a dashboard demonstration setting, **not** a flight failsafe.
- Event history exists only in process memory.
- Do not expose the dashboard via public reverse proxies, tunnels, port forwarding, or bind to 0.0.0.0.
- This prototype has no authentication. It is suitable only for a trusted local development machine.
- Before hardware integration: validate source packet captures, establish model-specific capabilities and safety interlocks, add authorization and integration tests.

## Next engineering gates

1. Execute CI and confirm all tests green (GitHub Actions workflow already exists).
2. Add HTTP API tests for valid and invalid transitions.
3. Compare LSRC wrapper against a recorded packet, including sequence handling.
4. Introduce a separately reviewed, disabled-by-default hardware transport interface.
