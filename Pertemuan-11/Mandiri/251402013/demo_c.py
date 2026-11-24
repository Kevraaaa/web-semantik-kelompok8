
# Demo C Pertemuan 11
# Graf dari endpoint DBpedia disimpan sebagai Turtle,
# lalu di-query secara lokal.

from SPARQLWrapper import SPARQLWrapper, TURTLE
from rdflib import Graph

# Membaca query Demo B
Q_NOLAN_GRAF = open("q_demo_b.rq", encoding="utf-8").read()

# Query untuk mencari film yang tidak mempunyai tanggal rilis
Q_TANPA_TANGGAL = """
PREFIX schema: <https://schema.org/>

SELECT (COUNT(?f) AS ?n)
WHERE {
    ?f a schema:Movie .
    FILTER NOT EXISTS {
        ?f schema:datePublished ?d
    }
}
"""

# User-Agent untuk identitas aplikasi
UA = "KelasWebSemantik/1.0 (mkevinramadhan013@students.usu.ac.id)"

# Menghubungkan ke DBpedia
sp = SPARQLWrapper("https://dbpedia.org/sparql", agent=UA)
sp.setQuery(Q_NOLAN_GRAF)
sp.setReturnFormat(TURTLE)

# Mengambil hasil query dan menyimpannya sebagai graf RDF
g = Graph().parse(
    data=sp.queryAndConvert(),
    format="turtle"
)

print(len(g), "triple")

# Menyimpan graf ke file Turtle
g.serialize("film_nolan.ttl", format="turtle")

# Membaca kembali file Turtle secara lokal
lokal = Graph().parse("film_nolan.ttl", format="turtle")

# Menjalankan query kedua pada graf lokal
for b in lokal.query(Q_TANPA_TANGGAL):
    print("Film tanpa tanggal:", b.n)
