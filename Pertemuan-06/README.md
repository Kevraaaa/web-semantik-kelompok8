# Pertemuan 6 - RDF Dasar

## Membaca dan Mengubah Triple RDF
| Kalimat                                       | Subject         | Predicate   | Object          |
| Ida Dadi adalah dosen.                        | ex:ida          | rdf:type    | ex:Lecturer     |
| Ida Dadi mengajar Web Semantik.               | ex:ida          | ex:mengajar | ex:web_semantik |
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
[isi IRI dasar]

## Ringkasan graf
- Jumlah triple: [isi]
- Namespace yang digunakan: [isi]
- Entitas: [isi]

## Contoh triple
1. [subject] - [predicate] - [object]
2. [subject] - [predicate] - [object]
3. [subject] - [predicate] - [object]

## Perbandingan serialisasi
- Turtle: [pengamatan]
- JSON-LD: [pengamatan]
- Pernyataan yang sama: [isi]

## Refleksi
1. Kapan object harus berupa IRI dan kapan berupa literal?
2. Mengapa prefix membantu keterbacaan tanpa mengubah IRI?
3. Sebutkan satu kesalahan pemodelan yang Anda hindari pada graf ini.