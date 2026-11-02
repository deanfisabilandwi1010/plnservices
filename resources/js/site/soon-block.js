import gsap from 'gsap';

/**
 * Closed sections (components/soon-block): two hazard tapes crossed corner to corner in an
 * X. They snap on as the block scrolls in, lean with the pointer, run faster on hover and
 * twang when pressed.
 */
export function initSoonBlocks(reduceMotion) {
    document.querySelectorAll('[data-soon-x]').forEach((block) => {
        const tapes = [...block.querySelectorAll('[data-soon-tape]')];
        const bands = [...block.querySelectorAll('[data-soon-band]')];
        const ghost = block.querySelector('[data-soon-ghost]');
        const sign = (i) => (i ? -1 : 1);
        let angle = 0;

        const place = () => {
            angle = (Math.atan2(block.clientHeight, block.clientWidth) * 180) / Math.PI;
            tapes.forEach((tape, i) => gsap.set(tape, { rotation: angle * sign(i) }));
        };
        gsap.set(tapes, { xPercent: -50, yPercent: -50 });
        place();
        window.addEventListener('resize', place);

        if (reduceMotion) return;

        gsap.fromTo(bands, { scaleX: 0 }, {
            scaleX: 1,
            duration: 0.9,
            ease: 'power4.out',
            stagger: 0.18,
            scrollTrigger: { trigger: block, start: 'top 80%', once: true },
        });

        const tilt = tapes.map((tape) => gsap.quickTo(tape, 'rotation', { duration: 0.6, ease: 'power3.out' }));
        const lift = tapes.map((tape) => gsap.quickTo(tape, 'y', { duration: 0.6, ease: 'power3.out' }));
        const ghostX = gsap.quickTo(ghost, 'x', { duration: 0.8, ease: 'power3.out' });
        const ghostY = gsap.quickTo(ghost, 'y', { duration: 0.8, ease: 'power3.out' });
        const speed = (playbackRate) => {
            const runs = [...block.querySelectorAll('[data-soon-text]')].flatMap((text) => text.getAnimations());
            gsap.to(runs, { playbackRate, duration: 0.4, overwrite: true });
        };

        block.addEventListener('pointerenter', () => speed(3));
        block.addEventListener('pointermove', (e) => {
            const box = block.getBoundingClientRect();
            const px = (e.clientX - box.left) / box.width - 0.5;
            const py = (e.clientY - box.top) / box.height - 0.5;
            tapes.forEach((_, i) => {
                tilt[i]((angle + px * 7) * sign(i));
                lift[i](py * 16);
            });
            ghostX(px * -20);
            ghostY(py * -12);
        });
        block.addEventListener('pointerleave', () => {
            speed(1);
            tapes.forEach((_, i) => {
                tilt[i](angle * sign(i));
                lift[i](0);
            });
            ghostX(0);
            ghostY(0);
        });
        block.addEventListener('pointerdown', () => {
            gsap.fromTo(bands, { scaleY: 1.5 }, { scaleY: 1, duration: 0.9, ease: 'elastic.out(1, 0.3)', stagger: 0.05, overwrite: 'auto' });
        });
    });
}
