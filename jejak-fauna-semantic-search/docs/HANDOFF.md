# Serah Terima Backend — Jejak Fauna Kalimantan

Project kini memiliki backend API fauna yang berjalan melalui HTTP. Endpoint aktual dan contoh payload lengkap tersedia di [docs/API.md](API.md). Dataset `dataset/fauna_kalimantan.ttl` terverifikasi berisi 3.125 triple dan 193 resource `jf:Fauna`. `SparqlService` dapat membaca dataset melalui Fuseki/SPARQL HTTP atau fallback parser lokal yang eksplisit.

Backend yang sudah tersedia:

- katalog fauna dengan pencarian teks, filter, dan paginasi;
- nilai filter dari dataset;
- detail fauna berdasarkan slug;
- rekomendasi berbasis kesamaan kelompok, status, dan habitat;
- pencarian semantik berbasis token dan sinonim Bahasa Indonesia;
- knowledge graph node/edge dari relasi fauna, kelompok, habitat, makanan, dan ancaman.

Validasi request dan error response API telah diuji melalui HTTP. Fallback lokal menggunakan file cache secara eksplisit sehingga endpoint fauna tidak bergantung pada database lokal. Mode Fuseki tidak memakai cache hasil query agar kehilangan koneksi dapat dikembalikan sebagai HTTP `503`.

## 1. Titik sambung `TODO(backend)`

Daftar komentar implementasi yang perlu ditindaklanjuti (nomor baris pada revisi ini):

| Lokasi | Integrasi yang perlu dilakukan |
|---|---|
| `routes/web.php:5, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26` | Ganti masing-masing route view dengan controller sesuai tanggung jawab route pada bagian 3. |
| `resources/js/api.js:19, 42, 68, 100, 118, 125, 133, 141, 154, 163, 171, 185, 192, 206, 213, 220` | Ganti pembacaan fixture dan simulasi dengan endpoint fauna, semantik, sebaran, laporan, graph, rekomendasi, favorit, profil, auth, dan verifikasi pada tabel bagian 2. |
| `resources/js/report.js:74` | Kirim FormData foto dan metadata ke endpoint laporan; tampilkan validasi server dan ID laporan. |
| `resources/js/admin.js:48` | Kirim keputusan moderasi/catatan ke API dan refresh status dengan respons server. |

Rincian baris komentar saat ini dapat diambil dengan:

```powershell
rg -n "TODO\(backend\)" routes resources
```

Nilai `status_iucn` pada fixture hanya contoh UI. Sebelum dipublikasikan, verifikasi taksonomi, kategori, tanggal penilaian, dan sumber status terkini, terutama status keberadaan Badak Sumatra di Kalimantan.

## 2. Kontrak endpoint yang direncanakan

Bagian ini adalah roadmap fitur yang belum terdaftar sebagai route API. Kontrak aktual yang sudah diimplementasikan berada di [docs/API.md](API.md).

Nama properti response di bawah mengikuti bentuk fixture `/dummy/*.json`. Tambahkan envelope `data` dan metadata paginasi sesuai standar API aplikasi bila diperlukan.

