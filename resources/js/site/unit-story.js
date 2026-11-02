import gsap from 'gsap';
import { REGIONS, rasterIndonesia } from './indonesia-map';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * "Jaringan dalam angka" scrollytelling: one square per service unit, pinned
 * while narrative steps scroll past. The squares morph continuously with the
 * scroll position between four arrangements:
 *   0 mixed grid → 1 clusters per work area → 2 a dot-map of Indonesia → 3 bars.
 * Every arrangement is scaled to fill the stage. Squares show a tooltip on
 * hover; legend buttons filter work areas.
 */
const clamp = (v, min = 0, max = 1) => Math.min(max, Math.max(min, v));
const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
const GAP = 0.74; // square size relative to its grid cell
const PHONE_LABEL = 100; // px; map labels on phones share a row with two others

export function initUnitStory(reduceMotion) {
    const section = document.querySelector('[data-unit-story]');
    if (!section) return;

    const areas = JSON.parse(section.dataset.areas || '[]');
    const stage = section.querySelector('[data-us-stage]');
    const tip = section.querySelector('[data-us-tip]');
    const legend = section.querySelector('[data-us-legend]');
    const pinned = stage.parentElement;
    const stepEls = [...section.querySelectorAll('[data-us-step]')];
    const total = areas.reduce((sum, a) => sum + a.units, 0);
    const per = Math.max(1, Math.ceil(total / 300)); // keep the DOM light for big networks
    const active = new Set(areas.map((_, i) => i));

    // --- Squares & labels -------------------------------------------------------------
    const squares = [];
    areas.forEach((area, ai) => {
        const count = Math.ceil(area.units / per);
        for (let i = 0; i < count; i++) {
            const el = document.createElement('span');
            el.className = 'pointer-events-auto absolute top-0 left-0 rounded-[2px] transition-[opacity] duration-300 will-change-transform';
            el.style.background = area.color;
            el.dataset.area = ai;
            stage.append(el);
            squares.push({ el, area: ai, index: i, count });
        }
    });
    const counts = areas.map((_, i) => squares.filter((s) => s.area === i).length);

    // Deterministic shuffle for the opening mixed grid.
    const mixedOrder = squares.map((s, i) => ({ i, k: Math.sin(i * 9301 + 49297) })).sort((a, b) => a.k - b.k).map((o) => o.i);

    const labels = areas.map((area) => {
        const el = document.createElement('div');
        el.className = 'pointer-events-none absolute top-0 left-0 font-display text-[13px] leading-snug font-bold text-ink opacity-0 will-change-transform';
        el.innerHTML = '<span></span> <span class="font-sans font-normal whitespace-nowrap text-subtle"></span>';
        el.children[0].textContent = area.name;
        el.children[1].textContent = `${area.units} unit`;
        stage.append(el);
        return el;
    });

    // --- Arrangements in grid units (1 = one cell), later fitted to the stage ------------
    const mobile = () => stage.clientWidth < 640;

    function mixed(aspect) {
        const cols = Math.max(6, Math.round(Math.sqrt(squares.length * aspect)));
        const pts = [];
        mixedOrder.forEach((sq, i) => (pts[sq] = [i % cols, Math.floor(i / cols)]));
        return { pts, labels: [] };
    }

    function clusters() {
        const cols = Math.max(4, Math.ceil(Math.sqrt(Math.max(...counts) * 0.75)));
        const perRow = mobile() ? Math.min(2, areas.length) : areas.length;
        const gapX = mobile() ? 4 : 3;
        const gapY = mobile() ? 5 : 3.2; // room for the label above each cluster (labels wrap on phones)
        const rowsOf = (i) => Math.ceil(counts[i] / cols);
        const pts = [];
        const anchors = [];
        let y = gapY;

        for (let r = 0; r * perRow < areas.length; r++) {
            const members = areas.map((_, i) => i).slice(r * perRow, r * perRow + perRow);
            const rowW = members.length * cols + (members.length - 1) * gapX;
            const offset = ((perRow * cols + (perRow - 1) * gapX) - rowW) / 2; // centre short rows
            members.forEach((ai, c) => {
                const x0 = offset + c * (cols + gapX);
                anchors[ai] = { x: x0 + cols / 2, y, align: -50, dy: -6 };
                squares.forEach((s, j) => {
                    if (s.area === ai) pts[j] = [x0 + (s.index % cols), y + Math.floor(s.index / cols)];
                });
            });
            y += Math.max(...members.map(rowsOf)) + gapY;
        }
        return { pts, labels: anchors };
    }

    const regionOf = (area, ai) => {
        const r = REGIONS.findIndex(({ match }) => match.test(area.name));
        return r < 0 ? ai % REGIONS.length : r;
    };
    const hash = (n) => {
        const v = Math.sin(n * 127.1 + 311.7) * 43758.5453;
        return v - Math.floor(v);
    };

    /**
     * A dot-map of Indonesia. Faint squares draw the islands; each work area's unit squares
     * are scattered over the cells of its own region. Phones keep cells of ~5 px, so a busy
     * region (Jawa) fills up solid and its extra units stack instead of shrinking the map.
     */
    function indonesia() {
        const phone = mobile();
        const need = REGIONS.map(() => 0);
        areas.forEach((area, ai) => (need[regionOf(area, ai)] += counts[ai]));
        const map = rasterIndonesia(need, phone ? (stage.clientWidth * 0.92) / 5 : Infinity);

        // Scatter: each region's cells in a fixed pseudo-random order, handed out area by area.
        const pools = REGIONS.map((_, r) =>
            map.cells.filter((c) => c.region === r).sort((a, b) => hash(a.gx * 997 + a.gy) - hash(b.gx * 997 + b.gy)),
        );
        const taken = REGIONS.map(() => 0);
        const used = new Set();
        const pts = [];
        const anchors = [];
        areas.forEach((area, ai) => {
            const r = regionOf(area, ai);
            const pool = pools[r].length ? pools[r] : map.cells;
            squares.forEach((sq, j) => {
                if (sq.area !== ai) return;
                const cell = pool[taken[r]++ % pool.length]; // more units than land: they stack
                used.add(cell);
                pts[j] = [cell.gx, cell.gy];
            });
            const { label, below, phone: row } = REGIONS[r];
            if (phone) {
                const [lx] = map.toGrid([row.lon, 0]);
                anchors[ai] = row.below
                    ? { x: lx + 0.37, y: map.rows, align: -50, top: true, dy: 8, w: PHONE_LABEL }
                    : { x: lx + 0.37, y: 0, align: -50, dy: -8, w: PHONE_LABEL };
            } else {
                const [lx, ly] = map.toGrid(label);
                anchors[ai] = { x: lx + 0.37, y: ly, align: -50, dy: below ? 22 : -4 };
            }
        });

        return {
            pts,
            labels: anchors,
            extent: phone ? [0, -9, map.cols, map.rows + 9] : [0, 0, map.cols, map.rows], // phones: label rows above and below
            land: map.cells.filter((c) => !used.has(c)).map((c) => [c.gx, c.gy]),
        };
    }

    function bars() {
        const order = areas.map((_, i) => i).sort((a, b) => areas[b].units - areas[a].units);
        const maxLine = mobile() ? Math.ceil(Math.max(...counts) / 2) : Math.max(...counts);
        const pts = [];
        const anchors = [];
        let y = mobile() ? 2 : 0;

        order.forEach((ai) => {
            anchors[ai] = mobile() ? { x: 0, y, align: 0, dy: -3 } : { x: 0, y: y + 0.5, align: -100, dx: -14, dy: 9 };
            squares.forEach((s, j) => {
                if (s.area === ai) pts[j] = [s.index % maxLine, y + Math.floor(s.index / maxLine)];
            });
            y += Math.ceil(counts[ai] / maxLine) + (mobile() ? 2.6 : 0.9);
        });
        return { pts, labels: anchors, reserveLeft: mobile() ? 0 : 200 };
    }

    /** Scale an arrangement so it fills the free part of the stage, then convert to px. */
    function fit({ pts, labels: anchors, reserveLeft = 0, extent = null, land = null }) {
        const W = stage.clientWidth;
        const H = stage.clientHeight;
        const top = mobile() ? 215 : 195; // below the narrative card
        const bottom = H - (mobile() ? 70 : 76); // above the legend (one scrollable row on phones)
        const availW = Math.min(W * 0.92, 1180) - reserveLeft;
        const availH = Math.max(120, bottom - top);

        const xs = pts.map((p) => p[0]);
        const ys = pts.map((p) => p[1]);
        const minX = extent ? extent[0] : Math.min(...xs);
        const minY = Math.min(extent ? extent[1] : Math.min(...ys), ...anchors.filter(Boolean).map((a) => a.y - (mobile() ? 3.4 : 1.6)));
        const bw = (extent ? extent[2] : Math.max(...xs) + 1) - minX;
        const bh = (extent ? extent[3] : Math.max(...ys) + 1) - minY;
        const k = clamp(Math.min(availW / bw, availH / bh), extent ? 4 : 6, mobile() ? 16 : 26);

        const ox = (W - (bw * k + reserveLeft)) / 2 + reserveLeft - minX * k;
        const oy = top + (availH - bh * k) / 2 - minY * k;

        return {
            k,
            x: pts.map((p) => ox + p[0] * k),
            y: pts.map((p) => oy + p[1] * k),
            size: k * GAP,
            land: land && land.map(([gx, gy]) => [ox + gx * k, oy + gy * k]),
            labels: areas.map((_, i) => {
                const a = anchors[i];
                if (!a) return null;
                const lx = ox + a.x * k + (a.dx || 0);
                const half = a.w ? a.w / 2 : labels[i].offsetWidth / 2;
                return {
                    x: a.align === -50 ? clamp(lx, half + 8, W - half - 8) : Math.max(8, lx),
                    y: oy + a.y * k + (a.dy || 0),
                    align: a.align,
                    top: a.top, // hang below the anchor instead of sitting on it
                    w: a.w,
                };
            }),
        };
    }

    const landCanvas = document.createElement('canvas');
    landCanvas.className = 'pointer-events-none absolute inset-0 h-full w-full';
    landCanvas.setAttribute('aria-hidden', 'true');
    stage.prepend(landCanvas);
    const landCtx = landCanvas.getContext('2d');
    let landAlpha = -1;

    function drawLand(alpha) {
        if (Math.abs(alpha - landAlpha) < 0.01) return;
        landAlpha = alpha;
        landCtx.clearRect(0, 0, landCanvas.width, landCanvas.height);
        const F = frames[2];
        if (!F?.land || alpha <= 0) return;
        landCtx.fillStyle = `rgba(160, 182, 204, ${0.45 * alpha})`;
        F.land.forEach(([x, y]) => {
            landCtx.beginPath();
            landCtx.roundRect(x, y, F.size, F.size, Math.min(2, F.size * 0.2));
            landCtx.fill();
        });
    }

    let frames = [];
    let labelWidth = '';
    const build = () => {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        landCanvas.width = stage.clientWidth * dpr;
        landCanvas.height = stage.clientHeight * dpr;
        landCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        landAlpha = -1;
        labelWidth = mobile() ? `${Math.min(170, stage.clientWidth / 2 - 20)}px` : '';
        labels.forEach((el) => {
            el.style.whiteSpace = mobile() ? 'normal' : 'nowrap';
            el.style.width = labelWidth;
        });
        const aspect = clamp((stage.clientWidth * 0.92) / Math.max(1, stage.clientHeight - 290), 0.6, 4);
        frames = [fit(mixed(aspect)), fit(clusters()), fit(indonesia()), fit(bars())];
    };

    // --- Rendering for a fractional step position p ∈ [0, frames.length - 1] -----------
    let lastP = 0;
    function render(p) {
        lastP = p = clamp(p, 0, frames.length - 1);
        drawLand(clamp(1 - Math.abs(p - 2) * 1.4)); // the islands show around the map arrangement
        const i = Math.min(Math.floor(p), frames.length - 2);
        const t = p - i;
        const A = frames[i];
        const B = frames[i + 1];
        const W = stage.clientWidth;

        for (let j = 0; j < squares.length; j++) {
            // Wave: squares heading further right start a little later.
            const delay = (B.x[j] / W) * 0.32;
            const e = ease(clamp((t - delay) / 0.68));
            const x = A.x[j] + (B.x[j] - A.x[j]) * e;
            const y = A.y[j] + (B.y[j] - A.y[j]) * e;
            const size = A.size + (B.size - A.size) * e;
            const el = squares[j].el;
            el.style.transform = `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, 0)`;
            el.style.width = el.style.height = `${size.toFixed(1)}px`;
        }

        const te = ease(t);
        labels.forEach((el, a) => {
            const la = A.labels[a];
            const lb = B.labels[a];
            let pos = lb || la;
            let alpha = 0;
            if (la && lb) {
                // Cross-fade: fade out where it was, fade in where it's going.
                pos = te < 0.5 ? la : lb;
                alpha = Math.abs(1 - 2 * te);
            } else if (lb) alpha = ease(clamp((t - 0.55) / 0.45));
            else if (la) alpha = 1 - ease(clamp(t / 0.4));
            if (!pos) return (el.style.opacity = '0');
            el.style.width = pos.w ? `${pos.w}px` : labelWidth; // swapped while the label is faded out
            el.style.transform = `translate3d(${pos.x.toFixed(1)}px, ${pos.y.toFixed(1)}px, 0) translate(${pos.align}%, ${pos.top ? 0 : -100}%)`;
            el.style.textAlign = pos.align === -50 ? 'center' : pos.align === -100 ? 'right' : 'left';
            el.style.opacity = String(alpha * (active.has(a) ? 1 : 0.35));
        });

        const legendAlpha = clamp((p - 1.35) / 0.5);
        legend.style.opacity = String(legendAlpha);
        legend.style.pointerEvents = legendAlpha > 0.5 ? 'auto' : 'none';
    }

    // --- Filters & tooltip ----------------------------------------------------------------
    legend.querySelectorAll('[data-us-filter]').forEach((button) =>
        button.addEventListener('click', () => {
            const i = Number(button.dataset.usFilter);
            // First click isolates an area; clicking the only highlighted one resets.
            if (active.has(i) && active.size === 1) areas.forEach((_, k) => active.add(k));
            else if (active.size === areas.length) {
                active.clear();
                active.add(i);
            } else active.has(i) ? active.delete(i) : active.add(i);
            legend.querySelectorAll('[data-us-filter]').forEach((b) => b.setAttribute('aria-pressed', String(active.has(Number(b.dataset.usFilter)))));
            squares.forEach((s) => (s.el.style.opacity = active.has(s.area) ? '1' : '0.12'));
            render(lastP);
        }),
    );

    stage.addEventListener('pointerover', (e) => {
        const el = e.target.closest('[data-area]');
        if (!el) return;
        const s = squares.find((q) => q.el === el);
        const area = areas[s.area];
        tip.style.background = area.color;
        tip.replaceChildren(
            Object.assign(document.createElement('p'), { className: 'font-display font-bold', textContent: area.name }),
            Object.assign(document.createElement('p'), { className: 'mt-0.5 text-xs text-white/85', textContent: `Unit ${(s.index * per + 1).toLocaleString('id-ID')} dari ${area.units.toLocaleString('id-ID')}` }),
            Object.assign(document.createElement('p'), { className: 'mt-2 text-[13px] leading-snug', textContent: area.provinces || '' }),
        );
        const r = el.getBoundingClientRect();
        const box = pinned.getBoundingClientRect();
        const left = Math.min(Math.max(8, r.left - box.left - 128), box.width - 264);
        const top = r.top - box.top - tip.offsetHeight - 10;
        tip.style.transform = `translate(${left}px, ${top < 70 ? r.bottom - box.top + 10 : top}px)`;
        tip.style.opacity = '1';
    });
    stage.addEventListener('pointerout', (e) => {
        if (e.target.closest('[data-area]')) tip.style.opacity = '0';
    });

    // --- Scroll wiring ----------------------------------------------------------------------
    build();
    const proxy = { p: reduceMotion ? 2 : 0 };
    render(proxy.p);

    // On phones the back-to-top button would cover the cards while the story is pinned.
    ScrollTrigger.create({ trigger: section, start: 'top top', end: 'bottom 30%', toggleClass: { targets: document.body, className: 'story-pinned' } });

    if (!reduceMotion && stepEls.length > 1) {
        // From the moment the 2nd card enters until the last card reaches the top,
        // every bit of scroll advances the morph — no dead zones.
        gsap.to(proxy, {
            p: frames.length - 1,
            ease: 'none',
            onUpdate: () => render(proxy.p),
            scrollTrigger: {
                trigger: stepEls[1],
                start: 'top bottom',
                endTrigger: stepEls[stepEls.length - 1],
                end: 'top top',
                scrub: 0.7,
                invalidateOnRefresh: true,
            },
        });
    }

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            build();
            render(lastP);
        }, 150);
    });
    document.fonts?.ready.then(() => {
        build(); // label widths change once web fonts load
        render(lastP);
    });
}
