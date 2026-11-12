# [Pertemuan 11](https://kelas.usu.ac.id/mod/googlemeet/view.php?id=225962 "Pertemuan 11") - Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple

- Triple dengan LIMIT 10: maksimal 30 triple
- Triple dengan LIMIT 30: 90 triple
- Baris SELECT LIMIT 10 / kota berbeda: 10/ 30
- Mengapa tidak (atau mengapa tepat) 30 triple: LIMIT membatasi jumlah solution, bukan jumlah triple yang dihasilkan. Satu solution dapat menghasilkan beberapa triple dari pola CONSTRUCT. Selain itu, triple yang sama hanya disimpan satu kali dalam graf RDF sehingga jumlah akhirnya bisa berbeda dari nilai LIMIT.

## Bagian B - DBpedia ke Turtle

- Output demo_c.py: [48] triple, film tanpa tanggal: [16]
- Sumber data: DBpedia
- Triple yang tetap tercetak untuk film tanpa tanggal: Triple tipe film (schema:Movie), nama film (schema:name), dan sutradara (schema:director) tetap tercetak selama datanya tersedia. Sedangkan tanggal rilis tidak tercetak jika tidak tersedia karena menggunakan OPTIONAL.
- Query lokal tanpa internet: Query tetap berhasil karena data sudah disimpan dalam file film_nolan.ttl. RDFLib membaca file tersebut secara lokal tanpa perlu mengakses endpoint kembali.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Karena tipe film yang disimpan adalah schema:Movie, bukan dbo:Film, sehingga pencarian menggunakan dbo:Film tidak menemukan hasil.

## Bagian C - Materialisasi

- Output demo_d.py:
  - Sebelum: 0
  - Sesudah: 3
  - Ada Rina: 3
- Mengapa Ada Rina bernilai 3: Karena aturan materialisasi dijalankan sebelum Rina dimasukkan ke graf. Setelah Rina ditambahkan sebagai ex:DosenTetap, aturan tidak otomatis dijalankan kembali sehingga Rina belum dihitung sebagai foaf:Person.
- Output materialisasi_ulang.py:
  - Putaran 1: 4 triple baru | Person: 3
  - Rina masuk | Person: 3
  - Putaran 2 | Person: 4
  - Triple turunan disimpan terpisah: 6
- Tiga triple yang bukan baru: Tiga triple tersebut sudah ada di graf awal sehingga tidak perlu ditambahkan kembali. Program memeriksa if t not in g agar hanya triple yang belum ada yang dimasukkan.
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: File ini digunakan untuk menyimpan triple hasil inferensi secara terpisah dari graf utama. Jika hubungan kelas berubah, triple turunan harus diperiksa kembali. Langkah manualnya adalah memperbarui aturan, menjalankan ulang materialisasi, dan menghapus triple lama yang sudah tidak sesuai.

## Bagian D - Diagnosis

| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | 0 triple | Graf kosong | Variabel ?film pada CONSTRUCT berbeda dengan ?f pada WHERE | 3 triple |
| rusak_b | Lebih dari 3 triple jika nama tersedia lebih dari satu | Nama orang muncul berulang | Baris ?o ?p ?n mengambil beberapa properti nama sekaligus | 3 triple |
| rusak_c | 0 triple jika tidak ada tipe langsung | Kelas turunan tidak ditemukan | Pola ?x a ex:Permukiman tidak menelusuri subkelas | 3 triple |

## Bagian E - Etika dan diskusi

| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | Tidak relevan karena hanya berisi query SPARQL | Menggunakan User-Agent KelasWebSemantik/1.0 dengan alamat email sebagai kontak |
| 2 LIMIT | Menggunakan LIMIT 10 untuk membatasi hasil eksplorasi | Tidak menggunakan LIMIT karena mengambil graf film untuk disimpan secara lokal |
| 3 Simpan, jangan ulangi | Tidak menyimpan hasil endpoint secara langsung | Menyimpan hasil ke film_nolan.ttl agar bisa digunakan kembali secara lokal |
| 4 Jangkar jalur | Menggunakan wdt:P31/wdt:P279* wd:Q515 dengan IRI tujuan yang jelas | Tidak menggunakan property path sehingga aturan ini tidak relevan |

- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya: Menggunakan property path * dengan dua variabel tanpa jangkar IRI tertentu. Hal ini dapat membuat endpoint menelusuri terlalu banyak kemungkinan hubungan dan membutuhkan sumber daya lebih besar. Query sengaja tidak dijalankan.

### Diskusi 1 - Taksonomi berubah

Jika hasil materialisasi Demo D disimpan ke graf produksi, perubahan taksonomi dapat menyebabkan beberapa triple hasil inferensi menjadi tidak sesuai lagi. Contohnya, sebelumnya ex:DosenTetap merupakan turunan dari foaf:Person sehingga Rina mendapatkan triple rdf:type foaf:Person. Jika hubungan tersebut dihapus atau diubah, triple hasil inferensi yang menyatakan Rina sebagai foaf:Person bisa menjadi tidak valid.

Untuk menemukannya, saya akan membandingkan kampus_v4.ttl dan kampus_turunan.ttl dari C2 dengan struktur taksonomi terbaru. Saya akan memeriksa hubungan rdfs:subClassOf untuk mengetahui apakah triple turunan masih memiliki dasar yang benar. Triple yang sudah tidak sesuai perlu dihapus atau dibuat ulang berdasarkan aturan terbaru.

Dari percobaan ini, saya memahami bahwa materialisasi menyimpan hasil kesimpulan ke dalam graf, tetapi tidak otomatis memperbarui kesimpulan ketika struktur kelas berubah. Oleh karena itu, materialisasi perlu dijalankan kembali setelah ada perubahan taksonomi agar data tetap konsisten dan tidak mengandung informasi yang sudah tidak sesuai.

### Diskusi 2 - SELECT atau CONSTRUCT

Menurut saya, SELECT lebih cocok digunakan untuk dashboard populasi kota karena dapat digunakan untuk menggabungkan dan mengolah data dari beberapa sumber. Data kota dapat dicocokkan berdasarkan identitasnya, kemudian dihitung menggunakan COUNT, SUM, dan GROUP BY sebelum ditampilkan dalam bentuk tabel atau grafik.

Hal ini berkaitan dengan hasil Bagian A, yaitu LIMIT tidak selalu menghasilkan jumlah triple yang sama dengan jumlah solution. Satu kota dapat memiliki beberapa data populasi sehingga muncul lebih dari satu kali. Jika data langsung dijumlahkan tanpa pemeriksaan, hasilnya bisa menjadi ganda.

Selain itu, pada percobaan rusak_b.rq, satu orang dapat mempunyai beberapa nama dari properti berbeda, seperti foaf:name, rdfs:label, dan schema:name. Hal ini menunjukkan bahwa penggabungan data dari beberapa sumber perlu memperhatikan perbedaan properti dan tag bahasa.

Dengan SELECT, data bisa disaring, dikelompokkan, dan dihitung sebelum ditampilkan. Sementara itu, CONSTRUCT lebih cocok jika hasil akhirnya ingin disimpan kembali sebagai graf RDF dengan struktur dan hubungan baru. Jadi, saya memilih SELECT untuk dashboard karena lebih sesuai untuk pengolahan dan penyajian data.

### Cek diri

| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | Bagian A menunjukkan CONSTRUCT menghasilkan triple berdasarkan solution dan menggabungkan triple yang sama. |
| 2 | Bagian D query rusak_a.rq menghasilkan graf kosong karena variabel ?film tidak terikat. |
| 3 | Bagian A menggunakan jalur /, sedangkan Bagian D query B menggunakan VALUES untuk memilih beberapa orang dan properti nama. |
| 4 | Bagian C menggunakan rdfs:subClassOf* untuk menelusuri hierarki kelas tanpa menyebutkan kelas perantara. |
| 5 | Bagian C menunjukkan hasil materialisasi harus dihitung ulang setelah ada data baru agar kesimpulan tetap sesuai. |