import gsap from 'gsap';

function initMobileMenu(lenis) {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (!toggle || !menu) return;

    const items = menu.querySelectorAll('[data-mobile-item]');
    let open = false;

    const tl = gsap.timeline({ paused: true })
        .set(menu, { display: 'flex' })
        .fromTo(menu, { yPercent: -100 }, { yPercent: 0, duration: 0.5, ease: 'power3.inOut' })
        .from(items, { y: 16, opacity: 0, stagger: 0.04, duration: 0.35, ease: 'power2.out' }, '-=0.15');

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

    // Smooth-scroll in-page anchors, clearing the fixed header.
    document.querySelectorAll('a[href^="#"]').forEach((a) =>
        a.addEventListener('click', (e) => {
            const target = document.querySelector(a.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            lenis ? lenis.scrollTo(target, { offset: 0 }) : target.scrollIntoView({ behavior: 'smooth' });
        }),
    );

    initMobileMenu(lenis);
}
