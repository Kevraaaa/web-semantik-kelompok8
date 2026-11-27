# Apache Jena Fuseki — Jejak Fauna

Status saat ini: **Fuseki 6.3.0 dan integrasi Laravel berhasil diuji lokal**.

Fuseki diunduh dari sumber resmi Apache, checksum SHA-512 diverifikasi, dan diekstrak portable. TDB2 berhasil dibuat dan diisi dengan dataset asli. Laravel berhasil melakukan query ke endpoint Fuseki; fallback parser lokal tetap tersedia melalui feature flag.

## Tujuan

Fuseki akan menjadi server SPARQL lokal untuk dataset `jejak-fauna`, dengan TDB2 sebagai penyimpanan persisten. Dataset sumber tetap:

```text
dataset/fauna_kalimantan.ttl
```

Jangan mengubah atau mengganti file Turtle tersebut. Database TDB2 adalah runtime data dan tidak boleh disimpan ke Git.

## Prasyarat Windows

Pilih salah satu:

1. Docker Desktop dengan Docker Compose; atau
2. Apache Jena Fuseki standalone dan Java.

Java sudah terdeteksi pada komputer ini. Docker Desktop dan Fuseki perlu dipasang manual oleh pengguna bila ingin melanjutkan. Jangan memasang software global tanpa persetujuan dan jangan membuka Fuseki ke jaringan publik.

## Opsi yang direkomendasikan: Fuseki standalone + TDB2

Distribusi yang dipakai pada audit ini adalah Apache Jena Fuseki 6.3.0, diekstrak ke:

```text
C:\_Ainuha\_Ainuha IT\_Semester03\Web_Semantik\jejak-fauna-fuseki-6.3.0\apache-jena-fuseki-6.3.0
```

Gunakan direktori runtime terpisah dari source Laravel, misalnya:

```text
C:\_Ainuha\_Ainuha IT\_Semester03\Web_Semantik\jejak-fauna-fuseki-runtime
C:\_Ainuha\_Ainuha IT\_Semester03\Web_Semantik\jejak-fauna-fuseki-runtime\databases\jejak-fauna
```

Dari PowerShell, setelah Fuseki tersedia:

```powershell
$fusekiHome = 'C:\_Ainuha\_Ainuha IT\_Semester03\Web_Semantik\jejak-fauna-fuseki-6.3.0\apache-jena-fuseki-6.3.0'
$java = 'C:\Program Files\Java\jdk-26.0.2.1\bin\java.exe'
$runtime = 'C:\_Ainuha\_Ainuha IT\_Semester03\Web_Semantik\jejak-fauna-fuseki-runtime'
$database = Join-Path $runtime 'databases\jejak-fauna'

New-Item -ItemType Directory -Force -Path $database | Out-Null

# Jalankan server read-only pada localhost menggunakan database TDB2 yang sudah dibuat.
$locArg = '--loc="' + $database + '"'
& $java -cp (Join-Path $fusekiHome 'fuseki-server.jar') `
  org.apache.jena.fuseki.main.cmds.FusekiMainCmd `
  --localhost $locArg /jejak-fauna
```

Loading awal dilakukan melalui endpoint Graph Store lokal sementara dengan `--update`, kemudian server dihentikan dan dijalankan ulang tanpa `--update`. Jangan mengaktifkan `--update` pada server yang dapat diakses publik.

Dengan server berjalan lokal, URL yang diharapkan:

- Fuseki UI: `http://localhost:3030/`
- Dataset: `http://localhost:3030/jejak-fauna`
- Query endpoint yang akan dipakai Laravel: `http://localhost:3030/jejak-fauna/query`

Fuseki juga mendokumentasikan endpoint query pada path dataset; pastikan path final terlihat pada UI/halaman dataset versi yang digunakan sebelum mengisi `.env`.

## Verifikasi SPARQL

Jalankan query berikut pada UI Fuseki atau endpoint query setelah server aktif:

```sparql
PREFIX jf: <https://jejakfauna.org/ontology#>

SELECT (COUNT(*) AS ?triples)
WHERE { ?s ?p ?o }
```

```sparql
PREFIX jf: <https://jejakfauna.org/ontology#>

SELECT (COUNT(?fauna) AS ?faunaCount)
WHERE { ?fauna a jf:Fauna }
```

```sparql
PREFIX jf: <https://jejakfauna.org/ontology#>

SELECT ?fauna ?nama
WHERE {
  ?fauna a jf:Fauna ;
         jf:namaLokal ?nama .
}
LIMIT 5
```

Catatan: jumlah triple bukan jumlah fauna. Jumlah fauna harus dihitung berdasarkan struktur resource aktual (`?fauna a jf:Fauna`), lalu dibandingkan dengan hasil audit dataset lokal.

## Konfigurasi Laravel setelah Fuseki tersedia

Jangan menambahkan nilai rahasia ke Git. Konfigurasi yang direncanakan:

```dotenv
FUSEKI_URL=http://localhost:3030
FUSEKI_DATASET=jejak-fauna
FUSEKI_QUERY_ENDPOINT=/query
```

`SparqlService` sekarang membaca konfigurasi ini. `FUSEKI_ENABLED=true` mengaktifkan query HTTP read-only; `false` memakai fallback parser lokal. Jika Fuseki aktif tetapi gagal dihubungi, API mengembalikan HTTP `503` dan tidak diam-diam berpindah ke parser lokal. Endpoint update/upload tidak dipakai oleh API publik.

## Stop dan restart

Untuk menghentikan standalone Fuseki, fokuskan jendela PowerShell yang menjalankan `fuseki-server.bat`, lalu tekan `Ctrl+C`. TDB2 tetap berada di direktori `$database` dan dapat dipakai kembali saat server dinyalakan.

Jangan menghapus direktori database untuk sekadar restart. Buat backup sebelum reload dataset atau migrasi versi Fuseki.

## Troubleshooting

- Port 3030 sibuk: periksa `Get-NetTCPConnection -LocalPort 3030`; jangan menghentikan proses yang tidak dikenal.
- Dataset kosong: pastikan loader memakai file `dataset/fauna_kalimantan.ttl` yang sama dan query diarahkan ke dataset `jejak-fauna`.
- Loader gagal: jalankan validasi RIOT dan periksa versi Java/Fuseki.
- Database terkunci: pastikan tidak ada proses Fuseki/Jena lain yang sedang memakai direktori TDB2.
- Laravel mendapat timeout: pastikan Fuseki aktif, URL dataset benar, dan endpoint query sesuai UI versi Fuseki.

## Keamanan lokal

Fuseki hanya untuk development lokal pada `localhost`. Jangan mengekspos port 3030 ke LAN/internet tanpa autentikasi, firewall, dan desain deployment yang disetujui. API Laravel tidak boleh meneruskan SPARQL mentah dari pengguna dan tidak boleh memberi akses update/upload.

Referensi resmi:

- [Fuseki quick start](https://jena.apache.org/documentation/fuseki2/fuseki-quick-start.html)
- [Running Fuseki](https://jena.apache.org/documentation/fuseki2/fuseki-server.html)
- [Fuseki configuration](https://jena.apache.org/documentation/fuseki2/fuseki-configuration.html)
- [TDB2 with Fuseki](https://jena.apache.org/documentation/tdb2/tdb2_fuseki.html)
- [TDB2 command-line tools](https://jena.apache.org/documentation/tdb2/tdb2_cmds.html)
