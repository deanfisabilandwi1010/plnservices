import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * Home hero (components/hero-venn): two coloured circles whose intersection
 * (the "lens") frames a project photo. Motion follows a three-beat rhythm:
 *
 *   separate  — the pair spins twice while shrinking apart; colours change
 *   collapse  — both shrink to dots and spin together into the centre
 *   pop       — they burst open with an overshoot; the next photo is in the lens
 *
 * The intro flies the two dots in from either side while spinning five turns.
 * Both circles are placed every frame from { angle, half, r } so the photo
 * stays upright while the lens orbits. Between changes the photo and the
 * blurred background slowly zoom and the rig leans towards the pointer.
 */
const CX = 380;
const CY = 300;
const FAR = { half: 620, r: 62 };
const APART = { half: 135, r: 86 };
const DOT = { half: 1, r: 28 };
const OPEN = { half: 76, r: 210 };
const DURATION = 6; // seconds a photo stays up
const GLYPHS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

/** Letters resolve from random glyphs, left to right. */
function scramble(el, text, duration = 0.6) {
    const state = { p: 0 };
    return gsap.to(state, {
        p: 1,
        duration,
        ease: 'none',
        onUpdate: () => {
            const done = Math.floor(state.p * text.length);
            el.textContent = text
                .split('')
                .map((ch, k) => (k < done || ch === ' ' ? ch : GLYPHS[Math.floor(Math.random() * GLYPHS.length)]))
                .join('');
        },
        onComplete: () => (el.textContent = text),
    });
}

/** Buttons that lean towards the pointer. */
function magnetic(root) {
    root.querySelectorAll('[data-magnetic]').forEach((el) => {
        const x = gsap.quickTo(el, 'x', { duration: 0.5, ease: 'power3.out' });
        const y = gsap.quickTo(el, 'y', { duration: 0.5, ease: 'power3.out' });
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            x((e.clientX - r.left - r.width / 2) * 0.3);
            y((e.clientY - r.top - r.height / 2) * 0.4);
        });
        el.addEventListener('pointerleave', () => (x(0), y(0)));
    });
}

