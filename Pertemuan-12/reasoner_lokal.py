from pathlib import Path
from rdflib import Graph, RDF, RDFS
from owlrl import DeductiveClosure, RDFS_Semantics

BASE = "https://contoh.github.io/web-semantik/251402095/kampus#"

SUMBER = Path("../pertemuan-09/kampus_usu_v2.ttl")  

asserted = Graph().parse(SUMBER, format="turtle")

asserted.update(f"""
PREFIX ex: <{BASE}>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

INSERT DATA {{
  ex:Lecturer rdfs:subClassOf ex:Pegawai .
  ex:Pegawai rdfs:subClassOf foaf:Person .
  ex:mengajar rdfs:domain ex:Lecturer ;
              rdfs:range  ex:Course .
}}""")

print("Sumber:", SUMBER, "| asserted:", len(asserted))

closure = Graph() + asserted
DeductiveClosure(RDFS_Semantics).expand(closure)

inferred = closure - asserted

TRIVIAL = {RDFS.Resource, RDF.Property}

bermakna = Graph()
for s, p, o in inferred:
    if str(s).startswith(BASE) and o not in TRIVIAL and s != o:
        bermakna.add((s, p, o))

print("Closure:", len(closure), 
      "| inferred:", len(inferred),
      "| bermakna:", len(bermakna))

asserted.serialize("kampus_usu_v5.ttl", format="turtle")        
bermakna.serialize("kampus_usu_inferred.ttl", format="turtle")  

V4 = Path("../pertemuan-11/kampus_usu_v4.ttl")

if V4.exists():
    v4 = Graph().parse(V4, format="turtle")
    tercampur = [t for t in v4 if t not in asserted]
    print("Triple di v4 yang tidak ada di asserted:", len(tercampur))
else:
    print("kampus_usu_v4.ttl tidak ditemukan")