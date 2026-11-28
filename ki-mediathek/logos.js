(() => {
  function decorate() {
    const logos = window.odpcMediaStationLogos || {};
    for (const badge of document.querySelectorAll('.odpc-media-track a b')) {
      const url = logos[badge.textContent.trim()];
      if (!url || badge.querySelector('img')) continue;
      const img = document.createElement('img');
      img.alt = '';
      img.src = url;
      img.decoding = 'async';
      img.width = 64;
      img.height = 22;
      img.style.cssText = 'width:64px;height:22px;object-fit:contain;background:white;border-radius:2px;padding:2px 4px;margin-inline-end:6px;vertical-align:middle';
      badge.prepend(img);
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', decorate, { once:true });
  else decorate();
})();
