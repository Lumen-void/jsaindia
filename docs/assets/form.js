(() => {
    const inquiryForms = Array.from(document.querySelectorAll('form[data-ajax="inquiry"]'));
    const leadForms = Array.from(document.querySelectorAll('form[data-ajax="lead"]'));
    const subscribeForms = Array.from(document.querySelectorAll('form[data-ajax="subscribe"]'));
    if (!inquiryForms.length && !leadForms.length && !subscribeForms.length) return;

    // Prefill inquiry forms from query params (for wizards and CTAs)
    try {
        const params = new URLSearchParams(window.location.search);
        const service = String(params.get('service') || '').trim();
        const source = String(params.get('source') || '').trim();
        const message = String(params.get('message') || '').trim();
        const slot = String(params.get('slot') || '').trim();

        if (service || source || message || slot) {
            inquiryForms.forEach((form) => {
                const svcSelect = form.querySelector('select[name="selected_service"]');
                if (svcSelect && service) {
                    const options = Array.from(svcSelect.querySelectorAll('option'));
                    const match = options.find((o) => String(o.value || '').toLowerCase() === service.toLowerCase());
                    if (match) svcSelect.value = match.value;
                }

                const slotSelect = form.querySelector('select[name="preferred_slot"]');
                if (slotSelect && slot) {
                    const options = Array.from(slotSelect.querySelectorAll('option'));
                    const match = options.find((o) => String(o.value || '').toLowerCase() === slot.toLowerCase());
                    if (match) slotSelect.value = match.value;
                }

                const msgEl = form.querySelector('textarea[name="message"]');
                if (msgEl) {
                    const parts = [];
                    if (source) parts.push(`Source: ${source}`);
                    if (service) parts.push(`Service: ${service}`);
                    if (message) parts.push(message);
                    const next = parts.filter(Boolean).join('\n');
                    if (next && !msgEl.value) msgEl.value = next;
                }
            });
        }
    } catch {
        // ignore
    }

    const emailOk = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    const setStatus = (form, type, message) => {
        let box = form.querySelector('.form-status');
        if (!box) {
            box = document.createElement('div');
            box.className = 'form-status';
            box.setAttribute('aria-live', 'polite');
            form.prepend(box);
        }
        box.classList.toggle('alert', true);
        box.classList.toggle('error', type === 'error');
        box.textContent = message;
    };

    const attach = (form, endpoint, validate) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fd = new FormData(form);
            if (!fd.get('form_type')) fd.set('form_type', endpoint === 'api/inquiry.php' ? 'inquiry' : 'lead');

            const errors = validate(fd);
            if (errors.length) return setStatus(form, 'error', errors.join(' '));

            const btn = form.querySelector('button[type="submit"], .btn[type="submit"]');
            const prevText = btn?.textContent;
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Submitting...';
            }

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'fetch' }
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    setStatus(form, 'error', (data.errors || ['Something went wrong. Please try again.']).join(' '));
                } else {
                    setStatus(form, 'success', data.message || 'Thanks. We will get back to you shortly.');
                    form.reset();
                    if (endpoint === 'api/lead.php') {
                        setTimeout(() => {
                            window.open('assets/jsa-compliance-calendar.txt', '_blank');
                        }, 200);
                    }
                }
            } catch (err) {
                setStatus(form, 'error', 'Network error. Please try again.');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = prevText || 'Submit';
                }
            }
        });
    };

    inquiryForms.forEach((form) =>
        attach(form, 'api/inquiry.php', (fd) => {
            const name = String(fd.get('name') || '').trim();
            const email = String(fd.get('email') || '').trim();
            const phone = String(fd.get('phone') || '').trim();
            const message = String(fd.get('message') || '').trim();
            const errors = [];
            if (!name) errors.push('Name is required.');
            if (!email || !emailOk(email)) errors.push('Valid email is required.');
            if (!phone) errors.push('Phone is required.');
            if (!message) errors.push('Message is required.');
            return errors;
        })
    );

    leadForms.forEach((form) =>
        attach(form, 'api/lead.php', (fd) => {
            const email = String(fd.get('email') || '').trim();
            const errors = [];
            if (!email || !emailOk(email)) errors.push('Valid email is required.');
            return errors;
        })
    );

    subscribeForms.forEach((form) =>
        attach(form, 'api/subscribe.php', (fd) => {
            const email = String(fd.get('email') || '').trim();
            const errors = [];
            if (!email || !emailOk(email)) errors.push('Valid email is required.');
            return errors;
        })
    );
})();
