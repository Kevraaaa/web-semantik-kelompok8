const DUMMY = '/dummy';
const API = '/api';
async function readJson(file) {
    const response = await fetch(`${DUMMY}/${file}`, { headers: { Accept: 'application/json' } });
    if (!response.ok) {
        throw new Error(`Data dummy ${file} gagal dimuat (${response.status}).`);
    }

    return response.json();
}

async function requestApi(path, options = {}) {
    const { body, ...requestOptions } = options;
    const response = await fetch(`${API}${path}`, {
        ...requestOptions,
        headers: {
            Accept: 'application/json',
            ...(body ? { 'Content-Type': 'application/json' } : {}),
            ...requestOptions.headers,
        },
        ...(body ? { body: typeof body === 'string' ? body : JSON.stringify(body) } : {}),
    });
    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload.message || `Permintaan API gagal (${response.status}).`);
    }

    return payload;
}

function queryString(filters = {}) {
    const params = new URLSearchParams();

    for (const [key, value] of Object.entries(filters)) {
        if (value === undefined || value === null || value === '') {
            continue;
        }

        if (Array.isArray(value)) {
            value.forEach((item) => params.append(`${key}[]`, item));
        } else {
            params.set(key, value);
        }
    }

    return params.toString();
}

const ICON_BY_GROUP = {
    Mamalia: '🐾',
    Burung: '🐦',
    Reptil: '🦎',
    Amfibi: '🐸',
    Ikan: '🐟',
    Krustasea: '🦀',
    Serangga: '🦋',
    Moluska: '🐚',
    Echinodermata: '⭐',
};

function optimizeFaunaImage(image) {
    if (typeof image !== 'string' || !image.trim()) {
        return '/images/fauna-placeholder.svg';
    }

    const commonsFilePath = 'http://commons.wikimedia.org/wiki/Special:FilePath/';
    const secureCommonsFilePath = 'https://commons.wikimedia.org/wiki/Special:FilePath/';
    const url = image.startsWith(commonsFilePath)
        ? image.replace('http://', 'https://')
        : image;

    if (!url.startsWith(secureCommonsFilePath) || /[?&]width=/.test(url)) {
        return url;
    }

    return `${url}${url.includes('?') ? '&' : '?'}width=800`;
}

function normalizeAnimal(animal) {
    const habitat = Array.isArray(animal.habitat) ? animal.habitat : [];

    return {
        ...animal,
        ikon: animal.ikon || ICON_BY_GROUP[animal.kelompok] || '🌿',
        ekosistem: animal.ekosistem || habitat[0] || 'Habitat belum diketahui',
        karakteristik: Array.isArray(animal.karakteristik) ? animal.karakteristik : [],
        sumber: Array.isArray(animal.sumber)
            ? animal.sumber
            : [animal.url_iucn, animal.wikidata].filter(Boolean),
        serupa: Array.isArray(animal.serupa) ? animal.serupa : [],
        habitat,
        makanan: Array.isArray(animal.makanan) ? animal.makanan : [],
        ancaman: Array.isArray(animal.ancaman) ? animal.ancaman : [],
        gambar: optimizeFaunaImage(animal.gambar),
        status_iucn: animal.status_iucn || 'Status belum diketahui',
    };
}

export async function getFaunaPage(filters = {}) {
    const params = queryString(filters);
    const response = await requestApi(`/fauna${params ? `?${params}` : ''}`);

    if (!Array.isArray(response.data) || !response.meta || !Number.isInteger(response.meta.total)) {
        throw new Error('Format respons /api/fauna tidak valid.');
    }

    return { ...response, data: response.data.map(normalizeAnimal) };
}

export async function getFauna(filters = {}) {
    const { page: _page, per_page: _perPage, ...queryFilters } = filters;
    const fauna = [];
    let page = 1;
    let total = Number.POSITIVE_INFINITY;

    while (fauna.length < total) {
        const response = await getFaunaPage({ ...queryFilters, page, per_page: 100 });
        fauna.push(...response.data);
        total = response.meta.total;

        if (response.data.length === 0 && fauna.length < total) {
            throw new Error('Paginasi /api/fauna berhenti sebelum seluruh hasil diterima.');
        }

        page += 1;
    }

    return fauna;
}

export async function getFilterOptions() {
    const response = await requestApi('/fauna/filter-options');

    if (!response.data || typeof response.data !== 'object') {
        throw new Error('Format respons /api/fauna/filter-options tidak valid.');
    }

    return response.data;
}

export async function getFaunaDetail(slug) {
    const response = await requestApi(`/fauna/${encodeURIComponent(slug)}`);

    if (!response.data || typeof response.data !== 'object') {
        throw new Error('Format respons detail fauna tidak valid.');
    }

    return normalizeAnimal(response.data);
}

