# Laporan Konteks Proyek Codex — Jejak Fauna

Tanggal audit: 2026-11-27  
Jenis pekerjaan: audit dan pemulihan konteks read-only.  
Sumber kebenaran: file aplikasi dan hasil pemeriksaan lokal saat audit ini.

## 1. Identitas dan tujuan

Jejak Fauna adalah aplikasi Web Semantik untuk menjelajah fauna langka di Kalimantan. Aplikasi ini merupakan Laravel app yang berada di dalam repository kelompok Web Semantik. Implementasi saat ini memadukan katalog UI berbasis fixture JSON dengan backend API fauna yang membaca dataset Turtle lokal.

## 2. Struktur repository aktual

- Git root terdeteksi pada folder induk `web-semantik-kelompok8`.
- Laravel application root: `jejak-fauna-semantic-search/`.
- Folder aplikasi berisi `app/`, `bootstrap/`, `config/`, `database/`, `dataset/`, `docs/`, `public/`, `resources/`, `routes/`, `storage/`, dan `tests/`.
- Dataset semantik aplikasi hanya satu file: `dataset/fauna_kalimantan.ttl`.
- Tidak ditemukan file `.owl`, `.rdf`, `.nt`, `.n3`, `.jsonld`, atau `.rq` di dalam Laravel app.
- Repository induk juga berisi materi pertemuan kuliah dan artefak RDF/OWL lama; artefak itu bukan dataset yang dipakai oleh aplikasi Laravel saat ini.

File penting:

- `composer.json`: dependensi Laravel/PHP.
- `bootstrap/app.php`: registrasi route web, API, console, dan format error JSON.
- `routes/api.php`: enam endpoint API aktual.
- `routes/web.php`: halaman web yang masih langsung memakai `Route::view`.
- `app/Http/Controllers/Api/FaunaController.php`: validasi request dan adapter HTTP.
- `app/Services/SparqlService.php`: parser TTL, filter, scoring, rekomendasi, dan pembentukan graph.
- `resources/js/api.js`: adapter frontend yang masih membaca fixture `/dummy` dan `localStorage`.
- `docs/API.md`: dokumentasi endpoint aktual.
- `docs/HANDOFF.md`: handoff backend dan roadmap endpoint yang belum ada.

## 3. Git state

Bukti yang dapat dibaca dari Git:

- Branch saat ini: `feature/backend-sparql-engine`.
- HEAD: `8a46539e68d82d8a0dd1c4b1bab0a0f211ef73be`.
- HEAD juga menunjuk ke `origin/feature/backend-sparql-engine`.
- Commit HEAD: `feat: implement and document semantic fauna API`.
- Commit sebelumnya yang relevan: `b4c6a5a` (integrasi frontend UI/UX), `b038fbc` (merge RDF data), dan `8faf9c4` (penambahan dataset RDF fauna).
- Branch lokal yang terdeteksi: `develop`, `feature/backend-sparql-engine`, dan `main`.
- Remote terdeteksi sebagai remote GitHub bernama `origin`; alamat lengkap sengaja tidak dicantumkan di laporan.

Status perubahan lokal lengkap berstatus **UNKNOWN** pada audit ini. Git root berada satu tingkat di luar writable workspace yang diberikan kepada pemeriksaan shell; `git status` tidak dapat melakukan `chdir` ke root induk. Perintah pembacaan index juga menampilkan banyak penghapusan relatif terhadap worktree yang tidak dapat diakses oleh sandbox, sehingga output itu tidak aman dijadikan bukti perubahan nyata. Tidak ada operasi branch, pull, fetch, merge, rebase, commit, push, reset, atau clean yang dilakukan.

## 4. Technology stack dan readiness

- PHP 8.5.12.
- Composer 2.11.3.
- Laravel Framework 13.40.0 (constraint `^13.17`).
- Node.js 24.13.1 dan npm 11.8.0.
- Vite 8, Tailwind CSS 4, Leaflet, Leaflet MarkerCluster, dan vis-network.
- PHPUnit 12.5.43, Laravel Pint 1.32.1, Laravel Boost 2.10.4.
- Java executable tersedia.
- Docker, Docker Compose, dan `fuseki-server` tidak ditemukan pada pemeriksaan executable.
- Port 3030 tidak sedang listening.
- Konfigurasi Fuseki dan endpoint SPARQL kini tersedia secara feature-flagged; runtime TDB2 berada di luar repository.

