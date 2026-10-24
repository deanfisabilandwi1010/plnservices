import gsap from 'gsap';

const html = document.documentElement;
const FLAG = 'pln:transition'; // set while navigating between pages
const STATE = 'pln:nav'; // { dir, bar: { left, width } } from the previous page

/** First visit: logo fades in, a thin line fills, then the loader fades away. */
function runLoader() {
    return new Promise((resolve) => {
        const el = document.querySelector('[data-loader]');

        try {
            sessionStorage.setItem('pln:visited', '1');
        } catch (e) {}

        const done = () => {
            html.classList.remove('show-loader');
            resolve();
        };
        setTimeout(done, 2500); // safety net if animation frames are throttled

        gsap.timeline({ onComplete: done })
            .fromTo(el.querySelector('[data-loader-logo]'), { opacity: 0, y: 8 }, { opacity: 1, y: 0, duration: 0.45, ease: 'power2.out' })
            .to(el.querySelector('[data-loader-bar]'), { scaleX: 1, duration: 0.7, ease: 'power2.inOut' }, '-=0.15')
            .to(el, { opacity: 0, duration: 0.35, ease: 'power1.out' }, '+=0.05');
    });
}

const readState = () => {
    try {
        const raw = sessionStorage.getItem(STATE);
        sessionStorage.removeItem(STATE);
        return raw ? JSON.parse(raw) : {};
    } catch (e) {
        return {};
    }
};

const wipeParts = () => {
    const wipe = document.querySelector('[data-wipe]');
    return wipe && {
        panels: [...wipe.querySelectorAll('[data-wipe-panel]')],
        label: wipe.querySelector('[data-wipe-label]'),
        title: wipe.querySelector('[data-wipe-title]'),
    };
};

/**
 * Arrival from an internal link: the brand panels (still covering the
 * content) sweep away in the direction of travel, the progress bar
 * completes, and the active-menu bar glides from the previous item.
 */
function arrive() {
    const { dir = 'forward', bar: from, title = '' } = readState();
    const w = wipeParts();
    const progress = document.querySelector('[data-nav-progress]');
    const bar = document.querySelector('.nav-active-bar');
    const exitTo = dir === 'back' ? 101 : -101; // forward: leave towards the left

    w.title.textContent = title;

    gsap.timeline()
        .fromTo(progress, { scaleX: 0.8, opacity: 1 }, { scaleX: 1, duration: 0.25, ease: 'power2.out' })
        .to(progress, { opacity: 0, duration: 0.3, onComplete: () => gsap.set(progress, { clearProps: 'all' }) }, '+=0.05');

    const reveal = () => {
        if (!html.classList.contains('is-arriving')) return;
        html.classList.remove('is-arriving');
        gsap.set([...w.panels, w.label], { clearProps: 'all' });
    };

    gsap.timeline({ delay: 0.1, onComplete: reveal })
        .to(w.label, { opacity: 0, x: exitTo * 0.3, duration: 0.25, ease: 'power2.in' })
        .to([...w.panels].reverse(), { xPercent: exitTo, duration: 0.6, stagger: 0.07, ease: 'power3.inOut' }, 0.1);
    // Safety net: never leave the content covered if animation frames are throttled.
    setTimeout(reveal, 1500);

    if (bar && from?.width) {
        const to = bar.getBoundingClientRect();
        gsap.fromTo(bar, { x: from.left - to.left, scaleX: from.width / to.width, transformOrigin: 'left center' }, {
            x: 0,
            scaleX: 1,
            duration: 0.6,
            ease: 'expo.out',
            clearProps: 'transform',
        });
    }
}

/** Resolves with the delay (s) page intros should wait for. */
export async function introDelay(reduceMotion) {
    if (reduceMotion) {
        html.classList.remove('show-loader', 'is-arriving');
        return 0;
    }

    if (html.classList.contains('show-loader')) {
        await runLoader();
        return 0;
    }

    if (html.classList.contains('is-arriving')) {
        arrive();
        return 0.45; // headings reveal as the panels clear
    }

    return 0.1;
}

function isInternalNavigation(link, event) {
    if (!link || event.defaultPrevented) return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return false;
    if (link.target && link.target !== '_self') return false;
    if (link.hasAttribute('download')) return false;

    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin) return false;
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/storage')) return false;
    if (url.pathname === location.pathname && url.search === location.search) return false;

    return true;
}

/** Position of a page in the main menu (detail pages count as their section). */
function navIndex(pathname) {
    let best = { index: -1, score: -1 };
    document.querySelectorAll('[data-header] [data-nav-index]').forEach((a) => {
        const path = new URL(a.href).pathname;
        const matches = path === '/' ? pathname === '/' : pathname === path || pathname.startsWith(path + '/');
        // Longest path wins; on a tie prefer the top-level menu item over a dropdown link.
        const score = path.length * 2 + (a.classList.contains('nav-link') ? 1 : 0);
        if (matches && score > best.score) best = { index: Number(a.dataset.navIndex), score };
    });
    return best.index;
}

/** Name shown on the wipe: the menu label for header links, otherwise the destination's menu section. */
function destinationTitle(link, index) {
    if (link.closest('[data-header]')) return link.textContent.trim().replace(/\s+/g, ' ');
    const section = document.querySelector(`[data-header] a.nav-link[data-nav-index="${index}"]`);
    return section ? section.textContent.trim() : 'PLN Services';
}

/**
 * Internal links: two flat brand panels sweep over the content area from the
 * destination's side of the menu, showing the page name, then we navigate.
 */
export function initTransitions(reduceMotion) {
    if (reduceMotion) return;
    const w = wipeParts();
    if (!w) return;

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!isInternalNavigation(link, event)) return;

        event.preventDefault();
        const href = link.href;
        const to = navIndex(new URL(href).pathname);
        const from = navIndex(location.pathname);
        const dir = to !== -1 && from !== -1 && to < from ? 'back' : 'forward';
        const bar = document.querySelector('.nav-active-bar')?.getBoundingClientRect();
        const title = destinationTitle(link, to);
        const enterFrom = dir === 'back' ? -101 : 101; // forward: sweep in from the right

        try {
            sessionStorage.setItem(FLAG, '1');
            sessionStorage.setItem(STATE, JSON.stringify({ dir, title, bar: bar ? { left: bar.left, width: bar.width } : null }));
        } catch (e) {}

        document.querySelector('[data-header]')?.classList.remove('is-hidden');
        w.title.textContent = title;
        html.classList.add('is-leaving');
        window.lenis?.stop();

        // Navigate when the sweep ends — or after a timeout, so a throttled
        // animation frame (background tab) never blocks the link.
        let gone = false;
        const go = () => {
            if (gone) return;
            gone = true;
            window.location.href = href;
        };
        setTimeout(go, 900);

        gsap.timeline({ onComplete: go })
            .set(w.panels, { x: 0, xPercent: enterFrom }) // x:0 — ignore the CSS translateX(-101%) GSAP would read as pixels
            .set(w.label, { opacity: 0, x: enterFrom * 0.3 })
            .to(w.panels, { xPercent: 0, duration: 0.55, stagger: 0.07, ease: 'power3.inOut' })
            .to(w.label, { opacity: 1, x: 0, duration: 0.3, ease: 'power2.out' }, '-=0.2');
    });

    // Restore when coming back through the back/forward cache.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;
        html.classList.remove('is-leaving', 'is-arriving');
        gsap.set([...w.panels, w.label], { clearProps: 'all' });
        window.lenis?.start();
        try {
            sessionStorage.removeItem(FLAG);
            sessionStorage.removeItem(STATE);
        } catch (e) {}
    });
}