export async function cariSemantik(kalimat) {
    if (!kalimat?.trim()) {
        return [];
    }

    const response = await requestApi('/pencarian-semantik', {
        method: 'POST',
        body: { kalimat },
    });

    if (!Array.isArray(response.data)) {
        throw new Error('Format respons /api/pencarian-semantik tidak valid.');
    }

    return response.data.map((result) => ({
        ...result,
        fauna: normalizeAnimal(result.fauna),
    }));
}

export async function getSebaran(filters = {}) {
    const fauna = await getFauna();

    return {
        type: 'FeatureCollection',
        features: fauna
            .filter((animal) => (
                Number.isFinite(animal.latitude)
                && Number.isFinite(animal.longitude)
                && animal.latitude >= -90
                && animal.latitude <= 90
                && animal.longitude >= -180
                && animal.longitude <= 180
                && (!filters.slug || filters.slug === animal.slug)
                && (!filters.status || filters.status === animal.status_iucn)
                && (!filters.habitat || animal.habitat.includes(filters.habitat))
            ))
            .map((animal) => ({
                type: 'Feature',
                geometry: {
                    type: 'Point',
                    coordinates: [animal.longitude, animal.latitude],
                },
                properties: {
                    slug: animal.slug,
                    kelimpahan: animal.kelimpahan || 'Belum diketahui',
                },
            })),
    };
}

export async function getKalimantanBoundary() {
    const response = await fetch('/data/kalimantan-admin1.geojson', {
        headers: { Accept: 'application/geo+json, application/json' },
    });

    if (!response.ok) {
        throw new Error(`Batas wilayah Kalimantan gagal dimuat (${response.status}).`);
    }

    const boundary = await response.json();
    if (
        boundary?.type !== 'FeatureCollection'
        || !Array.isArray(boundary.features)
        || boundary.features.length === 0
        || boundary.features.some((feature) => (
            feature?.type !== 'Feature'
            || !['Polygon', 'MultiPolygon'].includes(feature.geometry?.type)
        ))
    ) {
        throw new Error('Format GeoJSON batas wilayah Kalimantan tidak valid.');
    }

    return boundary;
}

/**
 * TODO(backend): ganti dengan GET /api/laporan-warga?status=publik; response {data: LaporanWarga[]}.
 */
export async function getLaporanWarga() {
    return readJson('laporan.json');
}

/**
 * TODO(backend): ganti dengan GET /api/admin/laporan?status=; response {data: Laporan[]} dengan data pelapor terbatas untuk admin berizin.
 */
export async function getLaporanAdmin(filters = {}) {
    const reports = await readJson('laporan.json');
    return reports.filter((report) => !filters.status || report.status === filters.status);
}

export async function getGraph(filter = {}) {
    const params = queryString(filter);
    const response = await requestApi(`/knowledge-graph${params ? `?${params}` : ''}`);

    if (!Array.isArray(response.nodes) || !Array.isArray(response.edges)) {
        throw new Error('Format respons /api/knowledge-graph tidak valid.');
    }

    return response;
}

export async function getRekomendasi(slug, limit = 6) {
    const params = queryString({ limit });
    const response = await requestApi(`/fauna/${encodeURIComponent(slug)}/rekomendasi?${params}`);

    if (!Array.isArray(response.data)) {
        throw new Error('Format respons rekomendasi fauna tidak valid.');
    }

    return response.data.map(normalizeAnimal);
}

/**
 * TODO(backend): ganti dengan POST /api/fauna/perbandingan {slugs: string[]}; response {data: Fauna[]}.
 */
export async function bandingkan(slugs) {
    const fauna = await getFauna();
    return slugs.map((slug) => fauna.find((animal) => animal.slug === slug)).filter(Boolean);
}

function favoritesStorageKey() {
    return `jf_favorites:${document.body.dataset.userScope || 'guest'}`;
}

export function getFavoriteSlugs() {
    const stored = localStorage.getItem(favoritesStorageKey());
    if (stored === null) return [];

    const favorites = JSON.parse(stored);
    if (!Array.isArray(favorites) || favorites.some((slug) => typeof slug !== 'string')) {
        throw new Error('Format data favorit lokal tidak valid.');
    }

    return favorites;
}

/**
 * TODO(backend): ganti dengan POST /api/favorit/{slug} dan DELETE /api/favorit/{slug}; response {favorit: boolean}.
 */
export async function toggleFavorit(slug) {
    const favorites = new Set(getFavoriteSlugs());
    if (favorites.has(slug)) {
        favorites.delete(slug);
    } else {
        favorites.add(slug);
    }
    localStorage.setItem(favoritesStorageKey(), JSON.stringify([...favorites]));
    return favorites.has(slug);
}

/**
 * TODO(backend): ganti dengan POST /api/admin/laporan/{id}/verifikasi {status, catatan}; response {data: Laporan}.
 */
export async function verifikasiLaporan(id, status, catatan) {
    return { id, status, catatan, updated_at: new Date().toISOString() };
}
