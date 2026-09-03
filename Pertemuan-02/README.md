# Pertemuan 2 - Format Dokumen XML

## 1. Profil XML
XML yang dibuat berisi salah satu data profil mahasiswa TI, yang berisikan NIM, nama, angkatan, program studi, hobi, dan deskripsi diri. Struktur XML tersebut sudah well-formed karena setiap elemen memiliki tag pembuka dan penutup yang sesuai serta dapat ditampilkan dengan baik di browser tanpa adanya pesan error.


## 2. Analisis Kesalahan XML

| No | Bagian yang Salah | Alasan | Perbaikan |
|---|---|---|---|
| 1 | `<nama>Budi Santoso</Nama>` | Tag pembuka `<nama>` beda kapitalisasi dengan tag penutup `</Nama>`. XML bersifat *case-sensitive*. | `<nama>Budi Santoso</nama>` |
| 2 | `<angkatan>2024` | Tag `<angkatan>` tidak memiliki tag penutup, sehingga dokumen XML tidak *well-formed*. | `<angkatan>2024</angkatan>` |
| 3 | `<hobi>Programming</hobi>`<br>`<hobi>Membaca</hobi>` | Elemen hobi ganda langsung berada di bawah root tanpa adanya parent pembungkus. | `<hobi>`<br>`<hobi>Programming</hobi>`<br>`<hobi>Membaca</hobi>`<br>`</hobi>` |

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
jawab: alasannya karena kedua title itu ada yg tunjuk seperti buku:title maka yg ditunjuk title itu ada lah buku dan begitu juga dengan web:title. tapi kalau `<title>` gkda menunjuk sesuatu maka elemen title itu dianggap sama

2. Fungsi prefix buku: dan web:?
jawab: gunanya agar elemen `<title>` dapat dibedakan antara menunjuk ke buku ataupun web. jika tidak ada prefix buku: ataupun web: nanti elemen title akan dianggap sama.

3. Apa fungsi atribut xmlns?
jawab: fungsinya supaya bisa meletakkan URI. jadi nanti komputer paham bahwa ketika ada jumpa data buku itu asalnya dari link "https://example.org/buku".

4. Apakah URI namespace harus dapat dibuka sebagai halaman web? Jelaskan.
jawab: tidak, karena URI itu hanya pembeda antar elemen. jadi kalau user ada membuat beberapa data salah satunya xmlns:buku="https://example.org/buku" maka komputer akan tau bahwa elemen buku tersebut menunjuk ke URI itu. 

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