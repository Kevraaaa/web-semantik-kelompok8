# Jejak Fauna API

Dokumentasi ini hanya mencakup route API yang benar-benar terdaftar pada aplikasi saat ini. Semua endpoint mengembalikan JSON dan menggunakan dataset `dataset/fauna_kalimantan.ttl` melalui `App\Services\SparqlService`.

## Ringkasan endpoint

| Method | URL | Fungsi |
|---|---|---|
| `GET` | `/api/fauna` | Katalog fauna, pencarian teks, filter, dan paginasi |
| `GET` | `/api/fauna/filter-options` | Nilai filter yang tersedia dari dataset |
| `GET` | `/api/fauna/{slug}` | Detail satu fauna |
| `GET` | `/api/fauna/{slug}/rekomendasi` | Rekomendasi fauna serupa |
| `POST` | `/api/pencarian-semantik` | Pencarian berdasarkan kalimat Bahasa Indonesia |
| `GET` | `/api/knowledge-graph` | Node dan edge knowledge graph |

Validasi gagal menggunakan HTTP `422` dengan bentuk umum:

```json
{
  "message": "The page field must be an integer.",
  "errors": {
    "page": ["The page field must be an integer."]
  }
}
```

## `GET /api/fauna`

Mengambil katalog fauna dari Turtle dengan pencarian teks, filter ontologi, dan paginasi.

Query parameters:

| Parameter | Tipe | Aturan |
|---|---|---|
| `query` | string | Opsional, maksimal 200 karakter; dicocokkan dengan nama, nama ilmiah, dan deskripsi |
| `status[]` | string[] | Opsional; key status seperti `StatusKritis`, `StatusTerancam`, `StatusRentan` |
| `habitat[]` | string[] | Opsional; key habitat dari dataset |
| `makanan[]` | string[] | Opsional; key makanan dari dataset |
| `kelompok[]` | string[] | Opsional; misalnya `Mamalia`, `Burung`, `Reptil`, `Amfibi` |
| `ancaman[]` | string[] | Opsional; key ancaman dari dataset |
| `page` | integer | Opsional, minimal 1; default `1` |
| `per_page` | integer | Opsional, 1–100; default `12` |

Contoh request:

```text
GET /api/fauna?query=orangutan&status[]=StatusKritis&per_page=2
```

Contoh response `200`:

```json
{
  "data": [
    {
      "slug": "orangutan-kalimantan",
      "nama": "Orangutan kalimantan",
      "nama_ilmiah": "Pongo pygmaeus",
      "kelompok": "Lainnya",
      "deskripsi": "Spesies orang utan asli pulau Kalimantan",
      "status_iucn": "Kritis (CR)",
      "habitat": ["Hutan Hujan Tropis"],
      "makanan": ["Telur Burung"],
      "ancaman": ["Kehilangan Dan Degradasi Habitat Alami"],
      "taksonomi": "Pongo Genus",
      "gambar": "http://commons.wikimedia.org/wiki/Special:FilePath/Nanga%20with%20male%20baby.jpg",
      "url_iucn": "https://www.iucnredlist.org/species/17975/259043172",
      "wikidata": "http://www.wikidata.org/entity/Q599672"
    }
  ],
  "meta": {"page": 1, "per_page": 2, "total": 1}
}
```

Status lain yang mungkin: `200` dengan `data` kosong untuk filter yang tidak cocok, atau `422` jika parameter tidak valid.

## `GET /api/fauna/filter-options`

Mengambil pilihan filter yang dihitung dari dataset aktif. Tidak menerima parameter.

Response `200`:

```json
{
  "data": {
    "status": {"StatusKritis": "Kritis (CR)", "StatusTerancam": "Terancam (EN)", "StatusRentan": "Rentan (VU)"},
    "habitat": {"HutanHujanTropis": "Hutan Hujan Tropis"},
    "makanan": {"BuahbuahanDaunDanSerangga": "Buahbuahan Daun Dan Serangga"},
    "kelompok": ["Amfibi", "Burung"],
    "ancaman": {"KehilanganDanDegradasiHabitatAlami": "Kehilangan Dan Degradasi Habitat Alami"}
  }
}
```

## `GET /api/fauna/{slug}`

Mengambil objek fauna publik berdasarkan slug hasil konversi subject CamelCase Turtle ke kebab-case.

Contoh request:

```text
GET /api/fauna/orangutan-kalimantan
```

Response `200` berbentuk `{ "data": Fauna }` dengan field yang sama seperti item katalog. Jika slug tidak ada, response `404`:

```json
{
  "message": "Fauna tidak ditemukan.",
  "slug": "tidak-ada"
}
```

## `GET /api/fauna/{slug}/rekomendasi`

Mengambil fauna lain yang memiliki kesamaan kelompok, status konservasi, atau habitat.

Query parameter `limit` opsional, integer 1–20, default `6`.

```text
GET /api/fauna/orangutan-kalimantan/rekomendasi?limit=2
```

Response `200`:

```json
{"data": [{"slug": "...", "nama": "..."}]}
```

Slug yang tidak ada menghasilkan `404` dengan format error detail fauna; `limit` invalid menghasilkan `422`.

## `POST /api/pencarian-semantik`

Mencari fauna berdasarkan token, sinonim habitat/makanan/status/kelompok, nama, dan deskripsi dari dataset.

Request JSON wajib:

```json
{"kalimat": "satwa pemakan buah di hutan"}
```

`kalimat` harus berupa string dengan panjang 2–500 karakter. Response `200`:

```json
{
  "data": [
    {
      "fauna": {"slug": "...", "nama": "..."},
      "skor": 2,
      "alasan_kecocokan": ["makanan: ...", "habitat: ..."]
    }
  ]
}
```

Payload kosong, terlalu pendek, atau terlalu panjang menghasilkan `422`.

## `GET /api/knowledge-graph`

Menghasilkan graph dari fauna, kelompok, habitat, makanan, dan ancaman pada dataset.

Query parameters:

| Parameter | Tipe | Aturan |
|---|---|---|
| `relasi[]` | string[] | Opsional; `hidupDi`, `memakan`, `terancamOleh`, `bagianDari` |
| `limit` | integer | Opsional, 1–200 fauna; default `50` |

Contoh request:

```text
GET /api/knowledge-graph?relasi[]=hidupDi&limit=2
```

Response `200`:

```json
{
  "nodes": [
    {"id": "fauna:orangutan-kalimantan", "label": "Orangutan kalimantan", "type": "fauna", "slug": "orangutan-kalimantan"}
  ],
  "edges": [
    {"from": "fauna:orangutan-kalimantan", "to": "habitat:HutanHujanTropis", "label": "hidup di", "type": "hidupDi"}
  ]
}
```

Nilai relasi atau limit yang tidak valid menghasilkan `422`.

## Sumber data dan batasan

- Sumber data: `dataset/fauna_kalimantan.ttl`.
- Parser berjalan in-process; belum ada Fuseki, database, atau library RDF eksternal.
- Dataset fauna diparse dan disimpan pada cache file Laravel selama 1 jam.
- Endpoint laporan warga, autentikasi, favorit, profil, moderasi, sebaran GeoJSON, dan perbandingan belum terdaftar pada route API saat ini.
