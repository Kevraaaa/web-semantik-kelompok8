
from pathlib import Path
from rdflib import Graph

SUMBER = Path("../pertemuan-09/kampus_usu_v2.ttl")

g = Graph().parse(SUMBER, format="turtle")

print("Sumber:", SUMBER)
print("Jumlah triple awal:", len(g))

PREFIX = """
PREFIX ex: <https://contoh.github.io/web-semantik/251402095/kampus#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX schema: <https://schema.org/>
"""

q_peta = PREFIX + """
CONSTRUCT {
    ?d a schema:Person ;
       schema:name ?nama .
}
WHERE {
    ?d a ex:Lecturer .
    {
        ?d foaf:name ?nama .
    }
    UNION
    {
        ?d ex:nama ?nama .
    }
}
"""

peta = Graph()

for t in g.query(q_peta):
    peta.add(t)

print("\nTriple hasil pemetaan:", len(peta))

peta.serialize("dosen_schema.ttl", format="turtle")

q_orang = PREFIX + """
SELECT ?x WHERE {
    ?x a foaf:Person .
}
"""

g.update(PREFIX + """
INSERT DATA {
    ex:Lecturer rdfs:subClassOf ex:Pegawai .
    ex:Pegawai rdfs:subClassOf foaf:Person .
}
""")

sebelum = len(list(g.query(q_orang)))
print("foaf:Person sebelum:", sebelum)

turunan = g.query(PREFIX + """
CONSTRUCT {
    ?x a ?super .
}
WHERE {
    ?x a/rdfs:subClassOf+ ?super .
}
""")

baru = [t for t in turunan if t not in g]

for t in baru:
    g.add(t)

print("Triple turunan baru:", len(baru))

sesudah = len(list(g.query(q_orang)))
print("foaf:Person sesudah:", sesudah)

g.serialize("kampus_usu_v4.ttl", format="turtle")

print("\nFile dosen_schema.ttl berhasil disimpan.")
print("File kampus_usu_v4.ttl berhasil disimpan.")