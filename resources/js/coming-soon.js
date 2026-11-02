import gsap from 'gsap';

import { initCursor } from './site/cursor';

/**
 * Coming Soon page. The title is drawn as a field of small squares, sampled from the real
 * <h1> text so it keeps the logo-like font and can't drift from the layout. Two clouds of
 * squares (the two merged companies) flow together into the letters; afterwards the pointer
 * pushes them around and charges them, a press sends a shock wave, and a band of current
 * sweeps through on its own so phones stay alive too. Entering the site pulls every square
 * into the button, which then opens the page from that exact spot.
 */
const BLUE = [26, 175, 237]; // the logo's "PLN"
const TEAL = [0, 88, 122]; // the logo's "Services"
const VOLT = [255, 200, 0];
const STEPS = 6; // heat levels; squares grow with each one
const rgb = (c) => `rgb(${c.join(',')})`;
// A hard flip to yellow: blending blue into yellow turns the letters a muddy green.
const palette = (base) => Array.from({ length: STEPS }, (_, i) => rgb(i < STEPS / 2 ? base : VOLT));
const PALETTES = [palette(BLUE), palette(TEAL)];

/** Wrap each letter in a span: its box tells the canvas exactly where to draw it. */
function splitLetters(words) {
    return words.flatMap((word, w) => {
        const letters = [...word.textContent.trim()].map((ch) => {
            const span = document.createElement('span');
            span.textContent = ch;
            span.dataset.word = w;
            return span;
        });
        word.replaceChildren(...letters);
        return letters;
    });
}

/** Home positions: every grid point covered by the title's letters. */
function sampleTitle(letters, W, H) {
    const style = getComputedStyle(letters[0]);
    const fontSize = parseFloat(style.fontSize);
    const gap = Math.max(3, Math.round(fontSize / 30));

    const off = document.createElement('canvas');
    off.width = W;
    off.height = H;
    const ctx = off.getContext('2d', { willReadFrequently: true });
    ctx.font = `${style.fontWeight} ${fontSize}px ${style.fontFamily}`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    letters.forEach((span) => {
        const box = span.getBoundingClientRect();
        ctx.fillStyle = span.dataset.word === '0' ? '#f00' : '#0f0'; // channel = word
        ctx.fillText(span.textContent.toUpperCase(), box.left + box.width / 2, box.top + box.height / 2);
    });

    const { data } = ctx.getImageData(0, 0, W, H);
    const dots = [];
    for (let y = 0; y < H; y += gap) {
        for (let x = 0; x < W; x += gap) {
            const i = (y * W + x) * 4;
            if (data[i + 3] > 128) dots.push({ hx: x, hy: y, word: data[i] > data[i + 1] ? 0 : 1 });
        }
    }
    return { dots, gap, fontSize };
}

