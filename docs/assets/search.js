(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const openBtns = Array.from(document.querySelectorAll('.search-toggle'));
    if (!openBtns.length) return;

    const escapeHtml = (s) =>
        String(s || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

    const overlay = document.createElement('div');
    overlay.className = 'search-overlay';
    overlay.hidden = true;

    const modal = document.createElement('div');
    modal.className = 'search-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-label', 'Search');

    modal.innerHTML = `
        <div class="search-head">
            <div>
                <div class="search-title">Search</div>
                <div class="search-sub">Services, insights, case studies, and regulatory updates.</div>
            </div>
            <button class="btn ghost search-close" type="button" aria-label="Close search">Close</button>
        </div>
        <div class="search-box">
            <input class="search-input" id="siteSearchModal" name="site_search" type="search" placeholder="Try: GST, payroll, ROC, virtual CFO, RBI..." autocomplete="search">
            <button class="btn search-go" type="button">Search</button>
        </div>
        <div class="search-results" aria-live="polite"></div>
        <div class="search-foot muted">Tip: Press Esc to close. Use Enter to open the first result.</div>
    `;

    document.body.appendChild(overlay);
    document.body.appendChild(modal);

    const input = modal.querySelector('.search-input');
    const closeBtn = modal.querySelector('.search-close');
    const goBtn = modal.querySelector('.search-go');
    const resultsEl = modal.querySelector('.search-results');

    const cacheKey = 'jsa_search_index_v1';
    const cacheTsKey = 'jsa_search_index_ts_v1';
    const ttlMs = 12 * 60 * 60 * 1000;

    const loadCache = () => {
        try {
            const ts = parseInt(localStorage.getItem(cacheTsKey) || '0', 10);
            if (Date.now() - ts > ttlMs) return null;
            const raw = localStorage.getItem(cacheKey);
            const parsed = JSON.parse(raw || 'null');
            if (!parsed || !Array.isArray(parsed.items)) return null;
            return parsed;
        } catch {
            return null;
        }
    };

    const saveCache = (payload) => {
        try {
            localStorage.setItem(cacheKey, JSON.stringify(payload));
            localStorage.setItem(cacheTsKey, String(Date.now()));
        } catch {
            // ignore
        }
    };

    let index = null;
    let loading = false;

    const fetchIndex = async () => {
        if (index) return index;
        if (loading) return null;
        loading = true;
        try {
            const cached = loadCache();
            if (cached) {
                index = cached;
                return index;
            }
            const res = await fetch('api/search-index.php', { headers: { 'X-Requested-With': 'fetch' } });
            const data = await res.json().catch(() => null);
            if (!data || !data.ok || !Array.isArray(data.items)) return null;
            index = { items: data.items };
            saveCache(index);
            return index;
        } finally {
            loading = false;
        }
    };

    const setOpen = (open) => {
        document.body.classList.toggle('search-open', open);
        overlay.hidden = !open;
        overlay.classList.toggle('is-active', open);
        modal.classList.toggle('open', open);
        if (open) {
            setTimeout(() => input?.focus(), prefersReducedMotion ? 0 : 40);
        }
    };

    const renderResults = (items, q) => {
        if (!resultsEl) return;
        if (!q) {
            resultsEl.innerHTML = '<div class="muted">Start typing to search.</div>';
            return;
        }
        if (!items.length) {
            resultsEl.innerHTML = '<div class="muted">No results found.</div>';
            return;
        }
        const html = [];
        html.push('<div class="search-list">');
        items.slice(0, 12).forEach((it, idx) => {
            const type = escapeHtml(it.type || '');
            const title = escapeHtml(it.title || '');
            const meta = escapeHtml(it.meta || '');
            const url = String(it.url || '#');
            const ext = it.external ? ' target="_blank" rel="noopener noreferrer"' : '';
            html.push(
                `<a class="search-item" href="${escapeHtml(url)}"${ext} data-idx="${idx}"><div class="search-item-title">${title}</div><div class="search-item-meta"><span class="pill soft">${type}</span><span class="muted">${meta}</span></div></a>`
            );
        });
        html.push('</div>');
        resultsEl.innerHTML = html.join('');
    };

    const queryIndex = (q) => {
        const needle = q.toLowerCase().trim();
        if (!needle) return [];
        const items = index?.items || [];
        const scored = [];
        for (const it of items) {
            const title = String(it.title || '');
            const meta = String(it.meta || '');
            const hay = (title + ' ' + meta + ' ' + String(it.type || '')).toLowerCase();
            const pos = hay.indexOf(needle);
            if (pos === -1) continue;
            const score = pos === 0 ? 0 : pos;
            scored.push([score, it]);
        }
        scored.sort((a, b) => a[0] - b[0]);
        return scored.map((x) => x[1]);
    };

    const doSearch = async () => {
        const q = String(input?.value || '').trim();
        resultsEl.innerHTML = '<div class="muted">Searching...</div>';
        await fetchIndex();
        const out = queryIndex(q);
        renderResults(out, q);
    };

    openBtns.forEach((btn) => {
        btn.addEventListener('click', async () => {
            setOpen(true);
            renderResults([], '');
            await fetchIndex();
        });
    });

    overlay.addEventListener('click', () => setOpen(false));
    closeBtn?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => {
        if (!document.body.classList.contains('search-open')) return;
        if (e.key === 'Escape') setOpen(false);
        if (e.key === 'Enter') {
            const first = modal.querySelector('.search-item');
            if (first && document.activeElement === input) {
                first.click();
            }
        }
    });

    let t = null;
    input?.addEventListener('input', () => {
        window.clearTimeout(t);
        t = window.setTimeout(doSearch, 110);
    });
    goBtn?.addEventListener('click', doSearch);
})();
