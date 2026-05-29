(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const clamp = (n, min, max) => Math.max(min, Math.min(max, n));

    class Slider {
        constructor(root) {
            this.root = root;
            this.track = root.querySelector('.slider-track');
            this.slides = Array.from(root.querySelectorAll('.slide'));
            this.dots = root.querySelector('.slider-dots');
            this.prevBtn = root.querySelector('[data-slider-prev]');
            this.nextBtn = root.querySelector('[data-slider-next]');
            this.index = 0;
            this.timer = null;
            this.autoplayMs = clamp(parseInt(root.dataset.autoplay || '5600', 10), 3500, 9000);
            this.isPaused = false;

            this.pointerDown = false;
            this.startX = 0;
            this.deltaX = 0;

            this.init();
        }

        init() {
            if (!this.track || this.slides.length === 0) return;

            if (this.dots) {
                this.dots.innerHTML = '';
                this.slides.forEach((_, i) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'dot';
                    b.setAttribute('aria-label', `Go to slide ${i + 1}`);
                    b.addEventListener('click', () => this.goTo(i));
                    this.dots.appendChild(b);
                });
            }

            this.prevBtn?.addEventListener('click', () => this.prev());
            this.nextBtn?.addEventListener('click', () => this.next());

            this.root.addEventListener('mouseenter', () => this.pause());
            this.root.addEventListener('mouseleave', () => this.resume());
            this.root.addEventListener('focusin', () => this.pause());
            this.root.addEventListener('focusout', () => this.resume());

            this.root.addEventListener('pointerdown', (e) => this.onPointerDown(e));
            this.root.addEventListener('pointermove', (e) => this.onPointerMove(e));
            this.root.addEventListener('pointerup', () => this.onPointerUp());
            this.root.addEventListener('pointercancel', () => this.onPointerUp());

            window.addEventListener('resize', () => this.render());

            this.goTo(0, true);
            if (prefersReducedMotion) {
                this.track.classList.add('no-anim');
            } else {
                this.startAutoplay();
            }
        }

        pause() {
            this.isPaused = true;
            this.stopAutoplay();
        }

        resume() {
            this.isPaused = false;
            if (!prefersReducedMotion) this.startAutoplay();
        }

        startAutoplay() {
            this.stopAutoplay();
            this.timer = window.setInterval(() => this.next(), this.autoplayMs);
        }

        stopAutoplay() {
            if (this.timer) window.clearInterval(this.timer);
            this.timer = null;
        }

        onPointerDown(e) {
            if (prefersReducedMotion) return;
            if (!e.isPrimary) return;
            this.pointerDown = true;
            this.startX = e.clientX;
            this.deltaX = 0;
            this.track.classList.add('dragging');
            this.pause();
        }

        onPointerMove(e) {
            if (!this.pointerDown) return;
            this.deltaX = e.clientX - this.startX;
            const width = this.root.getBoundingClientRect().width;
            const offset = -this.index * width + this.deltaX;
            this.track.style.transform = `translate3d(${offset}px, 0, 0)`;
        }

        onPointerUp() {
            if (!this.pointerDown) return;
            this.pointerDown = false;
            this.track.classList.remove('dragging');

            const width = this.root.getBoundingClientRect().width;
            const threshold = width * 0.18;
            if (this.deltaX > threshold) this.prev();
            else if (this.deltaX < -threshold) this.next();
            else this.render();

            this.resume();
        }

        prev() {
            this.goTo((this.index - 1 + this.slides.length) % this.slides.length);
        }

        next() {
            this.goTo((this.index + 1) % this.slides.length);
        }

        goTo(i, immediate = false) {
            this.index = clamp(i, 0, this.slides.length - 1);
            this.root.dataset.index = String(this.index);
            this.render(immediate);
        }

        render(immediate = false) {
            const width = this.root.getBoundingClientRect().width;
            if (prefersReducedMotion) {
                this.track.classList.add('no-anim');
            } else if (immediate) {
                this.track.classList.add('no-anim');
            } else {
                this.track.classList.remove('no-anim');
            }

            const offset = -this.index * width;
            this.track.style.transform = `translate3d(${offset}px, 0, 0)`;

            this.slides.forEach((s, idx) => s.setAttribute('aria-hidden', idx === this.index ? 'false' : 'true'));
            if (this.dots) {
                Array.from(this.dots.querySelectorAll('.dot')).forEach((d, idx) => d.classList.toggle('active', idx === this.index));
            }

            if (!prefersReducedMotion && immediate) {
                requestAnimationFrame(() => this.track.classList.remove('no-anim'));
            }
        }
    }

    document.querySelectorAll('[data-slider]').forEach((el) => new Slider(el));
})();
