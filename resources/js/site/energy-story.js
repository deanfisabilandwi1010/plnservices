import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * "Perjalanan Energi" scrollytelling (site/partials/energy-story): a canvas of
 * energy dots, pinned while narrative cards scroll past. The dots morph with
 * the scroll position between four arrangements:
 *   0 scattered sources → 1 split into conventional | renewable plants →
 *   2 flowing along transmission lines → 3 a light bulb that lights up.
 * Dots drift, flow and give way to the pointer; hovering one names its plant
 * type and the legend highlights one kind.
 */
const N = 170;
const BLUE = [52, 152, 219]; // brand
const YELLOW = [255, 205, 0]; // PLN yellow, a touch deeper for contrast on light backgrounds
const INK = '34, 34, 34';

const TYPES = {
    blue: [
        ['PLTD', 'Pembangkit Listrik Tenaga Diesel'],
        ['PLTU', 'Pembangkit Listrik Tenaga Uap'],
        ['PLTG', 'Pembangkit Listrik Tenaga Gas'],
        ['PLTMG', 'Pembangkit Listrik Tenaga Mesin Gas'],
    ],
    yellow: [
        ['PLTS', 'Pembangkit Listrik Tenaga Surya'],
        ['PLTA', 'Pembangkit Listrik Tenaga Air'],
        ['PLTB', 'Pembangkit Listrik Tenaga Bayu'],
        ['PLTP', 'Pembangkit Listrik Tenaga Panas Bumi'],
        ['PLTBm', 'Pembangkit Listrik Tenaga Biomassa'],
    ],
};

const clamp = (v, min = 0, max = 1) => Math.min(max, Math.max(min, v));
const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
const lerp = (a, b, t) => a + (b - a) * t;
const hash = (i, k) => {
    const s = Math.sin(i * 127.1 + k * 311.7) * 43758.5453;
    return s - Math.floor(s);
};

