(() => {
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const widget = document.getElementById('jsa-chatbot');
    if (!widget) return;

    const toggleBtn = widget.querySelector('[data-chat-toggle]');
    const panel = widget.querySelector('.chatbot-panel');
    const closeBtn = widget.querySelector('[data-chat-close]');
    const form = widget.querySelector('form');
    const input = widget.querySelector('textarea');
    const list = widget.querySelector('[data-chat-messages]');
    const chips = widget.querySelectorAll('[data-chat-chip]');

    const STORAGE_KEY = 'jsa_chat_history_v1';

    function loadHistory() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }

    function saveHistory(history) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(history.slice(-16)));
        } catch {}
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function linkify(text) {
        const esc = escapeHtml(text);
        const urlRe = /(https?:\/\/[^\s]+|(?:\/|[a-zA-Z0-9._-]+\/)?[a-zA-Z0-9._-]+\.(?:php|html)(?:\?[^\s]+)?)/g;
        return esc.replace(urlRe, (m) => {
            const href = m.startsWith('http') ? m : m;
            return `<a href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer">${escapeHtml(m)}</a>`;
        }).replaceAll('\n', '<br>');
    }

    function pushMessage(role, content) {
        const item = document.createElement('div');
        item.className = `chatbot-msg ${role === 'user' ? 'user' : 'assistant'}`;
        item.innerHTML = `<div class="bubble">${linkify(content)}</div>`;
        list.appendChild(item);
        list.scrollTop = list.scrollHeight;
    }

    function setOpen(open) {
        widget.classList.toggle('open', open);
        toggleBtn?.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel?.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) {
            panel?.setAttribute('tabindex', '-1');
        }
        if (open) {
            setTimeout(() => input?.focus(), prefersReducedMotion ? 0 : 120);
        }
    }

    function addTyping() {
        const item = document.createElement('div');
        item.className = 'chatbot-msg assistant';
        item.dataset.typing = '1';
        item.innerHTML = `<div class="bubble"><span class="dots" aria-label="Assistant is typing"><i></i><i></i><i></i></span></div>`;
        list.appendChild(item);
        list.scrollTop = list.scrollHeight;
    }

    function removeTyping() {
        const t = list.querySelector('[data-typing="1"]');
        if (t) t.remove();
    }

    function getHistoryForApi(history) {
        // Only send last 6 turns (already trimmed server-side too)
        return history.slice(-10).map((m) => ({ role: m.role, content: m.content }));
    }

    async function sendMessage(text) {
        const msg = String(text || '').trim();
        if (!msg) return;

        setOpen(true);
        pushMessage('user', msg);

        const history = loadHistory();
        history.push({ role: 'user', content: msg, ts: Date.now() });
        saveHistory(history);

        addTyping();

        try {
            const res = await fetch('api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg, history: getHistoryForApi(history) }),
            });
            const data = await res.json().catch(() => null);
            removeTyping();
            if (!res.ok || !data || !data.ok) {
                pushMessage('assistant', (data && (data.error || data.message)) ? String(data.error || data.message) : 'Sorry, something went wrong.');
                return;
            }
            const reply = String(data.reply || '').trim() || 'Okay.';
            pushMessage('assistant', reply);
            history.push({ role: 'assistant', content: reply, ts: Date.now() });
            saveHistory(history);
        } catch (e) {
            removeTyping();
            pushMessage('assistant', 'Network error. Please try again.');
        }
    }

    function restoreUiFromHistory() {
        const history = loadHistory();
        const last = history.slice(-8);
        if (!last.length) {
            pushMessage('assistant', 'Hi — I’m JSA Assistant. Ask me about services, pages, or finance/tax/GST basics.');
            return;
        }
        last.forEach((m) => {
            if (!m || !m.role || !m.content) return;
            pushMessage(m.role, m.content);
        });
    }

    toggleBtn?.addEventListener('click', () => setOpen(!widget.classList.contains('open')));
    closeBtn?.addEventListener('click', () => setOpen(false));

    // Clicking outside closes (desktop comfort)
    document.addEventListener('click', (e) => {
        if (!widget.classList.contains('open')) return;
        const t = e.target;
        if (!(t instanceof Node)) return;
        if (widget.contains(t)) return;
        setOpen(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && widget.classList.contains('open')) {
            setOpen(false);
        }
    });

    chips.forEach((c) => {
        c.addEventListener('click', () => {
            const text = c.getAttribute('data-chat-chip') || '';
            if (text) sendMessage(text);
        });
    });

    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        const val = input?.value || '';
        input.value = '';
        sendMessage(val);
    });

    input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form?.requestSubmit();
        }
    });

    restoreUiFromHistory();
})();
