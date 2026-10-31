"""Offline LSRC-S1S framing reference; no network or flight control."""
from dataclasses import dataclass

@dataclass(frozen=True)
class Axes:
    roll: int = 128
    pitch: int = 128
    throttle: int = 128
    yaw: int = 128
    def __post_init__(self):
        for value in (self.roll, self.pitch, self.throttle, self.yaw):
            if type(value) is not int or not 0 <= value <= 255:
                raise ValueError('axes must be integer bytes')

class LsrcS1sFramer:
    """Framing based on FahrulRPutra/reversing-wifi-uav src/lib/drone.py.

    Offline byte generation only; no E58 cross-model compatibility claim.
    """
    HEADER = bytes.fromhex('ef 02 7c 00 02 02 00 01 02 00 00 00')
    COUNTER1_SUFFIX = bytes.fromhex('00 00 14 00 66 14')
    CONTROL_SUFFIX = bytes(10)
    CHECKSUM_SUFFIX = bytes(46) + bytes.fromhex('32 4b 14 2d 00 00')
    COUNTER2_SUFFIX = bytes.fromhex('00 00 00 00 00 00 01 00 00 00 14 00 00 00 ff ff ff ff')
    COUNTER3_SUFFIX = bytes.fromhex('00 00 00 00 00 00 03 00 00 00 10 00 00 00')

    def __init__(self):
        self.c1 = 0
        self.c2 = 1
        self.c3 = 2

    def encode(self, axes=Axes(), command=0, headless=2):
        if type(command) is not int or not 0 <= command <= 255 or headless not in (2, 3):
            raise ValueError('invalid command or headless')
        controls = bytes((axes.roll, axes.pitch, axes.throttle, axes.yaw, command, headless))
        checksum = 0
        for v in controls:
            checksum ^= v
        packet = (self.HEADER + self.c1.to_bytes(2, 'little') + self.COUNTER1_SUFFIX
                  + controls + self.CONTROL_SUFFIX + bytes((checksum,))
                  + self.CHECKSUM_SUFFIX + self.c2.to_bytes(2, 'little')
                  + self.COUNTER2_SUFFIX + self.c3.to_bytes(2, 'little')
                  + self.COUNTER3_SUFFIX)
        self.c1 = (self.c1 + 1) & 0xffff
        self.c2 = (self.c2 + 1) & 0xffff
        # Upstream counter3 is not incremented in counter().
        return packet
