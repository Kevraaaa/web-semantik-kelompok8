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
1. [jawaban]
2. [jawaban]
3. [jawaban]

## Langkah 3 - Query lokal
- IRI dasar graf: [isi]
- Jumlah triple awal / akhir: [isi] / [isi]
- Fakta yang ditambahkan: [isi]

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
