import unittest
from lsrc_protocol import Axes, LsrcS1sFramer

class TestLsrc(unittest.TestCase):
    def test_reference_neutral(self):
        packet = LsrcS1sFramer().encode()
        self.assertEqual(len(packet), 125)
        self.assertEqual(packet[:12].hex(), 'ef027c000202000102000000')
        self.assertEqual(packet[18:20], bytes.fromhex('6614'))
        self.assertEqual(packet[20:26], bytes.fromhex('808080800002'))
        self.assertEqual(packet[36], 2)

    def test_sequence(self):
        f = LsrcS1sFramer()
        a, b = f.encode(), f.encode()
        self.assertEqual(a[12:14], bytes((0, 0)))
        self.assertEqual(b[12:14], bytes((1, 0)))
        self.assertNotEqual(a, b)

    def test_axes_validation(self):
        for v in (-1, 256, 1.5, True):
            with self.assertRaises(ValueError):
                Axes(roll=v)

    def test_checksum(self):
        p = LsrcS1sFramer().encode(Axes(roll=130), command=1)
        check = 0
        for b in p[20:26]:
            check ^= b
        self.assertEqual(p[36], check)

if __name__ == '__main__':
    unittest.main()
