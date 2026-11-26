const DUMMY = '/dummy';

async function readJson(file) {
    const response = await fetch(`${DUMMY}/${file}`, { headers: { Accept: 'application/json' } });
    if (!response.ok) {
        throw new Error(`Data dummy ${file} gagal dimuat (${response.status}).`);
    }

    return response.json();
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

async function readApiJson(url, options = {}) {
    const response = await fetch(url, {
        method: options.method || 'GET',
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        },
        ...(options.body ? { body: JSON.stringify(options.body) } : {}),
    });

    if (!response.ok) {
        throw new Error(`Permintaan API gagal (${response.status}) pada ${url}.`);
    }

    return response.json();
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
        gambar: animal.gambar || '/images/fauna-placeholder.svg',
        status_iucn: animal.status_iucn || 'Status belum diketahui',
    };
}

/**
 * Ambil satu halaman katalog dan pertahankan metadata paginasi dari API.
 */
export async function getFaunaPage(filters = {}) {
    const params = queryString(filters);
    const response = await readApiJson(`/api/fauna${params ? `?${params}` : ''}`);

    if (!Array.isArray(response.data) || !response.meta || !Number.isInteger(response.meta.total)) {
        throw new Error('Format respons /api/fauna tidak valid.');
    }

    return { ...response, data: response.data.map(normalizeAnimal) };
}

/**
 * Ambil seluruh hasil yang cocok. Fungsi ini menjaga kontrak array untuk pemakai katalog lain.
 */
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
    const response = await readApiJson('/api/fauna/filter-options');

    if (!response.data || typeof response.data !== 'object') {
        throw new Error('Format respons /api/fauna/filter-options tidak valid.');
    }

    return response.data;
}

/**
 * Ambil detail fauna dari API.
 */
export async function getFaunaDetail(slug) {
    const response = await readApiJson(`/api/fauna/${encodeURIComponent(slug)}`);

    if (!response.data || typeof response.data !== 'object') {
        throw new Error('Format respons detail fauna tidak valid.');
    }

    return normalizeAnimal(response.data);
}

/**
 * Pencarian semantik server mengembalikan kecocokan dari seluruh dataset.
 */
export async function cariSemantik(kalimat) {
    if (!kalimat?.trim()) {
        return [];
    }

    const response = await readApiJson('/api/pencarian-semantik', {
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

/**
 * TODO(backend): ganti dengan GET /api/sebaran?fauna=&status=&habitat=; response GeoJSON FeatureCollection.
 */
export async function getSebaran(filters = {}) {
    const [geojson, fauna] = await Promise.all([readJson('sebaran.json'), getFauna()]);
    const bySlug = new Map(fauna.map((animal) => [animal.slug, animal]));
    return {
        ...geojson,
        features: geojson.features.filter((feature) => {
            const animal = bySlug.get(feature.properties.slug);
            return animal
                && (!filters.slug || filters.slug === animal.slug)
                && (!filters.status || filters.status === animal.status_iucn)
                && (!filters.habitat || animal.habitat.includes(filters.habitat));
        }),
    };
}

/**
 * TODO(backend): ganti dengan GET /api/laporan-warga?status=publik; response {data: LaporanWarga[]}.
 */
export async function getLaporanWarga() {
    return readJson('laporan.json');
}

/**
 * TODO(backend): ganti dengan GET /api/profil/laporan; response {data: Laporan[]} khusus pengguna aktif.
 */
export async function getLaporanSaya() {
    const [profile, reports] = await Promise.all([readJson('pengguna.json'), getLaporanWarga()]);
    return reports.filter((report) => profile.laporan.includes(report.id));
}

/**
 * TODO(backend): ganti dengan GET /api/admin/laporan?status=; response {data: Laporan[]} dengan data pelapor terbatas untuk admin berizin.
 */
export async function getLaporanAdmin(filters = {}) {
    const reports = await readJson('laporan.json');
    return reports.filter((report) => !filters.status || report.status === filters.status);
}

/**
 * Ambil relasi fauna dari API, dengan filter opsional untuk fokus graf.
 */
export async function getGraph(filter = {}) {
    const params = queryString(filter);
    const response = await readApiJson(`/api/knowledge-graph${params ? `?${params}` : ''}`);

    if (!Array.isArray(response.nodes) || !Array.isArray(response.edges)) {
        throw new Error('Format respons /api/knowledge-graph tidak valid.');
    }

    return response;
}

/**
 * Ambil rekomendasi fauna dari API.
 */
export async function getRekomendasi(slug, limit = 6) {
    const params = queryString({ limit });
    const response = await readApiJson(`/api/fauna/${encodeURIComponent(slug)}/rekomendasi?${params}`);

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

/**
 * TODO(backend): ganti dengan POST /api/favorit/{slug} dan DELETE /api/favorit/{slug}; response {favorit: boolean}.
 */
export async function toggleFavorit(slug) {
    const favorites = new Set(JSON.parse(localStorage.getItem('jf_favorites') || '[]'));
    if (favorites.has(slug)) {
        favorites.delete(slug);
    } else {
        favorites.add(slug);
    }
    localStorage.setItem('jf_favorites', JSON.stringify([...favorites]));
    return favorites.has(slug);
}

/**
 * TODO(backend): ganti dengan POST /api/laporan-penemuan multipart/form-data {fauna_slug?, nama_bebas, deskripsi, tanggal, latitude, longitude, lokasi, foto[]}; response {data: Laporan}.
 */
export async function kirimLaporan(payload) {
    return { ...payload, id: `mock-${Date.now()}`, status: 'menunggu', created_at: new Date().toISOString() };
}

/**
 * TODO(backend): ganti dengan GET /api/profil; response {pengguna, favorit, riwayat_pencarian, laporan, aktivitas}.
 */
export async function getProfil() {
    const [profile, fauna] = await Promise.all([readJson('pengguna.json'), getFauna()]);
    const storedFavorites = localStorage.getItem('jf_favorites');
    const favorites = storedFavorites ? JSON.parse(storedFavorites) : profile.favorit;
    return {
        ...profile,
        favorit: favorites,
        faunaFavorit: fauna.filter((animal) => favorites.includes(animal.slug)),
    };
}

/**
 * TODO(backend): ganti dengan POST /api/auth/login {email, password}; response {user: {id, name, email}, session/token}.
 */
export async function masuk(payload) {
    return { user: { id: 'demo-user', name: 'Ayu Lestari', email: payload.email } };
}

/**
 * TODO(backend): ganti dengan POST /api/auth/register {name, email, password, password_confirmation}; response {user, session/token}.
 */
export async function daftar(payload) {
    return { user: { id: 'demo-user', name: payload.name, email: payload.email } };
}

/**
 * TODO(backend): ganti dengan POST /api/admin/laporan/{id}/verifikasi {status, catatan}; response {data: Laporan}.
 */
export async function verifikasiLaporan(id, status, catatan) {
    return { id, status, catatan, updated_at: new Date().toISOString() };
}
