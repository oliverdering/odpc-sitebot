"""Check host boundaries, legacy paths and preservation of existing server rules."""
import re
import unittest
from urllib.parse import urlsplit
from consolidate_customer_domains import PAIRS, updated_config, MARKER, References


def redirect(config, url):
    parsed = urlsplit(url)
    conditions = []
    for line in config.decode().splitlines():
        if line.startswith('RewriteCond '):
            _, variable, pattern, *_ = line.split()
            value = parsed.netloc if variable == '%{HTTP_HOST}' else parsed.path
            negated = pattern.startswith('!')
            matches = bool(re.search(pattern[1:] if negated else pattern, value, re.I))
            conditions.append(not matches if negated else matches)
        elif line.startswith('RewriteRule '):
            _, pattern, destination, *_ = line.split()
            match = re.search(pattern, parsed.path.lstrip('/'))
            if all(conditions) and match:
                result = destination
                for index, value in enumerate(match.groups(), 1):
                    result = result.replace('$' + str(index), value or '')
                if parsed.query and '?' not in result:
                    result += '?' + parsed.query
                return result
            conditions = []
    return None


class ConsolidationTests(unittest.TestCase):
    def test_dependency_scanner_distinguishes_css_urls_from_javascript_url_constructor(self):
        parser = References('https://atrium191.de/wohnungen/')
        parser.feed('<style>.cover{background:url(/assets/cover.webp)}</style><script>new URL(r)</script><img src="/plan.jpg">')
        self.assertEqual(parser.assets, {'https://atrium191.de/assets/cover.webp','https://atrium191.de/plan.jpg'})

    def test_atrium_legacy_paths_and_queries(self):
        config = updated_config(b'# original\n', 'atrium191-rb.odpc.de')
        self.assertEqual(redirect(config, 'https://atrium191-rb.odpc.de/'), 'https://atrium191.de/')
        self.assertEqual(redirect(config, 'https://atrium191-rb.odpc.de/index.php/wohnungen/?utm_source=old'), 'https://atrium191.de/wohnungen/?utm_source=old')
        self.assertEqual(redirect(config, 'https://atrium191-rb.odpc.de/sitemap.xml'), 'https://atrium191.de/sitemap.xml')

    def test_bahnhof_legal_destinations_are_real_wordpress_pages(self):
        config = updated_config(b'', 'bahnhof-grenzau.odpc.de')
        self.assertEqual(redirect(config, 'https://bahnhof-grenzau.odpc.de/impressum.html'), 'https://bahnhof-grenzau.de/?page_id=7')
        self.assertEqual(redirect(config, 'https://bahnhof-grenzau.odpc.de/datenschutz.html?old=1'), 'https://bahnhof-grenzau.de/?page_id=8')
        self.assertEqual(redirect(config, 'https://bahnhof-grenzau.odpc.de/index.html'), 'https://bahnhof-grenzau.de/')

    def test_production_and_other_hosts_never_redirect(self):
        for old, new in PAIRS.items():
            config = updated_config(b'', old)
            for host in (new, 'odpc.de', 'other.' + old):
                self.assertIsNone(redirect(config, 'https://' + host + '/'))

    def test_assets_and_administration_keep_original_handling(self):
        for host in PAIRS:
            config = updated_config(b'', host)
            for path in ('/assets/photo.webp', '/wp-content/uploads/plan.pdf', '/theme/site.css', '/wp-json/wp/v2/pages', '/wp-admin/', '/wp-login.php'):
                self.assertIsNone(redirect(config, 'https://' + host + path))

    def test_existing_configuration_remains_exact_and_reapplication_is_idempotent(self):
        original = b'# WordPress rules\nRewriteRule . /index.php [L]\n<Files secret.php>\nRequire all denied\n</Files>\n'
        once = updated_config(original, 'atrium191-rb.odpc.de')
        self.assertTrue(once.endswith(original))
        self.assertEqual(updated_config(once, 'atrium191-rb.odpc.de'), once)
        self.assertEqual(once.count(MARKER.encode()), 1)


if __name__ == '__main__':
    unittest.main()
