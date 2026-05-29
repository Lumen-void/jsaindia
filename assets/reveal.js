(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    const revealSelector = '[data-reveal], .reveal, .reveal-left, .reveal-right, .reveal-zoom';
    const revealed = new WeakSet();

    const normalizeRevealClass = (el) => {
        const variant = (el.dataset.reveal || '').toLowerCase();
        if (variant === 'left') el.classList.add('reveal-left');
        else if (variant === 'right') el.classList.add('reveal-right');
        else if (variant === 'zoom') el.classList.add('reveal-zoom');
        else el.classList.add('reveal');
    };

    const revealEl = (el) => {
        if (revealed.has(el)) return;
        revealed.add(el);
        el.classList.add('is-revealed');
    };

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                revealEl(entry.target);
                if ((entry.target.dataset.replay || 'false') !== 'true') {
                    io.unobserve(entry.target);
                }
            });
        },
        { root: null, threshold: 0.18, rootMargin: '0px 0px -8% 0px' }
    );

    const applyStagger = (container) => {
        const step = Math.max(60, Math.min(120, parseInt(container.dataset.stagger || '90', 10)));
        const children = Array.from(container.querySelectorAll('[data-reveal-child], .reveal, .reveal-left, .reveal-right, .reveal-zoom'));
        children.forEach((child, index) => {
            child.style.transitionDelay = `${index * step}ms`;
        });
    };

    document.querySelectorAll(revealSelector).forEach((el) => {
        if (!el.classList.contains('reveal') && !el.classList.contains('reveal-left') && !el.classList.contains('reveal-right') && !el.classList.contains('reveal-zoom')) {
            normalizeRevealClass(el);
        }
    });

    document.querySelectorAll('[data-stagger]').forEach(applyStagger);
    document.querySelectorAll(revealSelector).forEach((el) => io.observe(el));

    // Counters (animate once when visible)
    const counterEls = Array.from(document.querySelectorAll('[data-counter]'));
    if (!counterEls.length) return;

    const animateCounter = (el) => {
        if (el.dataset.done === '1') return;
        el.dataset.done = '1';

        const target = parseFloat(el.dataset.target || '0');
        const duration = Math.max(600, Math.min(1400, parseInt(el.dataset.duration || '1000', 10)));
        const decimals = parseInt(el.dataset.decimals || '0', 10);
        const prefix = el.dataset.prefix || '';
        const suffix = el.dataset.suffix || '';

        const startTime = performance.now();
        const startValue = 0;

        const tick = (now) => {
            const t = Math.min(1, (now - startTime) / duration);
            const eased = 1 - Math.pow(1 - t, 3); // ease-out
            const value = startValue + (target - startValue) * eased;
            el.textContent = `${prefix}${value.toFixed(decimals)}${suffix}`;
            if (t < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    };

    const counterObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            });
        },
        { threshold: 0.35 }
    );

    counterEls.forEach((el) => counterObserver.observe(el));
})();
