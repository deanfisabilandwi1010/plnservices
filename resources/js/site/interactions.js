import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/** Category filter with a FLIP-style re-layout (portfolio page). */
function filters() {
    document.querySelectorAll('[data-filter-group]').forEach((group) => {
        const buttons = group.querySelectorAll('[data-filter]');
        const grid = document.querySelector(group.dataset.filterGroup);
        if (!grid) return;
        const items = [...grid.children];

        buttons.forEach((btn) =>
            btn.addEventListener('click', () => {
                const value = btn.dataset.filter;
                buttons.forEach((b) => b.classList.toggle('is-active', b === btn));

                const show = items.filter((i) => value === '*' || i.dataset.category === value);
                const hide = items.filter((i) => !show.includes(i));

                gsap.to(hide, { opacity: 0, scale: 0.92, duration: 0.3, ease: 'power2.in', onComplete: () => hide.forEach((i) => (i.style.display = 'none')) });
                gsap.delayedCall(0.3, () => {
                    show.forEach((i) => (i.style.display = ''));
                    gsap.fromTo(show, { opacity: 0, y: 30, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.6, stagger: 0.06, ease: 'power3.out' });
                    ScrollTrigger.refresh();
                });
            }),
        );
    });
}

/** Simple accordion: [data-accordion] > [data-accordion-item] > button + [data-accordion-panel] */
function accordions() {
    document.querySelectorAll('[data-accordion-item]').forEach((item) => {
        const button = item.querySelector('button');
        const panel = item.querySelector('[data-accordion-panel]');
        button.addEventListener('click', () => {
            const open = item.classList.toggle('is-open');
            gsap.to(panel, { height: open ? 'auto' : 0, duration: 0.5, ease: 'power3.inOut', onComplete: () => ScrollTrigger.refresh() });
            gsap.to(button.querySelector('[data-accordion-icon]'), { rotate: open ? 45 : 0, duration: 0.4 });
        });
    });
}

/** Reading progress on article pages. */
function readingProgress() {
    const bar = document.querySelector('[data-reading-progress]');
    const article = document.querySelector('[data-article]');
    if (!bar || !article) return;

    window.addEventListener('scroll', () => {
        const r = article.getBoundingClientRect();
        const ratio = Math.min(1, Math.max(0, -r.top / (r.height - window.innerHeight)));
        bar.style.transform = `scaleX(${ratio})`;
    }, { passive: true });
}

/** Live character counter: <span data-char-count="textarea-id">. */
function charCounters() {
    document.querySelectorAll('[data-char-count]').forEach((out) => {
        const input = document.getElementById(out.dataset.charCount);
        if (!input) return;
        const update = () => (out.textContent = input.value.length.toLocaleString('id-ID'));
        input.addEventListener('input', update);
        update();
    });
}

export function initInteractions() {
    charCounters();
    filters();
    accordions();
    readingProgress();
}
