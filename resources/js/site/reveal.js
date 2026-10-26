import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

const inViewport = (el) => el.getBoundingClientRect().top < window.innerHeight * 0.9;

/** Headings: each line rises from behind a mask. */
function splitHeadings(delay) {
    document.querySelectorAll('[data-split]').forEach((el) => {
        const split = SplitText.create(el, { type: 'lines', mask: 'lines', linesClass: 'split-line' });
        gsap.set(el, { opacity: 1 });

        gsap.from(split.lines, {
            yPercent: 105,
            duration: 0.9,
            stagger: 0.08,
            ease: 'power3.out',
            delay: inViewport(el) ? delay + Number(el.dataset.delay || 0) : 0,
            scrollTrigger: inViewport(el) ? null : { trigger: el, start: 'top 88%' },
        });
    });
}

function revealBlocks(delay) {
    const from = {
        up: { y: 24 },
        down: { y: -40 },
        left: { x: -32 },
        right: { x: 32 },
        scale: { scale: 0.97 },
        fade: {},
    };

    document.querySelectorAll('[data-reveal]').forEach((el) => {
        const mode = el.dataset.reveal || 'up';

        if (mode === 'stagger') {
            gsap.set(el, { opacity: 1 });
            gsap.from(el.children, {
                y: 24,
                opacity: 0,
                duration: 0.7,
                stagger: 0.07,
                ease: 'power3.out',
                clearProps: 'opacity,transform', // leave hover states (e.g. .link-list dimming) to CSS
                delay: inViewport(el) ? delay + 0.15 : 0,
                scrollTrigger: inViewport(el) ? null : { trigger: el, start: 'top 85%' },
            });
            return;
        }

        gsap.fromTo(el, { opacity: 0, ...from[mode] }, {
            opacity: 1,
            x: 0,
            y: 0,
            scale: 1,
            duration: 0.8,
            ease: 'power3.out',
            delay: (inViewport(el) ? delay : 0) + Number(el.dataset.delay || 0),
            scrollTrigger: inViewport(el) ? null : { trigger: el, start: 'top 88%' },
            onStart: () => el.classList.add('is-in'), // e.g. section-title rules grow
        });
    });
}

function counters() {
    const format = (value, decimals) =>
        value.toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

    document.querySelectorAll('[data-counter]').forEach((el) => {
        const target = parseFloat(el.dataset.counter);
        const decimals = Number(el.dataset.decimals || 0);
        const state = { v: 0 };
        el.textContent = format(0, decimals);

        ScrollTrigger.create({
            trigger: el,
            start: 'top 90%',
            once: true,
            onEnter: () =>
                gsap.to(state, {
                    v: target,
                    duration: 2.2,
                    ease: 'power3.out',
                    onUpdate: () => (el.textContent = format(state.v, decimals)),
                }),
        });
    });
}

/** Statement text that lights up word by word as you scroll. */
function scrubWords() {
    document.querySelectorAll('[data-scrub-words]').forEach((el) => {
        const split = SplitText.create(el, { type: 'words' });
        gsap.fromTo(split.words, { opacity: 0.14 }, {
            opacity: 1,
            stagger: 0.1,
            ease: 'none',
            scrollTrigger: { trigger: el, start: 'top 80%', end: 'bottom 45%', scrub: true },
        });
    });
}

function parallax() {
    document.querySelectorAll('[data-parallax]').forEach((el) => {
        const speed = parseFloat(el.dataset.parallax) || 0.2;
        gsap.fromTo(el, { yPercent: -speed * 50 }, {
            yPercent: speed * 50,
            ease: 'none',
            scrollTrigger: { trigger: el.parentElement, start: 'top bottom', end: 'bottom top', scrub: true },
        });
    });
}

/** Images/blocks wiped in with a clip-path. */
function clipReveals() {
    document.querySelectorAll('[data-clip]').forEach((el) => {
        gsap.fromTo(el, { clipPath: 'inset(100% 0% 0% 0% round 28px)' }, {
            clipPath: 'inset(0% 0% 0% 0% round 28px)',
            duration: 1.4,
            ease: 'expo.out',
            scrollTrigger: { trigger: el, start: 'top 85%' },
        });
    });
}

function drawLines() {
    document.querySelectorAll('[data-draw]').forEach((path) => {
        const length = path.getTotalLength();
        gsap.fromTo(path, { strokeDasharray: length, strokeDashoffset: length }, {
            strokeDashoffset: 0,
            ease: 'none',
            scrollTrigger: { trigger: path.closest('section') || path, start: 'top 75%', end: 'bottom 60%', scrub: 1 },
        });
    });
}

export function initReveals(delay = 0) {
    splitHeadings(delay);
    revealBlocks(delay);
    counters();
    scrubWords();
    parallax();
    clipReveals();
    drawLines();
}
