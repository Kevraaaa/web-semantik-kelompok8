import { getFauna, getKalimantanBoundary, getLaporanWarga, getSebaran } from './api.js';

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
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    if ('ResizeObserver' in window) {
        const resizeObserver = new ResizeObserver(() => map.invalidateSize({ pan: false }));
        resizeObserver.observe(node);
        map.once('unload', () => resizeObserver.disconnect());
    } else {
        const resizeMap = () => map.invalidateSize({ pan: false });
        window.addEventListener('resize', resizeMap, { passive: true });
        map.once('unload', () => window.removeEventListener('resize', resizeMap));
    }

    return map;
}

function toLeafletLatLng(latitude, longitude) {
    const lat = typeof latitude === 'string' && latitude.trim() ? Number(latitude) : latitude;
    const lng = typeof longitude === 'string' && longitude.trim() ? Number(longitude) : longitude;

    if (
        typeof lat !== 'number'
        || typeof lng !== 'number'
        || !Number.isFinite(lat)
        || !Number.isFinite(lng)
        || lat < -90
        || lat > 90
        || lng < -180
        || lng > 180
    ) {
        return null;
    }

    return [lat, lng];
}

function getFeatureLatLng(feature) {
    if (feature?.geometry?.type !== 'Point' || !Array.isArray(feature.geometry.coordinates)) {
        return null;
    }

    const [longitude, latitude] = feature.geometry.coordinates;

    return toLeafletLatLng(latitude, longitude);
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
    const distributionStatus = document.querySelector('[data-map-distribution-status]');
    let animals = [];
    let reportMarkers = [];
    let faunaMarkers = [];
    let administrativeBoundaryPromise;
    let paintRequestId = 0;

    const renderAdministrativeBoundary = async () => {
        if (!administrativeBoundaryPromise) {
            administrativeBoundaryPromise = getKalimantanBoundary().then((boundary) => {
                const layer = L.geoJSON(boundary, {
                    style: {
                        color: '#d4d7c7',
                        weight: 1,
                        fillOpacity: 0,
                        dashArray: '4 5',
                    },
                }).addTo(map);
                layer.bringToBack();
                map.attributionControl.addAttribution(
                    'Batas: <a href="https://www.geoboundaries.org/api/current/gbOpen/IDN/ADM1/">geoBoundaries IDN ADM1</a> · <a href="https://opendatacommons.org/licenses/odbl/1-0/">ODbL 1.0</a> · sumber <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>/<a href="https://wambachers-osm.website/boundaries/">Wambacher</a> (2017)',
                );
                return layer;
            });
        }

        try {
            await administrativeBoundaryPromise;
        } catch (error) {
            administrativeBoundaryPromise = null;
            throw error;
        }
    };

    const paint = async () => {
        const requestId = ++paintRequestId;
        const filters = Object.fromEntries(controls.map((input) => [input.dataset.mapFilter, input.value]).filter(([, value]) => value));
        cluster.clearLayers();
        results.innerHTML = '';
        try {
            const [geojson, allAnimals, reports] = await Promise.all([getSebaran(filters), getFauna(filters.slug ? { query: '' } : {}), getLaporanWarga()]);
            if (requestId !== paintRequestId) return;

            animals = allAnimals;
            let boundaryLoaded = true;
            try {
                await renderAdministrativeBoundary();
            } catch (error) {
                if (requestId !== paintRequestId) return;
                console.error(error);
                boundaryLoaded = false;
            }
            if (requestId !== paintRequestId) return;

            if (distributionStatus) {
                const selectedAnimal = allAnimals.find((animal) => animal.slug === filters.slug);
                const selectedName = selectedAnimal ? ` untuk ${selectedAnimal.nama}` : ' per spesies';
                distributionStatus.textContent = boundaryLoaded
                    ? `Data persebaran${selectedName} belum tersedia. Garis provinsi hanya batas administrasi.`
                    : `Batas administrasi tidak dapat dimuat; data persebaran${selectedName} belum tersedia.`;
            }
            const animalBySlug = new Map(animals.map((animal) => [animal.slug, animal]));
            faunaMarkers = [];
            geojson.features.forEach((feature) => {
                const animal = animalBySlug.get(feature.properties?.slug);
                const latLng = getFeatureLatLng(feature);
                if (!animal || !latLng) return;
                const [lat, lng] = latLng;
                const marker = L.marker([lat, lng], { icon: dotIcon(L, 'fauna', animal.ikon || '🌿', feature.properties.kelimpahan) });
                const scientificName = animal.nama_ilmiah || 'Nama ilmiah belum tercatat';
                const conservationStatus = animal.status_iucn || 'Status konservasi belum tercatat';
                const regionNames = Array.isArray(animal.wilayah) && animal.wilayah.length
                    ? animal.wilayah.join(', ')
                    : 'Wilayah belum tercatat';
                marker.bindPopup(`<div class="flex items-center gap-2"><img src="${safe(animal.gambar)}" alt="" width="42" height="42" style="border-radius:8px;object-fit:cover"><div><strong>${safe(animal.nama)}</strong><br><em style="font-size:11px">${safe(scientificName)}</em><br><span style="font-size:11px">Status: ${safe(conservationStatus)}<br>Relasi wilayah RDF: ${safe(regionNames)}</span><br><a href="/eksplorasi/${encodeURIComponent(animal.slug)}" style="color:#eff4bd">Lihat detail →</a></div></div>`);
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
                    const latLng = toLeafletLatLng(report.lokasi?.lat, report.lokasi?.lng);
                    if (!latLng) return;
                    const marker = L.marker(latLng, { icon: dotIcon(L, 'report', '⌖') });
                    marker.bindPopup(`<strong>Laporan warga</strong><br>${safe(report.nama)}<br><span style="font-size:11px">${safe(report.lokasi.teks)}</span>`);
                    marker.addTo(cluster);
                    reportMarkers.push({ marker, report, lat: latLng[0], lng: latLng[1] });
                });
            }
            map.addLayer(cluster);

            if ((filters.slug || filters.status || filters.habitat) && faunaMarkers.length) {
                map.fitBounds(
                    L.latLngBounds(faunaMarkers.map(({ lat, lng }) => [lat, lng])),
                    { maxZoom: 8, padding: [20, 20] },
                );
            }

            const rows = [...faunaMarkers.map((item) => ({ ...item, isReport: false })), ...reportMarkers.map(({ marker, report, lat, lng }) => ({ marker, reportItem: report, lat, lng, isReport: true }))];
            count.textContent = `${rows.length} titik`;
            results.innerHTML = rows.length ? rows.map((row, index) => `<button type="button" class="map-result" data-map-item="${index}"><span class="map-result-dot ${row.isReport ? 'report' : ''}">${row.isReport ? '⌖' : (row.animal.ikon || '🌿')}</span><span class="min-w-0 flex-1 text-left"><strong class="block truncate text-xs">${safe(row.isReport ? row.reportItem.nama : row.animal.nama)}</strong><span class="mt-1 block truncate text-[.65rem] text-muted">${safe(row.isReport ? row.reportItem.lokasi.teks : `${row.abundance} · ${row.animal.status_iucn || 'Status belum tercatat'}`)}</span></span><span class="text-fresh-grown">→</span></button>`).join('') : '<p class="py-5 text-xs text-muted">Belum ada koordinat fauna valid di dataset untuk filter ini.</p>';
            results.querySelectorAll('[data-map-item]').forEach((button) => {
                button.addEventListener('click', () => {
                    const row = rows[Number(button.dataset.mapItem)];
                    map.flyTo([row.lat, row.lng], 8, { duration: .6 });
                    row.marker.openPopup();
                });
            });
        } catch (error) {
            if (requestId !== paintRequestId) return;
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
            const animal = animalBySlug.get(feature.properties?.slug);
            const latLng = getFeatureLatLng(feature);
            if (!animal || !latLng) return;
            const [lat, lng] = latLng;
            const marker = L.marker([lat, lng], { icon: dotIcon(L, 'fauna', animal.ikon || '🌿', feature.properties.kelimpahan) });
            marker.bindPopup(`<strong>${safe(animal.nama)}</strong><br>${safe(feature.properties.kelimpahan)}`);
            marker.addTo(group);
        });
        map.addLayer(group);

        const firstValidFeature = geojson.features.find((feature) => getFeatureLatLng(feature));
        if (slug && firstValidFeature) {
            map.setView(getFeatureLatLng(firstValidFeature), 7);
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
        const latLng = toLeafletLatLng(node.dataset.lat, node.dataset.lng);
        if (!latLng) {
            node.innerHTML = '<p class="p-3 text-xs text-rose-200">Koordinat lokasi laporan tidak valid.</p>';
            return;
        }
        L.marker(latLng, { icon: dotIcon(L, 'report', '⌖') }).addTo(map);
        map.setView(latLng, 8);
        window.setTimeout(() => map.invalidateSize(), 100);
    } catch (error) {
        console.error(error);
        node.innerHTML = '<p class="p-3 text-xs text-rose-200">Peta lokasi laporan tidak tersedia.</p>';
    }
});
