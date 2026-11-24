"""Verify the deployed mobile menu and its real database monitor in Chromium."""
from playwright.sync_api import sync_playwright


def test_mission_control(page):
    page.goto("https://drol.odpc.de/mission-control.html?check=mobile-contrast", wait_until="domcontentloaded")
    page.wait_for_function("document.querySelector('#modelstatus').textContent.includes('Katalog geladen')")
    page.locator("#goal").select_option("developer")
    page.locator("#model").select_option(index=0)
    model = page.locator("#selectedmodel").inner_text()
    with page.expect_response(lambda response: "data/models-db.json" in response.url):
        page.locator("#reload").click()
    page.wait_for_function("document.querySelector('#modelstatus').textContent.includes('Katalog geladen')")
    assert page.locator("#selectedmodel").inner_text() == model
    for style, tone in (("army", "light"), ("army", "dark"), ("tech", "dark"), ("tech", "light")):
        for key, value in (("Style", style), ("Tone", tone)):
            if page.evaluate("(key) => document.documentElement.dataset['drol' + key]", key) != value:
                page.locator("#drol-global-" + key.lower()).click()
        for level, count in (("start", 1), ("advanced", 2), ("dev", 3)):
            button = page.locator(f'#levels [data-level="{level}"]')
            button.click()
            assert button.get_attribute("aria-pressed") == "true"
            steps = page.locator("#missions h3").all_text_contents()
            assert len(steps) == count
            assert all(step in page.locator("#mission-output").text_content() for step in steps)
            assert page.evaluate(r"""() => {
                const luminance = color => color.match(/\d+(?:\.\d+)?/g).slice(0, 3)
                    .map(n => +n / 255)
                    .map(n => n <= .04045 ? n / 12.92 : ((n + .055) / 1.055) ** 2.4)
                    .reduce((sum, n, i) => sum + n * [.2126, .7152, .0722][i], 0);
                return [...document.querySelectorAll('#levels button, #missions .mission, #mission-output')]
                    .every(node => {
                        const {color, backgroundColor} = getComputedStyle(node);
                        const [low, high] = [luminance(color), luminance(backgroundColor)].sort((a,b) => a-b);
                        return (high+.05)/(low+.05) >= 4.5;
                    });
            }""")
        assert page.evaluate("document.documentElement.scrollWidth <= innerWidth")
    print("PASS Mission Control contrast, model refresh, levels and export on mobile")


def main():
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome")
        page = browser.new_page(viewport={"width": 390, "height": 844}, is_mobile=True, has_touch=True)
        for path, selector in (("/wissen.html", "#drol-global-header summary"), ("/", "#menu")):
            page.goto("https://drol.odpc.de" + path, wait_until="domcontentloaded")
            page.locator(selector).click()
            page.get_by_role("link", name="Datenbankmonitor", exact=True).filter(visible=True).first.click()
            page.wait_for_url("**/#knowledge-monitor")
            page.wait_for_function("""/^\\d+$/.test(document.querySelector('[data-km=model_facts_total]')?.textContent)""")
            assert "Letzte Datenbankmessung" in page.locator("#km-status").inner_text()
            print("PASS mobile navigation, inventory and status:", path)
        test_mission_control(page)
        browser.close()


if __name__ == "__main__":
    main()
