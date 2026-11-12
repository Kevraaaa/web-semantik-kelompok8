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
- Output demo_d.py: [isi]
- Mengapa Ada Rina bernilai 3: [jawaban]
- Output materialisasi_ulang.py: [isi]
- Tiga triple yang bukan baru: [jawaban]
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: [jawaban]

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | [isi] | [isi] | [isi] | [isi] |
| rusak_b | [isi] | [isi] | [isi] | [isi] |
| rusak_c | [isi] | [isi] | [isi] | [isi] |

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