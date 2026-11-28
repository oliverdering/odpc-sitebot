from datetime import datetime
from unittest import TestCase, main
from unittest.mock import patch
from collect import BERLIN, canonical, collect, relevant, station

class CatalogueTests(TestCase):
    def test_relevance(self):
        self.assertFalse(relevant('Kiel und seine Kinder','Blick auf ein Kino'))
        self.assertTrue(relevant('ChatGPT im Unterricht',''))
        self.assertTrue(relevant('Robotik im Labor','Forschende testen lernende Greifarme'))
        self.assertFalse(relevant('Robotik AG','Eine Zeichentrickserie'))
    def test_source(self):
        self.assertEqual(canonical('https://www.ardmediathek.de.evil.invalid/x'),'')
        self.assertEqual(canonical('https://www.zdf.de/'),'')
        self.assertEqual(station('rbtv'),'Radio Bremen')
    @patch('collect.time.sleep')
    @patch('collect.mv',side_effect=OSError)
    def test_outage(self,*unused):
        old={'items':[{'url':'https://www.arte.tv/de/videos/test','date':1,'title':'Stand','sender':'ARTE'}]}
        with self.assertRaises(RuntimeError):collect(old,datetime.now(BERLIN))
        self.assertEqual(old['items'][0]['title'],'Stand')
if __name__=='__main__':main()
