# Pertemuan 12 - Inferensi dan Reasoning

## Langkah 1 - Closure dengan tangan
| # | Triple turunan | Aturan | Premis |
|---|----------------|--------|--------|
| [isi] | [isi] | [isi] | [isi] |
- Jumlah triple turunan bermakna: [isi]
- Triple yang baru ditemukan di putaran kedua: [isi]
- Triple asserted yang tidak menurunkan apa pun, dan alasannya: [isi]

## Langkah 2 - Tebak lalu cek
| Soal | Profil | Tebakan | Hasil mesin | Penjelasan bila meleset |
|------|--------|---------|-------------|-------------------------|
| A | RDFS | [isi] | [isi] | [isi] |
| B | OWL RL | [isi] | [isi] | [isi] |
| C | RDFS | [isi] | [isi] | [isi] |
| C | OWL RL | [isi] | [isi] | [isi] |
| D | OWL RL | [isi] | [isi] | [isi] |

## Langkah 3 - Reasoner pada graf kampus
- Berkas sumber dan IRI dasar graf: `../pertemuan-09/kampus_usu_v2.ttl`; IRI dasar `https://contoh.github.io/web-semantik/251402095/kampus#`
- Asserted / closure / inferred / bermakna: 32 / 86 / 54 / 8
- Triple di v4 yang tidak ada di asserted: 6
- Turunan yang muncul karena domain dan range ex:mengajar: Hanya `ex:mk_baru a ex:Course`, hasil aturan rdfs3 dari range `ex:mengajar` (`ex:ida ex:mengajar ex:mk_baru`). Di v2, `ex:mk_baru` hanya punya nama dan tidak punya tipe, jadi reasoner yang mengisinya. Domain `ex:mengajar` tidak menambah triple baru, karena budi, ida, dan siti sudah asserted sebagai `ex:Lecturer`. Range juga tidak menambah apa pun untuk `ex:pbo`, `ex:web_semantik`, dan `ex:basis_data` karena ketiganya sudah asserted sebagai `ex:Course`.
- Bila menambah dosen baru: Cukup menambah triple asserted ke graf sumber (`kampus_usu_v2.ttl`), misalnya `ex:dosen_baru a ex:Lecturer ; ex:mengajar ex:mk_x .`, lalu menjalankan ulang `reasoner_lokal.py`. Triple `a ex:Pegawai` dan `a foaf:Person` untuk dosen itu muncul otomatis di `kampus_usu_inferred.ttl`, tanpa ditulis tangan seperti di v4.

**## Langkah 4 - Wikidata**

- ?n Q8 / Q9: **877 / 1.343**
- Penyebab selisih: Q9 hasilnya lebih besar karena pencariannya juga mencakup instance dari subkelas universitas, sedangkan Q8 hanya menghitung instance yang langsung termasuk universitas.
- Satu subkelas yang ditemukan: **Jezuit seminary (Q129844515)**, berdasarkan hasil Q7 Pertemuan 11.
- WDQS: **Tidak melakukan keduanya** dalam hal inferensi RDFS. Q9 menggunakan property path `P31/P279*` untuk menelusuri hubungan subkelas secara langsung saat query dijalankan. Jadi, aturan RDFS9 tidak otomatis dijalankan oleh WDQS.

## Langkah 5 - Konsistensi
- Pesan galat RDFS / OWL RL: [isi] / [isi]
- Individu yang disebut pesan: [isi]
- Setelah baris terbalik dihapus: [isi]
- Isi laporan_kelas.csv sebelum dan sesudah: [isi]
- Dari mana dosen menjadi mata kuliah: [jawaban]

## Refleksi
1. Mengapa graf asserted disimpan terpisah dari graf inferred, dan apa akibatnya bila keduanya dicampur seperti kampus_usu_v4.ttl?
2. Pada Langkah 2, profil RDFS tidak memberi tahu bahwa aksioma OWL diabaikan. Bagaimana Anda akan memastikan profil yang dipakai sudah tepat untuk data Anda?
3. Kapan Anda memilih menulis jalur seperti wdt:P279* di query, dan kapan memilih menjalankan reasoner lalu menyimpan closure??