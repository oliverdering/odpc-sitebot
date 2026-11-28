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

  // Homepage-only ODPC visual alignment. Never alter the portal page or ticker behavior.
  function alignHomepageMediaDesign() {
    if (!document.body.classList.contains('home')) return;
    const track = document.querySelector('.odpc-media-track');
    if (!track || document.getElementById('odpc-media-home-branding')) return;
    const css = document.createElement('style');
    css.id = 'odpc-media-home-branding';
    css.textContent = \`
      body.home .odpc-media-track {background:#10263c!important;color:#f3eadc!important;border-color:#b99b72!important;}
      body.home .odpc-media-track a {background:#18334c!important;color:#f7f0e6!important;border:1px solid rgba(195,163,119,.35)!important;border-radius:12px!important;box-shadow:0 5px 18px rgba(0,0,0,.15)!important;}
      body.home .odpc-media-track a:hover,body.home .odpc-media-track a:focus-visible {background:#24435c!important;border-color:#c6a67c!important;}
      body.home .odpc-media-track a b {color:#e6c79e!important;}
      body.home .odpc-media-track a img {background:#fff!important;}
      @media(max-width:600px){body.home .odpc-media-track a{border-radius:9px!important;}}
    \`;
    document.head.appendChild(css);
    // Scope the surrounding media panel, not unrelated homepage sections.
    const wrapper = track.parentElement;
    if (wrapper) {
      wrapper.style.setProperty('background-color','#10263c','important');
      wrapper.style.setProperty('color','#f3eadc','important');
      wrapper.style.setProperty('border-color','#b99b72','important');
    }
  }
  function initialize() { decorate(); alignHomepageMediaDesign(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once:true });
  else initialize();
})();
