(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const header = document.querySelector('.site-header');
    const nav = document.querySelector('.nav');
    const menuToggle = document.querySelector('.menu-toggle');
    const overlay = document.querySelector('.drawer-overlay');
    const drawer = document.querySelector('.nav-drawer');
    const drawerNav = document.querySelector('.drawer-nav');
    const drawerClose = document.querySelector('.drawer-close');
    const drawerSearch = document.querySelector('.drawer-search-card');
    const announceBar = document.querySelector('.announce-bar');
    const updateMegaTop = () => {
        if (!header) return;
        const bottom = Math.ceil(header.getBoundingClientRect().bottom);
        document.documentElement.style.setProperty('--mega-top', `${Math.max(0, bottom)}px`);
    };
    const pageTransition = (() => {
        const el = document.createElement('div');
        el.className = 'page-transition';
        document.body.appendChild(el);
        return el;
    })();
    const clearPageTransition = () => pageTransition.classList.remove('is-active');
    window.addEventListener('load', clearPageTransition);
    window.addEventListener('pageshow', clearPageTransition);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) clearPageTransition();
    });

    // Scroll progress (subtle)
    const progressBar = (() => {
        if (!header) return null;
        const bar = document.createElement('div');
        bar.className = 'scroll-progress';
        const fill = document.createElement('span');
        bar.appendChild(fill);
        header.appendChild(bar);
        return fill;
    })();

    // Sticky compact header
    let ticking = false;
    const onScroll = () => {
        if (!header) return;
        header.classList.toggle('is-scrolled', window.scrollY > 20);
        updateMegaTop();
        const backToTop = document.querySelector('.back-to-top');
        if (backToTop) backToTop.classList.toggle('is-visible', window.scrollY > 520);

        if (progressBar && !ticking) {
            ticking = true;
            requestAnimationFrame(() => {
                const scrollTop = window.scrollY;
                const docH = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
                const pct = Math.max(0, Math.min(100, (scrollTop / docH) * 100));
                progressBar.style.width = `${pct}%`;
                ticking = false;
            });
        }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', updateMegaTop);
    window.addEventListener('load', updateMegaTop);
    updateMegaTop();
    onScroll();

    // Back to top button
    const backBtn = document.createElement('button');
    backBtn.className = 'back-to-top';
    backBtn.type = 'button';
    backBtn.setAttribute('aria-label', 'Back to top');
    backBtn.textContent = 'Top';
    backBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
    });
    document.body.appendChild(backBtn);

    // Smooth scroll to anchors with header offset
    const scrollToHash = (hash) => {
        const id = hash.replace('#', '');
        const target = document.getElementById(id);
        if (!target) return false;
        const headerH = header ? header.getBoundingClientRect().height : 0;
        const y = window.scrollY + target.getBoundingClientRect().top - headerH - 12;
        window.scrollTo({ top: Math.max(0, y), behavior: prefersReducedMotion ? 'auto' : 'smooth' });
        return true;
    };

    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href^="#"]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || href === '#') return;
        if (scrollToHash(href)) {
            e.preventDefault();
            history.replaceState(null, '', href);
        }
    });

    // If page loads with a hash, apply offset scroll
    if (location.hash) {
        window.addEventListener(
            'load',
            () => {
                setTimeout(() => scrollToHash(location.hash), 0);
            },
            { once: true }
        );
    }

    // Mobile drawer menu
    const setDrawerOpen = (open) => {
        if (!drawer || !overlay || !menuToggle) return;
        document.body.classList.toggle('drawer-open', open);
        if (open) overlay.hidden = false;
        overlay.classList.toggle('is-active', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            requestAnimationFrame(() => drawer.classList.add('open'));
        } else {
            drawer.classList.remove('open');
            // allow overlay to fade out before hiding
            setTimeout(() => {
                if (!document.body.classList.contains('drawer-open')) overlay.hidden = true;
            }, 220);
        }
    };

    const closeDrawer = () => setDrawerOpen(false);

    if (nav && drawerNav) {
        const drawerMeta = {
            About: {
                eyebrow: 'Who we are',
                description: 'Firm profile, credentials, and how the team works.',
                icon: 'apartment',
                children: [
                    { href: 'about.php#team', label: 'Our Team' },
                ],
            },
            Services: {
                eyebrow: 'What we do',
                description: 'Accounting, tax, payroll, and compliance support.',
                icon: 'grid_view',
                featured: true,
            },
            Careers: {
                eyebrow: 'Join us',
                description: 'Open roles, hiring details, and how to apply.',
                icon: 'work',
            },
            'Latest Updates': {
                eyebrow: 'Stay current',
                description: 'Case studies, regulatory updates, and resources.',
                icon: 'campaign',
                children: [
                    { href: 'case-studies.php', label: 'Case Studies' },
                    { href: 'updates.php', label: 'Regulatory Updates' },
                    { href: 'resources.php', label: 'Resources' },
                ],
            },
            Insights: {
                eyebrow: 'What is new',
                description: 'Articles, practical notes, and regulatory updates.',
                icon: 'auto_stories',
            },
            Contact: {
                eyebrow: 'Reach us',
                description: 'Speak with the team and book a consultation.',
                icon: 'call',
            },
        };

        const items = Array.from(nav.children)
            .map((node) => {
                const anchor = node.matches('a') ? node : node.querySelector(':scope > a');
                if (!anchor) return null;
                const label = (anchor.textContent || '').trim();
                if (!label) return null;
                return {
                    href: anchor.getAttribute('href') || '#',
                    label,
                    current: anchor.getAttribute('aria-current') === 'page',
                    meta: drawerMeta[label] || {
                        eyebrow: 'Navigate',
                        description: 'Open this section.',
                        icon: 'arrow_forward',
                    },
                };
            })
            .filter(Boolean);

        drawerNav.replaceChildren();

        items.forEach((item) => {
            const link = document.createElement('a');
            const icon = document.createElement('span');
            const copy = document.createElement('span');
            const eyebrow = document.createElement('span');
            const title = document.createElement('span');
            const description = document.createElement('span');
            const arrow = document.createElement('span');
            const childLinks = Array.isArray(item.meta.children) ? item.meta.children : [];

            link.href = item.href;
            link.className = `drawer-link-card${item.meta.featured ? ' is-featured' : ''}`;
            if (item.current) link.setAttribute('aria-current', 'page');

            icon.className = 'drawer-link-icon ms-icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = item.meta.icon;

            copy.className = 'drawer-link-copy';

            eyebrow.className = 'drawer-link-eyebrow';
            eyebrow.textContent = item.meta.eyebrow;

            title.className = 'drawer-link-title';
            title.textContent = item.label;

            description.className = 'drawer-link-desc';
            description.textContent = item.meta.description;

            arrow.className = 'drawer-link-arrow ms-icon';
            arrow.setAttribute('aria-hidden', 'true');
            arrow.textContent = 'arrow_forward';

            copy.append(eyebrow, title, description);
            link.append(icon, copy, arrow);
            link.addEventListener('click', closeDrawer);

            if (!childLinks.length) {
                drawerNav.appendChild(link);
                return;
            }

            const panel = document.createElement('div');
            const subnav = document.createElement('div');

            panel.className = 'drawer-link-panel';
            subnav.className = 'drawer-link-subnav';

            childLinks.forEach((child) => {
                const childLink = document.createElement('a');
                const childUrl = new URL(child.href, window.location.href);
                childLink.href = child.href;
                childLink.className = 'drawer-link-pill';
                childLink.textContent = child.label;
                if (childUrl.pathname === window.location.pathname) {
                    childLink.setAttribute('aria-current', 'page');
                }
                childLink.addEventListener('click', closeDrawer);
                subnav.appendChild(childLink);
            });

            panel.append(link, subnav);
            drawerNav.appendChild(panel);
        });
    }

    if (drawerSearch) {
        drawerSearch.addEventListener('click', () => closeDrawer());
    }

    if (drawer) {
        drawer.querySelectorAll('.drawer-cta a').forEach((link) => {
            link.addEventListener('click', closeDrawer);
        });
    }

    if (menuToggle && drawer && overlay && drawerClose) {
        menuToggle.addEventListener('click', () => {
            const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            setDrawerOpen(!isOpen);
        });
        drawerClose.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDrawer();
        });
    }

    // Desktop dropdowns: keep menus attached on hover and make top-level clicks usable.
    const megaItems = Array.from(document.querySelectorAll('.nav-item.has-mega'));
    if (megaItems.length) {
        const closeMegas = (except = null) => {
            megaItems.forEach((item) => {
                if (item === except) return;
                item.classList.remove('is-open');
                const trigger = item.querySelector(':scope > a');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        };

        megaItems.forEach((item) => {
            const trigger = item.querySelector(':scope > a');
            if (!trigger) return;
            trigger.addEventListener('click', (event) => {
                if (window.matchMedia('(max-width: 980px)').matches) return;
                if (!item.classList.contains('is-open')) {
                    event.preventDefault();
                    updateMegaTop();
                    closeMegas(item);
                    item.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
            item.addEventListener('mouseenter', () => {
                updateMegaTop();
                closeMegas(item);
                item.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            });
            item.addEventListener('mouseleave', () => {
                item.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.nav-item.has-mega')) closeMegas();
        });
    }

    // Blog category + search filter (listing page)
    const filterButtons = Array.from(document.querySelectorAll('.filter-btn[data-filter]'));
    const filterCards = Array.from(document.querySelectorAll('[data-category]'));
    if (filterButtons.length && filterCards.length) {
        const searchInput = document.querySelector('[data-search]');
        let searchTerm = '';

        const setFilter = (category) => {
            filterButtons.forEach((b) => {
                const active = b.dataset.filter === category;
                b.classList.toggle('active', active);
                b.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            filterCards.forEach((card) => {
                const cardCat = card.dataset.category;
                const catOk = category === 'all' || cardCat === category;
                const searchText = (card.dataset.searchText || '').toLowerCase();
                const searchOk = !searchTerm || searchText.includes(searchTerm);
                card.style.display = catOk && searchOk ? '' : 'none';
            });
        };
        filterButtons.forEach((btn) => {
            btn.setAttribute('type', 'button');
            btn.setAttribute('aria-pressed', btn.classList.contains('active') ? 'true' : 'false');
            btn.addEventListener('click', () => setFilter(btn.dataset.filter || 'all'));
        });

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                searchTerm = String(searchInput.value || '').trim().toLowerCase();
                const active = filterButtons.find((b) => b.classList.contains('active'));
                setFilter(active?.dataset.filter || 'all');
            });
        }
    }

    // Lightweight page transitions on internal navigation
    const isInternalLink = (a) => {
        if (!a) return false;
        const href = a.getAttribute('href') || '';
        if (!href || href.startsWith('#')) return false;
        if (href.startsWith('mailto:') || href.startsWith('tel:')) return false;
        if (a.target && a.target !== '_self') return false;
        try {
            const url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) return false;
            return url.pathname.endsWith('.php') || url.searchParams.toString().length > 0;
        } catch {
            return false;
        }
    };

    document.addEventListener('click', (e) => {
        if (e.defaultPrevented) return;
        const a = e.target.closest('a');
        if (!isInternalLink(a) || prefersReducedMotion) return;
        const href = a.getAttribute('href');
        if (!href) return;
        // Skip if it's an in-page anchor on home that we already handle
        if (href.startsWith('#')) return;
        pageTransition.classList.add('is-active');
        setTimeout(() => {
            window.location.href = href;
        }, 150);
        e.preventDefault();
    });

    // Home: service selector wizard
    const needSelect = document.getElementById('needSelect');
    const needCta = document.getElementById('needCta');
    if (needSelect && needCta) {
        const update = () => {
            const slug = needSelect.value;
            if (!slug) {
                needCta.textContent = 'Get recommendation';
                needCta.setAttribute('href', 'contact.php#inquiry-form');
                return;
            }
            needCta.textContent = 'Open recommended service';
            needCta.setAttribute('href', `service.php?slug=${encodeURIComponent(slug)}`);
        };
        needSelect.addEventListener('change', update);
        update();
    }

    // Mobile sticky CTA bar (call/whatsapp/book)
    const stickyBar = (() => {
        const el = document.createElement('div');
        el.className = 'sticky-cta';
        el.innerHTML = `
            <a class="sticky-btn" href="mailto:jm@jsaindia.com">Email</a>
            <a class="sticky-btn" href="contact.php#inquiry-form">Contact</a>
            <a class="sticky-btn primary" href="services.php">Services</a>
        `;
        document.body.appendChild(el);
        return el;
    })();

    const stickyAllowed = window.matchMedia('(max-width: 820px)').matches;
    if (stickyAllowed) {
        const onSticky = () => {
            stickyBar.classList.toggle('is-visible', window.scrollY > 420);
        };
        window.addEventListener('scroll', onSticky, { passive: true });
        onSticky();
    }

    // Nav routes to pages (no home section anchor rewrites).

    // Ripple micro-interaction (buttons + cards)
    document.addEventListener('click', (e) => {
        const el = e.target.closest('.btn, .card');
        if (!el || prefersReducedMotion) return;
        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        el.appendChild(ripple);
        setTimeout(() => ripple.remove(), 520);
    });

    // Optional: subtle card tilt + glare (desktop only)
    const canTilt = !prefersReducedMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (canTilt) {
        const maxDeg = 3;
        document.querySelectorAll('.card[data-tilt]').forEach((card) => {
            const onMove = (e) => {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width;
                const y = (e.clientY - r.top) / r.height;
                const dx = (x - 0.5) * 2;
                const dy = (y - 0.5) * 2;
                card.style.setProperty('--tilt-y', `${dx * maxDeg}deg`);
                card.style.setProperty('--tilt-x', `${-dy * maxDeg}deg`);
                card.style.setProperty('--gx', `${Math.round(x * 100)}%`);
                card.style.setProperty('--gy', `${Math.round(y * 100)}%`);
            };
            const reset = () => {
                card.style.setProperty('--tilt-x', '0deg');
                card.style.setProperty('--tilt-y', '0deg');
            };
            card.addEventListener('pointermove', onMove);
            card.addEventListener('pointerleave', reset);
        });
    }

    // Accordion (FAQ) with smooth height (only one open)
    document.querySelectorAll('.faq').forEach((faq) => {
        const single = (faq.dataset.single ?? 'true') !== 'false';
        faq.querySelectorAll('.faq-item').forEach((item) => {
            const trigger = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            const inner = item.querySelector('.faq-answer-inner');
            if (!trigger || !answer || !inner) return;

            trigger.setAttribute('role', 'button');
            trigger.setAttribute('tabindex', '0');
            trigger.setAttribute('aria-expanded', item.classList.contains('active') ? 'true' : 'false');

            const setOpen = (open) => {
                item.classList.toggle('active', open);
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    answer.style.maxHeight = `${inner.scrollHeight}px`;
                } else {
                    answer.style.maxHeight = '0px';
                }
            };

            // Initialize
            setOpen(item.classList.contains('active'));

            const toggle = () => {
                const willOpen = !item.classList.contains('active');
                if (single && willOpen) {
                    faq.querySelectorAll('.faq-item.active').forEach((openItem) => {
                        if (openItem === item) return;
                        const openAnswer = openItem.querySelector('.faq-answer');
                        const openInner = openItem.querySelector('.faq-answer-inner');
                        const openTrigger = openItem.querySelector('.faq-question');
                        openItem.classList.remove('active');
                        if (openTrigger) openTrigger.setAttribute('aria-expanded', 'false');
                        if (openAnswer) openAnswer.style.maxHeight = '0px';
                        if (openInner) void openInner.offsetHeight;
                    });
                }
                setOpen(willOpen);
            };

            trigger.addEventListener('click', toggle);
            trigger.addEventListener('keydown', (evt) => {
                if (evt.key === 'Enter' || evt.key === ' ') {
                    evt.preventDefault();
                    toggle();
                }
            });
        });
    });

    // Announcement bar dismiss (remember per announcement id)
    if (announceBar) {
        const id = String(announceBar.dataset.announceId || 'default');
        const key = `jsa_announce_closed_${id}`;
        try {
            if (localStorage.getItem(key) === '1') {
                announceBar.remove();
            }
        } catch {
            // ignore
        }
        const close = announceBar.querySelector('.announce-close');
        close?.addEventListener('click', () => {
            try {
                localStorage.setItem(key, '1');
            } catch {
                // ignore
            }
            announceBar.remove();
        });
    }
})();
