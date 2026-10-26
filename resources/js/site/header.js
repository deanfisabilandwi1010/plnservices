import gsap from 'gsap';

function initMobileMenu(lenis) {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (!toggle || !menu) return;

    const panel = menu.querySelector('[data-menu-panel]');
    const aside = menu.querySelector('[data-menu-aside]');
    const items = menu.querySelectorAll('[data-mobile-item]');
    let open = false;

    // Two panels: the menu swings in from the left with a slight skew, contact details follow from the right.
    const tl = gsap.timeline({ paused: true })
        .set(menu, { display: 'flex' })
        .fromTo(panel, { xPercent: -100, skewX: -12, opacity: 0 }, { xPercent: 0, skewX: 0, opacity: 1, duration: 0.5, ease: 'power3.out' })
        .fromTo(aside, { xPercent: 40, opacity: 0 }, { xPercent: 0, opacity: 1, duration: 0.45, ease: 'power3.out' }, 0.2)
        .from(items, { x: -20, opacity: 0, stagger: 0.04, duration: 0.35, ease: 'power2.out' }, 0.2);

    toggle.addEventListener('click', () => {
        open = !open;
        toggle.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';
        open ? (lenis?.stop(), tl.timeScale(1).play()) : (lenis?.start(), tl.timeScale(1.5).reverse());
    });
}

export function initHeader(lenis) {
    const header = document.querySelector('[data-header]');
    const toTop = document.querySelector('[data-to-top]');
    let last = 0;

    const onScroll = () => {
        const y = window.scrollY;
        header?.classList.toggle('is-scrolled', y > 10);
        header?.classList.toggle('is-hidden', y > last && y > 500 && !document.body.style.overflow);
        last = y;

        toTop?.classList.toggle('opacity-0', y < 700);
        toTop?.classList.toggle('pointer-events-none', y < 700);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    toTop?.addEventListener('click', () => (lenis ? lenis.scrollTo(0, { duration: 1.2 }) : window.scrollTo({ top: 0, behavior: 'smooth' })));

    // Smooth-scroll in-page anchors, clearing the fixed header (and the section bar, if any).
    const anchorOffset = () => -(header?.offsetHeight || 0) - (document.querySelector('[data-section-nav]')?.offsetHeight || 0) + 1;
    document.querySelectorAll('a[href^="#"]').forEach((a) =>
        a.addEventListener('click', (e) => {
            const target = a.getAttribute('href').length > 1 && document.querySelector(a.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            lenis ? lenis.scrollTo(target, { offset: anchorOffset() }) : target.scrollIntoView({ behavior: 'smooth' });
        }),
    );

    initMobileMenu(lenis);
}
