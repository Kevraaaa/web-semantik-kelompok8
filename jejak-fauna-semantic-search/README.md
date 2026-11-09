# 👥 Anggota Kelompok dan Pembagian Tugas

Berikut adalah daftar anggota kelompok beserta fokus bagian yang dikerjakan:

| Nama Anggota | NIM | Bagian / Fokus Tugas |
| :--- | :---: | :--- |
| **Hadziq Naufal Sinaga** | 251402095 | Mengisi data hewan langka ke dalam format semantik. |
| **Aulia Syahfira** | 251402104 | Fitur pelengkap pencarian dan testing proyek. |
| **Ainuha Suraiya** | 251402122 | Bagian Progres & Pelaporan |
| **Aliyah Briek Salem** | 251402010 | Jembatan antara data semantik dan antarmuka web. |
| **Muhammad Kevin Ramadhan** | 251402013 | Membangun "otak" pengetahuannya (biasanya pakai software Protégé). |

---

# 📝 Deskripsi Tugas

### 1. Perancang Ontologi (Ontology Engineer)
Merancang struktur kelas (Class) seperti Fauna, Habitat, StatusKonservasi, Wilayah. Menentukan Object Property (misal: mempunyaiHabitat, berstatusKonservasi) dan Data Property (nama latin, populasi sisa). Dia yang memastikan mesin pencari nanti bisa paham konteks hubungan antar data.

### 2. Pengumpul & Pengolah Data RDF (Data Populator / Harvester)
Mencari sumber data fauna langka (misal dari Wikipedia, DBpedia, atau IUCN Red List), lalu mengubah/memasukkan data mentah tersebut ke dalam format Turtle (.ttl) atau RDF. Kalau Orang 1 bikin kerangka lemarinya, Orang 2 yang masuk-masukin bajunya ke lemari supaya siap dibaca oleh sistem.

### 3. Spesialis SPARQL & Backend Engine (Integrator)
Setup server triplestore (seperti Apache Jena Fuseki atau GraphDB). Menulis query SPARQL untuk mengeksekusi pencarian (misal: query untuk mencari hewan yang statusnya "Critically Endangered" di "Sumatera"). Mengatur backend (bisa pakai PHP dengan library EasyRdf, atau Python/Node.js) agar web bisa ngobrol dengan server SPARQL.

### 4. Frontend & Desain UI/UX (Search Interface)
Membuat landing page, mendesain kolom search engine (mesin pencari) utama, dan merancang layout hasil pencarian. Mengatur agar data dari backend (yang awalnya berupa raw JSON/XML dari SPARQL) tampil rapi dan responsif dalam bentuk card, tabel, atau galeri gambar fauna.

### 5. Fitur Pencarian Lanjut & Visualisasi (Advanced Semantic Features)
Membuat fitur filter cerdas di halaman pencarian (misal: user bisa nge-filter berdasarkan famili hewan atau pulau habitat). Kalau memungkinkan, dia juga bertugas membuat visualisasi graf (nampilin node/network hubungan antara fauna, makanan, dan habitatnya biar ciri khas semantiknya makin kelihatan), serta testing integrasi dari UI sampai query SPARQL sebelum didemokan.