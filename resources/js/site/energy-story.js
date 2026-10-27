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

        layouts = [scatter(), split(), null, null]; // 2 (flow) and 3 (bulb) are built per frame
        buildBulb();
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
        return { xL, xR, top, sag, gap: mobile() ? region.h * 0.12 : Math.min(34, region.h * 0.08) };
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
        // Short wires on phones: show every other dot per wire (the rest fade back in for the bulb) and keep them small.
        const sparse = mobile();
        return {
            pts,
            sizes: dots.map((d) => (sparse ? 1.6 + Math.min(d.size, 4) * 0.35 : 2 + Math.min(d.size, 6) * 0.45)),
            alpha: dots.map((_, i) => (sparse && Math.floor(i / 3) % 2 ? 0 : 1)),
            drift: 0,
            labels: { plant: [g.xL, g.top - region.h * 0.15], grid: [g.xR, g.top - region.h * 0.15] },
        };
    }

    // Light bulb as a halftone: a hexagonal grid of dots clipped to a globe-and-neck silhouette, dot size
    // and warmth falling off from the filament outwards; a screw base of blue ridge/groove rows; and faint
    // halo rings above the glass. No outlines — the dots alone draw the shape.
    // The 170 travelling dots take the brightest grid points; the remaining points (glass edge, base, halo)
    // form a static layer that fades in as the bulb assembles.
    let bulbGrid = null;

    function buildBulb() {
        const Rb = Math.min(region.w * 0.24, (region.h - 90) / 4.1); // leaves room for the cable and label
        const cx = region.cx;
        const cy = region.y + 34 + Rb * 1.66;
        const phi = (40 * Math.PI) / 180; // where the globe turns into the neck, from straight down
        const neckTop = cy + Rb * Math.cos(phi);
        const neckY = cy + Rb * 1.36;
        const neckW = Rb * 0.4;
        const baseH = Rb * 0.62;
        const baseBottom = neckY + baseH;
        const tipR = neckW * 0.48;
        const step = Rb * 0.15; // grid pitch
        const focus = [cx, cy + Rb * 0.1];

        const halfWidth = (y) => {
            if (y <= neckTop) return Math.sqrt(Math.max(0, Rb * Rb - (y - cy) ** 2));
            const t = clamp((y - neckTop) / (neckY - neckTop));
            return lerp(Rb * Math.sin(phi), neckW, 1 - (1 - t) ** 2); // concave sweep into the neck
        };

        const core = [];
        const base = [];
        const rowH = step * 0.866;
        for (let row = 0, y = cy - Rb + step * 0.4; y <= baseBottom + tipR; row++, y += rowH) {
            const offset = row % 2 ? step / 2 : 0;
            for (let x = cx - Rb - step + offset; x <= cx + Rb + step; x += step) {
                const dx = Math.abs(x - cx);
                if (y < neckY) {
                    if (dx <= halfWidth(y) - step * 0.32) {
                        const k = clamp(Math.hypot(x - focus[0], y - focus[1]) / (Rb * 1.05));
                        core.push({ x, y, k, part: 'glass' });
                    }
                } else if (y <= baseBottom - step * 0.2) {
                    if (dx <= neckW - step * 0.35) base.push({ x, y, k: 0, part: (Math.round((y - neckY) / rowH) % 2 ? 'groove' : 'ridge') });
                } else if (dx <= Math.sqrt(Math.max(0, tipR * tipR - (y - baseBottom) ** 2)) - step * 0.2) {
                    base.push({ x, y, k: 0, part: 'tip' });
                }
            }
        }
        core.sort((a, b) => a.k - b.k);

        const halo = [];
        [1.2, 1.42, 1.66].forEach((m, ring) => {
            const r = Rb * m;
            const from = (128 * Math.PI) / 180; // all the way round, leaving a gap under the base
            const to = (412 * Math.PI) / 180;
            const count = Math.max(4, Math.round(((to - from) * r) / (step * (1.2 + ring * 0.25))));
            for (let n = 0; n < count; n++) {
                const a = lerp(from, to, n / (count - 1));
                halo.push({ x: cx + Math.cos(a) * r, y: cy + Math.sin(a) * r, k: 1, part: 'halo', ring, d: r });
            }
        });

        bulbGrid = {
            shape: { Rb, cx, cy, neckY, neckW, baseBottom, tipR, step, focus },
            points: [...core, ...base, ...halo], // travelling dots take these in order
        };
    }

    const AMBER_HOT = [255, 222, 64];
    const AMBER_EDGE = [238, 160, 0];
    const BRIGHT = [255, 246, 190];
    const COLD = [196, 208, 222];
    const RIDGE = [52, 152, 219];
    const GROOVE = [31, 95, 143];
    const TIP_COLOR = [23, 63, 95];

    // Switching on: once the bulb has assembled, a spark runs in through the cable, the filament
    // flickers like a real lamp, then light floods the glass from the centre and keeps pulsing outwards.
    // Scrolling back up switches it off again.
    const FLICKER = [0.75, 0.05, 0.95, 0.2, 1];
    let igniteAt = null;
    let energy = { s: -1, pw: 0, reach: 0 };

    function updateEnergy(time) {
        if (p >= 2.9 && igniteAt === null) igniteAt = time;
        if (p < 2.6) igniteAt = null;
        const since = igniteAt === null ? -1 : time - igniteAt;
        let pw = 0;
        if (since >= 0.6) {
            const k = Math.floor((since - 0.6) / 0.11);
            pw = k < FLICKER.length ? FLICKER[k] : 1;
        }
        const flood = since < 0.9 ? 0 : ease(clamp((since - 0.9) / 0.9));
        energy = { s: since, pw, reach: bulbGrid.shape.Rb * (0.35 + flood * 1.75) };
    }

    /** How lit a point at distance d from the filament is (0 cold – 1 on), and the passing light wave there. */
    function lightAt(d) {
        const { Rb, step } = bulbGrid.shape;
        const on = energy.pw * clamp((energy.reach - d) / (step * 2.5));
        let wave = 0;
        if (energy.s > 2) {
            const rw = (((energy.s - 2) % 2.2) / 2.2) * Rb * 2.1;
            wave = Math.exp(-(((d - rw) / (step * 1.3)) ** 2));
        }
        return { on, wave: wave * on };
    }

    const mix = (a, b, t) => a.map((v, c) => lerp(v, b[c], t));

    /** Size, colour and opacity of a bulb point at a given time. */
    function bulbStyle(pt, time) {
        const { step, focus } = bulbGrid.shape;
        switch (pt.part) {
            case 'glass': {
                const d = Math.hypot(pt.x - focus[0], pt.y - focus[1]);
                const { on, wave } = lightAt(d);
                const shimmer = 1 + 0.06 * Math.sin(time * 2.2 - d / (step * 1.1));
                const warmSize = step * 0.45 * (1 - 0.7 * Math.pow(pt.k, 1.15)) * shimmer * (1 + 0.45 * wave);
                const warm = mix(mix(AMBER_HOT, AMBER_EDGE, Math.pow(pt.k, 0.8)), BRIGHT, 0.6 * wave);
                return { size: lerp(step * 0.17, warmSize, on), color: mix(COLD, warm, on), alpha: 1 };
            }
            case 'ridge':
                return { size: step * 0.28, color: RIDGE, alpha: 1 };
            case 'groove':
                return { size: step * 0.2, color: GROOVE, alpha: 1 };
            case 'tip':
                return { size: step * 0.24, color: TIP_COLOR, alpha: 1 };
            default: {
                const { on, wave } = lightAt(pt.d);
                const pulse = 0.5 + 0.5 * Math.sin(time * 2.6 - pt.ring * 1.1); // rings breathe outwards
                return {
                    size: step * [0.2, 0.15, 0.11][pt.ring] * (1 + 0.8 * wave),
                    color: mix(AMBER_EDGE, AMBER_HOT, wave),
                    alpha: clamp(([0.8, 0.5, 0.28][pt.ring] * pulse + 0.7 * wave) * on),
                };
            }
        }
    }

    function bulb(time) {
        const pts = [];
        const sizes = [];
        const colors = [];
        const alpha = [];
        const last = bulbGrid.points[bulbGrid.points.length - 1];
        for (let j = 0; j < N; j++) {
            const pt = bulbGrid.points[j];
            const st = bulbStyle(pt || last, time);
            pts[j] = [(pt || last).x, (pt || last).y];
            sizes[j] = st.size;
            colors[j] = st.color;
            alpha[j] = pt ? st.alpha : 0; // more travelling dots than grid points: hide the spares
        }
        return {
            pts,
            sizes,
            colors,
            alpha,
            drift: 0,
            labels: { home: [bulbGrid.shape.cx, bulbGrid.shape.baseBottom + bulbGrid.shape.tipR + bulbGrid.shape.step * 1.6 + 30] }, // below the power cable
            shape: bulbGrid.shape,
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

    /** Grid points not taken by travelling dots; they fade in with the bulb. */
    function drawBulbRest(alpha, time) {
        if (alpha <= 0.01) return;
        bulbGrid.points.slice(N).forEach((pt) => {
            const st = bulbStyle(pt, time);
            ctx.fillStyle = `rgba(${st.color.map(Math.round).join(', ')}, ${st.alpha * alpha})`;
            ctx.beginPath();
            ctx.arc(pt.x, pt.y, Math.max(0, st.size), 0, Math.PI * 2);
            ctx.fill();
        });
    }

    /** Support wires and the coiled filament: hairlines over the dots, the drawing's focal point. */
    function drawFilament(alpha, time) {
        if (alpha <= 0.01) return;
        const { Rb, cx, neckY, neckW, focus } = bulbGrid.shape;
        const coil = (u) => [cx + (u - 0.5) * Rb * 0.56, focus[1] - Rb * 0.04 - Math.sin(u * Math.PI) * Rb * 0.1 + Math.sin(u * Math.PI * 7) * Rb * 0.035];
        const [lx, ly] = coil(0);
        const [rx, ry] = coil(1);
        const pw = energy.pw;
        const trace = () => {
            ctx.beginPath();
            for (let u = 0; u <= 1.0001; u += 0.01) {
                const [x, y] = coil(u);
                u ? ctx.lineTo(x, y) : ctx.moveTo(x, y);
            }
            ctx.stroke();
        };
        ctx.save();
        ctx.lineCap = 'round';
        ctx.strokeStyle = `rgba(${INK}, ${0.45 * alpha})`;
        ctx.lineWidth = 1.1;
        ctx.beginPath();
        ctx.moveTo(cx - neckW * 0.3, neckY);
        ctx.lineTo(lx, ly);
        ctx.moveTo(cx + neckW * 0.3, neckY);
        ctx.lineTo(rx, ry);
        ctx.stroke();
        const wire = mix([110, 116, 124], [170, 100, 0], pw); // grey when off, hot amber when on
        ctx.strokeStyle = `rgba(${wire.map(Math.round).join(', ')}, ${(0.8 + 0.12 * Math.sin(time * 9) * pw) * alpha})`;
        ctx.lineWidth = 1.3 + pw * 1.2;
        trace();
        if (pw > 0.5) {
            ctx.strokeStyle = `rgba(255, 244, 170, ${pw * alpha})`; // white-hot core of the wire
            ctx.lineWidth = 0.9;
            trace();
        }
        ctx.restore();
    }

    /** Power cable into the base: current flows along it and sparks run up to the filament. */
    function drawFeed(alpha, time) {
        if (alpha <= 0.01) return;
        const { Rb, cx, neckY, baseBottom, tipR, step, focus } = bulbGrid.shape;
        const y = baseBottom + tipR + step * 1.6;
        const path = [
            [cx - Rb * 1.9, y],
            [cx - step * 1.2, y],
            [cx, baseBottom + tipR * 0.7],
            [cx, neckY],
            [cx, focus[1] + Rb * 0.05],
        ];
        ctx.save();
        ctx.lineCap = 'round';
        ctx.beginPath();
        ctx.moveTo(...path[0]);
        ctx.lineTo(...path[1]);
        ctx.quadraticCurveTo(cx, y, ...path[2]);
        ctx.strokeStyle = `rgba(${INK}, ${0.4 * alpha})`;
        ctx.lineWidth = 1.6;
        ctx.stroke();
        ctx.setLineDash([5, 11]);
        ctx.lineDashOffset = -time * 46; // current flowing in
        ctx.strokeStyle = `rgba(238, 160, 0, ${0.9 * alpha})`;
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.setLineDash([]);

        // Sparks along the whole route: cable → base → stem → filament.
        const lengths = path.slice(1).map((pt, k) => Math.hypot(pt[0] - path[k][0], pt[1] - path[k][1]));
        const total = lengths.reduce((a, b) => a + b, 0);
        for (let n = 0; n < 2; n++) {
            let dist = (((time * 0.75 + n * 0.5) % 1) * total);
            let k = 0;
            while (k < lengths.length - 1 && dist > lengths[k]) dist -= lengths[k++];
            const f = dist / lengths[k];
            const x = lerp(path[k][0], path[k + 1][0], f);
            const yy = lerp(path[k][1], path[k + 1][1], f);
            ctx.fillStyle = `rgba(255, 246, 190, ${alpha})`;
            ctx.strokeStyle = `rgba(214, 140, 0, ${alpha})`;
            ctx.lineWidth = 1.2;
            ctx.beginPath();
            ctx.arc(x, yy, Math.max(2.5, step * 0.2), 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
        }
        ctx.restore();
    }

    function frame(time) {
        const i = Math.min(Math.floor(p), 2);
        const t = p - i;
        updateEnergy(time);
        const live = (k) => (k === 2 ? flow(time) : k === 3 ? bulb(time) : layouts[k]); // 2 and 3 move with time
        const A = live(i);
        const B = live(i + 1);
        const wOf = (k) => (k === i ? 1 - ease(t) : k === i + 1 ? ease(t) : 0); // weight of arrangement k

        ctx.clearRect(0, 0, W, H);
        drawSplitLine(wOf(1));
        drawTowers(wOf(2));
        if (i === 2) drawBulbRest(ease(clamp((t - 0.35) / 0.65)), time); // the bulb only shows on the last stretch

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

            // Colour: an arrangement either sets it per dot (the bulb) or it follows the dot's kind.
            const colorOf = (L) => (L.colors ? L.colors[j] : d.kind === 'yellow' ? YELLOW : BLUE);
            const cA = colorOf(A);
            const cB = colorOf(B);
            const c = cA.map((v, k) => Math.round(lerp(v, cB[k], e)));
            const ringOf = (L) => (L.colors ? 0 : d.kind === 'yellow' ? 1 : 0); // halftone dots need no outline
            const lit = lerp(ringOf(A), ringOf(B), e);
            const fade = lerp(A.alpha ? A.alpha[j] : 1, B.alpha ? B.alpha[j] : 1, e);
            const alpha = (highlight[d.kind] || p > 1.6 ? 1 : 0.15) * fade;

            ctx.fillStyle = `rgba(${c[0]}, ${c[1]}, ${c[2]}, ${alpha})`;
            ctx.beginPath();
            ctx.arc(x, y, Math.max(0, size), 0, Math.PI * 2);
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

        if (i === 2) {
            drawFilament(ease(clamp((t - 0.55) / 0.45)), time);
            drawFeed(ease(clamp((t - 0.6) / 0.4)), time);
        }

        // Labels cross-fade with their arrangement.
        Object.entries(labelEls).forEach(([key, el]) => {
            let pos = null;
            let alpha = 0;
            [0, 1, 2, 3].forEach((k) => {
                const L = k === i ? A : k === i + 1 ? B : layouts[k];
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