function initField(canvas, title) {
    const ctx = canvas.getContext('2d');
    const letters = splitLetters([...title.querySelectorAll('[data-cs-word]')]);
    const pointer = { x: -9999, y: -9999, on: false };
    const waves = [];
    let W = 0;
    let H = 0;
    let dots = [];
    let size = 3;
    let reach = 120;
    let sinking = false; // the squares are being pulled into the button

    const build = (intro) => {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = window.innerWidth;
        H = window.innerHeight;
        canvas.width = W * dpr;
        canvas.height = H * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const sampled = sampleTitle(letters, W, H);
        size = sampled.gap * 0.78;
        reach = Math.max(90, sampled.fontSize);
        dots = sampled.dots.map((d) => {
            const angle = Math.random() * Math.PI * 2;
            const r = Math.sqrt(Math.random()) * Math.min(W, H) * 0.17;
            const cx = W * (d.word ? 0.75 : 0.25);
            const x = intro ? cx + Math.cos(angle) * r : d.hx;
            const y = intro ? H / 2 + Math.sin(angle) * r : d.hy;
            return { ...d, x, y, vx: 0, vy: 0, heat: 0, wait: intro ? 0.9 + Math.random() * 0.7 : 0 };
        });
    };

    build(true);

    let clock = 0;
    let sweep = -0.2; // position of the current band, in screen widths
    let nextSweep = 2.6;

    gsap.ticker.add((_, deltaMs) => {
        const dt = Math.min(deltaMs, 50) / 1000;
        const step = dt * 60;
        clock += dt;

        if (clock > nextSweep) {
            sweep += dt * 0.55;
            if (sweep > 1.2) {
                sweep = -0.2;
                nextSweep = clock + 3.5;
            }
        }
        const bandX = sweep * W;
        const sweeping = clock > nextSweep;

        waves.forEach((w) => (w.r += 13 * step));
        while (waves.length && waves[0].r > Math.max(W, H) * 1.2) waves.shift();

        const buckets = PALETTES.map(() => Array.from({ length: STEPS }, () => []));

        for (const d of dots) {
            let ax = 0;
            let ay = 0;

            if (clock > d.wait) {
                ax = (d.hx - d.x) * 0.05;
                ay = (d.hy - d.y) * 0.05;
            } else {
                ax = Math.sin(clock * 1.7 + d.hy) * 0.08; // the clouds simmer until released
                ay = Math.cos(clock * 1.3 + d.hx) * 0.08;
            }

            if (pointer.on && !sinking) {
                const dx = d.x - pointer.x;
                const dy = d.y - pointer.y;
                const dist = Math.hypot(dx, dy) || 1;
                if (dist < reach) {
                    const f = 1 - dist / reach;
                    ax += (dx / dist) * f * f * 5;
                    ay += (dy / dist) * f * f * 5;
                    d.heat = Math.max(d.heat, f * 1.3);
                }
            }

            for (const w of waves) {
                const dx = d.x - w.x;
                const dy = d.y - w.y;
                const dist = Math.hypot(dx, dy) || 1;
                const off = Math.abs(dist - w.r);
                if (off < 46) {
                    const f = (1 - off / 46) * w.force * Math.max(0, 1 - w.r / (Math.max(W, H) * 0.9));
                    ax += (dx / dist) * f;
                    ay += (dy / dist) * f;
                    d.heat = Math.max(d.heat, f / 3);
                }
            }

            if (sweeping) {
                const off = Math.abs(d.hx - bandX);
                if (off < 60) {
                    d.heat = Math.max(d.heat, 1 - off / 60);
                    ay += Math.sin(d.hx * 0.08 + clock * 30) * 0.5; // the current makes the letters hum
                }
            }

            d.vx = (d.vx + ax * step) * 0.84 ** step;
            d.vy = (d.vy + ay * step) * 0.84 ** step;
            d.x += d.vx * step;
            d.y += d.vy * step;
            d.heat *= 0.93 ** step;

            if (sinking && clock > d.wait) {
                d.heat = 1;
                if (Math.abs(d.x - d.hx) + Math.abs(d.y - d.hy) < 16) continue; // swallowed
            }

            buckets[d.word][Math.min(STEPS - 1, Math.floor(d.heat * STEPS))].push(d);
        }

        ctx.clearRect(0, 0, W, H);
        buckets.forEach((steps, word) =>
            steps.forEach((list, heat) => {
                if (!list.length) return;
                const s = size * (1 + (heat / (STEPS - 1)) * 0.5);
                ctx.fillStyle = PALETTES[word][heat];
                for (const d of list) ctx.fillRect(d.x, d.y, s, s);
            }),
        );
    });

    window.addEventListener('pointermove', (e) => Object.assign(pointer, { x: e.clientX, y: e.clientY, on: true }));
    window.addEventListener('pointerup', (e) => e.pointerType !== 'mouse' && (pointer.on = false));
    window.addEventListener('pointercancel', () => (pointer.on = false));
    document.documentElement.addEventListener('pointerleave', () => (pointer.on = false));
    window.addEventListener('pointerdown', (e) => {
        Object.assign(pointer, { x: e.clientX, y: e.clientY, on: true });
        waves.push({ x: e.clientX, y: e.clientY, r: 0, force: 7 });
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => sinking || build(false), 150);
    });

    return {
        absorb(x, y) {
            sinking = true;
            for (const d of dots) {
                d.wait = clock + Math.hypot(d.hx - x, d.hy - y) * 0.0005; // nearest letters go first
                d.hx = x;
                d.hy = y;
            }
        },
    };
}

