# Pertemuan 11 - CONSTRUCT dan Hierarki Data

## Langkah 1 - Solution menjadi triple
| Solution |   ?d    |    ?mk     |       Triple yang dicetak        |
|----------|---------|------------|----------------------------------|
|     1    | ex:budi | ex:tif2108 | ex:tif2108 a ex:MataKuliahAktif  |
|          |         |            | ex:tif2108 ex:diampuOleh ex:budi |
|     2    | ex:budi | ex:tif2104 | ex:tif2104 a ex:MataKuliahAktif  |
|          |         |            | ex:tif2104 ex:diampuOleh ex:budi |
|     3    | ex:sari | ex:tif2108 | ex:tif2108 a ex:MataKuliahAktif  |
|          |         |            | ex:tif2108 ex:diampuOleh ex:sari |
|     4    | ex:ida  | ex:tif2104 | ex:tif2104 a ex:MataKuliahAktif  |
|          |         |            | ex:tif2104 ex:diampuOleh ex:ida  |

- Jumlah solution : 4
- Jumlah pola template : 2
- Jumlah cetakan triple : 8
- Jumlah triple di graf hasil : 6
- Triple yang tercetak lebih dari sekali : ex:tif2108 a ex:MataKuliahAktif  
                                           ex:tif2104 a ex:MataKuliahAktif

## Langkah 2 - Tebak lalu cek
| Soal | Tebakan | Hasil mesin | Penjelasan bila meleset |
|------|---------|-------------|-------------------------|
| A    | [isi]   | [isi]       | [isi]                   |
| B    | [isi]   | [isi]       | [isi]                   |
| C    | [isi]   | [isi]       | [isi]                   |
| D    | [isi]   | [isi]       | [isi]                   |

## Langkah 3 - Graf kampus lokal
- Berkas sumber dan IRI dasar graf: ../pertemuan-09/kampus_usu_v2.ttl — 
https://contoh.github.io/web-semantik/251402095/kampus#
- Kelas dosen dan properti nama: ex:Lecturer dan foaf:name
- Triple hasil pemetaan: 6 triple
- foaf:Person sebelum / sesudah: 2 / 5
- Triple asli vs triple turunan: Graf awal memiliki 28 triple. Program menghasilkan 6 triple turunan baru dari 3 dosen. Setiap dosen memperoleh tipe tambahan ex:Pegawai dan foaf:Person.
- Dosen baru setelah program selesai: Dosen yang ditambahkan setelah program selesai tidak otomatis memperoleh triple turunan. Program harus dijalankan kembali agar dosen baru ikut diproses.

## Langkah 4 - Wikidata
- Jumlah triple Q6: 110
- Jumlah baris Q6 versi SELECT: 50
- Penjelasan perbedaan: Jumlah triplenya gak pas 150 karena hasil dari CONSTRUCT itu sifatnya unik (himpunan). Jadi kalau ada data atau triple yang duplikat, dia otomatis digabung dan cuma dihitung satu kali. Bedanya sama SELECT, kalau SELECT itu bakal nampilin semua baris data di tabel apa adanya, biarpun ada data yang berulang atau double.
- Q7 dengan + vs *: Kalau pakai tanda tambah +, dia cuma nyari jenis kampus turunan atau subkelas di bawah universitas (minimal beda 1 tingkat), jadi si "universitas" sendiri gak bakal ikut masuk di tabel. Nah, kalau pakai tanda bintang *, dia mulai nyari dari tingkat 0. Efeknya, entitas "universitas" (Q3918) itu sendiri bakal ikut terdaftar di dalam hasil pencarian.
- Kelas yang mengejutkan dan alasannya: Summer university. Alasannya karena universitas musim panas itu umumnya hanya berupa program kursus pendek atau konferensi akademis selama beberapa minggu di masa liburan. Lembaga ini bukan institusi perguruan tinggi resmi yang punya program gelar sarjana jangka panjang. Menariknya, di dalam sistem Wikidata kelas ini ternyata tetap dikategorikan langsung sebagai subkelas dari universitas.

## Langkah 5 - Graf dari Python
- Jumlah triple universitas.ttl: **126**
- Jumlah baris universitas_per_kota.csv: **47**
- setReturnFormat(TURTLE): Digunakan untuk menentukan format hasil query dari Wikidata menjadi Turtle agar bisa dibaca sebagai graf RDF.
- Graph().parse(data=...): Digunakan untuk membaca hasil data Turtle dan mengubahnya menjadi graf RDF menggunakan RDFLib.
- Mengapa q_kota tidak memanggil endpoint: Karena data universitas sudah disimpan di universitas.ttl. Jadi, query q_kota cukup dijalankan pada graf lokal tanpa perlu mengirim permintaan ulang ke Wikidata.

## Refleksi

1. Mengapa jumlah triple hasil CONSTRUCT bisa lebih kecil dari jumlah baris SELECT dengan WHERE yang sama?

   Karena CONSTRUCT menghasilkan graf RDF yang berisi triple unik, sehingga triple yang sama tidak disimpan berulang. Sedangkan SELECT menampilkan hasil dalam bentuk tabel dan masih bisa memiliki baris yang berulang.

2. Bila taksonomi di graf kampus Anda berubah bulan depan, apa yang terjadi pada triple turunan di kampus_usu_v4.ttl?

   Triple turunan yang sudah tersimpan tidak otomatis berubah ketika taksonomi sumber diperbarui. Jadi, file kampus_usu_v4.ttl perlu dibuat atau diperbarui kembali menggunakan data dan aturan yang terbaru agar hasilnya sesuai.

3. Kapan Anda menyimpan hasil query sebagai tabel (CSV), dan kapan sebagai graf (Turtle)?

   CSV digunakan ketika hasil query berupa data tabel yang ingin dihitung, direkap, atau dibuka menggunakan Excel. Sedangkan Turtle digunakan ketika hasil query berupa graf RDF yang masih memiliki hubungan antar data dan ingin diproses kembali menggunakan SPARQL.