| Method dan URL | Query / payload | Bentuk response yang diharapkan |
|---|---|---|
| `GET /api/fauna` | `query`, `status[]`, `habitat[]`, `makanan[]`, `kelompok[]`, `ancaman[]`, `page`, `per_page` | `{ "data": [{ "slug": "orangutan-kalimantan", "nama": "Orangutan Kalimantan" }], "meta": { "page": 1, "per_page": 12, "total": 10 } }`; objek `data` mengikuti contoh Fauna utuh. |
| `GET /api/fauna/{slug}` | Slug dari fauna | Satu objek Fauna dengan `slug`, `nama`, `nama_ilmiah`, `kelompok`, `deskripsi`, `status_iucn`, `ancaman[]`, `habitat[]`, `makanan[]`, `ekosistem`, `karakteristik[]`, `sebaran[]`, `sumber[]`, `gambar`, `serupa[]`. |
| `POST /api/pencarian-semantik` | `{ "kalimat": "satwa pemakan buah di hutan" }` | `{ "data": [{ "fauna": Fauna, "skor": 2, "alasan_kecocokan": ["habitat: Hutan hujan tropis", "makanan: Buah"] }] }` |
| `GET /api/sebaran` | `fauna`, `status`, `habitat`, opsi batas peta | `FeatureCollection` GeoJSON dengan `geometry.coordinates: [lng, lat]` dan `properties.slug`, `properties.kelimpahan`. |
| `GET /api/laporan-warga` | `status=publik`, batas peta | `{ "data": [{ "id", "nama", "slug", "tanggal", "status", "lokasi": { "lat", "lng", "teks" }, "pelapor", "foto": [], "catatan" }] }`. Hapus/sembunyikan koordinat sensitif sebelum dipublikasikan. |
| `GET /api/profil/laporan` | Session pengguna aktif | `{ "data": [Laporan] }` untuk daftar laporan milik pengguna. |
| `GET /api/admin/laporan` | `status?`, session admin | `{ "data": [Laporan] }`; batasi field pelapor dan lokasi berdasarkan kebijakan akses. |
| `GET /api/knowledge-graph` | `relasi[]` | `{ "nodes": [{ "id", "label", "type", "slug?" }], "edges": [{ "from", "to", "label", "type" }] }` dengan tipe relasi `hidupDi`, `memakan`, `bagianDari`, `terancamOleh`. |
| `GET /api/fauna/{slug}/rekomendasi` | `limit` | `{ "data": [Fauna] }`. |
| `POST /api/fauna/perbandingan` | `{ "slugs": ["bekantan", "pesut-mahakam"] }` | `{ "data": [Fauna] }` dalam urutan slug yang diminta. |
| `POST /api/favorit/{slug}` | Session pengguna; tanpa body | `{ "favorit": true }`. |
| `DELETE /api/favorit/{slug}` | Session pengguna; tanpa body | `{ "favorit": false }`. |
| `GET /api/profil` | Session pengguna | `{ "nama", "email", "bergabung", "favorit": [slug], "riwayat_pencarian": [{ "kata_kunci", "waktu" }], "laporan": [id], "aktivitas": [{ "judul", "waktu", "ikon" }] }`. |
| `POST /api/laporan-penemuan` | `multipart/form-data`: `fauna_slug?`, `nama_bebas`, `deskripsi`, `tanggal`, `latitude`, `longitude`, `lokasi`, `foto[]` | `{ "data": { "id", "status": "menunggu", "created_at" } }`. |
| `POST /api/auth/login` | `{ "email", "password" }` | Session/token dan `{ "user": { "id", "name", "email" } }`; respons validasi `422` untuk input tidak valid. |
| `POST /api/auth/register` | `{ "name", "email", "password", "password_confirmation" }` | Session/token dan objek `user`; validasi email unik di server. |
| `POST /api/admin/laporan/{id}/verifikasi` | `{ "status": "diverifikasi\|ditolak", "catatan": "..." }` | `{ "data": { "id", "status", "catatan", "updated_at" } }`; wajib middleware otorisasi admin. |

### Contoh response JSON

Objek Fauna yang dipakai untuk katalog, detail, rekomendasi, dan banding:

