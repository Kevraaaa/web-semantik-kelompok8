# Pertemuan 2 - Format Dokumen XML

## 1. Profil XML
Jelaskan secara singkat struktur XML yang Anda buat.

## 2. Analisis Kesalahan XML

| No | Bagian yang Salah | Alasan | Perbaikan |
|---|---|---|---|
| 1 | ... | ... | ... |
| 2 | ... | ... | ... |
| 3 | ... | ... | ... |

## 3. Analisis XML Schema
1. Root element: buku
2. Tipe data judul: xs:string
3. Tipe data tahun: xs:gYear
4. Tipe data harga: xs:decimal
5. Apakah atribut isbn boleh tidak dituliskan? Jelaskan
= Tidak boleh, karena atribut isbn menggunakan use="required".
jadi setiap elemen itu wajib punya atribut isbn, kalo tidak ditulis dokumen XML tidak sesuai sama aturan XSD.

## 4. Analisis Namespace
1. Mengapa kedua elemen title tidak sama? 
= Karena kedua elemen tersebut berada pada namespace yang berbeda. Elemen buku:title menggunakan namespace https://example.org/buku, sedangkan web:title menggunakan namespace https://example.org/web. Jadi, walaupun sama-sama bernama `title`, keduanya dianggap sebagai elemen yang berbeda.
2. Fungsi prefix: 
=  Prefix digunakan sebagai penanda atau singkatan untuk namespace. refix `Pb
3. Fungsi xmlns: 
= Atribut xmlns digunakan untuk mendeklarasikan namespace dan menghubungkan prefix dengan URI namespace.
4. Apakah URI namespace harus dapat dibuka? 
= Gak harus, URI namespace hanya digunakan sebagai identitas unik untuk membedakan suatu namespace dengan namespace lainnya. Jadi, URI tersebut tidak wajib bisa dibuka melalui browser.

## 5. Pertanyaan Evaluasi
1. Perbedaan XML dan HTML: ...
2. Apa yang dimaksud well-formed? ...
3. Perbedaan well-formed dan valid: ...
4. Mengapa XSD lebih kuat dibanding DTD? ...
5. Mengapa namespace penting? ...
6. Apa kegunaan XPath? ...