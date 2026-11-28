(() => {
  const guide = document.getElementById('odpc-ki-guide');
  if (!guide) return;
  const host = guide.querySelector('#kg-banner');
  const banner = host.querySelector('.odpc-media');
  // Snippet 3854 moves tickers before the footer at DOMContentLoaded. Restore
  // this same, already enhanced element, including its pause event and links.
  function placeBanner() { if (banner && !host.contains(banner)) host.append(banner); }
  if (document.readyState === 'complete') placeBanner();
  else window.addEventListener('load', placeBanner, {once:true});
  document.addEventListener('DOMContentLoaded', () => queueMicrotask(placeBanner), {once:true});
  const form = guide.querySelector('form');
  const more = guide.querySelector('.kg-more');
  const cards = Array.from(guide.querySelectorAll('[data-media-kind]'));
  const normalize = value => value.toLocaleLowerCase('de').normalize('NFKC');
  const texts = cards.map(card => normalize(card.textContent));
  let limit = 12;
  const showAll = guide.querySelector('.kg-show-all');
  function filter() {
    const q = normalize(form.elements.q.value.trim()).split(/\s+/).filter(Boolean);
    const fields = new FormData(form);
    let total = 0, available = 0;
    cards.forEach((card, i) => {
      const d = card.dataset;
      const match = q.every(word => texts[i].includes(word))
        && (!fields.get('sender') || fields.get('sender') === d.station)
        && (!fields.get('tag') || d.tags.split('|').includes(fields.get('tag')))
        && (!fields.get('kind') || fields.get('kind') === d.mediaKind)
        && (!fields.get('date') || d.date >= fields.get('date'))
        && (!fields.get('published') || d.published >= fields.get('published'));
      if (match) total++;
      if (match && d.mediaKind === 'now') available++;
      card.hidden = !match || (d.mediaKind === 'now' && available > limit);
    });
    guide.querySelectorAll('.kg-day').forEach(day => { day.hidden = !day.querySelector('[data-media-kind]:not([hidden])'); });
    more.hidden = available <= limit;
    if (showAll) { showAll.hidden = available <= limit; showAll.textContent = 'Alle ' + available + ' Beiträge anzeigen'; }
    form.querySelector('output').textContent = total ? total + ' Treffer' : 'Keine passenden Beiträge oder Sendungen.';
  }
  form.addEventListener('submit', event => event.preventDefault());
  form.addEventListener('input', () => { limit = 12; filter(); });
  form.addEventListener('reset', () => { limit = 12; setTimeout(filter, 0); });
  if (showAll) showAll.addEventListener('click', () => { limit = Infinity; filter(); guide.querySelector('#kg-jetzt').scrollIntoView({behavior:'smooth',block:'start'}); });
  more.addEventListener('click', () => {
    const previously = new Set(cards.filter(c => !c.hidden));
    limit += 12; filter();
    const first = cards.find(c => !c.hidden && !previously.has(c));
    if (first) first.querySelector('a').focus();
  });
  form.hidden = false; filter();
})();
