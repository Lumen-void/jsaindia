const emailOk = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

(() => {
    const page = String(document.body?.dataset.page || '');
    const hasHome = !!document.querySelector('.home-modern');
    const hasBooking = !!document.getElementById('bookingModal');
    if (!hasHome && !page.endsWith('index.php') && !hasBooking) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Animated counters
    const counterEls = Array.from(document.querySelectorAll('.counter'));
    if (counterEls.length) {
        const animateCounter = (el, target, duration = 2000) => {
            const startTime = performance.now();
            const start = 0;
            const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);
            const update = (now) => {
                const progress = Math.min(1, (now - startTime) / duration);
                const current = Math.floor(start + (target - start) * easeOutQuart(progress));
                el.textContent = current.toLocaleString();
                if (progress < 1) requestAnimationFrame(update);
                else el.textContent = target.toLocaleString();
            };
            requestAnimationFrame(update);
        };

        const counterObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    const el = entry.target;
                    if (!entry.isIntersecting || el.classList.contains('counted')) return;
                    const target = parseInt(el.dataset.target || '0', 10) || 0;
                    animateCounter(el, target);
                    el.classList.add('counted');
                });
            },
            { threshold: 0.5 }
        );

        counterEls.forEach((el) => counterObserver.observe(el));
    }

    // 3D globe canvas animation
    const canvas = document.getElementById('globeCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        if (ctx) {
            const dpr = Math.max(1, window.devicePixelRatio || 1);
            let width = 0;
            let height = 0;
            const globe = { centerX: 0, centerY: 0, radius: 0, rotation: 0 };
            const numLatLines = 18;
            const numLongLines = 36;
            const connectionPoints = [
                { theta: 0.4, phi: 0.5 },
                { theta: 0.7, phi: 1.2 },
                { theta: 0.5, phi: 2.1 },
                { theta: 0.6, phi: 3.5 },
                { theta: 0.8, phi: 4.2 },
                { theta: 0.3, phi: 5.0 }
            ];

            const resize = () => {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width * dpr;
                canvas.height = height * dpr;
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                const isWide = width > 900;
                globe.centerX = isWide ? width * 0.68 : width / 2;
                globe.centerY = isWide ? height * 0.5 : height * 0.45;
                globe.radius = Math.min(width, height) * (isWide ? 0.65 : 0.5);
            };
            resize();

            const project = (theta, phi, rotation) => {
                const adjustedPhi = phi + rotation;
                const x = Math.sin(theta) * Math.cos(adjustedPhi);
                const y = Math.cos(theta);
                const z = Math.sin(theta) * Math.sin(adjustedPhi);
                const scale = 1 / (1 + z * 0.3);
                return {
                    x: globe.centerX + x * globe.radius * scale,
                    y: globe.centerY + y * globe.radius * scale,
                    z,
                    visible: z > -0.5
                };
            };

            const drawGlobe = () => {
                ctx.clearRect(0, 0, width, height);
                globe.rotation += prefersReducedMotion ? 0 : 0.002;
                ctx.strokeStyle = 'rgba(6, 182, 212, 0.6)';
                ctx.lineWidth = 1;

                for (let lat = 0; lat < numLatLines; lat++) {
                    ctx.beginPath();
                    let firstPoint = true;
                    for (let i = 0; i <= 100; i++) {
                        const theta = (lat / numLatLines) * Math.PI;
                        const phi = (i / 100) * Math.PI * 2;
                        const projected = project(theta, phi, globe.rotation);
                        if (projected.visible) {
                            if (firstPoint) {
                                ctx.moveTo(projected.x, projected.y);
                                firstPoint = false;
                            } else {
                                ctx.lineTo(projected.x, projected.y);
                            }
                        } else {
                            firstPoint = true;
                        }
                    }
                    ctx.stroke();
                }

                for (let long = 0; long < numLongLines; long++) {
                    ctx.beginPath();
                    let firstPoint = true;
                    for (let i = 0; i <= 100; i++) {
                        const theta = (i / 100) * Math.PI;
                        const phi = (long / numLongLines) * Math.PI * 2;
                        const projected = project(theta, phi, globe.rotation);
                        if (projected.visible) {
                            if (firstPoint) {
                                ctx.moveTo(projected.x, projected.y);
                                firstPoint = false;
                            } else {
                                ctx.lineTo(projected.x, projected.y);
                            }
                        } else {
                            firstPoint = true;
                        }
                    }
                    ctx.stroke();
                }

                ctx.fillStyle = 'rgba(6, 182, 212, 0.8)';
                connectionPoints.forEach((point) => {
                    const projected = project(point.theta, point.phi, globe.rotation);
                    if (projected.visible) {
                        ctx.beginPath();
                        ctx.arc(projected.x, projected.y, 3, 0, Math.PI * 2);
                        ctx.fill();
                        const gradient = ctx.createRadialGradient(projected.x, projected.y, 0, projected.x, projected.y, 10);
                        gradient.addColorStop(0, 'rgba(6, 182, 212, 0.4)');
                        gradient.addColorStop(1, 'rgba(6, 182, 212, 0)');
                        ctx.fillStyle = gradient;
                        ctx.beginPath();
                        ctx.arc(projected.x, projected.y, 10, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.fillStyle = 'rgba(6, 182, 212, 0.8)';
                    }
                });
            };

            const animate = () => {
                drawGlobe();
                if (!prefersReducedMotion) requestAnimationFrame(animate);
            };
            animate();

            window.addEventListener('resize', () => {
                resize();
                drawGlobe();
            });

            window.addEventListener('scroll', () => {
                const offset = window.pageYOffset || 0;
                canvas.style.transform = `translateY(${offset * 0.3}px)`;
            });
        }
    }

    // Particles animation
    const particlesContainer = document.querySelector('.particles');
    if (particlesContainer && !prefersReducedMotion) {
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.style.position = 'absolute';
            particle.style.width = '4px';
            particle.style.height = '4px';
            particle.style.background = 'rgba(6, 182, 212, 0.3)';
            particle.style.borderRadius = '50%';
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            particle.style.animation = `float ${3 + Math.random() * 3}s ease-in-out infinite`;
            particle.style.animationDelay = `${Math.random() * 3}s`;
            particlesContainer.appendChild(particle);
        }
    }

    // Feature tabs
    const tabButtons = Array.from(document.querySelectorAll('.tab-button'));
    const featurePanels = Array.from(document.querySelectorAll('.feature-content'));
    if (tabButtons.length && featurePanels.length) {
        const setActiveFeature = (id) => {
            if (!id) return;
            tabButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.feature === id));
            featurePanels.forEach((panel) => panel.classList.toggle('active', panel.id === `feature-${id}`));
        };

        tabButtons.forEach((btn) => {
            btn.type = 'button';
            btn.addEventListener('click', () => setActiveFeature(btn.dataset.feature || ''));
        });

        const initial = tabButtons.find((btn) => btn.classList.contains('active')) || tabButtons[0];
        if (initial) setActiveFeature(initial.dataset.feature || '');
    }

    // Process timeline
    const timelineSteps = Array.from(document.querySelectorAll('.timeline-step'));
    const processPanels = Array.from(document.querySelectorAll('.process-content'));
    const progressBar = document.getElementById('timelineProgress');
    if (timelineSteps.length && processPanels.length) {
        const total = timelineSteps.length;
        const setStep = (index) => {
            timelineSteps.forEach((step, i) => {
                step.classList.toggle('active', i === index);
                step.classList.toggle('completed', i < index);
            });
            processPanels.forEach((panel, i) => panel.classList.toggle('active', i === index));
            if (progressBar) {
                const pct = total > 1 ? (index / (total - 1)) * 100 : 0;
                progressBar.style.width = `${pct}%`;
            }
        };

        timelineSteps.forEach((step, i) => {
            step.type = 'button';
            step.addEventListener('click', () => setStep(i));
        });
        setStep(0);
    }

    // Fade-in animations for cards
    const fadeItems = Array.from(document.querySelectorAll('.stat-card, .service-card, .metric-card'));
    if (fadeItems.length) {
        const fadeObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -100px 0px' }
        );
        fadeItems.forEach((el) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            fadeObserver.observe(el);
        });
    }

    // Booking modal
    const bookingModal = document.getElementById('bookingModal');
    if (bookingModal) {
        const openButtons = Array.from(document.querySelectorAll('[data-book-service]'));
        const closeButtons = Array.from(bookingModal.querySelectorAll('[data-booking-close]'));
        const serviceEl = bookingModal.querySelector('[data-booking-service]');
        const descEl = bookingModal.querySelector('[data-booking-desc]');
        const toggleBtn = bookingModal.querySelector('[data-booking-toggle]');
        const statusEl = bookingModal.querySelector('[data-booking-status]');
        const noteEl = bookingModal.querySelector('[data-booking-note]');
        const ctaBtn = bookingModal.querySelector('.booking-cta');
        const nameInput = bookingModal.querySelector('[data-booking-name]');
        const emailInput = bookingModal.querySelector('[data-booking-email]');
        const phoneInput = bookingModal.querySelector('[data-booking-phone]');
        const monthLabel = bookingModal.querySelector('#bookingMonthLabel');
        const daysEl = bookingModal.querySelector('#bookingDays');
        const availabilityEl = bookingModal.querySelector('#bookingAvailability');
        const prevBtn = bookingModal.querySelector('[data-booking-prev]');
        const nextBtn = bookingModal.querySelector('[data-booking-next]');
        const slotButtons = Array.from(bookingModal.querySelectorAll('.time-slot'));

        let viewDate = new Date();
        let selectedDate = new Date();
        let selectedService = { title: '', slug: '' };

        const formatMonthYear = (date) => date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        const formatAvailability = (date) =>
            date.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
        const formatDate = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };
        const toTime24 = (label) => {
            const parts = String(label || '').trim().toLowerCase().split(' ');
            const time = parts[0] || '';
            const period = parts[1] || 'am';
            const tParts = time.split(':');
            let hour = parseInt(tParts[0] || '0', 10);
            const minute = String(tParts[1] || '00').padStart(2, '0');
            if (period === 'pm' && hour !== 12) hour += 12;
            if (period === 'am' && hour === 12) hour = 0;
            return `${String(hour).padStart(2, '0')}:${minute}`;
        };

        const normalizeBookedTime = (value) => {
            const raw = String(value || '').trim().toLowerCase();
            if (!raw) return '';
            if (raw.includes('am') || raw.includes('pm')) return toTime24(raw);
            const parts = raw.split(':');
            if (parts.length >= 2) {
                return `${String(parts[0]).padStart(2, '0')}:${String(parts[1]).padStart(2, '0')}`;
            }
            return raw;
        };

        slotButtons.forEach((btn) => {
            if (!btn.dataset.time24) btn.dataset.time24 = toTime24(btn.dataset.time || '');
        });

        const renderCalendar = () => {
            if (!monthLabel || !daysEl) return;
            const year = viewDate.getFullYear();
            const month = viewDate.getMonth();
            monthLabel.textContent = formatMonthYear(viewDate);

            const firstDay = new Date(year, month, 1);
            const startDay = firstDay.getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevMonthDays = new Date(year, month, 0).getDate();

            const cells = [];
            const totalCells = 42;
            for (let i = 0; i < totalCells; i++) {
                const dayNum = i - startDay + 1;
                if (dayNum <= 0) {
                    const prevDay = prevMonthDays + dayNum;
                    cells.push(`<button class="booking-day is-muted" type="button" disabled>${prevDay}</button>`);
                } else if (dayNum > daysInMonth) {
                    const nextDay = dayNum - daysInMonth;
                    cells.push(`<button class="booking-day is-muted" type="button" disabled>${nextDay}</button>`);
                } else {
                    const isSelected =
                        dayNum === selectedDate.getDate() &&
                        month === selectedDate.getMonth() &&
                        year === selectedDate.getFullYear();
                    cells.push(
                        `<button class="booking-day${isSelected ? ' is-selected' : ''}" type="button" data-day="${dayNum}">${dayNum}</button>`
                    );
                }
            }
            daysEl.innerHTML = cells.join('');
        };

        const updateAvailability = () => {
            if (!availabilityEl) return;
            availabilityEl.textContent = `Availability for ${formatAvailability(selectedDate)}`;
        };

        const setStatus = (message, isError = false) => {
            if (!statusEl) return;
            statusEl.textContent = message;
            statusEl.classList.toggle('is-error', isError);
            statusEl.hidden = !message;
        };

        const updateBookedSlots = (booked) => {
            const bookedSet = new Set((booked || []).map((val) => normalizeBookedTime(val)).filter(Boolean));
            let hasAvailable = false;
            let hasSelected = slotButtons.some((btn) => btn.classList.contains('is-selected'));
            slotButtons.forEach((slot) => {
                const time24 = slot.dataset.time24 || toTime24(slot.dataset.time || '');
                const isBooked = bookedSet.has(time24);
                slot.disabled = isBooked;
                slot.classList.toggle('is-booked', isBooked);
                slot.style.display = isBooked ? 'none' : '';
                slot.title = isBooked ? 'Already booked' : '';
                if (isBooked) slot.classList.remove('is-selected');
                if (!isBooked) hasAvailable = true;
            });
            if (!hasSelected && hasAvailable) {
                const first = slotButtons.find((btn) => !btn.disabled);
                if (first) first.classList.add('is-selected');
            }
            if (noteEl) {
                if (!hasAvailable) {
                    noteEl.textContent = 'All time slots are booked for this date. Please choose another date.';
                    noteEl.hidden = false;
                } else {
                    noteEl.textContent = '';
                    noteEl.hidden = true;
                }
            }
        };

        const loadBookedSlots = async () => {
            const dateStr = formatDate(selectedDate);
            try {
                const res = await fetch(`api/booking.php?date=${encodeURIComponent(dateStr)}`, {
                    headers: { 'X-Requested-With': 'fetch' }
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) return updateBookedSlots([]);
                updateBookedSlots(data.booked || []);
            } catch {
                updateBookedSlots([]);
            }
        };

        const openModal = (button) => {
            const name = button?.dataset.service || 'Service';
            const desc = button?.dataset.serviceDesc || '';
            const slug = button?.dataset.serviceSlug || '';
            selectedService = { title: name, slug };
            viewDate = new Date();
            selectedDate = new Date();
            if (nameInput) nameInput.value = '';
            if (emailInput) emailInput.value = '';
            if (phoneInput) phoneInput.value = '';
            if (serviceEl) serviceEl.textContent = name;
            if (descEl) descEl.textContent = desc || 'Details will be confirmed during the booking.';
            if (toggleBtn && descEl) {
                const hasDesc = Boolean(desc);
                toggleBtn.style.display = hasDesc ? 'inline-flex' : 'none';
                toggleBtn.setAttribute('aria-expanded', 'false');
                descEl.hidden = true;
            }
            setStatus('', false);
            slotButtons.forEach((slot) => slot.classList.remove('is-selected'));
            if (slotButtons[0]) slotButtons[0].classList.add('is-selected');
            renderCalendar();
            updateAvailability();
            loadBookedSlots();
            bookingModal.classList.add('is-open');
            bookingModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('booking-open');
        };

        const closeModal = () => {
            bookingModal.classList.remove('is-open');
            bookingModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('booking-open');
        };

        openButtons.forEach((btn) => btn.addEventListener('click', () => openModal(btn)));
        closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (bookingModal.classList.contains('is-open')) closeModal();
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
                selectedDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);
                renderCalendar();
                updateAvailability();
                loadBookedSlots();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
                selectedDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);
                renderCalendar();
                updateAvailability();
                loadBookedSlots();
            });
        }

        if (daysEl) {
            daysEl.addEventListener('click', (event) => {
                const target = event.target.closest('.booking-day');
                if (!target || target.classList.contains('is-muted')) return;
                const day = parseInt(target.dataset.day || '0', 10);
                if (!day) return;
                selectedDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), day);
                renderCalendar();
                updateAvailability();
                setStatus('', false);
                loadBookedSlots();
            });
        }

        slotButtons.forEach((slot) => {
            slot.addEventListener('click', () => {
                if (slot.disabled) return;
                slotButtons.forEach((btn) => btn.classList.remove('is-selected'));
                slot.classList.add('is-selected');
                setStatus('', false);
            });
        });

        if (toggleBtn && descEl) {
            toggleBtn.addEventListener('click', () => {
                const isOpen = toggleBtn.getAttribute('aria-expanded') === 'true';
                toggleBtn.setAttribute('aria-expanded', String(!isOpen));
                descEl.hidden = isOpen;
            });
        }

        if (ctaBtn) {
            ctaBtn.addEventListener('click', async () => {
                const selectedSlot = slotButtons.find((btn) => btn.classList.contains('is-selected') && !btn.disabled);
                if (!selectedSlot) {
                    setStatus('Please select an available time slot.', true);
                    return;
                }
                const name = String(nameInput?.value || '').trim();
                const email = String(emailInput?.value || '').trim();
                const phone = String(phoneInput?.value || '').trim();
                if (!name) {
                    setStatus('Please enter your name.', true);
                    nameInput?.focus();
                    return;
                }
                if (!email || !emailOk(email)) {
                    setStatus('Please enter a valid email.', true);
                    emailInput?.focus();
                    return;
                }
                if (!phone) {
                    setStatus('Please enter a phone number.', true);
                    phoneInput?.focus();
                    return;
                }

                const payload = new FormData();
                payload.set('service_title', selectedService.title || '');
                payload.set('service_slug', selectedService.slug || '');
                payload.set('slot_date', formatDate(selectedDate));
                payload.set('slot_time', selectedSlot.dataset.time || '');
                payload.set('name', name);
                payload.set('email', email);
                payload.set('phone', phone);

                setStatus('Submitting booking request...', false);
                ctaBtn.disabled = true;
                try {
                    const res = await fetch('api/booking.php', {
                        method: 'POST',
                        body: payload,
                        headers: { 'X-Requested-With': 'fetch' }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok) {
                        setStatus(data.errors?.[0] || 'This time is already booked. Please choose another.', true);
                        await loadBookedSlots();
                        return;
                    }
                    setStatus('Booking requested. We will confirm shortly.', false);
                    await loadBookedSlots();
                } catch {
                    setStatus('Network error. Please try again.', true);
                } finally {
                    ctaBtn.disabled = false;
                }
            });
        }
    }
})();
