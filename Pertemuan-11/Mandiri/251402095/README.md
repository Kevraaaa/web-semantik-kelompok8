# Pertemuan 11 - Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: 30
- Triple dengan LIMIT 30: 90
- Baris SELECT LIMIT 10 / kota berbeda: 10 / 10
- Mengapa tidak (atau mengapa tepat) 30 triple: Hasilnya pas 30 triple karena ke-10 baris dari query `SELECT` (`LIMIT 10`) berasal dari 10 kota berbeda (Jakarta, Tubei, Turikale, Bondowoso, Tanah Merah, Kepanjen, Tanjung Selor, Sidoarjo, Atambua, dan Sintang), masing-masing dengan satu data populasi. Karena tidak ada kota yang ganda, ketiga pola di template `CONSTRUCT` langsung menghasilkan 30 triple unik (10 × 3 = 30) tanpa ada data kembar yang terhapus otomatis oleh graf RDF.

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 48 triple, film tanpa tanggal: 16
- Sumber data: DBpedia
- Triple yang tetap tercetak untuk film tanpa tanggal: Tiga pola triple yang wajib terikat pada klausa `WHERE`, yaitu tipe kelas (`?film a schema:Movie`), judul film (`schema:name`), dan sutradara (`schema:director dbr:Christopher_Nolan`). Karena pola `dbo:releaseDate` dibungkus dalam `OPTIONAL` dan tidak ditemukan pada ke-16 film tersebut di DBpedia, variabel `?rilis` tidak terikat sehingga hanya triple `schema:datePublished` saja yang dilewati.
- Query lokal tanpa internet: Bisa dijalankan tanpa internet karena graf hasil `CONSTRUCT` dari DBpedia sudah disimpan secara lokal ke berkas `film_nolan.ttl` lewat `g.serialize()`. Selanjutnya, `rdflib` membaca berkas lokal tersebut ke memori (`Graph().parse("film_nolan.ttl")`) dan mengeksekusi query secara mandiri tanpa mengakses endpoint DBpedia lagi.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Karena query `CONSTRUCT` pada `q_demo_b.rq` memetakan kelas `dbo:Film` dari ontologi DBpedia menjadi `schema:Movie` dari kosakata Schema.org. Di dalam `film_nolan.ttl`, semua entitas film tersimpan dengan tipe `schema:Movie` dan tidak memiliki triple `rdf:type dbo:Film` (serta tidak ada aturan inferensi yang menyetarakan kedua kelas tersebut).

## Bagian C - Materialisasi
- Output demo_d.py:
  ```text
   Sebelum: 0
   Sesudah: 3
   Ada Rina: 3
  ```
- Mengapa Ada Rina bernilai 3: Karena materialisasi menggunakan query `CONSTRUCT` bersifat statis (hanya memproses data yang ada pada saat query dijalankan) dan `rdflib.Graph` standar tidak memiliki *reasoner* RDFS otomatis. Triple `ex:rina a ex:DosenTetap` baru ditambahkan ke graf *setelah* blok `CONSTRUCT` selesai dieksekusi, sehingga triple turunan `ex:rina a foaf:Person` tidak otomatis terbentuk. Akibatnya, query `Q_ORANG` yang hanya mencari pola eksplisit `?x a foaf:Person` tetap mengembalikan 3 entitas lama.
- Output materialisasi_ulang.py:
  ```text
   Putaran 1: 4 triple baru | Person: 3
   Rina masuk | Person: 3
   Putaran 2 | Person: 4
   Triple turunan disimpan terpisah: 6
  ```
