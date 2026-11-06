# Pertemuan 9 - SPARQL Dasar: Tell & Ask dan Endpoint

## Langkah 1 - Tabel binding
|             Tahap              | ?dosen |   ?mk   |   ?namaMK    |
|--------------------------------|--------|---------|--------------|
| Pola 1, baris 1                |  budi  | tif2305 |       -      |
| Pola 1, baris 2                |  sari  | tif2410 |       -      |
| Pola 1, baris 3                |  sari  | tif2305 |       -      |
| Pola 2, baris 4                |    -   | tif2305 | Web Semantik |
| Pola 2, baris 5                |    -   | tif2410 | Data Mining  |
| Hasil Akhir 1 (gabung tif2305) |  budi  | tif2305 | Web Semantik |
| Hasil Akhir 2 (gabung tif2305) |  sari  | tif2305 | Web Semantik |
| Hasil Akhir 1 (gabung tif2410) |  sari  | tif2410 | Data Mining  |
- Jumlah baris hasil akhir: 3 baris
- Peran ?mk: ?mk berperan sebagai variabel penghubung (Join) antara Pola 1 dan Pola 2 walaupun digunakan tanpa kata JOIN. Ini digunakan untuk mencocokkan kode mata kuliah (?mk) dari dosen (?dosen) dengan nama mata kuliah nya (?namaMK).

## Langkah 2 - ASK dan dunia terbuka
1. Hasil: FALSE (Salah).
Alasannya, pada data Langkah 1 tidak ditemukan hubungan bahwa Budi mengajar mata kuliah TIF2410. Budi hanya mengajar TIF2305.
Menurut asumsi dunia terbuka (Open World Assumption), hasil FALSE bukan berarti Budi pasti tidak mengajar TIF2410, tetapi hanya berarti informasi tersebut belum ditemukan atau belum dinyatakan dalam graf

2. Hasil: 5 baris.
Rinciannya:
- TIF2305 menghasilkan 4 baris kombinasi (Budi–Budi, Budi–Sari, Sari–Budi, Sari–Sari).
- TIF2410 menghasilkan 1 baris (Sari–Sari).
Variabel ?a dan ?b dapat memiliki nilai yang sama karena kedua pola tidak memiliki syarat bahwa keduanya harus berbeda. Keduanya hanya perlu mengajar mata kuliah (?mk) yang sama.

3. Penyebabnya adalah perbedaan jenis literal. "Web Semantik" merupakan literal tanpa penanda bahasa, sedangkan "Web Semantik"@id merupakan literal dengan tag bahasa Indonesia (@id).
SPARQL mencocokkan literal berdasarkan nilainya beserta tag bahasanya. Karena keduanya berbeda, pola tersebut tidak menemukan kecocokan sehingga menghasilkan 0 baris.

## Langkah 3 - Query lokal
- IRI dasar graf: https://contoh.github.io/web-semantik/251402095/kampus#
- Jumlah triple awal / akhir: 26 / 28
- Fakta yang ditambahkan: Ida Adi mengajar mata kuliah Pemrograman Web.

## Langkah 4 - Wikidata
- Jumlah baris Q1 dengan wdt:P31: 20 baris (dibatasi oleh `LIMIT 20`; total data asli tanpa limit kurang lebih 30 sampai 50 baris).
- Jumlah baris Q1 dengan wdt:P31/wdt:P279*: 20 baris (kalau tetap menggunakan `LIMIT 20`; dan kalau `LIMIT 20` dihapus, hasilnya bertambah menjadi 100+ baris).
- Penjelasan perbedaan: 
  Sintaks `wdt:P31` (*instance of*) hanya mengambil objek yang secara eksplisit dicatat sebagai universitas secara langsung. Ketika diubah menjadi `wdt:P31/wdt:P279*`, tanda `/wdt:P279*` (*subclass of* secara transitif/berulang) menginstruksikan Wikidata untuk mencari objek yang merupakan universitas maupun seluruh sub-kategori turunannya (seperti Institut Teknologi, Politeknik, Sekolah Tinggi, dan Universitas Swasta/Negeri). Hal ini membuat pencarian mencakup lebih banyak jenis perguruan tinggi di Indonesia sehingga jumlah data yang ditemukan bertambah banyak.

## Langkah 5 - Endpoint dari Python
agent=UA: Digunakan untuk memberikan identitas aplikasi kepada server Wikidata agar server mengetahui program yang mengirimkan permintaan query.

setReturnFormat(JSON): Digunakan untuk menentukan format hasil query dari Wikidata menjadi JSON agar data lebih mudah dibaca dan diolah oleh Python.

b.get("kotaLabel", {}): Digunakan untuk mengambil data nama kota. Jika data kota tidak ditemukan, program akan menggunakan dictionary kosong sebagai nilai default sehingga tidak terjadi error.

## Refleksi
1.Di titik mana SPARQL berbeda dari SQL?

SPARQL digunakan untuk mengambil data dari RDF yang berbentuk triple (subject, predicate, object), sedangkan SQL digunakan untuk mengambil dan mengelola data dari database relasional yang berbentuk tabel.

2.Kapan Anda memakai graf lokal (rdflib) dan kapan endpoint publik?

Graf lokal menggunakan rdflib ketika ingin membuat, menyimpan, dan mengolah data RDF sendiri di komputer. Sedangkan endpoint publik digunakan ketika ingin mengambil data dari sumber luar seperti Wikidata tanpa harus mengunduh seluruh datanya terlebih dahulu.

3.Sebutkan satu hasil query yang mengejutkan dan jelaskan penyebabnya.

Salah satu hal yang menarik adalah hasil query hanya menampilkan 20 baris data universitas. Hal ini karena pada query terdapat perintah LIMIT 20 yang membatasi jumlah data yang ditampilkan. Jadi, bukan berarti jumlah universitas di Wikidata hanya 20.