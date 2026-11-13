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
| 1 User-Agent | Tidak relevan secara langsung, karena file ini hanya berisi query SPARQL, bukan kode yang mengirim HTTP request. | Pada baris UA = "KelasWebSemantik/1.0 (ainuha.ssuraiya07@gmail.com)" dan SPARQLWrapper(..., agent=UA). |
| 2 LIMIT | Dipenuhi oleh LIMIT 10 di akhir query. Batas ini membatasi jumlah solution, bukan jumlah triple hasil CONSTRUCT. | Tidak terlihat pada bagian kode yang diberikan. Query yang dikirim berasal dari q_demo_b.rq, jadi perlu memeriksa filenya untuk memastikan apakah menggunakan LIMIT. |
| 3 Simpan, jangan ulangi | Tidak dilakukan oleh file query ini, karena tidak ada perintah untuk menyimpan hasil ke file. | Dipenuhi oleh g.serialize("film_nolan.ttl", format="turtle"). Setelah itu, graf dibaca kembali dari file lokal melalui Graph().parse("film_nolan.ttl") dan diolah tanpa mengakses endpoint lagi. |
| 4 Jangkar jalur | Dipenuhi oleh ?kota wdt:P31/wdt:P279* wd:Q515. Jalur dimulai dari properti IRI wdt:P31 dan pencariannya diarahkan ke kelas berjangkar IRI wd:Q515. | Tidak dapat dipastikan dari demo_c.py saja, karena jalur yang digunakan terdapat di file q_demo_b.rq, bukan ditampilkan langsung di kode Python. |
- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya: 
Pada bagian ini : ?kota wdt:P31/wdt:P279* ?jenis ; (melanggar aturan 4), yang seharusnya jadi ini : ?kota wdt:P31/wdt:P279* wd:Q515 ; . Hal ini dikarenakan, ujung jalur tidak llagi dibatasi oleh IRI kelas tertentu seperti wd:Q515, tapi dengan ?jenis. Karena endpoint harus mempertimbangkan banyak kemungkinan kelas dan hasil penelusuran hierarki, akibatnya ruang pencarian jadi lebih luas, solution meningkat, dan beban pemrosesan endpoint menjadi lebih besar.

### Diskusi 1 - Taksonomi berubah
Kalau taksonomi berubah setelah hasil materialisasi demo_d.py disimpan ke graf produksi, triple hasil inferensi yang bergantung pada aturan lama dapat menjadi tidak sesuai lagi. Contohnya : jika hubungan ex:DosenTetap rdfs:subClassOf ex:Dosen dihapus, maka kesimpulan budi a ex:Dosen dan rina a ex:Dosen tidak lagi didukung oleh hierarki tersebut. Kesimpulan bahwa Budi dan Rina bertipe foaf:Person melalui jalur Dosen juga perlu diperiksa kembali. Jika hubungan ex:Dosen rdfs:subClassOf foaf:Person yang berubah, triple sari a foaf:Person juga perlu dievaluasi.

Perbandingan antara kampus_v4.ttl dari C1 dan kampus_turunan.ttl dari C2 membantu menemukan masalah ini. kampus_v4.ttl berisi graf lengkap, yaitu : data awal dan hasil inferensi, sedangkan kampus_turunan.ttl berisi enam triple hasil inferensi saja. Saya dapat memeriksa triple pada graf turunan, mencocokkannya dengan aturan taksonomi terbaru, lalu menghapus atau menghitung ulang kesimpulan yang sudah tidak valid. Setelah itu, materialisasi dijalankan kembali menggunakan data dan aturan terbaru. Dengan menyimpan hasil turunan secara terpisah, proses audit dan pembaruan menjadi lebih mudah, karena triple hasil inferensi dapat diperiksa tanpa mencampurkannya dengan data asli.

### Diskusi 2 - SELECT atau CONSTRUCT
Untuk dashboard populasi kota, saya memilih SELECT karena dashboard lebih membutuhkan data berbentuk tabel, contohnya seperti : nama kota dan jumlah penduduk, yang dapat langsung ditampilkan atau dihitung. Temuan Bagian A menunjukkan bahwa LIMIT 10 pada query CONSTRUCT menghasilkan 30 triple, sedangkan LIMIT 30 menghasilkan 90 triple. Artinya, 1 solution dapat menghasilkan beberapa triple dari template CONSTRUCT. Sementara itu, SELECT LIMIT 10 menghasilkan 10 baris untuk 10 kota berbeda. Hasil SELECT lebih mudah digunakan untuk menampilkan daftar kota dan membandingkan populasinya. Temuan populasi kembar juga mengingatkan bahwa dua kota dapat memiliki nilai populasi yang sama, tetapi tetap merupakan entitas yang berbeda sehingga tidak boleh disamakan hanya berdasarkan angka populasinya.

Untuk menggabungkan data kota dari tiga sumber, saya memilih CONSTRUCT, karena hasilnya dapat dibentuk menjadi graf RDF dengan struktur dan kosakata yang seragam. Bukti dari rusak_b.rq menunjukkan bahwa penggunaan beberapa predikat nama dapat menghasilkan 4 triple, karena Christopher Nolan memiliki dua nilai nama dengan tag bahasa berbeda, yaitu tanpa tag dan dengan tag @en. Perbaikan menggunakan SAMPLE menghasilkan 3 triple, 1 nama untuk setiap orang. Dengan CONSTRUCT, data dari berbagai sumber dapat dipetakan ke properti RDF yang konsisten. Namun, nilai bahasa dan identitas entitas tetap perlu diperhatikan agar informasi tidak hilang atau tergabung secara keliru.


### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 CONSTRUCT mencetak template sekali per solution dan menggabungkan triple kembar. | Pada LIMIT 10 menghasilkan 30 triple dan LIMIT 30 menghasilkan 90 triple. Ini menunjukkan 1 solution menghasilkan 3 triple dari template CONSTRUCT.|
|  2 Triple dengan variabel tak terikat tidak dicetak, tanpa error. | Pada rusak_a.rq menghasilkan 0 triple karena variabel pada template CONSTRUCT tidak terikat oleh pola WHERE. Querynya tetap dapat dieksekusi, tetapi triple tidak terbentuk.|
|  3 UNION, jalur , dan VALUES menyatukan predikat berbeda. | Pada (rusak_b.rq): penggunaan VALUES menentukan 3 individu yang diperiksa, sedangkan jalur foaf:name|rdfs:label|schema:name mengambil nilai dari beberapa predikat nama. Hasil awalnya adalah 4 triple, namun setelah diperbaiki menjadi 3 triple.|
|  4 Jalur + dan * menelusuri hierarki tanpa menyebut perantara. | Pada query materialisasi menggunakan a/rdfs:subClassOf* untuk menelusuri kelas induk. Contohnya : Rina yang bertipe ex:DosenTetap dapat disimpulkan sebagai ex:Dosen dan foaf:Person tanpa menuliskan setiap langkah hierarki secara terpisah.|
|  5 Materialisasi menyimpan kesimpulan, tetapi harus dihitung ulang. | Pada langkah C putaran pertama menghasilkan 4 triple baru dan 3 individu foaf:Person. Setelah Rina ditambahkan, jumlah Person tetap 3 sampai materialisasi dijalankan kembali. Pada putaran ke 2 jumlah Person menjadi 4 dan total triple turunan menjadi 6.|