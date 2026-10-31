"""D.R.O.L. adapter registry: protocol encoding is offline and opt-in only.

No sockets, serial ports, drone connections, or real aircraft commands.
"""
from dataclasses import dataclass
from typing import Callable

from protocol_sim import FlowUfo, LsrcS1s
from lsrc_protocol import LsrcS1sFramer

@dataclass(frozen=True)
class Profile:
    id: str
    name: str
    protocol: str
    packet_bytes: int
    flight_verified: bool = False
    transport_enabled: bool = False

PROFILES = (
    Profile("flow-ufo", "FLOW-UFO / KY", "udp:7099", 21),
    Profile("lsrc-s1s", "LSRC-S1S", "udp:8800", 125),
)

class OfflineAdapterRegistry:
    """Encode packets for test inspection only, never transmit them."""
    def __init__(self):
        self._factories: dict[str, Callable[[], object]] = {
            "flow-ufo": FlowUfo,
            "lsrc-s1s": LsrcS1sFramer,
        }

    def profiles(self):
        return [p.__dict__.copy() for p in PROFILES]

    def encode_neutral_preview(self, profile_id: str):
        if profile_id not in self._factories:
            raise ValueError("unknown offline adapter")
        adapter = self._factories[profile_id]()
        if profile_id == "flow-ufo":
            packet = adapter.encode_neutral()
        else:
            packet = adapter.encode()
        expected = next(p.packet_bytes for p in PROFILES if p.id == profile_id)
        if len(packet) != expected:
            raise RuntimeError("protocol length mismatch")
        return {
            "profile": profile_id,
            "length": len(packet),
            "hex": packet.hex(),
            "preview_only": True,
            "transmitted": False,
            "flight_verified": False,
        }
