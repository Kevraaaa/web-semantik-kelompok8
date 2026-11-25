import { getFauna, getLaporanWarga, getSebaran } from './api.js';

const center = [0.8, 114];
let leafletPromise;

async function loadLeaflet() {
    if (!leafletPromise) {
        leafletPromise = (async () => {
            const module = await import('leaflet');
            const L = module.default;
            window.L = L;
            await import('leaflet.markercluster');
            return L;
        })();
    }
    return leafletPromise;
}

function dotIcon(L, color, emoji = '•', abundance = '') {
    const abundanceClass = {
        'Sangat Umum': 'abundance-very-common',
        Umum: 'abundance-common',
        Jarang: 'abundance-rare',
        'Sangat Jarang': 'abundance-very-rare',
    }[abundance] || '';
    return L.divIcon({
        className: '',
        html: `<span class="map-marker ${color === 'report' ? 'report-marker' : abundanceClass}" aria-hidden="true">${emoji}</span>`,
        iconSize: [36, 36],
        iconAnchor: [18, 18],
        popupAnchor: [0, -18],
    });
}

function mapOptions(L, node, zoom = 5) {
    const map = L.map(node, { scrollWheelZoom: false, zoomControl: true }).setView(center, zoom);
    let tileLayer;
    const setBasemap = (theme = document.documentElement.dataset.theme) => {
        if (tileLayer) map.removeLayer(tileLayer);
        const style = theme === 'light' ? 'light_all' : 'dark_all';
        tileLayer = L.tileLayer(`https://{s}.basemaps.cartocdn.com/${style}/{z}/{x}/{y}{r}.png`, {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 19,
        }).addTo(map);
    };
    setBasemap();
    document.addEventListener('jf:theme-changed', (event) => setBasemap(event.detail?.theme));
    return map;
}

const safe = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));

async function setupMainMap(node) {
    const L = await loadLeaflet();
    const map = mapOptions(L, node, 5);
    const cluster = L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: 48 });
    const results = document.querySelector('[data-map-results]');
    const count = document.querySelector('[data-map-count]');
    const controls = [...document.querySelectorAll('[data-map-filter]')];
    const showReports = document.querySelector('[data-map-reports]');
    let animals = [];
    let reportMarkers = [];
    let faunaMarkers = [];

    const paint = async () => {
        cluster.clearLayers();
        results.innerHTML = '';
        const filters = Object.fromEntries(controls.map((input) => [input.dataset.mapFilter, input.value]).filter(([, value]) => value));
        try {
            const [geojson, allAnimals, reports] = await Promise.all([getSebaran(filters), getFauna(filters.slug ? { query: '' } : {}), getLaporanWarga()]);
            animals = allAnimals;
            const animalBySlug = new Map(animals.map((animal) => [animal.slug, animal]));
            faunaMarkers = [];
            geojson.features.forEach((feature) => {
                const animal = animalBySlug.get(feature.properties.slug);
                if (!animal) return;
                const [lng, lat] = feature.geometry.coordinates;
                const marker = L.marker([lat, lng], { icon: dotIcon(L, 'fauna', animal.ikon || '🌿', feature.properties.kelimpahan) });
                marker.bindPopup(`<div class="flex items-center gap-2"><img src="${safe(animal.gambar)}" alt="" width="42" height="42" style="border-radius:8px;object-fit:cover"><div><strong>${safe(animal.nama)}</strong><br><span style="font-size:11px">${safe(feature.properties.kelimpahan)} · ${safe(animal.status_iucn)}</span><br><a href="/eksplorasi/${encodeURIComponent(animal.slug)}" style="color:#eff4bd">Lihat detail →</a></div></div>`);
                marker.addTo(cluster);
                faunaMarkers.push({ marker, animal, lat, lng, abundance: feature.properties.kelimpahan });
            });

            reportMarkers = [];
            if (showReports?.checked) {
                reports.forEach((report) => {
                    if (filters.slug && report.slug !== filters.slug) return;
                    const reportAnimal = animalBySlug.get(report.slug);
                    if (filters.status && reportAnimal?.status_iucn !== filters.status) return;
                    if (filters.habitat && !reportAnimal?.habitat.includes(filters.habitat)) return;
                    const marker = L.marker([report.lokasi.lat, report.lokasi.lng], { icon: dotIcon(L, 'report', '⌖') });
                    marker.bindPopup(`<strong>Laporan warga</strong><br>${safe(report.nama)}<br><span style="font-size:11px">${safe(report.lokasi.teks)}</span>`);
                    marker.addTo(cluster);
                    reportMarkers.push({ marker, report });
                });
            }
            map.addLayer(cluster);

            const rows = [...faunaMarkers.map((item) => ({ ...item, isReport: false })), ...reportMarkers.map(({ marker, report }) => ({ marker, reportItem: report, lat: report.lokasi.lat, lng: report.lokasi.lng, isReport: true }))];
            count.textContent = `${rows.length} titik`;
            results.innerHTML = rows.length ? rows.map((row, index) => `<button type="button" class="map-result" data-map-item="${index}"><span class="map-result-dot ${row.isReport ? 'report' : ''}">${row.isReport ? '⌖' : (row.animal.ikon || '🌿')}</span><span class="min-w-0 flex-1 text-left"><strong class="block truncate text-xs">${safe(row.isReport ? row.reportItem.nama : row.animal.nama)}</strong><span class="mt-1 block truncate text-[.65rem] text-muted">${safe(row.isReport ? row.reportItem.lokasi.teks : `${row.abundance} · ${row.animal.status_iucn || 'Status belum tercatat'}`)}</span></span><span class="text-fresh-grown">→</span></button>`).join('') : '<p class="py-5 text-xs text-muted">Tidak ada titik sesuai filter.</p>';
            results.querySelectorAll('[data-map-item]').forEach((button) => {
                button.addEventListener('click', () => {
                    const row = rows[Number(button.dataset.mapItem)];
                    map.flyTo([row.lat, row.lng], 8, { duration: .6 });
                    row.marker.openPopup();
                });
            });
        } catch (error) {
            console.error(error);
            results.innerHTML = '<p class="text-xs text-rose-200">Data sebaran belum dapat dimuat.</p>';
        }
    };

    const select = document.querySelector('[data-map-filter="slug"]');
    const fauna = await getFauna();
    select.innerHTML += fauna.map((animal) => `<option value="${safe(animal.slug)}">${safe(animal.nama)}</option>`).join('');
    controls.forEach((control) => control.addEventListener('change', paint));
    showReports?.addEventListener('change', paint);
    await paint();
    window.setTimeout(() => map.invalidateSize(), 100);
}

