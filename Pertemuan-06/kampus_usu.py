from rdflib import Graph, Namespace, Literal
from rdflib.namespace import RDF, FOAF, XSD

g = Graph()
EX = Namespace("https://contoh.github.io/web-semantik/251402010/kampus#")

g.bind("ex", EX)
g.bind("foaf", FOAF)

# Dosen dan mata kuliah
g.add((EX.ida, RDF.type, EX.Lecturer))
g.add((EX.ida, FOAF.name, Literal("Ida Adi", lang="id")))
g.add((EX.web_semantik, RDF.type, EX.Course))
g.add((EX.web_semantik, FOAF.name, Literal("Web Semantik", lang="id")))
g.add((EX.ida, EX.mengajar, EX.web_semantik))

g.add((EX.alyh, RDF.type, FOAF.Person))
g.add((EX.alyh, FOAF.name, Literal("Aliyah", lang="id")))
g.add((EX.alyh, EX.mengambil, EX.web_semantik))
g.add((EX.alyh, EX.domisili, Literal("Medan", lang="id")))

print("=== Output Turtle ===")
print(g.serialize(format="turtle"))

g.serialize("kampus_usu.ttl", format="turtle")
g.serialize("kampus_usu.jsonld", format="json-ld", indent=2)

print("\nFile 'kampus_usu.ttl' dan 'kampus_usu.jsonld' berhasil dibuat!")