```json
{
  "slug": "orangutan-kalimantan",
  "nama": "Orangutan Kalimantan",
  "nama_ilmiah": "Pongo pygmaeus",
  "kelompok": "Mamalia · Primata",
  "deskripsi": "Kera besar arboreal yang hidup di hutan Kalimantan. Orangutan membantu menjaga regenerasi hutan melalui penyebaran biji dan memiliki kehidupan sosial yang erat antara induk dan anak.",
  "status_iucn": "Kritis (CR)",
  "ancaman": ["Kehilangan habitat", "Perburuan dan perdagangan ilegal", "Kebakaran hutan"],
  "habitat": ["Hutan hujan tropis", "Hutan rawa gambut", "Hutan dataran rendah"],
  "makanan": ["Buah", "Daun", "Kulit kayu", "Serangga"],
  "ekosistem": "Hutan hujan tropis",
  "karakteristik": ["Hidup di kanopi", "Lengan panjang untuk bergelantungan", "Anak diasuh lama oleh induk"],
  "sebaran": [
    {"lat": 0.8, "lng": 113.8, "kelimpahan": "Jarang"},
    {"lat": 1.2, "lng": 114.2, "kelimpahan": "Umum"},
    {"lat": -0.5, "lng": 110.6, "kelimpahan": "Jarang"}
  ],
  "sumber": ["IUCN Red List (perlu verifikasi)", "WWF Indonesia", "BKSDA Kalimantan"],
  "gambar": "/images/fauna-placeholder.svg",
  "ikon": "🦧",
  "serupa": ["owa-kalimantan", "bekantan", "lutung-kalimantan"]
}
```

```json
{
  "semantic_search": {
    "data": [{
      "fauna": {"slug": "orangutan-kalimantan", "nama": "Orangutan Kalimantan"},
      "skor": 2,
      "alasan_kecocokan": ["makanan: Buah", "habitat: Hutan hujan tropis"]
    }]
  },
  "geojson_feature": {
    "type": "Feature",
    "geometry": {"type": "Point", "coordinates": [113.8, 0.8]},
    "properties": {"slug": "orangutan-kalimantan", "kelimpahan": "Jarang"}
  },
  "graph": {
    "nodes": [{"id": "orangutan", "label": "Orangutan Kalimantan", "type": "fauna", "slug": "orangutan-kalimantan"}],
    "edges": [{"from": "orangutan", "to": "hutan", "label": "hidup di", "type": "hidupDi"}]
  },
  "report_created": {"data": {"id": "lap-005", "status": "menunggu", "created_at": "2026-03-08T09:30:00Z"}},
  "favorite": {"favorit": true},
  "admin_decision": {"data": {"id": "lap-001", "status": "diverifikasi", "catatan": "Informasi ditinjau.", "updated_at": "2026-03-08T09:30:00Z"}},
  "login": {"user": {"id": 12, "name": "Ayu Lestari", "email": "ayu@example.test"}, "token": "server-managed"}
}
```

Contoh objek Fauna saat ini ada di `public/dummy/fauna.json`; contoh laporan, GeoJSON, dan graf masing-masing ada di `public/dummy/laporan.json`, `public/dummy/sebaran.json`, dan `public/dummy/graph.json`.

## 3. Route web yang masih memakai view

| Nama route | Path | Tanggung jawab controller yang disarankan |
|---|---|---|
| `home` | `/` | Beranda, rekomendasi, ringkasan. |
| `explore` | `/eksplorasi` | Katalog, filter, pencarian. |
| `explore.show` | `/eksplorasi/{slug}` | Detail fauna, sebaran, rekomendasi, sumber. |
| `compare` | `/bandingkan` | Bandingkan 2–3 fauna. |
| `map` | `/peta` | Peta persebaran dan laporan publik. |
| `graph` | `/knowledge-graph` | Node dan edge. |
| `login` | `/masuk` | Form dan alur autentikasi. |
| `register` | `/daftar` | Pendaftaran pengguna. |
| `profile` | `/profil` | Data pengguna, favorit, riwayat, laporan. |
| `report.create` | `/lapor` | Form dan penyimpanan laporan. |
| `admin.reports` | `/admin/laporan` | Antrean dan moderasi dengan middleware admin. |

## 4. Fitur yang masih mock atau belum tersedia di backend

