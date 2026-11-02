/**
 * A coarse dot-map of Indonesia for the "Jaringan dalam Angka" story.
 *
 * Simplified island outlines in longitude/latitude, grouped into the five work-area
 * regions, rasterised onto a square grid. Good enough to read as the archipelago at
 * ~10 px per cell; not for anything that needs geographic accuracy.
 */

// Region order matches the work-area name patterns in REGIONS below.
const SHAPES = [
    // 0 Sumatera
    [[[95.3, 5.6], [97.9, 4.6], [99.8, 2.9], [101.8, 1.6], [103.5, 0.2], [104.6, -1.5], [106.1, -3.2], [105.9, -5.9], [104.6, -5.9], [102.3, -4.0], [100.4, -1.1], [98.7, 1.7], [96.9, 3.6], [95.2, 5.3]],
     [[105.1, -1.6], [106.8, -2.6], [106.6, -3.2], [105.4, -2.4]]], // Bangka
    // 1 Jawa, Madura & Bali
    [[[105.2, -6.8], [106.0, -5.9], [108.3, -6.2], [110.4, -6.9], [112.6, -6.9], [114.4, -7.7], [114.4, -8.7], [111.0, -8.3], [108.2, -7.8], [106.3, -7.4], [105.2, -6.9]],
     [[112.7, -6.85], [114.1, -6.85], [114.1, -7.25], [112.7, -7.25]],
     [[114.5, -8.05], [115.7, -8.25], [115.5, -8.85], [114.5, -8.5]]],
    // 2 Kalimantan
    [[[108.9, 0.5], [109.6, 1.9], [111.0, 1.0], [112.5, 1.5], [114.6, 1.4], [115.5, 3.0], [116.0, 4.3], [117.7, 4.2], [118.0, 2.3], [117.5, 0.8], [117.2, -0.5], [116.6, -1.3], [116.4, -3.4], [115.9, -3.9], [114.6, -4.1], [113.0, -3.3], [111.7, -3.0], [110.2, -2.9], [110.0, -1.4], [109.0, -0.4]]],
    // 3 Sulawesi & Nusa Tenggara
    [[[120.6, 0.5], [122.5, 1.0], [124.8, 1.7], [125.2, 1.3], [123.5, 0.3], [121.0, 0.0]],
     [[118.8, -2.6], [119.4, -0.6], [120.0, 0.6], [120.9, 0.4], [121.2, -1.0], [120.8, -2.6], [120.4, -5.6], [119.4, -5.6], [119.4, -3.5]],
     [[121.0, -0.8], [123.4, -0.8], [123.3, -1.4], [121.6, -1.8]],
     [[120.9, -2.6], [122.3, -3.9], [122.7, -5.5], [121.9, -5.0], [121.3, -4.0], [120.8, -3.4]],
     [[115.9, -8.25], [116.7, -8.25], [116.7, -8.95], [115.9, -8.85]], // Lombok
     [[116.8, -8.2], [119.2, -8.1], [119.1, -8.9], [116.8, -8.9]], // Sumbawa
     [[119.8, -8.35], [123.0, -8.15], [122.9, -8.85], [119.8, -8.85]], // Flores
     [[118.9, -9.35], [120.8, -9.55], [120.6, -10.25], [118.9, -9.8]], // Sumba
     [[123.5, -10.35], [125.1, -9.05], [125.3, -9.55], [123.7, -10.45]]], // Timor
    // 4 Maluku & Papua
    [[[127.4, 1.9], [128.2, 1.6], [128.0, 0.4], [128.8, 0.3], [128.0, -0.9], [127.6, 0.5]], // Halmahera
     [[127.9, -2.8], [130.8, -3.1], [130.6, -3.6], [128.2, -3.5]], // Seram
     [[125.9, -3.1], [127.2, -3.2], [127.0, -3.8], [126.0, -3.7]], // Buru
     [[131.0, -0.9], [132.5, -0.4], [134.1, -0.9], [134.2, -2.4], [135.5, -3.3], [137.8, -1.5], [140.9, -2.6], [141.0, -9.1], [139.0, -8.1], [137.6, -8.4], [138.1, -7.0], [136.0, -4.6], [133.6, -3.8], [132.8, -4.1], [131.9, -2.9], [133.2, -2.3], [132.2, -2.1], [131.3, -1.5]]],
];

/**
 * Work-area name → region index, plus where the region's label sits (lon, lat; below = hang
 * under it). Phones put the labels in a row above or below the whole map, at longitude `phone`.
 */
export const REGIONS = [
    { match: /sumatera|aceh|riau|lampung|bangka/i, label: [99.5, 6.6], phone: { lon: 99.5 } },
    { match: /jawa|bali|madura|banten|jakarta/i, label: [110.2, -9.0], below: true, phone: { lon: 109, below: true } },
    { match: /kalimantan/i, label: [113.4, 5.3], phone: { lon: 114.5 } },
    { match: /sulawesi|nusa tenggara|ntt|ntb/i, label: [124.2, 3.2], phone: { lon: 123, below: true } },
    { match: /maluku|papua/i, label: [135.6, 1.2], phone: { lon: 136 } },
];

const BOUNDS = { west: 94.8, east: 141.3, north: 6.8, south: -11 };

function inside([x, y], poly) {
    let hit = false;
    for (let i = 0, j = poly.length - 1; i < poly.length; j = i++) {
        const [xi, yi] = poly[i];
        const [xj, yj] = poly[j];
        if (yi > y !== yj > y && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi) hit = !hit;
    }
    return hit;
}

/**
 * Rasterise the map, picking the coarsest cell size (degrees) that still gives every
 * region at least `need[r]` cells. With `maxCols` (phones) the cell size is fixed instead,
 * so the map never gets finer than the screen can show. Returns cells in grid units plus a
 * lon/lat → grid helper.
 */
export function rasterIndonesia(need, maxCols = Infinity) {
    const coarsest = Math.max(8, Math.ceil((BOUNDS.east - BOUNDS.west) / maxCols / 0.05));
    const finest = Number.isFinite(maxCols) ? coarsest : 4;
    for (let tenth = coarsest; tenth >= finest; tenth--) {
        const step = tenth * 0.05; // 0.40° … 0.20° on wide screens
        const cells = [];
        const perRegion = SHAPES.map(() => 0);
        const cols = Math.ceil((BOUNDS.east - BOUNDS.west) / step);
        const rows = Math.ceil((BOUNDS.north - BOUNDS.south) / step);
        for (let gy = 0; gy < rows; gy++) {
            for (let gx = 0; gx < cols; gx++) {
                const pt = [BOUNDS.west + (gx + 0.5) * step, BOUNDS.north - (gy + 0.5) * step];
                const region = SHAPES.findIndex((polys) => polys.some((poly) => inside(pt, poly)));
                if (region < 0) continue;
                cells.push({ gx, gy, region });
                perRegion[region]++;
            }
        }
        if (perRegion.every((n, r) => n >= (need[r] || 0) * 1.1) || tenth === finest) {
            const toGrid = ([lon, lat]) => [(lon - BOUNDS.west) / step - 0.5, (BOUNDS.north - lat) / step - 0.5];
            return { cells, cols, rows, toGrid };
        }
    }
    return null; // unreachable: the last pass always returns
}