`.env` ada dan tidak ditampilkan. Kunci yang relevan diperiksa tanpa nilai rahasia: `APP_ENV=local`, `APP_DEBUG=true`, `DB_CONNECTION=mysql`, `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, dan `FILESYSTEM_DISK=local`. `APP_KEY` terdeteksi terisi, tetapi nilainya tidak dibaca atau dilaporkan. `.env` tercantum di `.gitignore` dan tidak ditemukan sebagai file tracked melalui pemeriksaan index yang tersedia.

Boost sudah tercantum di `composer.json` dan terpasang. Perintah instalasi paket tidak dijalankan karena brief audit secara eksplisit melarang instalasi dan perubahan dependensi.

## 5. Arsitektur backend aktual

Alur utama:

1. Request masuk melalui `routes/api.php`.
2. `FaunaController` memvalidasi parameter Laravel.
3. Controller memanggil `App\\Services\\SparqlService`.
4. Service mengambil `dataset/fauna_kalimantan.ttl`, mem-parse-nya baris demi baris menjadi array PHP, lalu menyimpan hasil parse pada Laravel **file cache** selama 3600 detik.
5. Service menjalankan filter, pagination, pencarian, rekomendasi, atau pembentukan node-edge di PHP.
6. Controller mengembalikan JSON.

Nama `SparqlService` bersifat deskriptif terhadap domain, tetapi implementasinya tidak menjalankan SPARQL. Tidak ada library RDF eksternal atau triplestore.

Controller hanya satu: `app/Http/Controllers/Api/FaunaController.php`. Model `app/Models/User.php` masih model Laravel standar; tidak ada model favorit, laporan, moderasi, atau entitas fauna. Migration yang ada hanya users, cache, dan jobs.

## 6. API yang benar-benar terdaftar

Hasil `php artisan route:list --path=api` menunjukkan tepat enam route:

| Method | URL | Handler | Input/validasi | Status implementasi |
|---|---|---|---|---|
| GET | `/api/fauna` | `FaunaController@index` | `query` string max 200; array `status`, `habitat`, `makanan`, `kelompok`, `ancaman`; `page` min 1; `per_page` 1–100 | Terimplementasi |
| GET | `/api/fauna/filter-options` | `filterOptions` | Tidak ada input | Terimplementasi |
| GET | `/api/fauna/{slug}` | `show` | slug path; 404 JSON jika tidak ada | Terimplementasi |
| GET | `/api/fauna/{slug}/rekomendasi` | `rekomendasi` | `limit` 1–20, default 6 | Terimplementasi |
| POST | `/api/pencarian-semantik` | `pencarianSemantik` | JSON `kalimat`, wajib, string 2–500 karakter | Terimplementasi |
| GET | `/api/knowledge-graph` | `knowledgeGraph` | `relasi[]` whitelist; `limit` 1–200, default 50 | Terimplementasi |

Response katalog memiliki `data` dan `meta` (`page`, `per_page`, `total`). Detail/fauna publik memuat slug, nama, nama ilmiah, kelompok, deskripsi, status, habitat, makanan, ancaman, taksonomi, gambar, URL IUCN, dan Wikidata. Error validasi Laravel menjadi JSON `422`; fauna yang tidak ada menjadi JSON `404`.

Endpoint yang hanya ada di roadmap/dokumentasi handoff, bukan route aktual: sebaran, laporan warga, profil, laporan pengguna, admin laporan, perbandingan, favorit, laporan penemuan, login, register, dan verifikasi admin.

## 7. Dataset RDF/Turtle

File primer adalah `dataset/fauna_kalimantan.ttl`.

- Ukuran: 171.518 byte, 3.464 baris.
- Prefix: `jf: <https://jejakfauna.org/ontology#>`, `owl:`, `rdfs:`, dan `xsd:`.
- Terdeteksi 334 subject fauna pada pola subject Turtle yang dipakai parser.
- 193 entri memiliki `jf:Fauna` dan property fauna lengkap yang terdeteksi oleh pola sederhana service; dataset juga memuat resource/individu pendukung dan kelas.
- Property utama fauna: `namaLokal`, `namaIlmiah`, `deskripsi`, `berstatusKonservasi`, `mempunyaiHabitat`, `memakan`, `mempunyaiAncaman`, `mempunyaiUpayaKonservasi`, `termasukTaksonomi`, `tersebarDi`, `urlGambar`, `rdfs:seeAlso`, dan `owl:sameAs`.
- Terdeteksi 186 `owl:sameAs`, 193 `rdfs:seeAlso`, dan 79 `urlGambar`.
- Status yang dipetakan service: `StatusKritis`, `StatusTerancam`, dan `StatusRentan`.
- Subject CamelCase dikonversi ke slug kebab-case, misalnya `BettaAlbimarginata` menjadi `betta-albimarginata`.

