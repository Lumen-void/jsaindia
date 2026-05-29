(() => {
    const page = String(document.body?.dataset.page || '');
    if (!page.endsWith('updates.php')) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const cardsWrap = document.querySelector('.updates-grid');
    const cards = Array.from(document.querySelectorAll('.updates-grid .update-card'));
    if (!cardsWrap || !cards.length) return;

    const storageKey = 'jsa_updates_bookmarks_v1';
    const loadBookmarks = () => {
        try {
            const raw = localStorage.getItem(storageKey);
            const arr = JSON.parse(raw || '[]');
            return Array.isArray(arr) ? arr : [];
        } catch {
            return [];
        }
    };
    const saveBookmarks = (arr) => {
        try {
            localStorage.setItem(storageKey, JSON.stringify(arr.slice(0, 400)));
        } catch {
            // ignore
        }
    };

    const getKey = (card) => String(card?.dataset.key || '');

    const setBookmarkState = (card, bookmarked) => {
        const btn = card.querySelector('.update-bookmark');
        if (!btn) return;
        btn.setAttribute('aria-pressed', bookmarked ? 'true' : 'false');
        btn.textContent = bookmarked ? 'Saved' : 'Save';
        card.classList.toggle('is-bookmarked', bookmarked);
    };

    const applyBookmarksUI = () => {
        const bms = new Set(loadBookmarks());
        cards.forEach((card) => {
            const k = getKey(card);
            setBookmarkState(card, k && bms.has(k));
        });
    };
    applyBookmarksUI();

    // Drawer (detail view)
    const overlay = document.createElement('div');
    overlay.className = 'updates-overlay';
    overlay.hidden = true;

    const drawer = document.createElement('aside');
    drawer.className = 'updates-drawer';
    drawer.setAttribute('aria-hidden', 'true');
    drawer.innerHTML = `
        <div class="updates-drawer-head">
            <div class="updates-drawer-title">Update details</div>
            <button type="button" class="updates-drawer-close btn ghost" aria-label="Close">Close</button>
        </div>
        <div class="updates-drawer-body"></div>
        <div class="updates-drawer-foot">
            <button type="button" class="btn ghost updates-copy">Copy link</button>
            <a class="btn updates-open-source" href="#" target="_blank" rel="noopener noreferrer">Open source</a>
            <a class="btn ghost" href="contact.php#inquiry-form">Ask our team</a>
        </div>
    `;
    document.body.appendChild(overlay);
    document.body.appendChild(drawer);

    const drawerBody = drawer.querySelector('.updates-drawer-body');
    const closeBtn = drawer.querySelector('.updates-drawer-close');
    const openSourceBtn = drawer.querySelector('.updates-open-source');
    const copyBtn = drawer.querySelector('.updates-copy');

    const setDrawerOpen = (open) => {
        document.body.classList.toggle('updates-drawer-open', open);
        overlay.hidden = !open;
        overlay.classList.toggle('is-active', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) {
            requestAnimationFrame(() => drawer.classList.add('open'));
        } else {
            drawer.classList.remove('open');
        }
    };

    const escapeHtml = (s) =>
        String(s || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

    let currentUrl = '';

    const openDrawerFor = (card) => {
        if (!drawerBody) return;

        const title = card.dataset.title || '';
        const src = card.dataset.sourceLabel || '';
        const tag = card.dataset.tag || '';
        const publishedAt = card.dataset.publishedAt || '';
        const pinned = card.dataset.pinned === '1';
        const importance = (card.dataset.importance || '').toLowerCase();
        const summary = card.dataset.summary || '';
        const effectiveDate = card.dataset.effectiveDate || '';
        const actionsRaw = card.dataset.actions || '';
        const url = card.dataset.url || '';

        currentUrl = url;
        if (openSourceBtn) openSourceBtn.href = url || '#';
        if (openSourceBtn) openSourceBtn.toggleAttribute('aria-disabled', !url);

        const actions = actionsRaw
            .split('\n')
            .map((x) => x.trim())
            .filter(Boolean);

        const meta = [];
        if (src) meta.push(`<span class="pill">${escapeHtml(src)}</span>`);
        if (pinned) meta.push(`<span class="pill soft">Pinned</span>`);
        if (importance) meta.push(`<span class="pill soft">${escapeHtml(importance)}</span>`);
        if (tag) meta.push(`<span class="pill soft">${escapeHtml(tag)}</span>`);
        if (publishedAt) meta.push(`<span class="pill soft">${escapeHtml(publishedAt.split('T')[0])}</span>`);

        const summaryHtml = summary
            ? `<div class="updates-drawer-section"><div class="updates-drawer-label">Summary</div><div class="updates-drawer-text">${escapeHtml(summary)}</div></div>`
            : `<div class="updates-drawer-section"><div class="updates-drawer-label">Summary</div><div class="updates-drawer-text muted">No summary added yet (can be added from Admin).</div></div>`;

        const effectiveHtml = effectiveDate
            ? `<div class="updates-drawer-section"><div class="updates-drawer-label">Effective date</div><div class="updates-drawer-text">${escapeHtml(effectiveDate)}</div></div>`
            : '';

        const actionsHtml = actions.length
            ? `<div class="updates-drawer-section"><div class="updates-drawer-label">Action items</div><ul class="updates-drawer-list">${actions
                  .slice(0, 10)
                  .map((a) => `<li>${escapeHtml(a)}</li>`)
                  .join('')}</ul></div>`
            : '';

        drawerBody.innerHTML = `
            <div class="meta">${meta.join('')}</div>
            <h3 class="updates-drawer-h">${escapeHtml(title)}</h3>
            ${summaryHtml}
            ${effectiveHtml}
            ${actionsHtml}
        `;

        setDrawerOpen(true);
    };

    overlay.addEventListener('click', () => setDrawerOpen(false));
    closeBtn?.addEventListener('click', () => setDrawerOpen(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setDrawerOpen(false);
    });

    copyBtn?.addEventListener('click', async () => {
        if (!currentUrl) return;
        const text = currentUrl;
        try {
            await navigator.clipboard.writeText(text);
            copyBtn.textContent = 'Copied';
            setTimeout(() => (copyBtn.textContent = 'Copy link'), 900);
        } catch {
            // fallback
            window.prompt('Copy link:', text);
        }
    });

    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('.update-open');
        if (openBtn) {
            const card = openBtn.closest('.update-card');
            if (card) openDrawerFor(card);
            return;
        }

        const saveBtn = e.target.closest('.update-bookmark');
        if (saveBtn) {
            const card = saveBtn.closest('.update-card');
            const k = getKey(card);
            if (!k) return;
            const arr = loadBookmarks();
            const set = new Set(arr);
            if (set.has(k)) set.delete(k);
            else set.add(k);
            const out = Array.from(set);
            saveBookmarks(out);
            setBookmarkState(card, set.has(k));
        }
    });

    // Quick filter chips (client-side)
    const chipsWrap = document.querySelector('.updates-chips');
    const isChipActive = (name) => Boolean(chipsWrap?.querySelector(`.chip.active[data-filter="${name}"]`));

    const parseDateTs = (iso) => {
        const t = Date.parse(String(iso || ''));
        return Number.isFinite(t) ? t : 0;
    };

    let countEl = null;
    let loadMoreBtn = null;
    const setFilteredOut = (card, out) => {
        card.classList.toggle('is-filtered-out', out);
    };
    const getVisibleCount = () => cards.filter((c) => !c.classList.contains('is-hidden') && !c.classList.contains('is-filtered-out')).length;

    const applyChipFilters = () => {
        const pinned = isChipActive('pinned');
        const new7 = isChipActive('new7');
        const pdf = isChipActive('pdf');
        const high = isChipActive('high');
        const any = pinned || new7 || pdf || high;

        const now = Date.now();
        const sevenDays = 7 * 86400 * 1000;

        cards.forEach((card) => {
            let ok = true;
            if (pinned) ok = ok && card.dataset.pinned === '1';
            if (new7) ok = ok && parseDateTs(card.dataset.publishedAt) >= now - sevenDays;
            if (pdf) ok = ok && String(card.dataset.doc || '').toUpperCase() === 'PDF';
            if (high) ok = ok && String(card.dataset.importance || '').toLowerCase() === 'high';
            setFilteredOut(card, any && !ok);
        });

        // If filtering, show all matching items (remove progressive hiding)
        if (any) {
            cards.forEach((c) => c.classList.remove('is-hidden'));
            loadMoreBtn?.remove();
            loadMoreBtn = null;
        }

        if (countEl) {
            countEl.querySelector('strong').textContent = String(getVisibleCount());
        }
    };

    // View toggle: Cards / Table
    const toolbar = document.querySelector('.updates-toolbar');
    if (toolbar) {
        countEl = document.createElement('div');
        countEl.className = 'updates-count';
        countEl.innerHTML = `Showing <strong>${cards.length}</strong>`;

        const switcher = document.createElement('div');
        switcher.className = 'updates-switch';
        switcher.innerHTML = `
            <div class="updates-switch-inner">
                <button type="button" class="tab active" data-view="cards">Cards</button>
                <button type="button" class="tab" data-view="table">Table</button>
                <button type="button" class="tab" data-view="saved">Saved</button>
            </div>
        `;
        toolbar.prepend(switcher);
        toolbar.appendChild(countEl);

        const tableWrap = document.createElement('div');
        tableWrap.className = 'updates-table card';
        tableWrap.hidden = true;
        toolbar.insertAdjacentElement('afterend', tableWrap);

        const buildTable = (filterMode = 'all') => {
            const bms = new Set(loadBookmarks());
            const rows = cards
                .filter((card) => {
                    if (filterMode === 'saved') return bms.has(getKey(card));
                    return true;
                })
                .map((card) => ({
                    title: card.dataset.title || '',
                    source: card.dataset.sourceLabel || '',
                    tag: card.dataset.tag || '',
                    date: (card.dataset.publishedAt || '').split('T')[0],
                    url: card.dataset.url || '',
                    pinned: card.dataset.pinned === '1',
                }));

            const html = [];
            html.push('<div class="tableish updates-tableish">');
            html.push('<div class="tableish-row tableish-head updates-row"><div>Title</div><div>Source</div><div>Date</div><div>Link</div></div>');
            rows.slice(0, 220).forEach((r) => {
                const link = r.url ? `<a href="${escapeHtml(r.url)}" target="_blank" rel="noopener noreferrer">Open</a>` : '';
                const title = escapeHtml(r.title) + (r.pinned ? ' <span class="highlight">(Pinned)</span>' : '');
                html.push(
                    `<div class="tableish-row updates-row"><div>${title}</div><div class="muted">${escapeHtml(r.source)}${r.tag ? ' - ' + escapeHtml(r.tag) : ''}</div><div class="muted">${escapeHtml(r.date)}</div><div>${link}</div></div>`
                );
            });
            html.push('</div>');
            tableWrap.innerHTML = html.join('');
        };

        const setView = (view) => {
            const buttons = Array.from(switcher.querySelectorAll('[data-view]'));
            buttons.forEach((b) => b.classList.toggle('active', b.dataset.view === view));

            if (view === 'table') {
                tableWrap.hidden = false;
                buildTable('all');
                cardsWrap.hidden = true;
                countEl.hidden = true;
            } else if (view === 'saved') {
                tableWrap.hidden = false;
                buildTable('saved');
                cardsWrap.hidden = true;
                countEl.hidden = true;
            } else {
                tableWrap.hidden = true;
                cardsWrap.hidden = false;
                countEl.hidden = false;
                countEl.querySelector('strong').textContent = String(getVisibleCount());
            }
        };

        switcher.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-view]');
            if (!btn) return;
            setView(btn.dataset.view || 'cards');
        });

        // Hook chips once switcher is ready (so we can reuse countEl)
        if (chipsWrap) {
            chipsWrap.addEventListener('click', (e) => {
                const chip = e.target.closest('.chip');
                if (!chip) return;
                const name = chip.dataset.filter || '';
                if (name === 'clear') {
                    chipsWrap.querySelectorAll('.chip.active').forEach((c) => c.classList.remove('active'));
                    cards.forEach((c) => c.classList.remove('is-filtered-out'));
                    countEl.querySelector('strong').textContent = String(getVisibleCount());
                    return;
                }
                chip.classList.toggle('active');
                applyChipFilters();
            });
        }

        // Initial count reflects server-side filtering
        countEl.querySelector('strong').textContent = String(getVisibleCount());
    }

    // Optional: small progressive load (no reflow heavy infinite scroll)
    const maxInitial = 24;
    if (cards.length > maxInitial) {
        cards.forEach((c, idx) => {
            if (idx >= maxInitial) c.classList.add('is-hidden');
        });
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn ghost updates-load';
        btn.textContent = 'Load more';
        cardsWrap.insertAdjacentElement('afterend', btn);
        loadMoreBtn = btn;
        btn.addEventListener('click', () => {
            const hidden = cards.filter((c) => c.classList.contains('is-hidden'));
            hidden.slice(0, 24).forEach((c) => c.classList.remove('is-hidden'));
            if (hidden.length <= 24) btn.remove();
            if (!prefersReducedMotion) btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
})();
