# Pertemuan 12 - Inferensi dan Reasoning

##  1. Closure dengan tangan
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

#### 2. Jumlah Triple Turunan Bermakna
Jumlah keseluruhan adalah **12 triple turunan bermakna**, yang diperoleh dari aturan `rdfs2`, `rdfs3`, `rdfs7`, `rdfs9`, dan `rdfs11`.

#### 3. Triple yang Baru Ditemukan pada Putaran Kedua
Triple yang baru dapat ditemukan pada putaran kedua adalah:
1. `ex:budi a ex:Pegawai`
2. `ex:andi a ex:Orang`
3. `ex:sari a ex:Pegawai`
4. `ex:DosenTamu rdfs:subClassOf ex:Orang`
5. `ex:budi a ex:Orang`
6. `ex:sari a ex:Orang`
Triple tersebut membutuhkan hasil inferensi dari putaran pertama sebagai premis tambahan.

#### 4. Triple Asserted yang Tidak Menurunkan Apa Pun
Triple yang tidak menghasilkan inferensi bermakna adalah:
```turtle
ex:andi a ex:MahasiswaBerprestasi .
```
Alasannya, tidak terdapat informasi yang menyatakan bahwa `ex:MahasiswaBerprestasi` merupakan subclass dari `ex:Mahasiswa` atau kelas lainnya. Reasoner tidak dapat menyimpulkan hubungan kelas hanya berdasarkan kemiripan nama.

## Langkah 2 - Tebak lalu cek
| Soal | Profil | Tebakan  | Hasil mesin | Penjelasan bila meleset |
|------|--------|----------|-------------|-------------------------|
|   A  | RDFS   | 3 baris  | 3 baris     | Budi disimpulkan sebagai Orang melalui domain membimbing, Sari melalui subclass DosenTamu, dan Andi melalui range membimbing. |
|   B  | OWL RL | 10 baris | 10 baris    | Properti beradaDi bersifat transitif sehingga menghasilkan 6 hubungan tambahan dari 4 hubungan awal. |
|   C  | RDFS   | 0 baris  | 0 baris     | RDFS tidak mengenal owl:inverseOf sehingga tidak dapat menyimpulkan hubungan dibimbing dari membimbing. |
|   C  | OWL RL | 1 baris  | 1 baris     | OWL RL mengenal owl:inverseOf sehingga menghasilkan hubungan Andi dibimbing Budi. |
|   D  | OWL RL | false    | false       | Tidak yang membuktikan bahwa medan ada sebuah Tempat. |

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
- Pesan galat RDFS / OWL RL: 0 pesan galat / 2 pesan galat
- Individu yang disebut pesan: `ex:ida` (`https://contoh.github.io/web-semantik/251402095/kampus#ida`) dan `ex:web_semantik` (`https://contoh.github.io/web-semantik/251402095/kampus#web_semantik`)
- Setelah baris terbalik dihapus: RDFS tetap 0 pesan galat dan OWL RL juga langsung bersih jadi 0 pesan galat (jumlah triple otomatis turun dari 94 ke 89 di RDFS, dan dari 195 ke 186 di OWL RL karena triple turunan dari data terbalik tadi sudah hilang).
- Isi laporan_kelas.csv sebelum dan sesudah:
  - **Sebelum (masih ada triple terbalik):**
    ```csv
    kelas,asserted,rdfs,owl_rl
    Lecturer,3,4,4
    Pegawai,0,4,4
    Course,3,5,5
    ```
  - **Sesudah (baris terbalik dihapus):**
    ```csv
    kelas,asserted,rdfs,owl_rl
    Lecturer,3,3,3
    Pegawai,0,3,3
    Course,3,4,4
    ```
