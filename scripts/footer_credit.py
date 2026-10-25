"""Small, idempotent transformations limited to ODPC footer credits."""
import re

PORTRAIT = 'https://odpc.de/wp-content/uploads/2026/08/1786371119667.png.webp'
VERSION = '2026-10-25'


def portrait(src=PORTRAIT):
    return (f'<img src="{src}" alt="Oliver Dering – odpc.de" width="64" height="64" '
            'loading="lazy" decoding="async" style="display:block;width:64px!important;'
            'height:64px!important;min-width:64px;max-width:64px!important;aspect-ratio:1;'
            'object-fit:cover!important;object-position:50% 12%!important;'
            'border-radius:50%!important;border:2px solid #fff;box-sizing:border-box;margin:0">')


def credit(src=PORTRAIT):
    return (f'<div class="odpc-credit" data-odpc-footer="{VERSION}" '
            'style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;'
            'grid-column:1/-1;flex-basis:100%;width:100%;margin-top:20px;'
            'padding-top:16px;border-top:1px solid rgba(128,128,128,.28);'
            'font-family:system-ui,sans-serif;text-align:left">'
            '<a href="https://odpc.de/" target="_blank" rel="noopener noreferrer" '
            'aria-label="Oliver Dering – odpc.de" style="display:block;flex:0 0 64px;'
            'width:64px;height:64px;line-height:0;border-radius:50%;overflow:hidden">'
            + portrait(src) + '</a><div style="min-width:0;flex:1 1 180px">'
            '<span style="display:block;font-size:.8rem;line-height:1.5">'
            'Webdesign, Technik &amp; KI-Integration</span>'
            '<a href="https://odpc.de/" target="_blank" rel="noopener noreferrer" '
            'style="font-size:.85rem;font-weight:600;line-height:1.5">'
            'Powered by odpc.de · Oliver Dering</a></div></div>')


def focus_existing(text):
    pattern = r'<img\b[^>]*\bsrc="' + re.escape(PORTRAIT) + r'"[^>]*>'
    updated, count = re.subn(pattern, lambda _: portrait(), text)
    if count != 1:
        raise ValueError(f'Expected exactly one existing footer portrait, found {count}')
    return updated


def grenzau(text):
    old = '<a class="credit" href="https://odpc.de/">powered by odpc.de</a>'
    if f'data-odpc-footer="{VERSION}"' in text:
        return text
    if text.count(old) != 1:
        raise ValueError('Unrecognized Grenzau footer')
    return text.replace(old, credit())


def meinkraut(text, javascript=False):
    marker = f'data-odpc-footer="{VERSION}"'
    if marker in text:
        return text
    start = text.index('function footer(){' if javascript else 'function mk_ssr_footer():')
    end = text.index('\n', start)
    part = text[start:end]
    if javascript:
        old = 'powered by <a href="https://odpc.de/">odpc.de</a>'
    else:
        old = '<small>powered by <a href="https://odpc.de/">odpc.de</a></small>'
    if part.count(old) != 1 or part.count('</footer>') != 1:
        raise ValueError('Unrecognized MeinKraut footer')
    part = part.replace(old, '').replace('</footer>', credit('/assets/oliver-dering.webp') + '</footer>')
    return text[:start] + part + text[end:]
