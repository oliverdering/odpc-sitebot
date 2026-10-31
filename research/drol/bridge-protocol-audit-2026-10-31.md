# D.R.O.L. Bridge – Protocol Audit and Adapter Specification (2026-10-31)

Status: research / design only. No flight hardware tested. No production deployment.

## Primary sources inspected
- https://github.com/hakimjanov/pyDroneWire (README.md, drone_controller.py lines 1–230)
- https://github.com/FahrulRPutra/reversing-wifi-uav (README.md, src/teleop.py)

## Findings
**FLOW-UFO / KY UFO**: pyDroneWire's `build_ctrl` creates a 21-byte datagram: `03 66 14 R P T Y CMD SPEED [ten 00] XOR 99`; XOR covers indexes 3 through 18 inclusive. Destination UDP/7099, 20 Hz, default address 192.168.1.1. RTSP camera is described at port 7070. Source implementation `send_heartbeat()` sends `01 01`, while README describes a longer heartbeat frame. Treat this discrepancy as unresolved until capture comparison. Takeoff/land is a toggle in the project's documented command set, not two independent commands. Optical-flow correction is an experimental software loop; it does not establish GPS positioning or flight safety.

**LSRC-S1S / WIFI UAV**: documented 20-byte inner command beginning 0x66 and ending 0x99, UDP/8800, axes centered around 0x80, takeoff=0x01, emergency=0x02, land=0x03, gyro calibration=0x04. Documentation mentions a separate packet sequence/wrapper; inspect `src/lib/drone.py` before implementing an encoder. `src/teleop.py` sends at ~10 Hz, not 20 Hz. Its keyboard code has potential key-release/stale-vector issues and an unbounded background thread; do not reuse directly for flight.

## API v0 – interface proposal
```python
from typing import Protocol
class DroneAdapter(Protocol):
    def capabilities(self) -> dict: ...
    def connect(self) -> None: ...
    def disconnect(self) -> None: ...
    def status(self) -> dict: ...
    def set_manual_axes(self, roll: float, pitch: float, throttle: float, yaw: float) -> None: ...
    def takeoff(self) -> None: ...
    def land(self) -> None: ...
```
Axis inputs normalized to [-1,1] with model-specific neutral and mapping. A capability is exposed only after verification for a specific hardware/firmware revision. Never expose a generic emergency motor-cut command as a normal flight control. `status()` must distinguish **observed telemetry**, **commanded state**, and **estimated state**; absence of ACK or telemetry is not proof of a healthy link.

## Driver separation
- `flow_ufo`: independent 21-byte packet encoder, 20-Hz scheduling, RTSP video adapter.
- `lsrc_s1s`: independent 20-byte command encoder plus verified outer transport wrapper, separate video receiver.
- `simulator`: deterministic dry-run adapter; default, no network packets.
- `potensic_atom`: unsupported for control; log-parser integration is a different capability.

## Safety gates
1. Simulator and golden-packet tests before enabling any UDP transport.
2. Hardware variant fingerprinting and explicit operator selection; never autodetect by SSID alone.
3. Manual enable required for motor-affecting commands; no autonomous takeoff by default.
4. Watchdog policies are **per-device**, not universal 500ms/2000ms. A neutral stick value does not guarantee hover (especially throttle).
5. Connection loss must not be treated as proof that a land command was received.
6. Physical testing: props removed first; safe test enclosure and independent pilot/kill plan before any flight.
7. No firmware flashing, signature bypass, radio power modifications or production website changes in this phase.

## Evidence and licensing
The README pages and Python source establish implementation claims, **not independent flight validation**. No license is assumed: inspect LICENSE files and per-file notices before copying any code. GPL separation by process alone does not automatically determine derivative-work status; obtain appropriate legal review for commercial distribution.

## Acceptance criteria for PoC 1
- Both protocol encoders have independent unit tests against known captured packets.
- Simulated state transitions and loss-of-link tests pass.
- All features labeled supported/unsupported/experimental per exact device revision.
- Video is optional; telemetry never fabricated.
- Hardware testing is explicitly authorized after reviewing a bench checklist.

## Next inspections
1. Fetch LSRC `src/lib/drone.py` and capture packet framing.
2. Compare FLOW-UFO heartbeat implementation to source capture.
3. Check LICENSE files in both repositories.
4. Build simulator and encoder unit tests in an isolated branch.