function initMagnet(button) {
    const x = gsap.quickTo(button, 'x', { duration: 0.5, ease: 'power3.out' });
    const y = gsap.quickTo(button, 'y', { duration: 0.5, ease: 'power3.out' });
    button.addEventListener('pointermove', (e) => {
        const box = button.getBoundingClientRect();
        x((e.clientX - box.left - box.width / 2) * 0.3);
        y((e.clientY - box.top - box.height / 2) * 0.3);
    });
    button.addEventListener('pointerleave', () => {
        x(0);
        y(0);
    });
}

/**
 * Entering the site: the title's energy runs into the button, the button flares, and the
 * brand panels open from it. They end exactly like the site's own page transition (same
 * panels, label and header), so the next page simply carries on and sweeps them away.
 */
function initEnter(link, wipe, field) {
    const TITLE = 'Selamat Datang';
    const panels = [...wipe.querySelectorAll('[data-cs-wipe-panel]')];
    const label = wipe.querySelector('[data-cs-wipe-label]');
    let entering = false;

    link.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        if (entering) return;
        entering = true;

        try {
            sessionStorage.setItem('pln:visited', '1');
            sessionStorage.setItem('pln:transition', '1');
            sessionStorage.setItem('pln:nav', JSON.stringify({ dir: 'forward', title: TITLE }));
        } catch (err) {}

        let gone = false;
        const go = () => {
            if (gone) return;
            gone = true;
            window.location.href = link.href;
        };
        setTimeout(go, 2600); // a throttled tab must not block the link

        const box = link.getBoundingClientRect();
        const x = box.left + box.width / 2;
        const y = box.top + box.height / 2;
        const at = `at ${x}px ${y - wipe.getBoundingClientRect().top}px`;
        const radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));

        field.absorb(x, y);
        gsap.timeline({ onComplete: go })
            .to('[data-cs-fade]', { opacity: 0, y: -12, duration: 0.3, ease: 'power1.in' }, 0)
            .to(link, { scale: 0.9, duration: 0.3, ease: 'power2.out' }, 0)
            .to(link, { scale: 1.16, duration: 0.3, ease: 'back.out(3)' }, 0.42)
            .fromTo(panels, { clipPath: `circle(0px ${at})` }, { clipPath: `circle(${radius}px ${at})`, duration: 0.8, ease: 'power3.inOut', stagger: 0.1 }, 0.62)
            .fromTo(label, { opacity: 0, x: 40 }, { opacity: 1, x: 0, duration: 0.4, ease: 'power2.out' }, 1.2)
            .to({}, { duration: 0.3 });
    });

    // Back from the site: this page was frozen mid-transition.
    window.addEventListener('pageshow', (e) => e.persisted && window.location.reload());
}

document.addEventListener('DOMContentLoaded', async () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const button = document.querySelector('[data-cs-enter]');
    if (reduceMotion) return; // the solid title and plain link are the whole page

    clearTimeout(window.csFallback);
    const title = document.querySelector('[data-cs-title]');
    // The letters are sampled from this font, but a stuck font request mustn't hold up the page.
    const font = document.fonts.load(`64px ${getComputedStyle(title).fontFamily}`).catch(() => {});
    await Promise.race([font, new Promise((resolve) => setTimeout(resolve, 3000))]);

    const field = initField(document.querySelector('[data-cs-canvas]'), title);
    initCursor(reduceMotion);
    initMagnet(button);
    initEnter(button, document.querySelector('[data-cs-wipe]'), field);

    gsap.timeline()
        .fromTo('[data-cs-tag]', { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out', stagger: 0.1 }, 0.1)
        .to('[data-cs-tag]', { opacity: 0, duration: 0.35, ease: 'power1.in' }, 1)
        .fromTo('[data-cs-in]', { opacity: 0, y: 18 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out', stagger: 0.12 }, 1.5);
});