Tidak ditemukan ontology schema terpisah. TTL memiliki namespace, class/property references, label, dan hubungan RDF, tetapi tidak terbukti memiliki deklarasi ontology formal yang lengkap (`owl:Class`, domain/range, restriction) di aplikasi ini. Validasi RDF/SHACL atau validasi dengan reasoner tidak dijalankan dan tidak dapat diklaim.

## 8. Pencarian, filter, rekomendasi, dan graph

### Pencarian

`listFauna` melakukan pencarian teks biasa pada nama, nama ilmiah, dan deskripsi. `pencarianSemantik` melakukan tokenisasi, menghapus stopword Bahasa Indonesia, lalu mencocokkan token dengan sinonim hard-coded untuk habitat, makanan, status, kelompok, nama, dan deskripsi. Skor berupa penjumlahan bobot sederhana dan alasan kecocokan. Ini adalah semantic-like keyword matching berbasis property RDF yang sudah diparse, bukan embedding, inferensi ontology, atau SPARQL.

### Filter dan pagination

Filter status, habitat, makanan, kelompok, ancaman, dan query dilakukan memakai `array_filter`; pagination dilakukan dengan `array_slice`. Opsi filter dihitung dari dataset saat runtime.

### Rekomendasi

Rekomendasi memberi skor berdasarkan kelompok yang sama, status yang sama, dan jumlah irisan habitat. Tidak ada model machine learning atau query graph traversal.

### Knowledge graph

`getKnowledgeGraph` membuat node dan edge PHP dari fauna pertama sampai `limit`: kelompok (`bagianDari`), habitat (`hidupDi`), makanan (`memakan`), dan ancaman (`terancamOleh`). Hasilnya cocok untuk visualisasi node-edge, tetapi bukan hasil query SPARQL terhadap graph store.

## 9. Frontend

Vite memasukkan `resources/css/app.css` dan `resources/js/app.js`. Blade views untuk beranda, eksplorasi, detail, peta, graph, auth, profil, laporan, admin, dan perbandingan sudah ada.

Integrasi backend belum selesai. `resources/js/api.js` menggunakan `fetch('/dummy/...')` untuk fauna, graph, peta, laporan, dan profil. Pencarian semantik frontend memakai synonym groups JavaScript. Favorit, status login demo, tema, dan pilihan perbandingan memakai `localStorage`. Pengiriman laporan, login, register, dan keputusan moderasi mengembalikan/simpan data mock. Banyak komentar `TODO(backend)` secara eksplisit menunjuk endpoint API yang harus menggantikan fixture.

## 10. Testing

Test yang terbaca:

- `tests/Feature/Api/FaunaApiTest.php`: empat test method yang memeriksa katalog/pagination, pencarian dan status filter, detail/rekomendasi/semantic search/graph, serta error 404/422.
- `tests/Feature/ExampleTest.php` dan `tests/Unit/ExampleTest.php`: test skeleton Laravel.

Test API membuktikan kontrak JSON dasar dan validasi route yang dicakup; test tersebut tidak membuktikan frontend, akurasi ontology, performa, autentikasi, upload, atau seluruh filter. PHPUnit kemudian dijalankan ulang dan lulus dengan 7 test serta 68 assertion, termasuk saat `FUSEKI_ENABLED=true` ketika Fuseki aktif.

`php artisan route:list` berhasil dijalankan. Test suite, server, dan build frontend tidak dijalankan agar audit tetap read-only dan tidak mengubah cache/runtime/build output.

## 11. Status matrix

