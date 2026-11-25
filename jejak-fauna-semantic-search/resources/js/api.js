const DUMMY = '/dummy';

async function readJson(file) {
    const response = await fetch(`${DUMMY}/${file}`, { headers: { Accept: 'application/json' } });
    if (!response.ok) {
        throw new Error(`Data dummy ${file} gagal dimuat (${response.status}).`);
    }

    return response.json();
}

function includesAny(values, selected, partial = false) {
    return selected.length === 0 || selected.some((selectedValue) => values.some((value) => partial
        ? value.toLocaleLowerCase('id').includes(selectedValue.toLocaleLowerCase('id'))
        : value === selectedValue));
}

/**
 * TODO(backend): ganti dengan GET /api/fauna?query=&status=&habitat=&makanan=&kelompok=&ancaman=; response {data: Fauna[], meta}.
 */
export async function getFauna(filters = {}) {
    const fauna = await readJson('fauna.json');
    const query = (filters.query || '').toLocaleLowerCase('id');
    const statuses = filters.status ? [].concat(filters.status) : [];
    const habitats = filters.habitat ? [].concat(filters.habitat) : [];
    const foods = filters.makanan ? [].concat(filters.makanan) : [];
    const groups = filters.kelompok ? [].concat(filters.kelompok) : [];
    const threats = filters.ancaman ? [].concat(filters.ancaman) : [];

    return fauna.filter((animal) => {
        const searchable = `${animal.nama} ${animal.nama_ilmiah} ${animal.deskripsi}`.toLocaleLowerCase('id');
        return (!query || searchable.includes(query))
            && includesAny([animal.status_iucn], statuses)
            && includesAny(animal.habitat, habitats)
            && includesAny(animal.makanan, foods)
            && includesAny([animal.kelompok], groups, true)
            && includesAny(animal.ancaman, threats, true);
    });
}

/**
 * TODO(backend): ganti dengan GET /api/fauna/{slug}; response satu objek Fauna atau 404.
 */
export async function getFaunaDetail(slug) {
    const fauna = await readJson('fauna.json');
    const match = fauna.find((animal) => animal.slug === slug);
    if (!match) {
        throw new Error('Fauna tidak ditemukan pada data dummy.');
    }
    return match;
}

const synonymGroups = [
    { terms: ['buah', 'buah-buahan', 'fruit'], values: ['buah', 'Ficus', 'Buah hutan'] },
    { terms: ['ikan', 'perairan', 'air tawar'], values: ['Ikan', 'Udang', 'Moluska'] },
    { terms: ['daun', 'dedaunan', 'herbivora'], values: ['Daun', 'Daun muda', 'Rumput', 'Tunas'] },
    { terms: ['sungai', 'air', 'mahakam'], values: ['Sungai', 'Sungai Mahakam', 'Danau air tawar', 'Tepi sungai'] },
    { terms: ['rawa', 'gambut'], values: ['Hutan rawa gambut', 'Hutan rawa'] },
    { terms: ['mangrove', 'bakau', 'pesisir'], values: ['Hutan mangrove', 'Mangrove'] },
    { terms: ['hutan', 'kanopi', 'pepohonan'], values: ['Hutan hujan tropis', 'Hutan dataran rendah', 'Kanopi hutan'] },
    { terms: ['mamalia', 'hewan menyusui'], values: ['Mamalia'] },
    { terms: ['primata', 'monyet', 'kera', 'bergantung'], values: ['Primata', 'buah', 'Hutan hujan tropis'] },
    { terms: ['burung', 'terbang'], values: ['Burung'] },
    { terms: ['terancam', 'langka', 'kritis', 'punah'], values: ['Kritis', 'Terancam', 'Rentan', 'Sangat Jarang'] },
];

/**
 * TODO(backend): ganti dengan POST /api/pencarian-semantik {kalimat}; response {data: [{fauna, skor, alasan_kecocokan}]}.
 * Status konservasi dummy wajib diverifikasi terhadap sumber IUCN Red List terbaru sebelum ditayangkan sebagai data faktual.
 */
export async function cariSemantik(kalimat) {
    const fauna = await getFauna();
    const normalized = (kalimat || '').toLocaleLowerCase('id').trim();
    const terms = synonymGroups.filter((group) => group.terms.some((term) => normalized.includes(term)));

    if (!normalized) {
        return [];
    }

    return fauna.map((animal) => {
        const reasons = [];
        for (const group of terms) {
            const habitatMatch = animal.habitat.find((item) => group.values.some((value) => item.toLocaleLowerCase('id').includes(value.toLocaleLowerCase('id'))));
            if (habitatMatch) reasons.push(`habitat: ${habitatMatch}`);
            const foodMatch = animal.makanan.find((item) => group.values.some((value) => item.toLocaleLowerCase('id').includes(value.toLocaleLowerCase('id'))));
            if (foodMatch) reasons.push(`makanan: ${foodMatch}`);
            if (group.values.some((value) => animal.kelompok.toLocaleLowerCase('id').includes(value.toLocaleLowerCase('id')))) {
                reasons.push(`kelompok: ${animal.kelompok}`);
            }
        }
        const exactHits = `${animal.nama} ${animal.nama_ilmiah}`.toLocaleLowerCase('id').split(/\s+/).filter((word) => word.length > 3 && normalized.includes(word));
        if (exactHits.length) {
            reasons.push(`nama: ${animal.nama}`);
        }
        return { fauna: animal, alasan_kecocokan: [...new Set(reasons)], skor: new Set(reasons).size };
    }).filter((result) => result.skor > 0).sort((a, b) => b.skor - a.skor);
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
 * TODO(backend): ganti dengan GET /api/knowledge-graph?relasi[]; response {nodes: Node[], edges: Edge[]}.
 */
export async function getGraph(filter = {}) {
    const graph = await readJson('graph.json');
    if (!filter.relasi || filter.relasi.length === 0) {
        return graph;
    }
    const edges = graph.edges.filter((edge) => filter.relasi.includes(edge.type));
    const ids = new Set(edges.flatMap((edge) => [edge.from, edge.to]));
    return { nodes: graph.nodes.filter((node) => ids.has(node.id)), edges };
}

/**
 * TODO(backend): ganti dengan GET /api/fauna/{slug}/rekomendasi?limit=6; response {data: Fauna[]}.
 */
export async function getRekomendasi(slug) {
    const animal = await getFaunaDetail(slug);
    const fauna = await getFauna();
    return fauna.filter((item) => animal.serupa.includes(item.slug));
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
