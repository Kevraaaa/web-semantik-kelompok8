# Pertemuan 5 - Ontology dan Arsitektur Web Semantik

## Ontology mini kampus
- IRI dasar: https://contoh.github.io/web-semantik/251402095/kampus
- Domain: Kampus

## Komponen ontology
| Komponen | Isi yang dibuat |
| --- | --- |
| Class | LudoGame, Board, Dice, Piece, Player |
| Subclass | Pawn, Blue, Green, Yellow, Red, ComputerPlayer, HumanPlayer |
| Object property | hasColor, hasPiece, hasPlayer, usesDice |
| Datatype property | diceValue, pieceNumber, playerName |
| Individual | blueColor1, redColor1, yellowColor1, greenColor1, bluePawn1, greenPawn1, yellowPawn1, redPawn1, LudoGame1, playerAinuha, playerAulia, playerAliyah, playerHadziq |
| Axiom/disjointness | HumanPlayer disjointWith ComputerPlayer |

## Layer Cake
Jelaskan posisi ontology dalam Semantic Web Layer Cake: Ontology berada di atas RDF/RDFS karena ontology seperti OWL menggunakan dasar RDF/RDFS untuk memberikan makna yang lebih detail dan hubungan yang lebih kompleks pada data. Dengan ontology, kita dapat mendefinisikan class, individual, property, serta aturan atau batasan dalam suatu domain.
Ontology berada sebelum SPARQL karena data dan hubungan yang dimodelkan menggunakan ontology dapat menjadi dasar untuk melakukan pencarian atau query menggunakan SPARQL. Jadi, secara sederhana, RDF/RDFS digunakan untuk membentuk struktur dasar data, ontology memperkaya makna dan hubungan data, sedangkan SPARQL digunakan untuk mengambil informasi dari data tersebut.

## Perbandingan serialisasi
- Turtle:
  - Menggunakan `@prefix` sehingga IRI dapat ditulis lebih singkat.
  - Menggunakan `;` dan `.` untuk menyusun triple.

- RDF/XML:
  - Menggunakan tag XML seperti `<rdf:RDF>` dan `<owl:Class>`.
  - Penulisan IRI menggunakan atribut atau elemen XML sehingga lebih panjang.

- Kesamaan makna:
  Kedua file merepresentasikan ontology yang sama, sehingga class, property, individual, dan IRI dasar tetap memiliki makna yang sama.

## Refleksi

1. Apa perbedaan ontology dan taksonomi?
Ontology menjelaskan konsep dalam suatu domain beserta hubungan dan aturan antar konsep, sedangkan taksonomi lebih fokus pada pengelompokan konsep secara bertingkat berdasarkan kategori atau hierarki.

2. Mengapa domain pada OWL bukan constraint database?
Karena domain pada OWL digunakan untuk menyimpulkan kelas dari suatu resource berdasarkan property yang digunakan, bukan untuk membatasi data agar hanya boleh memiliki tipe tertentu seperti constraint pada database.

3. Mengapa kosakata yang sudah ada sebaiknya dipakai kembali sebelum membuat yang baru?
Karena menggunakan kosakata yang sudah ada membuat data lebih mudah dipahami, terhubung, dan digunakan bersama oleh sistem lain. Selain itu, kita tidak perlu membuat istilah baru jika istilah yang sesuai sudah tersedia.