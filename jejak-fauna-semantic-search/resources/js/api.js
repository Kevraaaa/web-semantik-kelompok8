const DUMMY = '/dummy';
const API = '/api';
const ARRAY_FILTERS = new Set(['status', 'habitat', 'makanan', 'kelompok', 'ancaman']);

async function readJson(file) {
    const response = await fetch(`${DUMMY}/${file}`, { headers: { Accept: 'application/json' } });
    if (!response.ok) {
        throw new Error(`Data dummy ${file} gagal dimuat (${response.status}).`);
    }

    return response.json();
}

async function requestApi(path, options = {}) {
    const response = await fetch(`${API}${path}`, {
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...options.headers,
        },
        ...options,
    });
    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload.message || `Permintaan API gagal (${response.status}).`);
    }

    return payload;
}

export async function getFauna(filters = {}) {
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries({ ...filters, per_page: 100 })) {
        if (value === undefined || value === null || value === '') continue;
        for (const item of [].concat(value)) {
            params.append(ARRAY_FILTERS.has(key) ? `${key}[]` : key, item);
        }
    }

    const firstPage = await requestApi(`/fauna?${params}`);
    const fauna = [...firstPage.data];
    const pageCount = Math.ceil(firstPage.meta.total / firstPage.meta.per_page);
    for (let page = 2; page <= pageCount; page += 1) {
        params.set('page', page);
        const nextPage = await requestApi(`/fauna?${params}`);
        fauna.push(...nextPage.data);
    }

    return fauna;
}

export async function getFaunaDetail(slug) {
    return (await requestApi(`/fauna/${encodeURIComponent(slug)}`)).data;
}

export async function cariSemantik(kalimat) {
    const result = await requestApi('/pencarian-semantik', {
        method: 'POST',
        body: JSON.stringify({ kalimat }),
    });

    return result.data;
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

export async function getGraph(filter = {}) {
    const params = new URLSearchParams();
    for (const relation of filter.relasi || []) {
        params.append('relasi[]', relation);
    }
    params.set('limit', 200);

    return requestApi(`/knowledge-graph?${params}`);
}

export async function getRekomendasi(slug) {
    const result = await requestApi(`/fauna/${encodeURIComponent(slug)}/rekomendasi?limit=6`);
    return result.data;
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
