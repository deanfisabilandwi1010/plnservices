import gsap from 'gsap';

/**
 * Soft launch: links into sections that aren't ready (home cards, footer, CTAs, ...) stay
 * in place but don't navigate. Hovering shows "Coming Soon" on the cursor; a tap (or Enter)
 * pops the same words at the pointer. Delegated, so links rendered later are covered too.
 */
export function initSoonLinks() {
    const closed = document.body.dataset.closedPaths?.split(',');
    if (!closed) return;

    const closedLink = (target) => {
        const link = target.closest?.('a[href]');
        if (!link) return null;
        const url = new URL(link.href, location.href);
        return url.origin === location.origin && closed.includes(url.pathname.split('/')[1]) ? link : null;
    };

    const toast = document.createElement('div');
    toast.className = 'soon-toast';
    toast.setAttribute('role', 'status');
    toast.innerHTML = '<i></i><span></span>';
    toast.children[1].textContent = 'Coming Soon';
    document.body.append(toast);

    const pop = (x, y) => {
        const left = gsap.utils.clamp(70, window.innerWidth - 70, x);
        gsap.killTweensOf(toast);
        gsap.timeline()
            .set(toast, { left, top: Math.max(100, y) - 14 })
            .fromTo(toast, { opacity: 0, yPercent: -60, scale: 0.8 }, { opacity: 1, yPercent: -120, scale: 1, duration: 0.3, ease: 'back.out(2.5)' })
            .to(toast, { opacity: 0, duration: 0.3 }, '+=1.1');
    };

    // Capture phase: before the cursor's hover check and the page-transition click handler.
    document.addEventListener(
        'pointerover',
        (e) => {
            const link = closedLink(e.target);
            if (!link) return;
            link.classList.add('is-soon');
            link.dataset.cursorLabel = 'Coming Soon';
        },
        true,
    );

    document.addEventListener(
        'click',
        (e) => {
            const link = closedLink(e.target);
            if (!link) return;
            e.preventDefault();
            e.stopPropagation();
            if (e.detail && document.querySelector('.cursor-ring')) return; // the cursor already says it
            const box = link.getBoundingClientRect();
            pop(e.clientX || box.left + box.width / 2, e.clientY || box.top);
        },
        true,
    );
}