- Pencarian semantik memakai pencocokan kata kunci/sinonim JavaScript, bukan embedding atau Fuseki/SPARQL.
- Peta memakai GeoJSON/titik ilustratif dan tile basemap publik; kelimpahan serta koordinat bukan hasil survei.
- Knowledge graph memakai relasi contoh, bukan hasil query ontologi.
- Favorit, pilihan perbandingan, tema, status login demo (toggle `window.__DUMMY_LOGGED_IN__` di DevTools), dan keputusan moderasi disimpan di `localStorage`.
- Login/daftar hanya validasi browser dan flag demo; lupa kata sandi hanya toast.
- Laporan warga tidak diunggah atau disimpan. Pratinjau foto memakai object URL sementara.
- Profil, riwayat pencarian, aktivitas, serta antrean laporan hanya fixture dummy.
- Aksi setujui/tolak tidak memiliki otorisasi dan tidak mengubah data server.

Endpoint API yang belum tersedia: `/api/sebaran`, `/api/laporan-warga`, `/api/profil/laporan`, `/api/admin/laporan`, `/api/profil`, `/api/fauna/perbandingan`, `/api/favorit`, `/api/laporan-penemuan`, `/api/auth`, dan verifikasi laporan admin.

## 5. Verifikasi backend

Audit terakhir dijalankan pada server lokal Laravel melalui HTTP:

- 6 route API terdaftar sesuai `php artisan route:list --path=api`.
- Endpoint sukses mengembalikan HTTP `200` dan JSON valid.
- Fauna tidak ditemukan mengembalikan `404` JSON.
- Payload kosong dan parameter invalid mengembalikan `422` JSON.
- Dataset Fuseki menghasilkan 3.125 triple dan 193 resource `jf:Fauna`; pencarian, filter, rekomendasi, dan graph berhasil diuji.
- PHPUnit: 7 test lulus dengan 68 assertion, termasuk regression test API.
- PHP syntax check: lulus untuk controller, service, dan route.
- Laravel Pint lulus. `git diff --check` belum dapat dijalankan karena Git root berada di luar akses sandbox.

Bug yang diperbaiki: parser lokal sebelumnya memasukkan subject non-fauna dan melewatkan 21 deklarasi multi-baris; service kini memfilter resource `jf:Fauna`, sehingga fallback konsisten menghasilkan 193 fauna. Mode Fuseki menggunakan query read-only dan mengembalikan `503` saat server tidak tersedia.

## 6. Teknologi dan rencana pengembangan

Teknologi backend saat ini adalah Laravel 13, PHP 8.5, route/controller Laravel, query SPARQL HTTP melalui Fuseki 6.3.0, parser Turtle fallback, Laravel file cache untuk fallback, PHPUnit, dan Pint. Tahap berikutnya adalah menghubungkan frontend ke endpoint aktual serta menambahkan persistence/authentication untuk laporan dan pengguna.

## 7. Menjalankan proyek

Jalankan dari direktori aplikasi:

```powershell
composer install
npm install
npm run dev
php artisan serve
```

Tidak perlu menjalankan migration untuk pratinjau UI ini. Untuk bundle produksi, gunakan `npm run build`.

## 8. Status Apache Jena Fuseki

Apache Jena Fuseki 6.3.0 berhasil diunduh dari sumber resmi Apache, checksum SHA-512 diverifikasi, dan diekstrak secara portable di luar repository. Java yang tersedia adalah 26.0.2.1. Dataset TDB2 persisten berada di:

```text
C:\_Ainuha\_Ainuha IT\_Semester03\Web_Semantik\jejak-fauna-fuseki-runtime\databases\jejak-fauna
```

Fuseki dijalankan read-only pada `localhost:3030`; endpoint query yang dipakai Laravel adalah `http://localhost:3030/jejak-fauna/query`. Loading awal dilakukan melalui endpoint lokal sementara dalam mode update, lalu server dijalankan ulang tanpa `--update`.

Mode Fuseki Laravel diaktifkan dengan `FUSEKI_ENABLED=true`. Enam endpoint API berhasil diuji melalui Laravel di port lokal alternatif. Saat Fuseki dihentikan, API mengembalikan HTTP `503` terstruktur. Fallback lokal juga diuji dan menghasilkan 193 fauna.

Detail perintah startup, verifikasi SPARQL, dan troubleshooting tersedia di [FUSEKI_SETUP.md](FUSEKI_SETUP.md).
