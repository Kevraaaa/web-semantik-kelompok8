
import csv
from pathlib import Path

from SPARQLWrapper import SPARQLWrapper, TURTLE
from rdflib import Graph

# Lokasi folder program
BASE_DIR = Path(__file__).resolve().parent

# Identitas pengguna
UA = "RelasiWebSemantik/1.0 (muhammadkevin09@gmail.com)"

# Membaca query Q6
q6 = (BASE_DIR / "queries" / "q6_universitas_graf.rq").read_text(
    encoding="utf-8"
)

# Menghubungkan ke endpoint Wikidata
sp = SPARQLWrapper(
    "https://query.wikidata.org/sparql",
    agent=UA
)

sp.setQuery(q6)
sp.setReturnFormat(TURTLE)
sp.setOnlyConneg(True)

# Mengirim query satu kali dan menyimpan hasilnya
try:
    data = sp.queryAndConvert()

    g = Graph()
    g.parse(data=data, format="turtle")

    print("Triple dari Wikidata:", len(g))

    # Menyimpan graf ke file Turtle
    g.serialize(
        destination=str(BASE_DIR / "universitas.ttl"),
        format="turtle"
    )

except Exception as e:
    print("Gagal mengambil data dari Wikidata:")
    print(str(e)[:200])
    raise

# Query lokal pada graf yang sudah disimpan
# Tidak mengirim permintaan ke Wikidata lagi
g_lokal = Graph()
g_lokal.parse(
    str(BASE_DIR / "universitas.ttl"),
    format="turtle"
)
q_kota = """
PREFIX ex: <https://contoh.github.io/web-semantik/251402095/kampus#>

SELECT ?kota (COUNT(?u) AS ?jumlah)
WHERE {
    ?u a ex:Universitas .
    ?u ex:lokasi ?kota .
}
GROUP BY ?kota
"""

# Menyimpan hasil perhitungan ke CSV
with open(BASE_DIR / "universitas_per_kota.csv", "w",
          newline="", encoding="utf-8") as f:
    w = csv.writer(f)
    w.writerow(["kota", "jumlah_universitas"])

    for b in g_lokal.query(q_kota):
        w.writerow([str(b.kota), int(b.jumlah)])

print("CSV tersimpan: universitas_per_kota.csv")