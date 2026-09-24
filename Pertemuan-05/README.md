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
Jelaskan posisi ontology dalam Semantic Web Layer Cake: [isi jawaban]

## Perbandingan serialisasi
- Turtle: [dua pengamatan sintaks]
- RDF/XML: [dua pengamatan sintaks]
- Kesamaan makna: [isi]

## Refleksi
1. Apa perbedaan ontology dan taksonomi?
2. Mengapa domain pada OWL bukan constraint database?
3. Mengapa kosakata yang sudah ada sebaiknya dipakai kembali sebelum membuat yang baru?