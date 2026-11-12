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