| Kategori | Status | Evidence | Catatan |
|---|---|---|---|
| Laravel configuration | IMPLEMENTED AND VERIFIED | `composer.json`, `bootstrap/app.php`, `php artisan about` | Laravel 13.40.0; debug local aktif |
| API routes | IMPLEMENTED AND VERIFIED | `routes/api.php`, `route:list` | Tepat 6 route |
| Fauna listing | IMPLEMENTED BUT NOT VERIFIED | Controller/service/test | Belum diuji ulang pada audit ini |
| Pagination | IMPLEMENTED BUT NOT VERIFIED | `listFauna`, API test | `array_slice`, max 100 |
| Search | IMPLEMENTED BUT NOT VERIFIED | `listFauna`, `resources/js/api.js` | Backend teks; frontend fixture |
| Semantic search | IMPLEMENTED BUT NOT VERIFIED | `pencarianSemantik`, `hitungSkor` | Keyword/sinonim, bukan SPARQL/embedding |
| Filter options | IMPLEMENTED BUT NOT VERIFIED | `getFilterOptions` | Dihitung dari TTL |
| Fauna details | IMPLEMENTED BUT NOT VERIFIED | `show`, `getFaunaBySlug` | Field berasal dari parser |
| Recommendations | IMPLEMENTED BUT NOT VERIFIED | `getRekomendasiBySlug` | Skor kelompok/status/habitat |
| Knowledge graph | IMPLEMENTED BUT NOT VERIFIED | `getKnowledgeGraph`, graph test | Graph dibentuk di PHP |
| RDF dataset | IMPLEMENTED AND VERIFIED | `dataset/fauna_kalimantan.ttl`, query Fuseki | 3.125 triple; 193 `jf:Fauna`; 334 subject total |
| Ontology | PARTIALLY IMPLEMENTED | Prefix/class/property references | Tidak ada schema ontology terpisah yang ditemukan |
| SPARQL processing | COMPLETED AND VERIFIED | `SparqlService`, Fuseki query endpoint | Read-only SPARQL HTTP; scoring semantik tetap berbobot di PHP |
| API tests | PARTIALLY IMPLEMENTED | 4 method pada API test | Cakupan hanya endpoint fauna aktual |
| Frontend integration | PARTIALLY IMPLEMENTED | Blade + JS + API mock | UI ada, tetapi masih `/dummy`/localStorage |
| Authentication | NOT IMPLEMENTED | Tidak ada auth API/controller/middleware aplikasi | Model User standar saja |
| User management | NOT IMPLEMENTED | Tidak ada endpoint/fitur backend | Migration users masih skeleton |
| Favorites | NOT IMPLEMENTED | `localStorage` di JS | Belum tersimpan server |
| Reporting | NOT IMPLEMENTED | Form/fixture frontend saja | Tidak ada upload/persistence endpoint |
| Moderation | NOT IMPLEMENTED | UI mock dan fixture | Tidak ada otorisasi/decision server |
| Documentation | IMPLEMENTED AND VERIFIED | `README.md`, `docs/API.md`, `docs/HANDOFF.md`, `docs/FUSEKI_SETUP.md` | Dokumentasi mencerminkan mode Fuseki dan fallback |
| Apache Jena Fuseki | COMPLETED AND VERIFIED | Fuseki 6.3.0 portable, `localhost:3030` | Dataset aktif dan diuji langsung |
| TDB2 persistent storage | COMPLETED AND VERIFIED | Runtime `jejak-fauna-fuseki-runtime\databases\jejak-fauna` | Di luar repository; tidak di-commit |

## 12. Security dan maintainability review

- `.env` di-ignore dan nilai rahasia tidak dimasukkan ke laporan. `APP_DEBUG=true` sesuai local development, tetapi harus dimatikan untuk deployment.
- Tidak ada endpoint upload dataset atau SPARQL Update; risiko unrestricted upload/update belum muncul pada implementasi sekarang.
- Tidak ada input SPARQL dinamis, sehingga risiko SPARQL injection pada kode saat ini tidak terbukti. Risiko tersebut perlu ditinjau jika Fuseki/query builder ditambahkan.
- Validasi request tersedia pada controller untuk query, array filter, pagination, semantic query, relasi graph, dan limit.
- Path dataset dibentuk internal dengan `base_path`, bukan dari request pengguna.
- Parser TTL adalah parser line-oriented dengan regex dan asumsi format indentation/subject tertentu. Perubahan sintaks Turtle yang sah dapat membuat data diam-diam tidak terbaca; belum ada validasi parser formal.
- Cache file eksplisit mencegah ketergantungan endpoint fauna pada database, tetapi cache dapat stale sampai satu jam setelah dataset berubah.
- Konfigurasi `.env` memakai database/session/cache/queue driver database pada environment lokal, sementara fitur fauna sendiri memilih file cache. Endpoint fitur yang akan memakai session/database belum tersedia dan berpotensi membutuhkan setup database.
- `resources/js/api.js` menampilkan data fixture dan mencatat beberapa status sebagai dummy; fixture tidak boleh dianggap data faktual tanpa verifikasi sumber.
- Dataset memuat URL gambar HTTP dan referensi eksternal IUCN/Wikidata; deployment HTTPS perlu meninjau mixed content dan ketersediaan pihak ketiga.
- Tidak ditemukan implementasi otorisasi admin, rate limit khusus API, upload validation server, atau redaksi koordinat laporan—fitur-fitur itu memang belum tersedia.

