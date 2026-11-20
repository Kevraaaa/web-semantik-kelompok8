# Pertemuan 12 - Inferensi dan Reasoning

## Langkah 1 - Closure dengan tangan
| # | Triple turunan | Aturan | Premis |
|---|----------------|--------|--------|
| [isi] | [isi] | [isi] | [isi] |
- Jumlah triple turunan bermakna: [isi]
- Triple yang baru ditemukan di putaran kedua: [isi]
- Triple asserted yang tidak menurunkan apa pun, dan alasannya: [isi]

## Langkah 2 - Tebak lalu cek
### 1. Triple Turunan RDFS
Berikut adalah seluruh triple turunan bermakna yang diperoleh menggunakan aturan RDFS. Bagian OWL tidak diperhitungkan.
| # | Triple Turunan | Aturan | Premis |
|---|---|---|---|
| 1 | `ex:budi a ex:Dosen` | `rdfs2` | Domain `ex:membimbing` + `ex:budi ex:membimbing ex:andi` |
| 2 | `ex:andi a ex:Mahasiswa` | `rdfs3` | Range `ex:membimbing` + `ex:budi ex:membimbing ex:andi` |
| 3 | `ex:budi ex:mengenal ex:andi` | `rdfs7` | `ex:membimbing rdfs:subPropertyOf ex:mengenal` + `ex:budi ex:membimbing ex:andi` |
| 4 | `ex:sari a ex:Dosen` | `rdfs9` | `ex:sari a ex:DosenTamu` + `ex:DosenTamu rdfs:subClassOf ex:Dosen` |
| 5 | `ex:DosenTamu rdfs:subClassOf ex:Pegawai` | `rdfs11` | `ex:DosenTamu rdfs:subClassOf ex:Dosen` + `ex:Dosen rdfs:subClassOf ex:Pegawai` |
| 6 | `ex:Dosen rdfs:subClassOf ex:Orang` | `rdfs11` | `ex:Dosen rdfs:subClassOf ex:Pegawai` + `ex:Pegawai rdfs:subClassOf ex:Orang` |
| 7 | `ex:budi a ex:Pegawai` | `rdfs9` | `ex:budi a ex:Dosen` + `ex:Dosen rdfs:subClassOf ex:Pegawai` |
| 8 | `ex:andi a ex:Orang` | `rdfs9` | `ex:andi a ex:Mahasiswa` + `ex:Mahasiswa rdfs:subClassOf ex:Orang` |
| 9 | `ex:sari a ex:Pegawai` | `rdfs9` | `ex:sari a ex:Dosen` + `ex:Dosen rdfs:subClassOf ex:Pegawai` |
| 10 | `ex:DosenTamu rdfs:subClassOf ex:Orang` | `rdfs11` | `ex:DosenTamu rdfs:subClassOf ex:Pegawai` + `ex:Pegawai rdfs:subClassOf ex:Orang` |
| 11 | `ex:budi a ex:Orang` | `rdfs9` | `ex:budi a ex:Pegawai` + `ex:Pegawai rdfs:subClassOf ex:Orang` |
| 12 | `ex:sari a ex:Orang` | `rdfs9` | `ex:sari a ex:Dosen` + `ex:Dosen rdfs:subClassOf ex:Orang` |

### 2. Jumlah Triple Turunan Bermakna
Jumlah keseluruhan adalah **12 triple turunan bermakna**, yang diperoleh dari aturan `rdfs2`, `rdfs3`, `rdfs7`, `rdfs9`, dan `rdfs11`.

### 3. Triple yang Baru Ditemukan pada Putaran Kedua
Triple yang baru dapat ditemukan pada putaran kedua adalah:

1. `ex:budi a ex:Pegawai`
2. `ex:andi a ex:Orang`
3. `ex:sari a ex:Pegawai`
4. `ex:DosenTamu rdfs:subClassOf ex:Orang`
5. `ex:budi a ex:Orang`
6. `ex:sari a ex:Orang`
Triple tersebut membutuhkan hasil inferensi dari putaran pertama sebagai premis tambahan.

### 4. Triple Asserted yang Tidak Menurunkan Apa Pun
Triple yang tidak menghasilkan inferensi bermakna adalah:
```turtle
ex:andi a ex:MahasiswaBerprestasi .
```
Alasannya, tidak terdapat informasi yang menyatakan bahwa `ex:MahasiswaBerprestasi` merupakan subclass dari `ex:Mahasiswa` atau kelas lainnya. Reasoner tidak dapat menyimpulkan hubungan kelas hanya berdasarkan kemiripan nama.

## Langkah 3 - Reasoner pada graf kampus
- Berkas sumber dan IRI dasar graf: `../pertemuan-09/kampus_usu_v2.ttl`; IRI dasar `https://contoh.github.io/web-semantik/251402095/kampus#`
- Asserted / closure / inferred / bermakna: 32 / 86 / 54 / 8
- Triple di v4 yang tidak ada di asserted: 6
- Turunan yang muncul karena domain dan range ex:mengajar: Hanya `ex:mk_baru a ex:Course`, hasil aturan rdfs3 dari range `ex:mengajar` (`ex:ida ex:mengajar ex:mk_baru`). Di v2, `ex:mk_baru` hanya punya nama dan tidak punya tipe, jadi reasoner yang mengisinya. Domain `ex:mengajar` tidak menambah triple baru, karena budi, ida, dan siti sudah asserted sebagai `ex:Lecturer`. Range juga tidak menambah apa pun untuk `ex:pbo`, `ex:web_semantik`, dan `ex:basis_data` karena ketiganya sudah asserted sebagai `ex:Course`.
- Bila menambah dosen baru: Cukup menambah triple asserted ke graf sumber (`kampus_usu_v2.ttl`), misalnya `ex:dosen_baru a ex:Lecturer ; ex:mengajar ex:mk_x .`, lalu menjalankan ulang `reasoner_lokal.py`. Triple `a ex:Pegawai` dan `a foaf:Person` untuk dosen itu muncul otomatis di `kampus_usu_inferred.ttl`, tanpa ditulis tangan seperti di v4.

## Langkah 4 - Wikidata

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