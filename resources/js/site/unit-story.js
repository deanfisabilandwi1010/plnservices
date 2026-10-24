import gsap from 'gsap';

/**
 * "Jaringan dalam angka" scrollytelling: one square per service unit, pinned
 * while narrative steps scroll past. The squares morph continuously with the
 * scroll position between four arrangements:
 *   0 mixed grid → 1 clusters per work area → 2 "islands" on a west–east map → 3 bars.
 * Every arrangement is scaled to fill the stage. Squares show a tooltip on
 * hover; legend buttons filter work areas.
 */
const clamp = (v, min = 0, max = 1) => Math.min(max, Math.max(min, v));
const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
const GAP = 0.74; // square size relative to its grid cell

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
        el.className = 'pointer-events-none absolute top-0 left-0 whitespace-nowrap font-display text-[13px] font-bold text-ink opacity-0 will-change-transform';
        el.innerHTML = '<span></span> <span class="font-sans font-normal text-subtle"></span>';
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
        const gapY = 3.2; // room for the label above each cluster
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
                anchors[ai] = { x: x0 + cols / 2, y, align: -50, dy: -24 };
                squares.forEach((s, j) => {
                    if (s.area === ai) pts[j] = [x0 + (s.index % cols), y + Math.floor(s.index / cols)];
                });
            });
            y += Math.max(...members.map(rowsOf)) + gapY;
        }
        return { pts, labels: anchors };
    }

    // Rough west→east placement of work areas, matched by name (normalised 0–1).
    const GEO = [
        [/sumatera|aceh|riau|lampung|bangka/i, 0.1, 0.32],
        [/jawa|bali|madura|banten|jakarta/i, 0.36, 0.84],
        [/kalimantan/i, 0.42, 0.3],
        [/sulawesi|nusa tenggara|ntt|ntb/i, 0.64, 0.6],
        [/maluku|papua/i, 0.9, 0.42],
    ];

    /** Each work area becomes a round "island" (phyllotaxis spiral) at its rough location. */
    function islands() {
        const Wu = mobile() ? 24 : 64;
        const Hu = mobile() ? 34 : 24;
        const pts = [];
        const anchors = [];
        areas.forEach((area, ai) => {
            const hit = GEO.find(([re]) => re.test(area.name));
            const nx = hit ? hit[1] : (ai + 0.5) / areas.length;
            const ny = hit ? hit[2] : 0.5;
            const cx = mobile() ? 4 + (ai % 2) * 14 + (ai === areas.length - 1 && ai % 2 === 0 ? 7 : 0) : nx * Wu;
            const cy = mobile() ? 4 + Math.floor(ai / 2) * 11 : ny * Hu;
            const radius = Math.sqrt(counts[ai]) * 0.6;
            squares.forEach((s, j) => {
                if (s.area !== ai) return;
                const r = Math.sqrt(s.index + 0.5) * 0.6;
                const theta = s.index * 2.39996; // golden angle
                pts[j] = [cx + r * Math.cos(theta), cy + r * Math.sin(theta)];
            });
            anchors[ai] = { x: cx + 0.37, y: cy - radius - 0.6, align: -50, dy: -18 };
        });
        return { pts, labels: anchors };
    }

    function bars() {
        const order = areas.map((_, i) => i).sort((a, b) => areas[b].units - areas[a].units);
        const maxLine = mobile() ? Math.ceil(Math.max(...counts) / 2) : Math.max(...counts);
        const pts = [];
        const anchors = [];
        let y = mobile() ? 2 : 0;

        order.forEach((ai) => {
            anchors[ai] = mobile() ? { x: 0, y, align: 0, dy: -20 } : { x: 0, y: y + 0.5, align: -100, dx: -14, dy: -9 };
            squares.forEach((s, j) => {
                if (s.area === ai) pts[j] = [s.index % maxLine, y + Math.floor(s.index / maxLine)];
            });
            y += Math.ceil(counts[ai] / maxLine) + (mobile() ? 2.6 : 0.9);
        });
        return { pts, labels: anchors, reserveLeft: mobile() ? 0 : 200 };
    }

    /** Scale an arrangement so it fills the free part of the stage, then convert to px. */
    function fit({ pts, labels: anchors, reserveLeft = 0 }) {
        const W = stage.clientWidth;
        const H = stage.clientHeight;
        const top = mobile() ? 215 : 195; // below the narrative card
        const bottom = H - (mobile() ? 150 : 76); // above the legend (3 rows on phones)
        const availW = Math.min(W * 0.92, 1180) - reserveLeft;
        const availH = Math.max(120, bottom - top);

        const xs = pts.map((p) => p[0]);
        const ys = pts.map((p) => p[1]);
        const minX = Math.min(...xs);
        const minY = Math.min(...ys, ...anchors.filter(Boolean).map((a) => a.y - (a.dy ? 1.6 : 0)));
        const bw = Math.max(...xs) + 1 - minX;
        const bh = Math.max(...ys) + 1 - minY;
        const k = clamp(Math.min(availW / bw, availH / bh), 6, mobile() ? 16 : 26);

        const ox = (W - (bw * k + reserveLeft)) / 2 + reserveLeft - minX * k;
        const oy = top + (availH - bh * k) / 2 - minY * k;

        return {
            k,
            x: pts.map((p) => ox + p[0] * k),
            y: pts.map((p) => oy + p[1] * k),
            size: k * GAP,
            labels: areas.map((_, i) => {
                const a = anchors[i];
                if (!a) return null;
                const lx = ox + a.x * k + (a.dx || 0);
                const half = labels[i].offsetWidth / 2;
                return {
                    x: a.align === -50 ? clamp(lx, half + 8, W - half - 8) : Math.max(8, lx),
                    y: oy + a.y * k + (a.dy || 0),
                    align: a.align,
                };
            }),
        };
    }

    let frames = [];
    const build = () => {
        const aspect = clamp((stage.clientWidth * 0.92) / Math.max(1, stage.clientHeight - 290), 0.6, 4);
        frames = [fit(mixed(aspect)), fit(clusters()), fit(islands()), fit(bars())];
    };

    // --- Rendering for a fractional step position p ∈ [0, frames.length - 1] -----------
    let lastP = 0;
    function render(p) {
        lastP = p = clamp(p, 0, frames.length - 1);
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
            el.style.transform = `translate3d(${pos.x.toFixed(1)}px, ${pos.y.toFixed(1)}px, 0) translateX(${pos.align}%)`;
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
