# Pertemuan 11 - Tugas Mandiri: Dari Endpoint ke Graf Lokal


## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: 30 triple.
- Triple dengan LIMIT 30: 90 triple.
- Baris SELECT LIMIT 10 / kota berbeda: 10 baris / 10 kota berbeda.
- Mengapa tidak (atau mengapa tepat) 30 triple: Hasil mencapai 30 triple karena terdapat 3 pola triple dalam CONSTRUCT, yaitu `rdf:type`, `rdfs:label`, dan `ex:populasi`. Setiap solusi menghasilkan 3 triple sehingga 10 solusi menghasilkan 30 triple.

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 48 triple, film tanpa tanggal: 16.
- Sumber data: DBpedia.
- Triple yang tetap tercetak untuk film tanpa tanggal: Triple tipe film dan nama film tetap dihasilkan, sedangkan triple tanggal rilis tidak dihasilkan karena variabel tanggal tidak memiliki nilai (unbound). Hal ini tidak menyebabkan error karena SPARQL melewati triple yang variabelnya tidak terikat.
- Query lokal tanpa internet: Query tetap berhasil dijalankan karena data sudah tersimpan dalam file `film_nolan.ttl`. Namun, menjalankan seluruh `demo_c.py` tanpa internet akan gagal karena program masih membutuhkan koneksi ke endpoint DBpedia.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Karena data film dalam graf menggunakan tipe `schema:Movie`, bukan `dbo:Film`. Keduanya merupakan kelas yang berbeda sehingga query `dbo:Film` tidak menemukan hasil.

## Bagian C - Materialisasi
- Output demo_d.py: Sebelum: 0, Sesudah: 3, Ada Rina: 3.
- Mengapa Ada Rina bernilai 3: Karena materialisasi dilakukan sebelum Rina ditambahkan, sehingga tipe ex:DosenTetap milik Rina belum diturunkan menjadi foaf:Person.
- Output materialisasi_ulang.py: Putaran 1: 4 triple baru, Person: 3. Rina masuk: Person: 3. Putaran 2: Person: 4. Triple turunan disimpan terpisah: 6.
- Tiga triple yang bukan baru: Triple tipe langsung individu yang sudah ada sebelumnya. Ketiganya dihasilkan dari zero-length path (*), tanpa melewati hubungan rdfs:subClassOf.
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: Menyimpan triple hasil inferensi secara terpisah agar mudah diperiksa dan diperbarui. Jika hubungan subclass berubah, triple inferensi yang tidak valid harus dihapus secara manual, kemudian materialisasi dijalankan ulang.

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | 0 triple | Graf kosong tanpa error | Variabel `?film` pada CONSTRUCT berbeda dengan `?f` pada WHERE. | 3 triple |
| rusak_b | Lebih dari 3 triple (jika beberapa properti cocok) | Satu orang memiliki beberapa nilai nama | Baris `?o foaf:name\|rdfs:label\|schema:name ?n .` mengambil nilai dari tiga properti sekaligus. | 3 triple |
| rusak_c | 0 triple (jika tidak ada tipe langsung `ex:Permukiman`) | Superkelas tidak ditemukan | Baris `?x a ex:Permukiman .` tidak menelusuri hubungan subclass. | 3 triple (Medan, Surabaya, Tomok) |

## Bagian E - Etika dan diskusi

| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | Tidak relevan, hanya berisi query. | Menggunakan User-Agent saat mengakses DBpedia. |
| 2 LIMIT | Menggunakan LIMIT untuk membatasi solution. | Tidak mengatur LIMIT secara langsung. |
| 3 Simpan, jangan ulangi | Hasil query dapat disimpan ke file. | Menyimpan hasil ke `film_nolan.ttl` untuk diolah secara lokal. |
| 4 Jangkar jalur | Tidak relevan jika tidak menggunakan `*` atau `+`. | Tidak menggunakan property path `*` atau `+`. |

- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya:
```sparql
SELECT ?awal ?akhir
WHERE {
  ?awal rdfs:subClassOf* ?akhir .
}```
Query tersebut tidak memiliki IRI pembatas sehingga endpoint harus menelusuri banyak kemungkinan hubungan dan membutuhkan proses lebih besar.

### Diskusi 1 - Taksonomi berubah
Jika taksonomi berubah, triple hasil inferensi yang tidak lagi sesuai dengan hubungan subclass menjadi salah. Pada C1, `kampus_v4.ttl` menyimpan data asli dan hasil inferensi dalam satu graf sehingga lebih sulit dibedakan. Sementara itu, C2 menyimpan hasil inferensi secara terpisah di `kampus_turunan.ttl`, sehingga lebih mudah diperiksa. Untuk menemukannya, saya membandingkan triple turunan dengan taksonomi terbaru. Triple yang tidak valid harus dihapus secara manual, kemudian materialisasi dijalankan ulang agar hasilnya sesuai dengan hubungan kelas terbaru.

### Diskusi 2 - SELECT atau CONSTRUCT
Untuk dashboard populasi kota, saya memilih SELECT karena lebih sesuai untuk menampilkan dan mengolah nilai populasi. Pada Bagian A, LIMIT 10 menghasilkan 10 solution, sedangkan CONSTRUCT menghasilkan 30 triple karena setiap solution mencetak tiga pola. Triple yang sama juga dapat digabung. Untuk menggabungkan data kota dari tiga sumber, saya memilih CONSTRUCT karena dapat menyatukan data ke dalam struktur RDF yang seragam. Pada `rusak_b.rq`, penggunaan tiga properti nama dapat menghasilkan beberapa nilai untuk satu orang. Perbedaan tag bahasa juga dapat membuat nilai yang tampak sama dianggap berbeda. Karena itu, data perlu diseragamkan sebelum digabungkan.

### Cek diri

| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | Bagian A: LIMIT 10 menghasilkan 30 triple dan LIMIT 30 menghasilkan 90 triple. |
| 2 | Bagian B: Triple tanggal tidak dicetak karena variabel tanggal tidak terikat. |
| 3 | Bagian D: `rusak_b.rq` menggunakan `|` dan `VALUES` untuk menggabungkan properti dan menentukan orang. |
| 4 | Bagian C: `rdfs:subClassOf*` menelusuri hierarki kelas. |
| 5 | Bagian C: C1 dan C2 menunjukkan hasil materialisasi perlu diperbarui ketika data berubah. |