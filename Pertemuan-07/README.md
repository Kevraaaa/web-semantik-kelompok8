# Pertemuan 7 - Serialisasi RDF


| Format | Kekuatan utama | Skenario tepat |
|---------|---------|---------|
| Turtle | Ringkas dan mudah dibaca manusia | Saat membuat, mengedit, atau mempelajari graf RDF secara manual karena sintaksnya sederhana dan mudah dipahami. |
| JSON-LD | Cocok web/API dan HTML | Saat data RDF digunakan pada aplikasi web, REST API, atau disisipkan ke dalam halaman HTML untuk SEO dan pertukaran data. |
| RDF/XML | Kompatibilitas data lama | Saat berintegrasi dengan sistem lama yang masih menggunakan XML sebagai format utama pertukaran data. |
| N-Triples | Satu triple per baris; stabil untuk diff | Saat melakukan debugging, validasi, atau melacak perubahan data menggunakan Git. |
| N-Quads | Menambahkan konteks graf | Saat mengelola beberapa named graph atau sumber data dalam satu dataset RDF. |

### Artefak

* **Graf asal:** 26 triple pada graf kampus setelah penggabungan.
* **Format ekspor:** Turtle (`.ttl`), TriG (`.trig`), JSON-LD, dan N-Triples.
* **File yang dibuat:**

  * `fakultas.ttl`
  * `kampus_tergabung.trig`
* **Named Graph:**

  1. Kampus: `https://contoh.github.io/graph/kampus`
  2. Fakultas: `https://contoh.github.io/graph/fakultas`
  3. Reifikasi: `https://contoh.github.io/graph/reifikasi`
* **Hasil validasi:** 36 triple dari 3 named graph.

### Reifikasi dan Provenance

* **Triple yang dianotasi:** `ex:ida ex:mengajar ex:web_semantik`
* **Creator:** Mahasiswa penyusun tugas.
* **Date:** 9 Oktober 2026.
* **Source:** `kampus_usu.ttl` dan `fakultas.ttl`.

## Perbandingan
- Format paling mudah dibaca manusia: Turtle (.ttl), karena memiliki sintaks yang ringkas, mendukung penulisan prefix untuk menghemat IRI, serta struktur penulisan predikat dan objek yang menyerupai bahasa alami.
- Format untuk HTML/API: JSON-LD, karena berbasis struktur JSON standar yang mudah dipahami dan diolah oleh skrip JavaScript web/API, serta bisa langsung disisipkan ke dalam tag `<script type="application/ld+json">` pada HTML untuk SEO.
- Perbedaan reifikasi klasik dan RDF-star: Reifikasi klasik lebih verbose (panjang/berbelit-belit) karena membutuhkan 1 node `rdf:Statement` dan 4 triple tambahan (`rdf:type`, `rdf:subject`, `rdf:predicate`, `rdf:object`) hanya untuk merekam provenance 1 triple. Sedangkan RDF-star lebih efisien dan ringkas karena mengizinkan sebuah triple dibungkus langsung sebagai subjek/objek dengan sintaks `<< subject predicate object >>` (seperti `<< ex:ida ex:mengajar ex:web_semantik >> dct:creator ex:ida .`) tanpa perlu memecahnya menjadi banyak triple terpisah.

## Refleksi
1. Mengapa named graph berguna saat menggabungkan data dari sumber berbeda?

   Named graph berguna untuk membedakan data berdasarkan sumbernya, sehingga data lebih mudah dikelola, dibandingkan, dan diketahui asalnya saat digabungkan.

2. Mengapa provenance penting untuk sebuah triple?

   Provenance penting untuk mengetahui asal-usul sebuah triple, seperti siapa yang membuatnya dan dari sumber mana data tersebut diperoleh. Dengan begitu, kebenaran dan kepercayaan terhadap data bisa diperiksa.

3. Format apa yang Anda pilih untuk git diff, dan mengapa?

   Saya memilih format Turtle (.ttl) karena sintaksnya sederhana, mudah dibaca, dan perubahan pada triple lebih mudah terlihat saat menggunakan git diff.
