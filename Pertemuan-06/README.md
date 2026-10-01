# Pertemuan 6 - RDF Dasar

## Membaca dan Mengubah Triple RDF
| Kalimat                                       | Subject         | Predicate   | Object          |
| Ida adi adalah dosen.                        | ex:ida          | rdf:type    | ex:Lecturer     |
| Ida adi mengajar Web Semantik.               | ex:ida          | ex:mengajar | ex:web_semantik |
| Mata Kuliah itu memiliki nama "Web Semantik". | ex:web_semantik | foaf:name   | "Web Semantik"  |

## IRI, Literal, Blank Node, dan Prefix
### 1. Identifikasi jenis node untuk ex:ida, "Ida Adi"@id, dan [ ex:kota "Medan" ].
ex:ida merupakan IRI, "Ida Adi"@id merupakan literal, sedangkan [ ex:kota "Medan" ] merupakan blank node.
### 2. Mengapa literal tidak boleh menjadi subject RDF?
Literal tidak boleh menjadi subject karena literal hanya digunakan untuk menyimpan nilai/data, bukan sebagai identitas suatu resource.
### 3. Buat IRI dasar untuk graf Anda dengan pola HTTP, misalnya https://contoh.github.io/web-semantik/ISI_NIM/kampus#.
Contoh IRI dasar: https://contoh.github.io/web-semantik/251402104/kampus#
### 4. Tuliskan kepanjangan namespace rdf, rdfs, xsd, dan foaf.
rdf = Resource Description Framework, rdfs = RDF Schema, xsd = XML Schema Definition, dan foaf = Friend of a Friend.

## IRI dasar graf
https://contoh.github.io/web-semantik/251402095/kampus#

## Ringkasan graf
- Jumlah triple: 20
- Namespace yang digunakan: ex, foaf, rdf, xsd
- Entitas: 3 Dosen (ex:ida, ex:budi, ex:siti), 3 Mata Kuliah (ex:web_semantik, ex:pbo, ex:basis_data), 2 Mahasiswa (ex:alyh, ex:hadziq)

## Contoh triple
1. ex:ida - rdf:type - ex:Lecturer
2. ex:ida - ex:mengajar - ex:web_semantik
3. ex:web_semantik - ex:jumlahKredit - "3"^^xsd:integer

## Perbandingan serialisasi
- Turtle: Formatnya berbasis teks ringkas, mudah dibaca manusia, dan mengelompokkan predikat-objek untuk subjek yang sama dengan titik koma (;).
- JSON-LD: Formatnya berupa struktur objek JSON berbasis kunci-nilai (@id, @type), sehingga mudah diintegrasikan dengan aplikasi web atau JavaScript.
- Pernyataan yang sama: Untuk entitas ex:ida, Turtle menampilkannya sebagai blok triples berantai, sedangkan JSON-LD menyajikannya sebagai objek JSON tersendiri di dalam array graf.

## Refleksi

1. Kapan object harus berupa IRI dan kapan berupa literal?
   Object berupa IRI jika menunjuk ke entitas lain dalam graf, sedangkan literal digunakan untuk menyimpan nilai seperti nama, angka, atau teks.

2. Mengapa prefix membantu keterbacaan tanpa mengubah IRI?
   Prefix membuat IRI yang panjang menjadi lebih singkat dan mudah dibaca, tetapi tetap mengacu pada IRI yang sama.

3. Sebutkan satu kesalahan pemodelan yang Anda hindari pada graf ini.
   Saya menghindari penggunaan literal untuk entitas, misalnya pada relasi mengajar, object dibuat sebagai IRI mata kuliah (EX.web_semantik), bukan teks "Web Semantik".