- Tiga triple yang bukan baru: Ketiga triple tersebut (`ex:budi a ex:DosenTetap`, `ex:sari a ex:Dosen`, dan `ex:andi a ex:Mahasiswa`) adalah deklarasi kelas awal yang sudah ada di `kampus.ttl`. Operator `*` (nol langkah atau lebih) pada `a/rdfs:subClassOf*` ikut mencetaknya saat menempuh 0 langkah (hanya mengevaluasi `rdf:type` aslinya), lalu disaring oleh `if t not in g:` karena sudah tersimpan di graf awal.
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: Pemisahan ke `kampus_turunan.ttl` menjaga data fakta di `kampus.ttl` tetap bersih dari triple turunan. Jika relasi `ex:Dosen rdfs:subClassOf foaf:Person` dicabut, triple usang milik Budi, Sari, dan Rina (`ex:budi`, `ex:sari`, `ex:rina a foaf:Person`) tidak mencemari data asli. Langkah manual yang perlu dilakukan adalah menghapus relasi subkelas tersebut di `kampus.ttl`, menghapus `kampus_turunan.ttl` dan `kampus_v4.ttl` lama, lalu menjalankan ulang materialisasi agar hanya `ex:andi` yang bertipe `foaf:Person`.

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
| :--- | :--- | :--- | :--- | :--- |
| `rusak_a` | 0 triple | Hasil kosong karena variabel `CONSTRUCT` tidak terikat | Nama variabel tidak cocok antara `CONSTRUCT` (`?film`) dan `WHERE` (`?f`). | 3 triple (1 judul per film Nolan: Following, Memento, Insomnia) |
| `rusak_b` | 4 triple | Triple ganda (4 triple untuk 3 orang) | Baris `?o foaf:name\|rdfs:label\|schema:name ?n .` tidak memfilter bahasa, sehingga `dbr:Christopher_Nolan` tercetak 2 kali. | 3 triple (1 `ex:nama` per orang: Christopher Nolan, Joko Anwar, Mira Lesmana) |
| `rusak_c` | 0 triple | Hasil kosong karena subkelas tidak ditelusuri | Baris `?x a ex:Permukiman .` hanya mengecek tipe langsung tanpa `rdfs:subClassOf*`. | 3 triple (`ex:Medan`, `ex:Surabaya`, `ex:Tomok` sebagai `ex:Permukiman`) |

## Bagian E - Etika dan diskusi
| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | Tidak diatur di berkas karena berisi query SPARQL murni yang dijalankan lewat antarmuka web Wikidata. | Diterapkan; menyertakan identitas aplikasi dan kontak email pada variabel `UA` melalui parameter `agent=UA`. |
| 2 LIMIT | Diterapkan; memakai klausa `LIMIT 10` dan `LIMIT 30` untuk membatasi baris hasil dari Wikidata. | Tanpa `LIMIT` eksplisit, tetapi hasil sudah dibatasi oleh pola spesifik `dbo:director dbr:Christopher_Nolan` (16 film). |
| 3 Simpan, jangan ulangi | Belum diterapkan; hasil tidak disimpan lokal sehingga setiap pengujian mengirim ulang permintaan ke endpoint. | Diterapkan; query ke DBpedia dikirim sekali lalu disimpan ke `film_nolan.ttl` untuk diproses secara lokal. |
| 4 Jangkar jalur | Diterapkan; jalur `wdt:P31/wdt:P279*` dijangkarkan ke kelas kota (`wd:Q515`) dan negara Indonesia (`wdt:P17 wd:Q252`). | Diterapkan; query pada `q_demo_b.rq` tanpa jalur transitif (`*`/`+`) dan langsung mengikat `?film` ke `dbr:Christopher_Nolan`. |
- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya: Menghapus jangkar kelas maupun negara pada `a_kota_limit.rq` (misalnya menjadi `?kota wdt:P31/wdt:P279* ?kelas` tanpa `wdt:P17 wd:Q252`). Query ini tidak dijalankan karena penelusuran transitif `wdt:P279*` tanpa jangkar memaksa endpoint menelusuri hierarki jutaan entitas Wikidata sehingga membebani server dan memicu timeout.

### Diskusi 1 - Taksonomi berubah
Perubahan taksonomi pada ontologi merupakan tantangan utama dalam pendekatan materialisasi graf menggunakan kueri SPARQL `CONSTRUCT`. Pada pustaka seperti `rdflib`, hasil `CONSTRUCT` bersifat statis dan tidak dilengkapi mekanisme *truth maintenance* otomatis. Artinya, ketika relasi hierarki kelas diubah—misalnya `ex:Dosen` dicabut sebagai subkelas dari `foaf:Person` atau struktur kelas baru ditambahkan—triple turunan yang sudah terlanjur dicetak sebelumnya (seperti `ex:budi a foaf:Person` dan `ex:rina a foaf:Person`) tidak akan terhapus dengan sendirinya. Jika triple hasil inferensi tersebut langsung digabungkan ke dalam graf data utama, graf akan tercemar oleh fakta kadaluarsa (*stale data*) yang tidak lagi sesuai dengan skema terbaru.
Untuk mengatasi masalah ini, pemisahan antara berkas fakta dasar (`kampus.ttl`) dan berkas hasil inferensi (`kampus_turunan.ttl`) menjadi praktik yang sangat krusial. Ketika taksonomi berubah atau ada penambahan individu baru setelah materialisasi, pengembang tidak perlu melacak dan menghapus satu per satu triple turunan yang salah secara manual. Cukup dengan memperbarui skema pada berkas sumber utama, membuang berkas turunan lama, lalu menjalankan ulang skrip materialisasi dari awal agar graf turunan dibangun kembali secara bersih. Pendekatan pemisahan dan regenerasi ulang ini menjaga integritas data sekaligus menjadi fondasi pemahaman sebelum menggunakan *reasoner* RDFS/OWL yang mengelola perubahan inferensi secara otomatis.

