
# Demo D Pertemuan 11 - Materialisasi kelas
# Menggunakan CONSTRUCT pada graf lokal (kampus.ttl).

from rdflib import Graph

# Membaca graf RDF
g = Graph().parse("kampus.ttl", format="turtle")

# Query untuk menghitung jumlah individu foaf:Person
Q_ORANG = """
PREFIX foaf: <http://xmlns.com/foaf/0.1/>

SELECT ?x
WHERE {
    ?x a foaf:Person
}
"""

print("Sebelum:", len(g.query(Q_ORANG)))

# Membuat aturan materialisasi menggunakan CONSTRUCT
turunan = g.query("""
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

CONSTRUCT {
    ?x a ?super
}
WHERE {
    ?x a/rdfs:subClassOf* ?super
}
""")

# Menambahkan hasil inferensi ke graf
for t in turunan:
    g.add(t)

print("Sesudah:", len(g.query(Q_ORANG)))

# Menyimpan graf hasil materialisasi
g.serialize("kampus_v4.ttl", format="turtle")

# Menambahkan data dosen baru bernama Rina
RINA = """
@prefix ex: <http://contoh.id/kampus/> .

ex:rina a ex:DosenTetap .
"""

g.parse(data=RINA, format="turtle")

print("Ada Rina:", len(g.query(Q_ORANG)))
