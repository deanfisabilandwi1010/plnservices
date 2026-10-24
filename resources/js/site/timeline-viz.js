import gsap from 'gsap';

/** Interactive project/news timeline: tooltips, category filter and scroll-in animation. */
export function initTimelineViz(reduceMotion) {
    const section = document.querySelector('[data-timeline]');
    if (!section) return;

    const body = section.querySelector('[data-tl-body]');
    const tip = section.querySelector('[data-tl-tip]');
    const marks = [...section.querySelectorAll('[data-tl-mark]')];
    const hidden = new Set();

    const show = (mark) => {
        tip.style.background = mark.dataset.color;
        tip.innerHTML = '';
        tip.append(
            Object.assign(document.createElement('p'), { className: 'text-xs text-white/85', textContent: mark.dataset.meta }),
            Object.assign(document.createElement('p'), { className: 'mt-1 font-display font-bold leading-snug', textContent: mark.dataset.title }),
        );
        const r = mark.getBoundingClientRect();
        const box = body.getBoundingClientRect();
        const left = Math.min(Math.max(0, r.left - box.left - 120), box.width - 256);
        const top = r.top - box.top - tip.offsetHeight - 10;
        tip.style.transform = `translate(${left}px, ${top < 0 ? r.bottom - box.top + 10 : top}px)`;
        tip.style.opacity = '1';
    };
    const hide = () => (tip.style.opacity = '0');

    marks.forEach((mark) => {
        mark.addEventListener('pointerenter', () => show(mark));
        mark.addEventListener('focus', () => show(mark));
        mark.addEventListener('pointerleave', hide);
        mark.addEventListener('blur', hide);
    });

    section.querySelectorAll('[data-tl-filter]').forEach((button) =>
        button.addEventListener('click', () => {
            const category = button.dataset.tlFilter;
            hidden.has(category) ? hidden.delete(category) : hidden.add(category);
            button.setAttribute('aria-pressed', String(!hidden.has(category)));
            marks.forEach((m) => {
                const off = m.dataset.category && hidden.has(m.dataset.category);
                m.style.opacity = off ? '0.12' : '1';
                m.tabIndex = off ? -1 : 0;
            });
        }),
    );

    if (reduceMotion) return;

    // Rows draw their axis segment and pop their squares in as they enter.
    section.querySelectorAll('[data-tl-row]').forEach((row) => {
        const marksInRow = row.querySelectorAll('[data-tl-mark]');
        const notes = row.querySelectorAll('[data-tl-axis] ~ span');
        const tl = gsap.timeline({ scrollTrigger: { trigger: row, start: 'top 85%' } });
        tl.fromTo(row.querySelector('[data-tl-axis]'), { scaleY: 0 }, { scaleY: 1, duration: 0.6, ease: 'power2.out' });
        if (marksInRow.length) tl.fromTo(marksInRow, { scale: 0 }, { scale: 1, duration: 0.4, stagger: 0.04, ease: 'back.out(2)' }, 0.1);
        if (notes.length) tl.fromTo(notes, { opacity: 0, y: 8 }, { opacity: 1, y: 0, duration: 0.4, stagger: 0.08 }, 0.1);
    });
}
