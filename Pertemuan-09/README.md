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
- Jumlah baris Q1 dengan wdt:P31: [isi]
- Jumlah baris Q1 dengan wdt:P31/wdt:P279*: [isi]
- Penjelasan perbedaan: [isi]

## Langkah 5 - Endpoint dari Python
- agent=UA: [isi]
- setReturnFormat(JSON): [isi]
- b.get("kotaLabel", {}): [isi]

## Refleksi
1. Di titik mana SPARQL berbeda dari SQL?
2. Kapan Anda memakai graf lokal (rdflib) dan kapan endpoint publik?
3. Sebutkan satu hasil query yang mengejutkan dan jelaskan penyebabnya.
