import L from 'leaflet';

const esc = (value) =>
    String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

/**
 * Office map on a dark basemap. Markup:
 * <div data-office-map data-offices='[{"name":..,"lat":..,"lng":..,"type":..,"address":..}]'></div>
 */
export function initMaps() {
    document.querySelectorAll('[data-office-map]').forEach((el) => {
        const offices = JSON.parse(el.dataset.offices || '[]').filter((o) => o.lat && o.lng);
        const interactive = el.dataset.interactive !== 'false';

        const map = L.map(el, {
            zoomControl: interactive,
            scrollWheelZoom: false,
            dragging: interactive,
            attributionControl: true,
        }).setView([-2.5, 118], 5);

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 16,
        }).addTo(map);

        const markers = offices.map((o) => {
            const icon = L.divIcon({
                className: '',
                html: `<div class="map-marker ${o.type === 'Kantor Pusat' ? 'is-hq' : ''}"><span></span><span></span></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });

            return L.marker([o.lat, o.lng], { icon })
                .addTo(map)
                .bindPopup(`<div style="min-width:180px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3498db;font-weight:700">${esc(o.type)}</div><div style="font-weight:800;font-size:15px;margin:4px 0">${esc(o.name)}</div><div style="color:#4b5b6b;font-size:13px">${esc(o.address)}</div></div>`);
        });

        const bounds = markers.length ? L.featureGroup(markers).getBounds().pad(0.15) : null;
        let touched = false;
        const fit = () => bounds && !touched && map.fitBounds(bounds);
        fit();
        // Re-fit while the container settles (reveal animation, sticky layout) until the user takes over.
        map.on('dragstart popupopen', () => (touched = true));

        // Office list items can fly the map to their marker.
        document.querySelectorAll(`[data-map-target="${el.id}"]`).forEach((item) => {
            item.addEventListener('click', () => {
                const index = Number(item.dataset.index);
                const office = offices[index];
                if (!office) return;
                map.flyTo([office.lat, office.lng], 9, { duration: 1.6 });
                markers[index].openPopup();
            });
        });

        new ResizeObserver(() => {
            map.invalidateSize();
            fit();
        }).observe(el);
        setTimeout(() => {
            map.invalidateSize();
            fit();
        }, 1600);
    });
}
