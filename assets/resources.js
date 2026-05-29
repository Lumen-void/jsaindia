(() => {
    const page = String(document.body?.dataset.page || '');
    if (!page.endsWith('resources.php')) return;

    const tabs = document.querySelector('[data-res-filter]')?.closest('.updates-tabs');
    const cards = Array.from(document.querySelectorAll('.resource-card'));
    if (!tabs || !cards.length) return;

    const setFilter = (key) => {
        tabs.querySelectorAll('.tab').forEach((b) => {
            const active = String(b.dataset.resFilter || '') === key;
            b.classList.toggle('active', active);
            b.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        cards.forEach((c) => {
            const cat = String(c.dataset.category || '');
            const ok = key === 'all' || cat === key;
            c.style.display = ok ? '' : 'none';
        });
    };

    tabs.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-res-filter]');
        if (!btn) return;
        setFilter(String(btn.dataset.resFilter || 'all'));
    });
})();

