/**
 * Scroll-snap carousel with arrows, dots and optional autoplay.
 *
 * <div data-carousel data-autoplay="5000">
 *   <div data-carousel-track> …items… </div>
 *   <button data-carousel-prev></button><button data-carousel-next></button>
 *   <div data-carousel-dots></div>
 * </div>
 */
function setup(root) {
    const track = root.querySelector('[data-carousel-track]');
    const items = [...track.children];
    const prev = root.querySelector('[data-carousel-prev]');
    const next = root.querySelector('[data-carousel-next]');
    const dotsWrap = root.querySelector('[data-carousel-dots]');
    if (!items.length) return;

    const step = () => items[0].getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0);
    const perView = () => Math.max(1, Math.round(track.clientWidth / step()));
    const pages = () => Math.max(1, Math.ceil((items.length - perView()) / perView()) + 1);
    const page = () => Math.round(track.scrollLeft / (step() * perView()));

    const go = (index) => {
        const total = pages();
        const target = ((index % total) + total) % total;
        track.scrollTo({ left: target * step() * perView(), behavior: 'smooth' });
    };

    const renderDots = () => {
        if (!dotsWrap) return;
        dotsWrap.innerHTML = '';
        for (let i = 0; i < pages(); i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'h-2.5 rounded-full bg-brand/25 transition-all duration-300';
            dot.setAttribute('aria-label', `Slide ${i + 1}`);
            dot.addEventListener('click', () => go(i));
            dotsWrap.append(dot);
        }
        syncDots();
    };

    const syncDots = () => {
        [...(dotsWrap?.children ?? [])].forEach((dot, i) => {
            const active = i === page();
            dot.classList.toggle('w-8', active);
            dot.classList.toggle('!bg-brand', active);
            dot.classList.toggle('w-2.5', !active);
        });
    };

    prev?.addEventListener('click', () => go(page() - 1));
    next?.addEventListener('click', () => go(page() + 1));
    track.addEventListener('scroll', () => requestAnimationFrame(syncDots), { passive: true });
    window.addEventListener('resize', renderDots);
    renderDots();

    const delay = Number(root.dataset.autoplay);
    if (delay && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        let timer = setInterval(() => go(page() + 1), delay);
        const pause = () => clearInterval(timer);
        const resume = () => {
            clearInterval(timer);
            timer = setInterval(() => go(page() + 1), delay);
        };
        root.addEventListener('pointerenter', pause);
        root.addEventListener('pointerleave', resume);
        root.addEventListener('focusin', pause);
    }
}

export function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach(setup);
}