export function initHero(reduceMotion, delay = 0) {
    const root = document.querySelector('[data-hero]');
    if (!root) return;

    const slides = JSON.parse(root.dataset.slides);
    const svg = root.querySelector('[data-hv]');
    const art = root.querySelector('[data-hero-art]');
    const copy = root.querySelector('[data-hero-copy]');
    const rig = root.querySelector('[data-hv-rig]');
    const a = root.querySelectorAll('[data-hv="a"]'); // visible circle + its clip-path copy
    const b = root.querySelectorAll('[data-hv="b"]');
    const fillA = root.querySelector('[data-hv-fill="a"]');
    const fillB = root.querySelector('[data-hv-fill="b"]');
    const lens = root.querySelector('[data-hv-lens]');
    const photo = root.querySelector('[data-hv-photo]');
    const images = root.querySelectorAll('[data-hv-img]');
    const backgrounds = root.querySelectorAll('[data-hero-bg]');
    const lines = root.querySelectorAll('[data-hv-line]');
    const texts = root.querySelectorAll('[data-hv-text]');
    const dots = root.querySelectorAll('[data-hero-dot]');
    const fills = root.querySelectorAll('[data-hero-fill]');
    const count = root.querySelector('[data-hero-count]');
    const caption = root.querySelector('[data-hero-caption]');

    let index = 0;
    let busy = false;
    let progress = null;
    let drift = [];

    // --- Geometry ------------------------------------------------------------------------
    const st = { angle: 0, ...OPEN };
    const render = () => {
        const rad = (st.angle * Math.PI) / 180;
        const dx = Math.cos(rad) * st.half;
        const dy = Math.sin(rad) * st.half;
        const r = Math.max(0, st.r).toFixed(2);
        a.forEach((c) => (c.setAttribute('cx', (CX - dx).toFixed(2)), c.setAttribute('cy', (CY - dy).toFixed(2)), c.setAttribute('r', r)));
        b.forEach((c) => (c.setAttribute('cx', (CX + dx).toFixed(2)), c.setAttribute('cy', (CY + dy).toFixed(2)), c.setAttribute('r', r)));
    };

    const lineLength = lines[0].getTotalLength();
    gsap.set(lines, { strokeDasharray: lineLength });

    // --- Beats -----------------------------------------------------------------------------
    const labelsIn = () =>
        gsap.timeline()
            .fromTo(lines, { strokeDashoffset: lineLength }, { strokeDashoffset: 0, duration: 0.5, ease: 'power2.inOut', stagger: 0.08 })
            .set(texts, { opacity: 1 }, 0.3)
            .add(scramble(texts[0], slides[index].a), 0.3)
            .add(scramble(texts[1], slides[index].b), 0.4);

    const labelsOut = () =>
        gsap.timeline()
            .to(lines, { strokeDashoffset: -lineLength, duration: 0.5, ease: 'power2.inOut' })
            .to(texts, { opacity: 0, duration: 0.3 }, 0.15);

    const captionIn = () => gsap.fromTo(caption, { yPercent: 60, opacity: 0 }, { yPercent: 0, opacity: 1, duration: 0.6, ease: 'power3.out' });
    const captionOut = () => gsap.to(caption, { yPercent: -60, opacity: 0, duration: 0.35, ease: 'power2.in' });

    const separate = () =>
        gsap.timeline()
            .set(st, { angle: 0 })
            .to(st, { angle: 720, duration: 1, ease: 'power4.out' }, 0)
            .to(st, { r: APART.r, half: APART.half, duration: 1, ease: 'power2.out' }, 0);

    const collapse = () =>
        gsap.timeline()
            .set(st, { angle: 0 })
            .to(st, { angle: 720, duration: 1, ease: 'power4.out' }, 0)
            .to(st, { r: DOT.r, half: DOT.half, duration: 1, ease: 'power2.out' }, 0);

    const pop = () =>
        gsap.timeline({ onComplete: () => gsap.set(st, { angle: 0 }) })
            .set(lens, { opacity: 1 })
            .to(st, { r: OPEN.r, duration: 0.55, ease: 'back.inOut(5)' }, 0)
            .to(st, { half: OPEN.half, duration: 0.55, ease: 'power2.out' }, 0)
            .fromTo(images[index], { scale: 1.25, transformOrigin: '50% 50%' }, { scale: 1, duration: 1.1, ease: 'expo.out', immediateRender: false }, 0)
            .to(backgrounds[index], { opacity: 1, duration: 1, ease: 'power2.inOut' }, 0)
            .add(labelsIn(), 0.3)
            .add(captionIn(), 0.35)
            .add(startDrift, 0.5);

    const paint = (i, duration = 1) =>
        gsap.timeline()
            .to(fillA, { fill: slides[i].colors[0], duration }, 0)
            .to(fillB, { fill: slides[i].colors[1], duration }, 0);

    // --- Slide state ----------------------------------------------------------------------
    const setSlide = (i) => {
        images.forEach((img, k) => img.setAttribute('opacity', k === i ? 1 : 0));
        count.textContent = String(i + 1).padStart(2, '0');
        caption.textContent = slides[i].caption;
        dots.forEach((dot, k) => (k === i ? dot.setAttribute('aria-current', 'true') : dot.removeAttribute('aria-current')));
        fills.forEach((fill, k) => gsap.set(fill, { scaleX: k < i ? 1 : 0 }));
    };

    /** Slow zoom on the current photo and background while the slide is up. */
    function startDrift() {
        drift.forEach((t) => t.kill());
        drift = [
            gsap.to(images[index], { scale: 1.08, duration: DURATION + 2, ease: 'none', delay: 0.6 }),
            gsap.fromTo(backgrounds[index], { scale: 1.1 }, { scale: 1.22, xPercent: index % 2 ? 2 : -2, duration: DURATION + 2, ease: 'none' }),
        ];
    }

    // Pause while hovered or scrolled away.
    let hovering = false;
    let visible = true;
    const sync = () => progress && (hovering || !visible ? progress.pause() : progress.resume());

    const startProgress = () => {
        progress?.kill();
        progress = gsap.fromTo(fills[index], { scaleX: 0 }, { scaleX: 1, duration: DURATION, ease: 'none', onComplete: () => go(index + 1) });
        sync();
    };

    function go(target) {
        const i = (target + slides.length) % slides.length;
        if (busy || i === index) return;

        if (reduceMotion) {
            index = i;
            setSlide(i);
            texts[0].textContent = slides[i].a;
            texts[1].textContent = slides[i].b;
            gsap.set(fillA, { fill: slides[i].colors[0] });
            gsap.set(fillB, { fill: slides[i].colors[1] });
            backgrounds.forEach((bg, k) => gsap.set(bg, { opacity: k === i ? 1 : 0 }));
            return;
        }

        busy = true;
        progress?.kill();
        const previous = index;

        gsap.timeline({ onComplete: () => ((busy = false), startProgress()) })
            // separate: spin apart, recolour, labels and background out
            .add(separate(), 0)
            .add(paint(i), 0)
            .add(labelsOut(), 0)
            .add(captionOut(), 0)
            .to(backgrounds[previous], { opacity: 0, duration: 1, ease: 'power4.out' }, 0)
            // collapse: swap content while the lens is shut
            .set(lens, { opacity: 0 }, 1)
            .call(() => ((index = i), setSlide(i)), null, 1)
            .add(collapse(), 1)
            // pop
            .add(() => pop(), 2)
            .to({}, { duration: 0.6 }, 2); // let the pop land before the progress restarts
    }

    dots.forEach((dot, k) => dot.addEventListener('click', () => go(k)));

    // Click the circles for the next photo; swipe them either way.
    let downX = null;
    svg.addEventListener('pointerdown', (e) => (downX = e.clientX));
    svg.addEventListener('pointerup', (e) => {
        if (downX === null) return;
        const dx = e.clientX - downX;
        downX = null;
        go(Math.abs(dx) > 40 && dx > 0 ? index - 1 : index + 1);
    });
    svg.addEventListener('mouseenter', () => ((hovering = true), sync()));
    svg.addEventListener('mouseleave', () => ((hovering = false), sync()));

    if (reduceMotion) return;

    // Re-place the circles every frame while any part of the hero is on screen.
    // (Starting at 'top bottom' keeps it active at scroll 0 — 'top top' toggled off when scrolling back to the very top.)
    let rendering = false;
    const setRendering = (on) => {
        if (on === rendering) return;
        rendering = on;
        on ? gsap.ticker.add(render) : gsap.ticker.remove(render);
    };
    setRendering(true);
    ScrollTrigger.create({
        trigger: root,
        start: 'top bottom',
        end: 'bottom top',
        onToggle: (self) => {
            visible = self.isActive;
            sync();
            setRendering(visible);
            render();
        },
    });

    // --- Intro: two dots fly in spinning five turns, collapse, then pop open ------------------
    Object.assign(st, { angle: 0, ...FAR });
    render();
    gsap.set(lens, { opacity: 0 });
    gsap.set(backgrounds, { opacity: 0, scale: 1.1 });
    gsap.set([...texts, caption], { opacity: 0 });
    gsap.set(lines, { strokeDashoffset: lineLength });
    gsap.set(fills[0], { scaleX: 0 });

    busy = true; // no slide changes until the intro has landed
    gsap.timeline({ delay: delay + 0.1, onComplete: () => ((busy = false), startProgress()) })
        .to(st, { angle: 1800, duration: 2, ease: 'power4.out' }, 0)
        .to(st, { r: APART.r, duration: 2, ease: 'power2.inOut' }, 0)
        .to(st, { half: APART.half, duration: 1.7, ease: 'power2.inOut' }, 0)
        .add(collapse(), 2)
        .add(() => pop(), 3)
        .to({}, { duration: 0.6 }, 3);

    // --- Pointer depth: the rig leans towards the pointer, the photo slightly against it ------
    if (window.matchMedia('(pointer: fine)').matches) {
        const moveX = gsap.quickTo(rig, 'x', { duration: 0.9, ease: 'power3.out' });
        const moveY = gsap.quickTo(rig, 'y', { duration: 0.9, ease: 'power3.out' });
        const photoX = gsap.quickTo(photo, 'x', { duration: 1.1, ease: 'power3.out' });
        const photoY = gsap.quickTo(photo, 'y', { duration: 1.1, ease: 'power3.out' });
        const copyX = gsap.quickTo(copy, 'x', { duration: 1.2, ease: 'power3.out' });

        root.addEventListener('pointermove', (e) => {
            const nx = e.clientX / window.innerWidth - 0.5;
            const ny = e.clientY / window.innerHeight - 0.5;
            moveX(nx * 34);
            moveY(ny * 26);
            photoX(nx * -28);
            photoY(ny * -20);
            copyX(nx * -10);
        });
        magnetic(root);
    }

    // --- Scrolling away: the graphic sinks and turns, the copy lifts and fades ----------------
    gsap.timeline({ scrollTrigger: { trigger: root, start: 'top top', end: 'bottom top', scrub: 0.6 } })
        .to(art, { yPercent: 18, scale: 0.86, rotation: -8, ease: 'none' }, 0)
        .to(copy, { yPercent: -18, opacity: 0.15, ease: 'none' }, 0);
}
