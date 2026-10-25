from pathlib import Path
import unittest
from footer_credit import credit, focus_existing, grenzau, meinkraut


class FooterChanges(unittest.TestCase):
    def test_existing_changes_only_portrait(self):
        source = Path('site-deploy/tante-lenchen/footer.php').read_text()
        result = focus_existing(source)
        self.assertEqual(focus_existing(result), result)
        self.assertIn('object-position:50% 12%', result)
        self.assertEqual(source.split('<img')[0], result.split('<img')[0])

    def test_grenzau_preserves_content(self):
        source = Path('site-deploy/bahnhof-grenzau/index.html').read_text()
        result = grenzau(source)
        self.assertEqual(grenzau(result), result)
        self.assertEqual(source.split('<footer>')[0], result.split('<footer>')[0])
        self.assertEqual(result.count('data-odpc-footer='), 1)

    def test_meinkraut_both_renderers_and_local_image(self):
        for path, js in [('site-deploy/meinkraut/meinkraut.js', True), ('site-deploy/meinkraut/server/public.php', False)]:
            source = Path(path).read_text()
            result = meinkraut(source, js)
            self.assertEqual(meinkraut(result, js), result)
            self.assertEqual(result.count('data-odpc-footer='), 1)
            self.assertIn('src="/assets/oliver-dering.webp"', result)
            start = 'function footer(){' if js else 'function mk_ssr_footer():'
            self.assertEqual(source.split(start)[0], result.split(start)[0])
            self.assertEqual(source.split(start)[1].split('\n', 1)[1], result.split(start)[1].split('\n', 1)[1])

    def test_unknown_markup_is_refused(self):
        for transform in (focus_existing, grenzau, meinkraut):
            with self.assertRaises(ValueError):
                transform('<footer>unrelated website</footer>')


if __name__ == '__main__':
    unittest.main()
