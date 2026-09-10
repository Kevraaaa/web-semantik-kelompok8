# Latihan Pertemuan 3 - JSON-LD dan Structured Data

## Identitas
- Nama: ISI_NAMA
- NIM: ISI_NIM

## Struktur Hasil
- `profil_saya.jsonld`
- `profil_perbaikan.jsonld`
- `seminar.html`
- folder `screenshots`

## 1. JSON Biasa dan JSON-LD
1. Perbedaan fungsi kunci: ...
2. Fungsi `@context`, `@type`, dan `@id`: ...
3. Node tanpa `@id`: ...

## 2. Pemeriksaan schema.org
1. Alasan memilih tipe paling spesifik: Karena tipe yang lebih spesifik membuat makna data lebih jelas dan membuat mesin memahami informasi dengan lebih akurat. Selain itu, kita dianjurkan memilih tipe yang paling spesifik yang masih sesuai dengan entitas yang dimodelkan.
2. Nama properti dan bahasa nilai: Nama properti harus mengikuti schema.org karena schema.org merupakan kosakata atau kamus bersama yang digunakan oleh berbagai mesin pencari dan aplikasi untuk memahami makna data. Namun, nilai dari properti boleh menggunakan bahasa apa pun, termasuk Bahasa Indonesia.
3. Manfaat array pada `knowsAbout`: Array pada knowsAbout digunakan ketika seseorang memiliki lebih dari satu bidang pengetahuan atau keahlian.

## 3. Perbaikan Lima Kesalahan
| No. | Bagian Salah | Alasan | Perbaikan |
|---|---|---|---|
| 1 | ... | ... | ... |
| 2 | ... | ... | ... |
| 3 | ... | ... | ... |
| 4 | ... | ... | ... |
| 5 | ... | ... | ... |

## 4. Triple dari JSON-LD Playground
Tuliskan satu baris N-Quads yang terbentuk:

```text
ISI_TRIPLE
```

## 5. Hasil Validasi
- Schema Markup Validator: ...
- Rich Results Test: ...
- JSON-LD Playground: ...

## 6. Refleksi
1. Mengapa `@context` disebut jembatan menuju makna?
2. Apa perbedaan fungsi Schema Markup Validator dan Rich Results Test?
3. Mengapa isi JSON-LD harus sama dengan konten yang terlihat pada halaman?

## Bukti
![Schema Markup Validator](screenshots/profil-schema-validator.png)
![JSON-LD Playground](screenshots/profil-playground.png)
![Rich Results Test](screenshots/seminar-rich-results.png)