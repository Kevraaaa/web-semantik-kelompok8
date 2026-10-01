from rdflib import Graph, Namespace, Literal
from rdflib.namespace import RDF, FOAF, XSD

g = Graph()
EX = Namespace("https://contoh.github.io/web-semantik/251402095/kampus#")

g.bind("ex", EX)
g.bind("foaf", FOAF)

g.add((EX.ida, RDF.type, EX.Lecturer))
g.add((EX.ida, FOAF.name, Literal("Ida Adi", lang="id")))

g.add((EX.budi, RDF.type, EX.Lecturer))
g.add((EX.budi, FOAF.name, Literal("Budi Santoso", lang="id")))

g.add((EX.siti, RDF.type, EX.Lecturer))
g.add((EX.siti, FOAF.name, Literal("Siti Rahmah", lang="id")))

g.add((EX.web_semantik, RDF.type, EX.Course))
g.add((EX.web_semantik, FOAF.name, Literal("Web Semantik", lang="id")))
g.add((EX.web_semantik, EX.jumlahKredit, Literal(3, datatype=XSD.integer)))
g.add((EX.web_semantik, EX.hariKuliah, Literal("Senin", lang="id")))

g.add((EX.pbo, RDF.type, EX.Course))
g.add((EX.pbo, FOAF.name, Literal("Pemrograman Berorientasi Objek", lang="id")))
g.add((EX.pbo, EX.jumlahKredit, Literal(3, datatype=XSD.integer)))

g.add((EX.basis_data, RDF.type, EX.Course))
g.add((EX.basis_data, FOAF.name, Literal("Sistem Basis Data", lang="id")))
g.add((EX.basis_data, EX.jumlahKredit, Literal(4, datatype=XSD.integer)))

g.add((EX.alyh, RDF.type, FOAF.Person))
g.add((EX.alyh, FOAF.name, Literal("Aliyah", lang="id")))

g.add((EX.hadziq, RDF.type, FOAF.Person))
g.add((EX.hadziq, FOAF.name, Literal("Hadziq Naufal Sinaga", lang="id")))

g.add((EX.ida, EX.mengajar, EX.web_semantik))
g.add((EX.budi, EX.mengajar, EX.pbo))
g.add((EX.siti, EX.mengajar, EX.basis_data))

g.add((EX.alyh, EX.mengambil, EX.web_semantik))
g.add((EX.hadziq, EX.mengambil, EX.web_semantik))
g.add((EX.hadziq, EX.mengambil, EX.pbo))

print("=== Output Turtle ===")
print(g.serialize(format="turtle"))

g.serialize("kampus_usu.ttl", format="turtle")
g.serialize("kampus_usu.jsonld", format="json-ld", indent=2)

print("\nFile 'kampus_usu.ttl' dan 'kampus_usu.jsonld' berhasil diperbarui!")