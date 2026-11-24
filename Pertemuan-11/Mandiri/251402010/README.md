# Pertemuan 11 Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: 24 triple (bisa bervariasi tergantung Wikidata)
- Triple dengan LIMIT 30: 72 triple
- Baris SELECT LIMIT 10 / kota berbeda: 10 baris / 8 kota berbeda
- Mengapa tidak (atau mengapa tepat) 30 triple: Tidak mencapai batas atas 30 triple karena satu kota memiliki multi-nilai (misalnya beberapa nilai populasi atau label bahasa alternatif) sehingga 1 solusi `SELECT` menghasilkan lebih dari 1 baris triple pada templat `CONSTRUCT`.

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 13 triple, film tanpa tanggal: 1
- Sumber data: DBpedia / data contoh film.ttl
- Yang tetap tercetak untuk film tanpa tanggal: Properti `a schema:Movie`, `schema:name`, dan `schema:director` tetap tercetak, sementara `schema:datePublished` tidak dicetak karena sifat `OPTIONAL`.
- Query lokal tanpa internet: Berhasil karena data telah di-serialisasi secara lokal ke `film_nolan.ttl`. Jika `demo_c.py` dijalankan ulang tanpa internet, proses unduh dari endpoint DBpedia akan gagal/timeout.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Karena pada graf lokal `film_nolan.ttl`, tipe entitas film telah diselaraskan dan diubah menjadi `schema:Movie` (sesuai templat CONSTRUCT Demo B), sehingga query dengan `dbo:Film` tidak mencocokkan pola apa pun.

## Bagian C - Materialisasi
- Output demo_d.py: Sebelum: 0 | Sesudah: 3 | Ada Rina: 3
- Mengapa Ada Rina bernilai 3: Karena penambahan data `ex:rina a ex:DosenTetap` dilakukan setelah proses materialisasi putaran pertama selesai, sehingga relasi inferensi turunan ke `foaf:Person` belum dijalankan ulang untuk Rina.
- Output materialisasi_ulang.py: Putaran 1: 4 triple baru | Person: 3 \n Rina masuk | Person: 3 \n Putaran 2 | Person: 4 \n Triple turunan disimpan terpisah: 6
- Tiga triple yang bukan baru: Triple refleksif/inferensi dasar seperti subkelas terhadap dirinya sendiri (`ex:Dosen rdfs:subClassOf ex:Dosen`) atau relasi yang sudah ada di graf dasar `kampus.ttl`.
- Kegunaan kampus_turunan.ttl dan langkah manual: Memisahkan data asli dan data hasil inferensi memudahkan pembersihan/pembaruan saat taksonomi berubah. Langkah manual yang tersisa adalah menjalankan ulang skrip materialisasi saat data/taksonomi diubah.

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | 6 triple | Dua nilai yang tampak sama / Triple lebih sedikit | Kurang filter bahasa (`FILTER (LANG(?j) = "en")`) sehingga label ganda tereksekusi. | 3 triple, satu judul per film Nolan |
| rusak_b | 5 triple | Dua nilai yang tampak sama | Variabel nama mengambil multi-label/multi-bahasa tanpa filter. | 3 triple, tepat satu ex:nama per orang |
| rusak_c | 0 triple | Graf kosong, tanpa error | Menggunakan `?x a ex:Permukiman` langsung tanpa penelusuran subkelas `a/rdfs:subClassOf*`. | 3 triple: Medan, Surabaya, Tomok |

## Bagian E - Etika dan diskusi
| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | Tidak ada (karena dijalankan di browser web GUI Wikidata) | Ada (`UA = "KelasWebSemantik/1.0 (251402098@student.usu.ac.id)"`) |
| 2 LIMIT | Memakai `LIMIT 10` | Tidak memakai LIMIT karena mengambil seluruh film Nolan yang jumlahnya terbatas |
| 3 Simpan, jangan ulangi | Tidak relevan (eksplorasi langsung) | Memenuhi (`g.serialize("film_nolan.ttl")`) |
| 4 Jangkar jalur | Memenuhi (`?kota wdt:P31/wdt:P279* wd:Q515`) | Tidak memakai jalur properti berpola |
- Versi yang melanggar aturan 4 (tidak dijalankan): `?x wdt:P31/wdt:P279* ?y` tanpa batasan IRI awal, yang membuat mesin SPARQL harus menelusuri seluruh semesta data endpoint dari segala arah (sangat membebani performa server).

### Diskusi 1 - Taksonomi berubah
Jika taksonomi berubah (misalnya `ex:Dosen` tidak lagi menjadi subkelas dari `foaf:Person`), maka triple inferensi yang disimpan di `kampus_turunan.ttl` akan menjadi usang atau salah karena masih memuat kesimpulan lama. Kita dapat menemukannya dengan membandingkan ulang hasil materialisasi dari graf dasar yang diperbarui dengan isi `kampus_turunan.ttl` (mendeteksi inkonsistensi relasi). Dibandingkan `kampus_v4.ttl` (yang mencampur data asli dan turunan dalam satu berkas tunggal dan sulit dibersihkan secara selektif), penggunaan berkas terpisah `kampus_turunan.ttl` pada C2 sangat membantu karena kita bisa menghapus atau menimpa seluruh isi berkas turunan dengan mudah tanpa merusak data sumber asli di `kampus.ttl`.

### Diskusi 2 - SELECT atau CONSTRUCT
Untuk dashboard populasi kota, penggunaan `SELECT` lebih disukai jika tujuannya adalah menampilkan tabel data bersih atau agregasi langsung di antarmuka pengguna, karena `SELECT` mengembalikan baris data mentah yang mudah diolah aplikasi. Sebaliknya, `CONSTRUCT` lebih tepat digunakan jika kita ingin mentransformasikan data antar-skema RDF atau membangun graf lokal terintegrasi dari berbagai sumber. Berdasarkan temuan Bagian A (populasi kembar/multivalue) dan `rusak_b.rq` (nama dengan tag bahasa berbeda), pengambilan data mentah dengan `SELECT` memungkinkan kita menerapkan fungsi pembersihan seperti `GROUP_CONCAT`, filter bahasa, atau deduplikasi di sisi aplikasi klien sebelum data tersebut direpresentasikan ulang menjadi triple graf yang konsisten.

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | Bagian A: 1 solusi `SELECT` menghasilkan beberapa baris triple pada templat `CONSTRUCT`. |
| 2 | Bagian D (`rusak_c.rq`): Mengembalikan 0 triple tanpa pesan error karena pola WHERE tidak cocok. |
| 3 | Bagian D (`rusak_b.rq`): Menggunakan union predikat `foaf:name|rdfs:label|schema:name`. |
| 4 | Bagian A & D (`perbaikan_c.rq`): Menggunakan jalur properti `a/rdfs:subClassOf*`. |
| 5 | Bagian C (`materialisasi_ulang.py`): Kesimpulan disimpan lewat fungsi materialisasi dan harus dihitung/dijalankan ulang saat data baru masuk. |