export function initEnergyStory(reduceMotion) {
    const section = document.querySelector('[data-energy-story]');
    if (!section) return;

    const pin = section.querySelector('[data-es-pin]');
    const canvas = section.querySelector('[data-es-canvas]');
    const ctx = canvas.getContext('2d');
    const tip = section.querySelector('[data-es-tip]');
    const legend = section.querySelector('[data-es-legend]');
    const labelLayer = section.querySelector('[data-es-labels]');
    const steps = [...section.querySelectorAll('[data-es-step]')];

    // --- Dots ---------------------------------------------------------------------------
    const dots = Array.from({ length: N }, (_, i) => {
        const kind = hash(i, 1) < 0.58 ? 'blue' : 'yellow';
        const types = TYPES[kind];
        return {
            kind,
            type: types[Math.floor(hash(i, 2) * types.length)],
            r: 2.4 + Math.pow(hash(i, 3), 3.2) * 15, // mostly small, a few large plants
            phase: hash(i, 4) * Math.PI * 2,
            delay: hash(i, 5) * 0.3,
            ox: 0,
            oy: 0,
            x: 0,
            y: 0,
            rr: 0,
        };
    });
    const blues = dots.filter((d) => d.kind === 'blue');
    const yellows = dots.filter((d) => d.kind === 'yellow');
    const highlight = { blue: true, yellow: true };

    // HTML labels (crisp text), positioned per arrangement.
    const labelEls = {};
    const label = (key, text) => {
        const el = document.createElement('div');
        el.className = 'absolute top-0 left-0 font-display text-[13px] font-bold tracking-[0.12em] whitespace-nowrap text-ink uppercase opacity-0';
        el.textContent = text;
        labelLayer.append(el);
        labelEls[key] = el;
    };
    label('conv', 'Pembangkit konvensional');
    label('ebt', 'Energi baru terbarukan');
    label('plant', 'Pembangkit');
    label('grid', 'Gardu distribusi');
    label('home', 'Pelanggan');

    // --- Geometry -------------------------------------------------------------------------
    let W = 0;
    let H = 0;
    let region = null;
    let layouts = [];

    const mobile = () => W < 1024;

    function measure() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = pin.clientWidth;
        H = pin.clientHeight;
        canvas.width = W * dpr;
        canvas.height = H * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        // Graphic area: right of the narrative column on desktop; on phones the top half,
        // below the header and section bar, with the cards along the bottom.
        region = mobile()
            ? { x: 20, y: 196, w: W - 40, h: Math.max(220, H - 196 - 290) } // leaves room for the card at the bottom
            : { x: W * 0.42, y: 120, w: W * 0.54, h: H - 200 };

        // Labels wrap into two columns on phones instead of running into each other.
        Object.values(labelEls).forEach((el) => {
            el.style.whiteSpace = mobile() ? 'normal' : '';
            el.style.width = mobile() ? `${Math.round(W * 0.42)}px` : '';
            el.style.textAlign = 'center';
            el.style.fontSize = mobile() ? '11px' : '';
        });
        region.cx = region.x + region.w / 2;
        region.cy = region.y + region.h / 2;
        const scale = clamp(Math.min(region.w / 760, region.h / 520), 0.45, 1.15);
        dots.forEach((d) => (d.size = d.r * scale));

        layouts = [scatter(), split(), null, bulb()]; // 2 (flow) depends on time
    }

    function scatter() {
        return {
            pts: dots.map((_, i) => [region.x + hash(i, 6) * region.w, region.y + hash(i, 7) * region.h]),
            sizes: dots.map((d) => d.size),
            drift: 7,
            labels: {},
        };
    }

    function split() {
        const pts = [];
        const R = Math.min(region.w * (mobile() ? 0.22 : 0.2), region.h * 0.4);
        const place = (group, cx) => {
            const sorted = [...group].sort((a, b) => b.r - a.r);
            sorted.forEach((d, k) => {
                const rad = Math.sqrt((k + 0.5) / group.length) * R;
                const theta = k * 2.39996;
                const i = dots.indexOf(d);
                pts[i] = [cx + rad * Math.cos(theta) * 1.1 + (hash(i, 8) - 0.5) * 14, region.cy + rad * Math.sin(theta) + (hash(i, 9) - 0.5) * 14];
            });
        };
        const off = region.w * 0.26;
        place(blues, region.cx - off);
        place(yellows, region.cx + off);
        return {
            pts,
            sizes: dots.map((d) => d.size * 0.92),
            drift: 3,
            labels: { conv: [region.cx - off, region.cy - R - (mobile() ? 30 : 46)], ebt: [region.cx + off, region.cy - R - (mobile() ? 30 : 46)] },
        };
    }

    // Transmission: three conductors sagging between two towers.
    function wires() {
        const xL = region.x + region.w * 0.08;
        const xR = region.x + region.w * 0.92;
        const top = region.cy - region.h * 0.12;
        const sag = region.h * 0.12;
        return { xL, xR, top, sag, gap: Math.min(34, region.h * 0.08) };
    }
    const onWire = (g, k, s) => {
        const x = lerp(g.xL, g.xR, s);
        const y = g.top + k * g.gap + 4 * g.sag * s * (1 - s);
        return [x, y];
    };

    function flow(time) {
        const g = wires();
        const pts = dots.map((_, i) => {
            const k = i % 3;
            const s = (i / N + time * 0.045 + k * 0.11) % 1;
            return onWire(g, k, s);
        });
        return {
            pts,
            sizes: dots.map((d) => 2 + Math.min(d.size, 6) * 0.45),
            drift: 0,
            labels: { plant: [g.xL, g.top - region.h * 0.15], grid: [g.xR, g.top - region.h * 0.15] },
        };
    }

    // Light bulb: dots fill the glass (lit yellow) and the screw base (blue).
    function bulb() {
        const Rb = Math.min(region.w, region.h) * 0.3;
        const cx = region.cx;
        const cy = region.cy - Rb * 0.25;
        const baseTop = cy + Rb * 0.92;
        const baseW = Rb * 0.78;
        const baseH = Rb * 0.6;
        const glassCount = Math.round(N * 0.8);
        const pts = [];
        const lit = [];
        dots.forEach((d, i) => {
            if (i < glassCount) {
                const rad = Math.sqrt((i + 0.5) / glassCount) * Rb * 0.94;
                const theta = i * 2.39996;
                let y = cy + rad * Math.sin(theta);
                const x = cx + rad * Math.cos(theta);
                if (y > cy + Rb * 0.55) y = cy + Rb * 0.55 + (y - cy - Rb * 0.55) * 0.5; // taper toward the neck
                pts[i] = [x, y];
                lit[i] = 1;
            } else {
                const k = i - glassCount;
                const perRow = 9;
                const row = Math.floor(k / perRow);
                const col = k % perRow;
                const rowW = baseW * (1 - row * 0.08);
                pts[i] = [cx - rowW / 2 + (col + 0.5) * (rowW / perRow), baseTop + (row + 0.5) * (baseH / 4)];
                lit[i] = 0;
            }
        });
        return {
            pts,
            sizes: dots.map((d, i) => (i < glassCount ? Math.min(d.size, 9) * 0.85 + 1.5 : 3.2)),
            drift: 1.5,
            lit,
            labels: { home: [cx, baseTop + baseH + 34] },
            shape: { cx, cy, Rb, baseTop, baseW, baseH },
        };
    }

    // --- Drawing ----------------------------------------------------------------------------
    const pointer = { x: -9999, y: -9999 };
    let p = 0;
    let hovered = null;

    function drawTowers(alpha) {
        if (alpha <= 0.01) return;
        const g = wires();
        ctx.save();
        ctx.strokeStyle = `rgba(${INK}, ${0.35 * alpha})`;
        ctx.lineWidth = 1.2;
        [g.xL, g.xR].forEach((x) => {
            const h = region.h * 0.42;
            const base = g.top + h * 0.85;
            const top = g.top - h * 0.18;
            ctx.beginPath();
            ctx.moveTo(x - 22, base);
            ctx.lineTo(x - 4, top);
            ctx.lineTo(x + 4, top);
            ctx.lineTo(x + 22, base);
            for (let k = 0; k < 4; k++) {
                const y0 = lerp(base, top, k / 4);
                const y1 = lerp(base, top, (k + 1) / 4);
                const w0 = lerp(22, 4, k / 4);
                const w1 = lerp(22, 4, (k + 1) / 4);
                ctx.moveTo(x - w0, y0);
                ctx.lineTo(x + w1, y1);
                ctx.moveTo(x + w0, y0);
                ctx.lineTo(x - w1, y1);
            }
            ctx.moveTo(x - 30, g.top);
            ctx.lineTo(x + 30, g.top);
            ctx.moveTo(x - 24, g.top + 2 * g.gap);
            ctx.lineTo(x + 24, g.top + 2 * g.gap);
            ctx.stroke();
        });
        ctx.strokeStyle = `rgba(${INK}, ${0.18 * alpha})`;
        for (let k = 0; k < 3; k++) {
            ctx.beginPath();
            for (let s = 0; s <= 1.0001; s += 0.02) {
                const [x, y] = onWire(g, k, s);
                s === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            }
            ctx.stroke();
        }
        ctx.restore();
    }

    function drawSplitLine(alpha) {
        if (alpha <= 0.01) return;
        const h = region.h * 0.95 * alpha;
        ctx.save();
        ctx.strokeStyle = `rgba(${INK}, 0.75)`;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(region.cx, region.cy - h / 2);
        ctx.lineTo(region.cx, region.cy + h / 2);
        ctx.stroke();
        ctx.restore();
    }

    function drawBulb(alpha, shape) {
        if (alpha <= 0.01) return;
        const { cx, cy, Rb, baseTop, baseW, baseH } = shape;
        ctx.save();
        ctx.strokeStyle = `rgba(${INK}, ${0.22 * alpha})`;
        ctx.lineWidth = 1.2;
        ctx.beginPath();
        const a = Math.asin(clamp((baseW / 2 + 4) / Rb, 0, 0.99));
        ctx.arc(cx, cy, Rb, Math.PI / 2 + a, Math.PI / 2 - a);
        ctx.lineTo(cx + baseW / 2 + 2, baseTop);
        ctx.lineTo(cx + baseW / 2 + 2, baseTop + baseH);
        ctx.lineTo(cx - baseW / 2 - 2, baseTop + baseH);
        ctx.lineTo(cx - baseW / 2 - 2, baseTop);
        ctx.closePath();
        ctx.stroke();
        ctx.restore();
    }

    function frame(time) {
        const i = Math.min(Math.floor(p), 2);
        const t = p - i;
        const A = i === 2 ? flow(time) : layouts[i];
        const B = i + 1 === 2 ? flow(time) : layouts[i + 1];
        const wOf = (k) => (k === i ? 1 - ease(t) : k === i + 1 ? ease(t) : 0); // weight of arrangement k

        ctx.clearRect(0, 0, W, H);
        drawSplitLine(wOf(1));
        drawTowers(wOf(2));
        drawBulb(wOf(3), layouts[3].shape);

        hovered = null;
        let best = Infinity;

        dots.forEach((d, j) => {
            const e = ease(clamp((t - d.delay) / 0.7));
            const drift = lerp(A.drift, B.drift, e);
            let x = lerp(A.pts[j][0], B.pts[j][0], e) + Math.sin(time * 0.6 + d.phase) * drift;
            let y = lerp(A.pts[j][1], B.pts[j][1], e) + Math.cos(time * 0.5 + d.phase * 1.3) * drift;

            const size = lerp(A.sizes[j], B.sizes[j], e);

            // Neighbours give way to the pointer and spring back; the dot right under it stays put to be hovered.
            const dx = x - pointer.x;
            const dy = y - pointer.y;
            const dist = Math.hypot(dx, dy);
            const push = dist > size + 6 && dist < 110 ? ((110 - dist) / 110) * 26 : 0;
            d.ox += ((push ? (dx / dist) * push : 0) - d.ox) * 0.12;
            d.oy += ((push ? (dy / dist) * push : 0) - d.oy) * 0.12;
            x += d.ox;
            y += d.oy;

            const litA = A.lit ? A.lit[j] : d.kind === 'yellow' ? 1 : 0;
            const litB = B.lit ? B.lit[j] : d.kind === 'yellow' ? 1 : 0;
            const lit = lerp(litA, litB, e);
            const c = BLUE.map((v, k) => Math.round(lerp(v, YELLOW[k], lit)));
            const alpha = highlight[d.kind] || p > 1.6 ? 1 : 0.15;

            ctx.fillStyle = `rgba(${c[0]}, ${c[1]}, ${c[2]}, ${alpha})`;
            ctx.beginPath();
            ctx.arc(x, y, size, 0, Math.PI * 2);
            ctx.fill();
            if (lit > 0.5) {
                ctx.strokeStyle = `rgba(214, 160, 0, ${0.5 * alpha})`; // keeps yellow legible on white
                ctx.lineWidth = 1;
                ctx.stroke();
            }

            d.x = x;
            d.y = y;
            d.rr = size;
            const pd = Math.hypot(pointer.x - x, pointer.y - y);
            if (pd < size + 6 && pd < best) {
                best = pd;
                hovered = d;
            }
        });

        // Labels cross-fade with their arrangement.
        Object.entries(labelEls).forEach(([key, el]) => {
            let pos = null;
            let alpha = 0;
            [0, 1, 2, 3].forEach((k) => {
                const L = k === 2 ? (k === i ? A : k === i + 1 ? B : null) : layouts[k];
                if (L?.labels[key] && wOf(k) > alpha) {
                    alpha = wOf(k);
                    pos = L.labels[key];
                }
            });
            el.style.opacity = String(clamp((alpha - 0.4) / 0.5));
            if (pos) {
                const half = el.offsetWidth / 2;
                el.style.transform = `translate(${clamp(pos[0], half + 8, W - half - 8)}px, ${pos[1]}px) translate(-50%, -50%)`;
            }
        });

        legend.style.opacity = String(clamp((1.7 - p) / 0.4));
        legend.style.pointerEvents = p < 1.5 ? 'auto' : 'none';

        updateTip();
    }

    function updateTip() {
        if (!hovered || p > 1.6) {
            tip.style.opacity = '0';
            pin.style.cursor = '';
            return;
        }
        pin.style.cursor = 'pointer';
        const [code, name] = hovered.type;
        tip.children[0].textContent = code;
        tip.children[1].textContent = name;
        tip.children[2].textContent = hovered.kind === 'blue' ? 'Pembangkit konvensional' : 'Energi baru terbarukan';
        tip.style.borderColor = hovered.kind === 'blue' ? '#3498db' : '#ffcd00';
        const left = clamp(hovered.x - tip.offsetWidth / 2, 8, W - tip.offsetWidth - 8);
        const top = hovered.y - hovered.rr - tip.offsetHeight - 12;
        tip.style.transform = `translate(${left}px, ${top < 90 ? hovered.y + hovered.rr + 12 : top}px)`;
        tip.style.opacity = '1';
    }

    // --- Wiring ----------------------------------------------------------------------------
    measure();

    // pointerdown too, so a tap on a phone picks a dot.
    ['pointermove', 'pointerdown'].forEach((type) => pin.addEventListener(type, (e) => {
        const box = pin.getBoundingClientRect();
        pointer.x = e.clientX - box.left;
        pointer.y = e.clientY - box.top;
    }));
    pin.addEventListener('pointerleave', () => ((pointer.x = -9999), (pointer.y = -9999)));

    legend.querySelectorAll('[data-es-filter]').forEach((button) =>
        button.addEventListener('click', () => {
            const kind = button.dataset.esFilter;
            const other = kind === 'blue' ? 'yellow' : 'blue';
            // Isolate the clicked kind; clicking it again shows both.
            const isolated = highlight[kind] && !highlight[other];
            highlight[kind] = true;
            highlight[other] = isolated;
            legend.querySelectorAll('[data-es-filter]').forEach((b) => b.setAttribute('aria-pressed', String(highlight[b.dataset.esFilter])));
        }),
    );

    if (reduceMotion) {
        p = 1;
        frame(0);
        window.addEventListener('resize', () => (measure(), frame(0)));
        return;
    }

    const proxy = { p: 0 };
    gsap.to(proxy, {
        p: 3,
        ease: 'none',
        onUpdate: () => (p = proxy.p),
        scrollTrigger: {
            trigger: steps[1],
            start: 'top bottom',
            endTrigger: steps[steps.length - 1],
            end: 'top top',
            scrub: 0.7,
            invalidateOnRefresh: true,
        },
    });

    // On phones the back-to-top button would cover the cards while the story is pinned.
    ScrollTrigger.create({ trigger: section, start: 'top top', end: 'bottom 30%', toggleClass: { targets: document.body, className: 'story-pinned' } });

    // Only animate while the section is on screen.
    let running = false;
    const tick = (time) => frame(time);
    ScrollTrigger.create({
        trigger: section,
        start: 'top bottom',
        end: 'bottom top',
        onToggle: (self) => {
            if (self.isActive && !running) gsap.ticker.add(tick);
            if (!self.isActive && running) gsap.ticker.remove(tick);
            running = self.isActive;
        },
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(measure, 150);
    });
}
