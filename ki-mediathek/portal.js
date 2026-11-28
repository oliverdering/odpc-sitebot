(() => {
  const guide = document.getElementById('odpc-ki-guide');
  if (!guide) return;
  const host = guide.querySelector('#kg-banner');
  const banner = host?.querySelector('.odpc-media');
  function placeBanner() { if (banner && !host.contains(banner)) host.append(banner); }
  if (document.readyState === 'complete') placeBanner();
  else window.addEventListener('load', placeBanner, {once:true});
  document.addEventListener('DOMContentLoaded', () => queueMicrotask(placeBanner), {once:true});

  const form = guide.querySelector('form.kg-filters');
  const pageSize = guide.querySelector('#kg-page-size');
  const showAll = guide.querySelector('.kg-show-all');
  const cards = Array.from(guide.querySelectorAll('#kg-jetzt .kg-card'));
  const otherCards = Array.from(guide.querySelectorAll('#kg-programm [data-media-kind]'));
  const normalize = value => String(value || '').toLocaleLowerCase('de').normalize('NFKC');
  const texts = new Map([...cards, ...otherCards].map(card => [card, normalize(card.textContent)]));
  let page = 1;

  function matches(card, words, fields) {
    const d = card.dataset;
    return words.every(word => texts.get(card).includes(word))
      && (!fields.get('sender') || fields.get('sender') === d.station)
      && (!fields.get('tag') || d.tags.split('|').includes(fields.get('tag')))
      && (!fields.get('kind') || fields.get('kind') === d.mediaKind)
      && (!fields.get('date') || d.date >= fields.get('date'))
      && (!fields.get('published') || d.published >= fields.get('published'));
  }

  function render() {
    const words = normalize(form.elements.q.value.trim()).split(/\s+/).filter(Boolean);
    const fields = new FormData(form);
    const matched = cards.filter(card => matches(card, words, fields));
    const perPage = pageSize.value === 'all' ? Math.max(1, matched.length) : Number(pageSize.value);
    const pages = Math.max(1, Math.ceil(matched.length / perPage));
    page = Math.min(page, pages);
    const start = (page - 1) * perPage;
    const visible = new Set(matched.slice(start, start + perPage));
    cards.forEach(card => { card.hidden = !visible.has(card); });
    otherCards.forEach(card => { card.hidden = !matches(card, words, fields); });
    guide.querySelectorAll('.kg-day').forEach(day => {
      day.hidden = !day.querySelector('[data-media-kind]:not([hidden])');
    });
    const output = form.querySelector('output');
    output.textContent = matched.length + ' verfügbare Beiträge' + (pageSize.value === 'all' ? ' · alle sichtbar' : ' · Seite ' + page + ' von ' + pages);
    showAll.hidden = pageSize.value === 'all' || matched.length <= perPage;
    showAll.textContent = 'Alle ' + matched.length + ' Beiträge anzeigen';

    guide.querySelectorAll('.kg-pagination').forEach(nav => {
      nav.replaceChildren();
      if (pages <= 1) { nav.hidden = true; return; }
      nav.hidden = false;
      const button = (label, target, disabled, current) => {
        const el = document.createElement('button');
        el.type = 'button'; el.textContent = label;
        el.disabled = disabled;
        if (current) el.setAttribute('aria-current', 'page');
        el.addEventListener('click', () => {
          page = target; render();
          guide.querySelector('#kg-jetzt').scrollIntoView({block:'start',behavior:'smooth'});
        });
        nav.append(el);
      };
      button('‹ Zurück', page - 1, page === 1, false);
      for (let i = 1; i <= pages; i++) button(String(i), i, false, i === page);
      button('Weiter ›', page + 1, page === pages, false);
    });
  }

  form.addEventListener('submit', event => event.preventDefault());
  form.addEventListener('input', () => { page = 1; render(); });
  form.addEventListener('reset', () => { page = 1; setTimeout(render, 0); });
  pageSize.addEventListener('change', () => { page = 1; render(); });
  showAll.addEventListener('click', () => { pageSize.value = 'all'; page = 1; render(); });
  form.hidden = false;
  render();
})();
