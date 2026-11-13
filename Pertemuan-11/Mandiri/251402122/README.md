# Pertemuan 11 - Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: ada 30 hasil
- Triple dengan LIMIT 30: ada 90 hasil
- Baris SELECT LIMIT 10 / kota berbeda: ada 10 baris / ada 10 kota berbeda
- Mengapa tidak (atau mengapa tepat) 30 triple: Maksud dari LIMIT 10 adalah maksimal 10 solution, bukannya maksimal 10 triple. Jadi, jika setiap solution menghasilkan 3 triple yang berbeda, maka hasilnya akan ada maksimal 30 triple. Hal ini dikarenakan setiap solution atau kota yang ditemukan menghasilkan tiga triple dari template CONSTRUCT, jadi 10 solution x 3 triple = 30 baris triple. Disini tidak ada pengurangan jumlah triple akibat duplikasi makanya hasil 30. Begitu pula dengan LIMIT 30, 30 solution x 3 triple = 90 baris triple.
	Lalu pada hasil SELECT LIMIT 10, terdapat hasil 10 baris, dan 10 kota yang berbda. Hal ini menunjukkan bahwa setiap solution menghasilkan 3 triple yang berbeda, dan tidak ada triple kembar yang ditemukan dan yang dapat menyebabkan pengurangan jumlah hasil. Oleh karena itu, tidak ada kota yang menyebabkan selisih dan tidak ada pola template yang mencetak triple duplikat.

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 48 triple, film tanpa tanggal: 16
- Sumber data: DBpedia, data film yang diperoleh dikonversi ke format RDF Turtle dan disimpan dalam file local film_nolan.ttl.
- Triple yang tetap tercetak untuk film tanpa tanggal: 3 triple, yaitu : rdf:type schema:Movie, schema:name, dan schema director. Triple schema:dataPublished tidak berbentuk karena film tersebut tidak memiliki informasi tanggal rilis. Hal ini terjadi karena penggunaan klausa OPTIONAL pada query SPARQL.
- Query lokal tanpa internet: Query local dapat dijalankan tanpa internet karena datanya sudah di ambil dari dbpedia dan disimpan di film_nolan.ttl. Saat mengambil data terbaru dari dbpedia tetap butuh internet, tapi program yang sudah disimpan di film_nolan.ttl menggunakan RDFLib untuk membaca dan melakukan query file nya secara local.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Hal ini dikarenakan, pada query CONSTRUCT, data film diubah dengan kosakata Schema.org, yaitu schema:Movie, bukan dbo:Film. Makanya, pencarian yang memakai dbo:Film, hasilnya 0 karena triple tersebut tidak terdapat didalam graf film_nolan.ttl.

## Bagian C - Materialisasi
- Output demo_d.py: 0 (Sebelum ditambahkan), 3 (Setelah data ditambahkan, terdapat 3 triple dalam graf RDF), dan 3 (Ada Rina, ada 3 triple terkait Rina)
- Mengapa Ada Rina bernilai 3: Karena, Rina baru ditambah setelah proses materialisasi selesai. Walaupun Rina bertipe ex:DosenTetap dan kelas tersebut merupakan turunan foaf:Person, triple kesimpulan Rina a foaf:Person belum dibuat. Python RDFLib tidak otomatis menjalankan ulang aturan RDFS setiap kali ada data baru yang ditambahkan.
- Output materialisasi_ulang.py: 
Putaran 1: 4 triple baru | Person: 3
Rina masuk | Person: 3
Putaran 2 | Person: 4
Triple turunan disimpan terpisah: 6
- Tiga triple yang bukan baru: budi a ex:dosen, budi a foaf:Person, dan sari a foaf:Person. Hal ini dikarenakan, ketiganya sudah dihasilkan pada putaran pertama, jadi saat dijalankan lagi, setelah Rina ditambahkan, programnya hanya memasukkan triple yang belum ada di dalam graf (if t not in g). Jadi, triple Budi dan Sari tidak dihitung sebagai triple baru lagi.
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: Ini untuk menyimpan triple hasil inferensi secara terpisah dari data asli. Caranya adalah dengan menjalankan kembali proses materialisasi setelah ada data baru, memeriksa hasil inferensi, dan menyimpan hasilnya ke dalam file. Hasil inferensi disimpan ke kampus_turunan.ttl, sedangkan graf lengkap disimpan ke kampus_v4.ttl.

## Bagian D - Diagnosis
|  Query  | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|---------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | 0 triple    | Tidak ada hasil judul film | variable ?film di CONSTRUCT beda dengan ?f pada WHERE. Jadi, ?film tidak memiliki value | ada 3 triple : Following, Insomnia, dan Memento |
| rusak_b | 4 triple    | Christopher_Nolan menghasilkan 2 nama, sedangkan yang lain hanya 1 | Property path foaf:name\|rdfs:label\|schema:name mengambil semua nama yang ada tanpa mengelompokkan hasilnya | ada 3 triple : Setiap orang hanya ada 1 ex:nama, yaitu : Christopher_Nolan, Joko_Anwar, dan Mira_Lesmana |
| rusak_c | 0 triple    | Tidak ada hasil permukiman dari data kota | Query mencari ?x a ex:Permukiman secara langsung, padahal data menggunakan tipe yang lebih spesifik seperti ex:KotaMetropolitan, ex:KotaDiIndonesia, dan ex:Desa. Mesin tidak otomatis melakukan inferensi subclass. | ada 3 triple : Medan, Surabaya, dan Tomok (ex:Permukiman) |

## Bagian E - Etika dan diskusi
| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | [isi] | [isi] |
| 2 LIMIT | [isi] | [isi] |
| 3 Simpan, jangan ulangi | [isi] | [isi] |
| 4 Jangkar jalur | [isi] | [isi] |
- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya: [isi]

### Diskusi 1 - Taksonomi berubah
[150-250 kata]

### Diskusi 2 - SELECT atau CONSTRUCT
[150-250 kata]

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | [isi] |
| 2 | [isi] |
| 3 | [isi] |
| 4 | [isi] |
| 5 | [isi] |