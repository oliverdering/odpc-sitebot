/* YourBusiness public-data research UI. Loaded after the base shortcode. */
(() => {
  const start = () => {
    const root = document.getElementById('yb-demo');
    if (!root || !window.YourBusinessResearch) return;
    const el = id => root.querySelector('#yb-' + id);
    const editor = root.querySelector('.yb-editor');
    const name = el('name'), city = el('city');
    if (!name || !city || !editor) return;
    const css = document.createElement('style');
    css.textContent = '.yb-research-action{width:100%;background:#067c60!important;color:#fff!important}.yb-research-info{font-size:12px;color:#526179}.yb-research-source{background:#fff;border:1px solid #d7deea;border-radius:12px;margin:12px 0;padding:12px;font-size:13px}.yb-research-source a{overflow-wrap:anywhere}.yb-research-source ul{padding-left:20px}.yb-research-warning{color:#875400}.yb-research-results button{display:block;width:100%;text-align:left}.yb-research-advanced{padding:12px;background:#fff;border:1px solid #d7deea;border-radius:12px;margin:12px 0}.yb-research-advanced summary{font-weight:700;cursor:pointer}';
    document.head.appendChild(css);

    // Place the two-field search above the existing detailed customer form.
    const button = document.createElement('button');
    button.type = 'button'; button.className = 'yb-research-action';
    button.textContent = 'Firmendaten automatisch recherchieren';
    const note = document.createElement('p'); note.className = 'yb-research-info';
    note.textContent = 'Nur Firmenname und Ort werden für die öffentliche Recherche übermittelt. Keine kostenpflichtige Google-API. Kontaktdaten vor Veröffentlichung prüfen.';
    const results = document.createElement('div'); results.className = 'yb-research-results';
    const sources = document.createElement('div');
    const locationButton = el('location');
    editor.insertBefore(button, locationButton);
    editor.insertBefore(note, locationButton);
    editor.insertBefore(results, locationButton);
    const advanced = document.createElement('details');
    advanced.className = 'yb-research-advanced';
    const summary = document.createElement('summary');
    summary.textContent = 'Weitere Angaben, Fotos, Logo und Pflichtangaben prüfen';
    advanced.appendChild(summary);
    editor.insertBefore(advanced, locationButton);
    const sectorLabel = el('sector')?.closest('label');
    if (sectorLabel) advanced.appendChild(sectorLabel);


    // Keep every pre-existing field/event handler. Move only the detailed controls.
    const consent = el('consent').closest('label');
    let moving = locationButton;
    while (moving && moving !== consent) {
      const next = moving.nextSibling;
      advanced.appendChild(moving);
      moving = next;
    }
    const field = (id, caption, rows) => {
      const label = document.createElement('label');
      label.textContent = caption;
      const input = rows ? document.createElement('textarea') : document.createElement('input');
      input.id = 'yb-' + id;
      if (rows) input.rows = rows;
      else { input.type = 'text'; input.maxLength = 200; }
      label.appendChild(input);
      advanced.appendChild(label);
      return input;
    };
    field('address', 'Adresse (aus öffentlicher Recherche, überprüfen)');
    field('hours', 'Öffnungszeiten (noch nicht vom Kunden bestätigt)', 2);
    field('imprint', 'Betreiber / Impressum (nur bestätigte Kundendaten)', 3);
    const menuIntro = document.createElement('p');
    menuIntro.className = 'yb-research-info';
    menuIntro.textContent = 'Getränkekarte: Die Recherche kann einen Entwurf vorbereiten. Nur tatsächlich bestätigte Preise eintragen.';
    advanced.appendChild(menuIntro);
    const menuLabel = document.createElement('label');
    menuLabel.textContent = 'Getränkekarte bearbeiten: # Kategorie oder Getränk | Menge | bestätigter Preis';
    const menuInput = document.createElement('textarea');
    menuInput.id = 'yb-menu-draft'; menuInput.rows = 11;
    menuInput.placeholder = '# Bier\nBitburger Pils | 0,3 l | \n# Alkoholfrei\nMineralwasser | 0,3 l | ';
    menuLabel.appendChild(menuInput);
    advanced.appendChild(menuLabel);
    const demoPriceBox = document.createElement('details');
    const demoPriceTitle = document.createElement('summary');
    demoPriceTitle.textContent = 'Unverbindliche Preisbeispiele aus dem Dossier ansehen';
    demoPriceBox.appendChild(demoPriceTitle);
    const demoPriceContent = document.createElement('div');
    demoPriceContent.className = 'yb-research-info';
    demoPriceBox.appendChild(demoPriceContent);
    advanced.appendChild(demoPriceBox);
    const legalBox = document.createElement('details');
    const legalTitle = document.createElement('summary');
    legalTitle.textContent = 'Checkliste vor Veröffentlichung';
    legalBox.appendChild(legalTitle);
    const legalContent = document.createElement('ul');
    legalBox.appendChild(legalContent);
    advanced.appendChild(legalBox);
    editor.insertBefore(sources, consent);

    const status = message => { const output = el('status'); if (output) output.textContent = message; };
    const val = id => (el(id)?.value || '').trim();
    const autoFilled = new Map();
    const fill = (id, value) => {
      const input = el(id);
      if (!input || !value) return;
      if (id === 'sector' || !input.value.trim() || autoFilled.get(id) === input.value) {
        input.value = value;
        autoFilled.set(id, value);
      }
    };
    let selectedCompany = null;
    function applyCompany(record) {
      selectedCompany = record;
      ['name', 'city', 'sector', 'phone', 'email', 'address', 'hours'].forEach(key => fill(key, record[key]));
      fill('site', record.website);
      fill('google', record.google);
      fill('services', record.services);
      if (Array.isArray(record.menu_sections) && record.menu_sections.length) {
        const menuLines = [];
        demoPriceContent.replaceChildren();
        const explain = document.createElement('p');
        explain.textContent = record.menu_notice || 'Unverbindliche Preisbeispiele. Vor Nutzung bestätigen.';
        demoPriceContent.appendChild(explain);
        for (const section of record.menu_sections) {
          menuLines.push('# ' + (section.title || 'Getränke').replace(' (Vorschlag)', ''));
          const h = document.createElement('strong');
          h.textContent = section.title || 'Getränke';
          demoPriceContent.appendChild(h);
          const ul = document.createElement('ul');
          for (const item of (section.items || [])) {
            menuLines.push([item.name || '', item.size || '', ''].join(' | '));
            const li = document.createElement('li');
            li.textContent = [item.name, item.size, item.sample_price ? '[' + item.sample_price + ' – nur Musterpreis]' : ''].filter(Boolean).join(' · ');
            ul.appendChild(li);
          }
          demoPriceContent.appendChild(ul);
          menuLines.push('');
        }
        if (!menuInput.value.trim() || menuInput.dataset.prefilled === 'yes') {
          menuInput.value = menuLines.join('\n').trim();
          menuInput.dataset.prefilled = 'yes';
        }
      }
      legalContent.replaceChildren();
      if (Array.isArray(record.publication_checks)) for (const check of record.publication_checks) {
        const li = document.createElement('li'); li.textContent = check;
        legalContent.appendChild(li);
      }
      sources.replaceChildren();
      const section = document.createElement('div'); section.className = 'yb-research-source';
      const title = document.createElement('strong');
      title.textContent = 'Recherchequellen: ' + (record.source_label || 'Öffentliche Daten');
      section.appendChild(title);
      const list = document.createElement('ul');
      (record.sources || []).forEach(item => {
        try {
          const url = new URL(item.url);
          if (!['http:', 'https:'].includes(url.protocol)) return;
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = url.href; a.target = '_blank'; a.rel = 'noopener noreferrer';
          a.textContent = item.label;
          li.appendChild(a); list.appendChild(li);
        } catch {}
      });
      section.appendChild(list);
      const warning = document.createElement('p'); warning.className = 'yb-research-warning';
      warning.textContent = record.warning || 'Öffentliche Angaben bitte prüfen.';
      section.appendChild(warning); sources.appendChild(section);
      advanced.open = true;
      status('Daten vorbefüllt. Telefonnummer, Öffnungszeiten und Impressum bitte prüfen; anschließend Vorschau erstellen.');
      results.replaceChildren();
    }

    // Support a direct customer demo link without retyping the two search fields.
    const searchParams = new URLSearchParams(window.location.search);
    if (searchParams.get('yb_company') && searchParams.get('yb_city')) {
      name.value = searchParams.get('yb_company').slice(0, 100);
      city.value = searchParams.get('yb_city').slice(0, 100);
    }

    button.addEventListener('click', async () => {
      if (val('name').length < 3 || val('city').length < 2) {
        status('Bitte nur den Firmennamen und den Ort eingeben.'); return;
      }
      button.disabled = true; button.textContent = 'Öffentliche Unternehmensdaten werden gesucht …';
      results.replaceChildren();
      status('Recherche läuft – noch keine Daten veröffentlicht.');
      try {
        const body = new URLSearchParams({
          action: 'yb_discover', nonce: window.YourBusinessResearch.nonce,
          company: val('name'), city: val('city')
        });
        const response = await fetch(window.YourBusinessResearch.url, {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
          credentials: 'same-origin', body: body.toString()
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload?.data?.message || 'Recherche nicht möglich');
        const records = payload.data?.candidates || [];
        if (!records.length) {
          status(payload.data?.notice || 'Kein eindeutiger Treffer.');
          const a = document.createElement('a');
          a.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(val('name') + ' ' + val('city'));
          a.textContent = 'In Google Maps gegenprüfen'; a.target = '_blank'; a.rel = 'noopener noreferrer';
          results.appendChild(a); return;
        }
        if (records.length === 1) { applyCompany(records[0]); return; }
        status('Mehrere Treffer: bitte das richtige Unternehmen auswählen.');
        for (const record of records) {
          const choice = document.createElement('button'); choice.type = 'button';
          choice.textContent = record.name + ' – ' + (record.address || record.city);
          choice.addEventListener('click', () => applyCompany(record));
          results.appendChild(choice);
        }
      } catch(err) {
        status('Automatische Recherche nicht verfügbar: ' + err.message);
      } finally {
        button.disabled = false; button.textContent = 'Firmendaten automatisch recherchieren';
      }
    });

    // Mark researched editorial copy as draft. Never fabricate a legal imprint.
    const extendPreview = () => {
      const company = selectedCompany;
      if (!company) return;
      const content = root.querySelector('.yb-preview .yb-content');
      if (!content) return;
      const section = document.createElement('section');
      section.style.marginTop = '28px';
      const heading = document.createElement('h2');
      heading.textContent = 'Über uns';
      section.appendChild(heading);
      if (company.intro) {
        const p = document.createElement('p');
        p.textContent = company.intro;
        section.appendChild(p);
      }
      if (Array.isArray(company.features) && company.features.length) {
        const h = document.createElement('h3'); h.textContent = 'Was dich erwartet';
        section.appendChild(h);
        const ul = document.createElement('ul');
        company.features.forEach(f => { const li = document.createElement('li'); li.textContent = f; ul.appendChild(li); });
        section.appendChild(ul);
      }
      if (company.history) {
        const h = document.createElement('h3'); h.textContent = 'Geschichte unseres Standorts';
        const p = document.createElement('p'); p.textContent = company.history;
        section.appendChild(h); section.appendChild(p);
      }
      const menuLines = menuInput.value.split(/\r?\n/).map(line => line.trim()).filter(Boolean);
      if (menuLines.length) {
        const menuHeader = document.createElement('h2');
        menuHeader.textContent = 'Getränkekarte – Entwurf';
        section.appendChild(menuHeader);
        const note = document.createElement('p');
        note.style.cssText = 'font-size:12px;color:#875400';
        note.textContent = 'Sortiment, Mengen und Preise sind vor Veröffentlichung vom Betrieb zu bestätigen. Fehlende Preise sind kein verbindliches Angebot.';
        section.appendChild(note);
        const menuList = document.createElement('div'); menuList.className = 'yb-draft-menu';
        for (const line of menuLines) {
          if (line.startsWith('#')) {
            const h = document.createElement('h3'); h.textContent = line.replace(/^#+\s*/, '');
            menuList.appendChild(h);
            continue;
          }
          const fields = line.split('|').map(x => x.trim());
          const row = document.createElement('div');
          row.style.cssText = 'display:flex;gap:12px;justify-content:space-between;align-items:baseline;border-bottom:1px solid #e1e5ec;padding:8px 0';
          const item = document.createElement('span');
          item.textContent = fields[0] + (fields[1] ? ' · ' + fields[1] : '');
          const price = document.createElement('span');
          price.style.whiteSpace = 'nowrap';
          price.textContent = fields[2] || 'Preis folgt';
          row.appendChild(item); row.appendChild(price); menuList.appendChild(row);
        }
        section.appendChild(menuList);
      }
      const disclaimer = document.createElement('p');
      disclaimer.style.cssText = 'font-size:12px;color:#526179;border-top:1px solid #ddd;padding-top:12px';
      disclaimer.textContent = 'Unveröffentlichter Entwurf: Inhalte und Pflichtangaben vor dem Livegang mit dem Betrieb prüfen.';
      section.appendChild(disclaimer);
      content.appendChild(section);
    };
    const previewButton = el('build');
    if (previewButton) previewButton.addEventListener('click', extendPreview);
    // Show researched address/hours in the local preview without publishing them.
    const build = el('build');
    if (build) build.addEventListener('click', () => {
      const contact = root.querySelector('.yb-preview .yb-contact');
      if (!contact) return;
      for (const id of ['address', 'hours']) {
        const value = val(id);
        if (!value) continue;
        const paragraph = document.createElement('p');
        paragraph.textContent = (id === 'hours' ? 'Öffnungszeiten – Entwurf, bitte prüfen: ' : '') + value;
        contact.appendChild(paragraph);
      }
    });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
