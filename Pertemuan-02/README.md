# Pertemuan 2 - Format Dokumen XML

## 1. Profil XML
Jelaskan secara singkat struktur XML yang Anda buat.

## 2. Analisis Kesalahan XML

| No | Bagian yang Salah | Alasan | Perbaikan |
|---|---|---|---|
| 1 | `<nama>Budi Santoso</Nama>` | Tag pembuka `<nama>` menggunakan huruf kecil, sedangkan tag penutup `</Nama>` menggunakan huruf kapital di awal. XML bersifat case-sensitive sehingga nama tag harus sama persis. | `<nama>Budi Santoso</nama>` |
| 2 | `<angkatan>2024` | Tag `<angkatan>` tidak memiliki tag penutup, sehingga dokumen XML tidak *well-formed*. | `<angkatan>2024</angkatan>` |
| 3 | `<hobi>Programming</hobi>`<br>`<hobi>Membaca</hobi>` | Elemen hobi ganda langsung berada di bawah root tanpa adanya parent pembungkus untuk mengelompokkan daftar hobi tersebut. | `<hobi>`<br>`<hobi>Programming</hobi>`<br>`<hobi>Membaca</hobi>`<br>`</hobi>` |

## 3. Analisis XML Schema
1. Root element: ...
2. Tipe data judul: ...
3. Tipe data tahun: ...
4. Tipe data harga: ...
5. Atribut ISBN: ...

## 4. Analisis Namespace
1. Mengapa kedua elemen title tidak sama? ...
2. Fungsi prefix: ...
3. Fungsi xmlns: ...
4. Apakah URI namespace harus dapat dibuka? ...

## 5. Pertanyaan Evaluasi

1. Perbedaan XML dan HTML
XML digunakan untuk menyimpan dan mengatur data, sedangkan HTML digunakan untuk menampilkan informasi pada halaman web. XML tidak memiliki tag bawaan, sementara HTML memiliki tag yang sudah ditentukan seperti `<p>`, `<h1>`, dan `<table>`.
2. Apa yang dimaksud well-formed?
Well-formed adalah dokumen XML yang mengikuti aturan dasar XML, seperti memiliki satu root element, semua tag ditutup dengan benar, dan penulisan tag pembuka serta penutup harus sesuai.
3. Perbedaan well-formed dan valid
Well-formed berarti XML sudah mengikuti aturan dasar penulisan XML. Valid berarti XML tidak hanya well-formed, tetapi juga sesuai dengan aturan yang ditentukan oleh DTD atau XML Schema (XSD).
4. Mengapa XSD lebih kuat dibanding DTD?
XSD lebih kuat karena dapat menentukan tipe data secara rinci, seperti string, integer, date, dan lainnya. Selain itu, XSD ditulis menggunakan sintaks XML sehingga lebih fleksibel dan mudah dikembangkan dibandingkan DTD.
5. Mengapa namespace penting?
Namespace digunakan untuk menghindari konflik nama elemen ketika menggabungkan XML dari sumber yang berbeda. Dengan namespace, elemen yang memiliki nama sama dapat dibedakan berdasarkan prefix atau URI-nya.
6. Apa kegunaan XPath?
XPath digunakan untuk mencari, memilih, dan menavigasi elemen atau atribut tertentu dalam dokumen XML. XPath memudahkan pengambilan data dari XML berdasarkan lokasi atau kondisi tertentu.