## 13. Known gaps, bug risk, dan technical debt

1. Frontend belum tersambung ke enam endpoint backend aktual.
2. Frontend dan backend menghasilkan representasi/field yang tidak selalu sama karena frontend masih memakai fixture.
3. `SparqlService` bukan SPARQL engine; istilah ini dapat membingungkan saat presentasi akademik.
4. Tidak ada parser RDF formal, ontology validation, reasoner, atau SPARQL endpoint.
5. Tidak ada persistence untuk auth, favorit, laporan, riwayat, moderasi, dan perbandingan.
6. Dokumentasi handoff memuat roadmap endpoint yang belum ada; `docs/API.md` lebih akurat untuk route aktual.
7. Status Git worktree penuh belum dapat diverifikasi karena keterbatasan akses sandbox ke Git root induk.
8. Test suite tidak mencakup semua filter, malformed Turtle, cache invalidation, frontend, security boundary, dan deployment.

## 14. Integrasi Fuseki/Jena dan TDB2

Apache Jena Fuseki 6.3.0 berhasil dijalankan secara portable di luar repository, dengan TDB2 persisten pada `C:\_Ainuha\_Ainuha IT\_Semester03\Web_Semantik\jejak-fauna-fuseki-runtime\databases\jejak-fauna`. Dataset asli menghasilkan 3.125 triple dan 193 resource `jf:Fauna` melalui query SPARQL.

`SparqlService` sekarang memiliki mode Fuseki eksplisit melalui `FUSEKI_ENABLED=true`, endpoint terpusat, timeout, parsing SPARQL JSON, dan error `503` ketika Fuseki tidak tersedia. `FUSEKI_ENABLED=false` mempertahankan parser lokal sebagai fallback. Tidak ada endpoint SPARQL Update atau upload yang diekspos oleh API Laravel.

## 15. Rekomendasi milestone berikutnya

1. Tetapkan kontrak response backend sebagai sumber resmi dan sambungkan `resources/js/api.js` ke endpoint yang sudah terdaftar.
2. Tambahkan test untuk semua filter, filter-options, boundary pagination, cache, dan dataset parsing.
3. Pertahankan query SPARQL read-only dan pindahkan scoring semantik ke query Fuseki hanya jika kebutuhan dan vocabulary dataset mendukungnya.
4. Rancang persistence/authentication/favorites/reporting beserta middleware otorisasi sebelum mengaktifkan aksi pengguna.
5. Validasi dataset dan ontology dengan tooling RDF formal sebelum integrasi Fuseki/TDB2.
6. Setelah perubahan implementasi, ulangi audit route, test, frontend build, security, dan Git status.

## 16. Follow-up Fuseki dan konfirmasi ruang lingkup

Pada follow-up 2026-11-27, Apache Jena Fuseki 6.3.0 diunduh dari sumber resmi, checksum diverifikasi, dijalankan lokal, dan diintegrasikan secara feature-flagged ke `SparqlService`.

Dokumentasi setup ada di `docs/FUSEKI_SETUP.md`, dan `docs/HANDOFF.md` diperbarui dengan status aktual. File dataset dan frontend tidak diubah; perubahan kode ada pada konfigurasi, `.env` lokal non-rahasia, bootstrap error handling, exception, `SparqlService`, dan konfigurasi PHPUnit.

Verifikasi tambahan: `php artisan route:list --path=api` tetap menampilkan enam route; PHP syntax check lulus untuk 27 file; Pint lulus. Fuseki langsung mengembalikan 3.125 triple, 193 fauna, sample fauna, dan sample relasi. Enam endpoint Laravel melalui Fuseki mengembalikan `200` untuk request valid, `404` untuk fauna hilang, `422` untuk payload invalid, dan `503` saat Fuseki sengaja dihentikan. PHPUnit lulus dengan 7 test dan 68 assertion. Tidak ada commit atau push.
