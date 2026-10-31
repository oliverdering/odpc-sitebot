"""D.R.O.L. protocol prototype: OFFLINE ONLY. No socket or flight commands."""
from dataclasses import dataclass
from typing import Protocol
import unittest

@dataclass(frozen=True)
class Axes:
    roll: int = 128
    pitch: int = 128
    throttle: int = 128
    yaw: int = 128

    def __post_init__(self):
        if any(not isinstance(v, int) or not 0 <= v <= 255
               for v in (self.roll, self.pitch, self.throttle, self.yaw)):
            raise ValueError("axis bytes must be integers 0..255")

class Adapter(Protocol):
    def capabilities(self) -> dict: ...
    def encode_neutral(self) -> bytes: ...

class FlowUfo:
    def capabilities(self):
        return {"model": "FLOW-UFO KY Y-series", "flight_verified": False,
                "transport": "udp:7099", "telemetry": False}
    @staticmethod
    def encode(axes=Axes(), command=0, speed=2):
        if not 0 <= command <= 255 or not 0 <= speed <= 255:
            raise ValueError("invalid command/speed")
        packet = bytearray([3, 0x66, 0x14, axes.roll, axes.pitch,
                            axes.throttle, axes.yaw, command, speed] + [0]*10)
        checksum = 0
        for byte in packet[3:19]:
            checksum ^= byte
        packet.extend((checksum, 0x99))
        return bytes(packet)
    def encode_neutral(self):
        return self.encode()

class LsrcS1s:
    """Implements the 20-byte inner control frame ONLY, not full UDP wrapper."""
    def capabilities(self):
        return {"model": "LSRC-S1S", "flight_verified": False,
                "transport": "udp:8800 (wrapper not implemented)", "telemetry": False}
    @staticmethod
    def encode_inner(axes=Axes(), command=0, headless=2):
        if not 0 <= command <= 255 or headless not in (2, 3):
            raise ValueError("invalid command/headless")
        packet = bytearray([0x66, 0x14, axes.roll, axes.pitch,
                            axes.throttle, axes.yaw, command, headless] + [0]*10)
        checksum = 0
        for byte in packet[2:8]:
            checksum ^= byte
        packet.extend((checksum, 0x99))
        return bytes(packet)
    def encode_neutral(self):
        return self.encode_inner()

class Simulator:
    def __init__(self):
        self.history = []
        self.connected = False
    def capabilities(self):
        return {"model": "simulator", "flight_verified": False, "telemetry": False}
    def connect(self):
        self.connected = True
    def disconnect(self):
        self.connected = False
    def record(self, name):
        if not self.connected:
            raise RuntimeError("simulator not connected")
        self.history.append(name)

class ProtocolTests(unittest.TestCase):
    def test_flow_format(self):
        p = FlowUfo().encode_neutral()
        self.assertEqual(len(p), 21)
        self.assertEqual(p[:3], bytes([3, 0x66, 0x14]))
        self.assertEqual(p[-1], 0x99)
        self.assertEqual(p[-2], 2)
    def test_lsrc_inner_format(self):
        p = LsrcS1s().encode_neutral()
        self.assertEqual(len(p), 20)
        self.assertEqual(p[:2], bytes([0x66, 0x14]))
        self.assertEqual(p[-2:], bytes([2, 0x99]))
    def test_reject_invalid_axes(self):
        with self.assertRaises(ValueError):
            Axes(roll=300)
    def test_simulation(self):
        s = Simulator()
        with self.assertRaises(RuntimeError):
            s.record("takeoff")
        s.connect()
        s.record("neutral")
        self.assertEqual(s.history, ["neutral"])
        s.disconnect()

if __name__ == "__main__":
    unittest.main()