### Diskusi 2 - SELECT atau CONSTRUCT
Pemilihan antara bentuk kueri `SELECT` dan `CONSTRUCT` dalam SPARQL bergantung sepenuhnya pada kebutuhan pemrosesan data hilir. Kueri `SELECT` menghasilkan keluaran berbentuk tabel relasional yang berisi ikatan variabel dalam baris dan kolom. Format tabular ini sangat tepat digunakan ketika data ingin langsung ditampilkan ke antarmuka pengguna, dianalisis secara statistik menggunakan fungsi agregasi seperti `COUNT`, atau diperiksa secara cepat untuk proses *debugging*. Namun, keluaran `SELECT` melepaskan struktur graf RDF sehingga hasilnya tidak dapat digabungkan kembali dengan graf lain maupun dikueri ulang menggunakan SPARQL.
Sebaliknya, kueri `CONSTRUCT` mengembalikan graf RDF utuh berupa sekumpulan triple unik yang dibentuk sesuai pola templat. Bentuk ini menjadi pilihan utama dalam tiga skenario penting: pertama, pemetaan kosakata (*vocabulary mapping*) antarontologi yang berbeda, seperti mengubah kelas `dbo:Film` dari DBpedia menjadi `schema:Movie`; kedua, penyimpanan lokal (*caching*) subgraf dari *endpoint* publik ke berkas Turtle (`film_nolan.ttl`) agar dapat dikueri berulang kali secara luring tanpa membebani server; dan ketiga, materialisasi fakta turunan berbasis aturan hierarki kelas. Dengan demikian, gunakan `SELECT` untuk kebutuhan pelaporan tabular yang siap konsumsi, dan pilih `CONSTRUCT` ketika keluaran harus tetap menjadi data semantik yang dapat diintegrasikan atau dinalar lebih lanjut.

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | Menjalankan query `CONSTRUCT` dan `SELECT` pada Wikidata (`a_kota_limit.rq`) dengan `LIMIT 10` (30 triple dari 10 kota unik) dan `LIMIT 30` (90 triple), serta membuktikan batas atas jumlah triple dan mekanisme deduplikasi graf RDF (lihat `screenshots/a-limit10.png` dan analisis Bagian A). |
| 2 | Menarik 48 triple dari DBpedia menggunakan pemetaan `dbo:Film` ke `schema:Movie` dan `OPTIONAL` pada `q_demo_b.rq`, menyimpannya ke berkas lokal `film_nolan.ttl` lewat `demo_c.py`, lalu menjalankan query luring tanpa internet yang mendeteksi 16 film tanpa tanggal rilis (lihat `screenshots/b-dbpedia.png`). |
| 3 | Melakukan materialisasi hierarki kelas menggunakan jalur `a/rdfs:subClassOf*` pada `kampus.ttl` lewat `demo_d.py` dan `materialisasi_ulang.py`, memisahkan 6 triple turunan ke `kampus_turunan.ttl`, serta mendemonstrasikan materialisasi ulang saat entitas baru `ex:rina` masuk (lihat `screenshots/c-materialisasi.png`). |
| 4 | Mendiagnosis tiga query `CONSTRUCT` yang tidak memunculkan pesan error (`rusak_a.rq`, `rusak_b.rq`, `rusak_c.rq`) dengan membandingkan jumlah triple terhadap `film.ttl` dan `kota.ttl`, lalu memperbaikinya menjadi `perbaikan_a.rq`, `perbaikan_b.rq`, dan `perbaikan_c.rq` (masing-masing menghasilkan tepat 3 triple). |
| 5 | Menerapkan empat etika akses *endpoint* publik—menyertakan email pada `User-Agent` di `demo_c.py`, membatasi baris dengan `LIMIT` di `a_kota_limit.rq`, menyimpan graf lokal ke `film_nolan.ttl` agar tidak mengulang permintaan, dan menjangkarkan jalur properti transitif (lihat tabel Bagian E). |