- Dari mana dosen menjadi mata kuliah: Di skema graf kampus kita, properti `ex:mengajar` sudah diatur punya `rdfs:domain ex:Lecturer` dan `rdfs:range ex:Course`, terus `ex:Lecturer` juga merupakan subkelas dari `ex:Pegawai`. Waktu kita sengaja masukin triple terbalik (`ex:web_semantik ex:mengajar ex:ida`), posisi `ex:ida` yang aslinya dosen malah nyasar jadi objek dari `ex:mengajar`. Gara-gara aturan `rdfs3` (range), reasoner otomatis menyimpulkan kalau `ex:ida` itu juga anggota `ex:Course`. Padahal `ex:ida` jelas-jelas `ex:Lecturer` yang lewat aturan `rdfs9` otomatis diturunkan jadi `ex:Pegawai`. Begitu OWL RL ngecek aksioma `ex:Pegawai owl:disjointWith ex:Course`, langsung ketahuan kalau `ex:ida` (dan sebaliknya `ex:web_semantik`) punya status ganda yang tabrakan, makanya muncul 2 `ErrorMessage`. Kalau profil RDFS diam aja dan ngasih 0 galat karena RDFS memang nggak ngerti kosakata OWL kayak `owl:disjointWith`, jadi dia cuma nurunin triple baru tanpa ngecek ada kontradiksi logika atau nggak.

## Refleksi
1. Mengapa graf asserted disimpan terpisah dari graf inferred, dan apa akibatnya bila keduanya dicampur seperti kampus_usu_v4.ttl?
   Graf `asserted` harus dipisah karena berkas itu adalah sumber kebenaran utama (*single source of truth*) yang isinya murni fakta dan aturan skema yang kita tulis sendiri, sedangkan `inferred` hanyalah hasil hitungan mesin yang sifatnya sementara dan bisa di-generate ulang kapan saja. Kalau keduanya dicampur dalam satu file seperti `kampus_usu_v4.ttl`, graf jadi susah dirawat saat ada pembaruan atau penghapusan data—misalnya kalau satu relasi `ex:mengajar` dihapus, triple turunannya tetap ketinggalan di dalam file karena kita nggak bisa membedakan mana triple tulisan asli dan mana triple hasil tebakan reasoner. Selain bikin ukuran file membengkak, pencampuran ini juga berbahaya kalau sempat ada data salah (seperti kasus triple terbalik di Langkah 5), karena fakta keliru yang terlanjur diturunkan bakal ikut tersimpan permanen meskipun sumber kesalahannya sudah diperbaiki.

2. Pada Langkah 2, profil RDFS tidak memberi tahu bahwa aksioma OWL diabaikan. Bagaimana Anda akan memastikan profil yang dipakai sudah tepat untuk data Anda?
   Langkah pertama yang paling aman adalah mengecek daftar kosakata dan predikat yang dipakai di dalam skema atau data `.ttl` sebelum menjalankan reasoner (bisa dicek manual di file atau lewat query `SELECT DISTINCT ?p WHERE { ?s ?p ?o }`). Kalau skema cuma memakai `rdfs:subClassOf`, `rdfs:subPropertyOf`, `rdfs:domain`, dan `rdfs:range`, profil RDFS sudah cukup; tapi begitu muncul kosakata `owl:` seperti `owl:TransitiveProperty`, `owl:inverseOf`, `owl:SymmetricProperty`, atau `owl:disjointWith`, kita wajib memakai profil `OWLRL_Semantics`. Selain itu, kita nggak boleh cuma mengandalkan "program jalan tanpa error", melainkan harus melakukan pengujian kecil (*sanity check*) dengan query uji dan mengecek `ERR.ErrorMessage` untuk memastikan aturan OWL-nya benar-benar aktif bekerja.

3. Kapan Anda memilih menulis jalur seperti wdt:P279* di query, dan kapan memilih menjalankan reasoner lalu menyimpan closure?
   Menulis jalur langsung di query seperti `wdt:P279*` dipilih saat kita mengambil data dari graf publik berskala raksasa dan dinamis seperti Wikidata, di mana kita nggak punya kendali untuk mematerialisasi seluruh datanya dan isinya terus berubah setiap saat—cara ini juga pas kalau penalarannya cuma butuh menelusuri satu jalur hierarki tertentu secara cepat. Sebaliknya, menjalankan reasoner dan menyimpan closure dipilih untuk graf lokal atau database aplikasi kita sendiri yang skemanya kompleks (melibatkan kombinasi `domain`, `range`, `inverseOf`, sifat transitif, hingga validasi `disjointWith`), terutama kalau graf tersebut bakal sering di-query berulang kali sehingga butuh respons cepat tanpa menghitung ulang inferensi di setiap permintaan.
