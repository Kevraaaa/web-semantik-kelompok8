
# Program untuk menjalankan query SPARQL pada file Turtle
# Pemakaian:
# python jalankan.py DATA.ttl QUERY.rq

import sys
from rdflib import Graph

# Memeriksa argumen terminal
if len(sys.argv) != 3:
    print("Cara penggunaan: python jalankan.py DATA.ttl QUERY.rq")
    sys.exit(1)

# Membaca nama file dari terminal
data, berkas_rq = sys.argv[1], sys.argv[2]

# Membaca graf Turtle
g = Graph().parse(data, format="turtle")

# Menambahkan prefix
g.bind("ex", "http://contoh.id/")
g.bind("schema", "https://schema.org/")

PREFIX = """
PREFIX dbo: <http://dbpedia.org/ontology/>
PREFIX dbr: <http://dbpedia.org/resource/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX schema: <https://schema.org/>
PREFIX ex: <http://contoh.id/>
"""

# Menjalankan query
query = PREFIX + open(berkas_rq, encoding="utf-8").read()
hasil = g.query(query)

# Mengambil namespace
nm = g.namespace_manager

# Menampilkan hasil
if hasil.type == "CONSTRUCT":
    print(len(hasil.graph), "triple")

    for s, p, o in sorted(hasil.graph):
        print(
            " ",
            nm.normalizeUri(s),
            nm.normalizeUri(p),
            nm.normalizeUri(o),
            "."
        )
else:
    baris = list(hasil)
    print(len(baris), "baris")

    for b in baris:
        print(
            " ",
            " | ".join(
                v.n3(nm) if v is not None else "-"
                for v in b
            )
        )
