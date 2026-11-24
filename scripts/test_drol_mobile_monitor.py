"""Verify the deployed mobile menu and its real database monitor in Chromium."""
from playwright.sync_api import sync_playwright


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
        browser.close()


if __name__ == "__main__":
    main()
