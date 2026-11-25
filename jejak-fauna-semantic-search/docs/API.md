# Jejak Fauna API

Dokumentasi ini mencakup route API yang terdaftar pada aplikasi. Endpoint fauna menjalankan SPARQL melalui RDFLib pada `dataset/fauna_kalimantan.ttl`; Laravel memanggil adapter `scripts/rdf_semantic.py`. Validasi membaca `dataset/JejakFauna_Ontology_Final.owl` sebagai OWL/XML dengan XML parser, bukan parser RDF/XML.

## Ringkasan endpoint

| Method | URL | Fungsi |
|---|---|---|
| `GET` | `/api/fauna` | Katalog fauna, pencarian teks, filter, dan paginasi |
| `GET` | `/api/fauna/filter-options` | Nilai filter yang tersedia dari dataset |
| `GET` | `/api/fauna/{slug}` | Detail satu fauna |
| `GET` | `/api/fauna/{slug}/rekomendasi` | Rekomendasi fauna serupa |
| `POST` | `/api/pencarian-semantik` | Pencarian berdasarkan kalimat Bahasa Indonesia |
| `GET` | `/api/ontology/validation` | Validasi vocabulary dan data RDF terhadap OWL/XML |
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
| `query` | string | Opsional, maksimal 200 karakter; pencocokan nama, nama ilmiah, deskripsi dan toleransi typo pada nama |
| `status[]` | string[] | Opsional; key status seperti `StatusKritis` atau label tampilan `Kritis (CR)` |
| `habitat[]` | string[] | Opsional; key atau label habitat dari dataset |
| `makanan[]` | string[] | Opsional; key atau istilah yang cocok dengan label makanan |
| `kelompok[]` | string[] | Opsional; misalnya `Mamalia`, `Burung`, `Reptil`, `Amfibi` |
| `ancaman[]` | string[] | Opsional; key atau label ancaman dari dataset |
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
      "kelompok": "Mamalia",
      "deskripsi": "Spesies orang utan asli pulau Kalimantan",
      "status_iucn": "Kritis (CR)",
      "habitat": ["Hutan Hujan Dataran Rendah Kalimantan"],
      "makanan": ["Telur burung"],
      "ancaman": ["Kehilangan Dan Degradasi Habitat Alami"],
      "taksonomi": ["Pongo (Genus)"],
      "wilayah": ["Kalimantan"],
      "upaya_konservasi": ["Perlindungan kawasan habitat dan pemantauan populasi"],
      "gambar": "http://commons.wikimedia.org/wiki/Special:FilePath/Nanga%20with%20male%20baby.jpg",
      "url_iucn": "https://www.iucnredlist.org/species/17975/259043172",
      "wikidata": "http://www.wikidata.org/entity/Q599672",
      "sumber": ["https://www.iucnredlist.org/species/17975/259043172", "http://www.wikidata.org/entity/Q599672"]
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

Response `200` berbentuk `{ "data": Fauna }` dengan field yang sama seperti item katalog, termasuk habitat, makanan, ancaman, wilayah, dan upaya konservasi. Jika slug tidak ada, response `404`:

```json
{
  "message": "Fauna tidak ditemukan.",
  "slug": "tidak-ada"
}
```

## `GET /api/fauna/{slug}/rekomendasi`

Mengambil fauna lain melalui relasi SPARQL yang berbagi habitat, makanan, taksonomi, status konservasi, atau kelas kelompok.

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

Mencari fauna dengan mencocokkan istilah query terhadap literal nama/deskripsi serta objek relasi RDF (habitat, makanan, ancaman, wilayah, upaya konservasi, status, dan kelompok). Relasi yang cocok dikembalikan pada `alasan_kecocokan`. Token query juga ditoleransi terhadap typo kecil.

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

## `GET /api/ontology/validation`

Memvalidasi dataset Turtle terhadap ontology OWL/XML tanpa memodifikasi kedua file. Response memisahkan vocabulary yang belum didefinisikan dari vocabulary OWL yang sudah didefinisikan tetapi belum digunakan. Pemeriksaan mencakup namespace, domain/range, datatype literal, dan hubungan fauna.

Response `200`:

```json
{
  "data": {
    "triple_count": 3125,
    "fauna_count": 193,
    "class_count": 12,
    "property_count": 16,
    "valid_count": 2031,
    "vocabulary": {
      "namespace": "https://jejakfauna.org/ontology#",
      "namespace_matches": true
    },
    "classes": {
      "used": ["Amfibi", "Ancaman", "Burung", "Fauna", "Habitat", "Makanan", "Mamalia", "Reptil", "StatusKonservasi", "Taksonomi", "UpayaKonservasi", "Wilayah"],
      "undefined": [],
      "unused": ["StatusEndemisme"]
    },
    "properties": {
      "used": ["berstatusKonservasi", "deskripsi", "deskripsiAncaman", "deskripsiKonservasi", "kodeIUCN", "memakan", "mempunyaiAncaman", "mempunyaiHabitat", "mempunyaiUpayaKonservasi", "namaHabitat", "namaIlmiah", "namaLokal", "namaWilayah", "termasukTaksonomi", "tersebarDi", "urlGambar"],
      "undefined": [],
      "unused": ["beratTubuh", "berstatusEndemisme", "panjangTubuh"]
    },
    "errors": [],
    "warnings": [
      "114 fauna individuals have no urlGambar value.",
      "OWL classes defined but unused by RDF: StatusEndemisme.",
      "OWL properties defined but unused by RDF: beratTubuh, berstatusEndemisme, panjangTubuh."
    ]
  }
}
```

Angka `valid_count` menunjukkan jumlah triple berpredikat pada namespace Jejak Fauna yang tidak melanggar deklarasi ontology. Target object property tanpa tipe eksplisit dilaporkan sebagai peringatan, mengikuti open-world semantics OWL; jika tipe eksplisit bertentangan dengan range, validasi menghasilkan error. Dataset kosong menghasilkan hitungan nol dan warning, bukan response sukses berbentuk error.

Kesalahan runtime Python/RDFLib atau file dataset/ontology yang tidak tersedia menghasilkan HTTP `503` JSON, dan detail teknis dicatat pada log server.

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
- Laravel menjalankan RDFLib `7.6.0` sebagai subprocess dan seluruh pengambilan katalog, pencarian semantik, detail, rekomendasi, graph, serta filter menggunakan query SPARQL terhadap Turtle.
- OWL/XML hanya dipakai untuk validasi vocabulary dan constraint. Dataset tidak diubah atau dipaksakan menjadi RDF/XML.
- Cache file Laravel menyimpan hasil katalog SPARQL selama 1 jam; perubahan file dataset membentuk cache key baru.
- Jumlah individu `jf:Fauna` saat ini 193; jumlah triple dataset 3.125. Keduanya dihitung dari file saat validasi.
- Dataset tidak menyediakan koordinat geospasial. Peta tetap memakai GeoJSON ilustratif yang diberi label di UI.
- Endpoint laporan warga, autentikasi, favorit, profil, moderasi, sebaran GeoJSON, dan perbandingan belum terdaftar pada route API saat ini.

## Menyiapkan RDFLib

Pasang dependency Python dari root proyek:

```powershell
python -m pip install -r requirements.txt
```

Secara default Laravel menjalankan `python`, membaca file dataset/ontology di folder `dataset`, dan memberi waktu operasi maksimal 30 detik. Atur `RDF_PYTHON_BINARY`, `RDF_DATASET_PATH`, `RDF_ONTOLOGY_PATH`, atau `RDF_TIMEOUT` pada environment deployment jika lokasi/runtime berbeda.
