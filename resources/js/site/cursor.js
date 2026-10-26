import gsap from 'gsap';

/**
 * A ring that trails the pointer (desktop only). It grows over links and
 * buttons, turns into a labelled disc over [data-cursor-label] elements and
 * steps aside over text fields. The native cursor stays visible.
 */
export function initCursor(reduceMotion) {
    if (reduceMotion || !window.matchMedia('(pointer: fine)').matches) return;

    const ring = document.createElement('div');
    ring.className = 'cursor-ring';
    ring.setAttribute('aria-hidden', 'true');
    ring.append(document.createElement('span'));
    document.body.append(ring);

    const x = gsap.quickTo(ring, 'x', { duration: 0.45, ease: 'power3.out' });
    const y = gsap.quickTo(ring, 'y', { duration: 0.45, ease: 'power3.out' });

    window.addEventListener('pointermove', (e) => {
        if (!ring.classList.contains('is-visible')) gsap.set(ring, { x: e.clientX, y: e.clientY });
        ring.classList.add('is-visible');
        x(e.clientX);
        y(e.clientY);
    });
    document.documentElement.addEventListener('pointerleave', () => ring.classList.remove('is-visible'));

    document.addEventListener('pointerover', (e) => {
        const labelled = e.target.closest('[data-cursor-label]');
        const text = e.target.closest('input, textarea, select, [contenteditable]');
        const link = e.target.closest('a, button, label, [role="button"], summary');

        ring.classList.toggle('is-label', !!labelled);
        ring.classList.toggle('is-text', !!text);
        ring.classList.toggle('is-link', !labelled && !text && !!link);
        ring.firstChild.textContent = labelled?.dataset.cursorLabel || '';
    });

    window.addEventListener('pointerdown', () => ring.classList.add('is-down'));
    window.addEventListener('pointerup', () => ring.classList.remove('is-down'));
}
