import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * Editorial interactions for the inner pages: line drawings that connect the
 * dots as you scroll, a guide line that runs through the sections and lights
 * up their nodes, a sticky section bar with scroll-spy, accordions, marker
 * highlights and hover previews.
 */

/** <x-line-art>: dots pop in and lines draw between them, in document order. */
function lineArt(delay) {
    document.querySelectorAll('[data-line-art]').forEach((svg) => {
        const tl = gsap.timeline({ paused: true });

        svg.querySelectorAll('[data-la]').forEach((el, i) => {
            const at = i * 0.09;
            if (el.hasAttribute('fill')) {
                tl.fromTo(el, { scale: 0, transformOrigin: '50% 50%' }, { scale: 1, duration: 0.4, ease: 'back.out(2.2)' }, at);
            } else {
                const length = el.getTotalLength();
                tl.fromTo(el, { strokeDasharray: length, strokeDashoffset: length, opacity: 0 }, { strokeDashoffset: 0, opacity: 1, duration: 0.55, ease: 'power1.inOut' }, at);
            }
        });

        const flows = svg.querySelectorAll('[data-la-flow]');
        if (flows.length) tl.fromTo(flows, { opacity: 0 }, { opacity: 1, duration: 0.4 });

        svg.classList.add('is-ready');

        if (svg.dataset.lineArt === 'load') {
            tl.delay(delay + 0.2).play();
        } else {
            ScrollTrigger.create({ trigger: svg, start: 'top 85%', end: 'bottom 55%', scrub: 0.8, animation: tl });
        }
    });
}

/** [data-guide]: a vertical line that grows with the scroll; nodes light up as its head passes. */
function guideLines() {
    document.querySelectorAll('[data-guide]').forEach((wrap) => {
        const fill = wrap.querySelector('[data-guide-fill]');
        const head = wrap.querySelector('[data-guide-head]');

        gsap.set(fill, { scaleY: 0, transformOrigin: 'top center' });
        gsap.set(head, { top: 0, opacity: 0 });

        ScrollTrigger.create({
            trigger: wrap,
            start: 'top 55%',
            end: 'bottom 55%',
            onUpdate: ({ progress }) => {
                gsap.set(fill, { scaleY: progress });
                gsap.set(head, { top: `${progress * 100}%`, opacity: progress > 0 && progress < 1 ? 1 : 0 });
            },
        });

        wrap.querySelectorAll('[data-guide-node]').forEach((node) =>
            ScrollTrigger.create({ trigger: node, start: 'center 55%', end: 'max', toggleClass: 'is-lit' }),
        );
    });
}

/** [data-section-nav]: sticky in-page menu whose underline follows the section in view. */
function sectionNavs(reduceMotion) {
    document.querySelectorAll('[data-section-nav]').forEach((nav) => {
        const scroller = nav.querySelector('[data-section-scroller]');
        const bar = nav.querySelector('[data-section-bar]');
        const links = [...nav.querySelectorAll('a[href^="#"]')];

        const activate = (link) => {
            links.forEach((l) => l.classList.toggle('is-active', l === link));
            if (!link) return gsap.to(bar, { opacity: 0, duration: 0.2 });

            gsap.to(bar, { x: link.offsetLeft, width: link.offsetWidth, opacity: 1, duration: reduceMotion ? 0 : 0.5, ease: 'expo.out' });
            scroller?.scrollTo({ left: link.offsetLeft - 24, behavior: reduceMotion ? 'auto' : 'smooth' });
        };

        links.forEach((link, i) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;

            ScrollTrigger.create({
                trigger: target,
                start: 'top 50%',
                end: 'bottom 50%',
                onToggle: (self) => self.isActive && activate(link),
                onLeaveBack: () => i === 0 && activate(null),
            });
        });

        ScrollTrigger.create({ trigger: nav, start: 'top 81px', end: 'max', toggleClass: 'is-stuck' });
    });
}

/** [data-accordion]: one item open at a time; the body height animates in CSS. */
function accordions() {
    document.querySelectorAll('[data-accordion]').forEach((list) => {
        const items = [...list.querySelectorAll('[data-acc-item]')];

        items.forEach((item) =>
            item.querySelector('[data-acc-toggle]').addEventListener('click', () => {
                const open = !item.classList.contains('is-open');
                items.forEach((other) => {
                    const isOpen = other === item && open;
                    other.classList.toggle('is-open', isOpen);
                    other.querySelector('[data-acc-toggle]').setAttribute('aria-expanded', String(isOpen));
                });
                setTimeout(() => ScrollTrigger.refresh(), 600); // page height changed
            }),
        );
    });
}

/** .mark: a flat marker sweeps under key phrases when they scroll into view. */
function marks(reduceMotion) {
    document.querySelectorAll('.mark').forEach((el) => {
        if (reduceMotion) return el.classList.add('is-marked');
        ScrollTrigger.create({ trigger: el, start: 'top 82%', once: true, onEnter: () => el.classList.add('is-marked') });
    });
}

/** [data-preview]: hovering or focusing an item swaps the matching preview pane. */
function previews() {
    document.querySelectorAll('[data-preview]').forEach((root) => {
        const panes = root.querySelectorAll('[data-preview-pane]');
        const show = (key) => panes.forEach((pane) => pane.classList.toggle('is-active', pane.dataset.previewPane === key));

        root.querySelectorAll('[data-preview-key]').forEach((item) => {
            const key = item.dataset.previewKey;
            item.addEventListener('mouseenter', () => show(key));
            item.addEventListener('focus', () => show(key));
        });
    });
}

export function initEditorial(reduceMotion, delay = 0) {
    sectionNavs(reduceMotion);
    accordions();
    previews();
    marks(reduceMotion);

    if (!reduceMotion) {
        lineArt(delay);
        guideLines();
    }
}