async function setupSimpleMap(node, slug = '', picker = false) {
    const L = await loadLeaflet();
    const map = mapOptions(L, node, slug ? 6 : 5);
    if (!picker) {
        const group = L.markerClusterGroup({ showCoverageOnHover: false });
        const [geojson, animals] = await Promise.all([getSebaran(slug ? { slug } : {}), getFauna()]);
        const animalBySlug = new Map(animals.map((animal) => [animal.slug, animal]));

        geojson.features.forEach((feature) => {
            const animal = animalBySlug.get(feature.properties.slug);
            if (!animal) return;
            const [lng, lat] = feature.geometry.coordinates;
            const marker = L.marker([lat, lng], { icon: dotIcon(L, 'fauna', animal.ikon || '🌿', feature.properties.kelimpahan) });
            marker.bindPopup(`<strong>${safe(animal.nama)}</strong><br>${safe(feature.properties.kelimpahan)}`);
            marker.addTo(group);
        });
        map.addLayer(group);

        if (slug && geojson.features[0]) {
            const [lng, lat] = geojson.features[0].geometry.coordinates;
            map.setView([lat, lng], 7);
        }
    }
    if (picker) {
        let selection;
        map.on('click', (event) => {
            if (selection) map.removeLayer(selection);
            selection = L.marker(event.latlng).addTo(map);
            const lat = document.querySelector('[data-lat]');
            const lng = document.querySelector('[data-lng]');
            if (lat && lng) {
                lat.value = event.latlng.lat.toFixed(6);
                lng.value = event.latlng.lng.toFixed(6);
            }
        });
    }
    window.setTimeout(() => map.invalidateSize(), 150);
    return map;
}

async function boot() {
    try {
        const main = document.querySelector('[data-map]');
        if (main) await setupMainMap(main);
        for (const node of document.querySelectorAll('[data-mini-map]')) await setupSimpleMap(node);
        const picker = document.querySelector('[data-picker-map]');
        if (picker) await setupSimpleMap(picker, '', true);
    } catch (error) {
        console.error(error);
        document.querySelectorAll('[data-map], [data-mini-map], [data-detail-map], [data-picker-map]').forEach((node) => {
            node.insertAdjacentHTML('beforeend', '<p class="p-4 text-xs text-rose-200">Peta belum dapat dimuat. Periksa koneksi peta dan coba lagi.</p>');
        });
    }
}

boot();

document.addEventListener('jf:results-rendered', () => {
    const detailMap = document.querySelector('[data-detail-map]');
    if (detailMap && detailMap.dataset.mapSlug && !detailMap.dataset.ready) {
        detailMap.dataset.ready = 'true';
        setupSimpleMap(detailMap, detailMap.dataset.mapSlug || '').catch((error) => console.error(error));
    }
});

document.addEventListener('jf:admin-map-ready', async () => {
    const node = document.querySelector('[data-admin-map]');
    if (!node || node.dataset.ready) return;
    node.dataset.ready = 'true';
    try {
        const L = await loadLeaflet();
        const map = mapOptions(L, node, 8);
        const lat = Number(node.dataset.lat);
        const lng = Number(node.dataset.lng);
        L.marker([lat, lng], { icon: dotIcon(L, 'report', '⌖') }).addTo(map);
        map.setView([lat, lng], 8);
        window.setTimeout(() => map.invalidateSize(), 100);
    } catch (error) {
        console.error(error);
        node.innerHTML = '<p class="p-3 text-xs text-rose-200">Peta lokasi laporan tidak tersedia.</p>';
    }
});
