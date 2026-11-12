
from rdflib import Graph

# Aturan materialisasi
ATURAN = """
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

CONSTRUCT {
    ?x a ?super
}
WHERE {
    ?x a/rdfs:subClassOf* ?super
}
"""

# Query untuk menghitung jumlah Person
Q_ORANG = """
PREFIX foaf: <http://xmlns.com/foaf/0.1/>

SELECT ?x
WHERE {
    ?x a foaf:Person
}
"""

# Fungsi materialisasi
def materialisasi(g):
    """Jalankan aturan pada graf dan tambahkan triple baru."""

    baru = Graph()

    for t in g.query(ATURAN):
        if t not in g:
            baru.add(t)

    for t in baru:
        g.add(t)

    return baru


# Membaca graf kampus
g = Graph().parse("kampus.ttl", format="turtle")

# Materialisasi putaran pertama
turunan = materialisasi(g)

print(
    "Putaran 1:",
    len(turunan),
    "triple baru | Person:",
    len(g.query(Q_ORANG))
)

# Menambahkan data dosen baru bernama Rina
RINA = """
@prefix ex: <http://contoh.id/kampus/> .

ex:rina a ex:DosenTetap .
"""

g.parse(data=RINA, format="turtle")

print("Rina masuk | Person:", len(g.query(Q_ORANG)))

# Menjalankan kembali aturan setelah Rina masuk
turunan += materialisasi(g)

print("Putaran 2 | Person:", len(g.query(Q_ORANG)))

# Menyimpan triple turunan secara terpisah
turunan.serialize("kampus_turunan.ttl", format="turtle")

# Menyimpan graf lengkap
g.serialize("kampus_v4.ttl", format="turtle")

print("Triple turunan disimpan terpisah:", len(turunan))
