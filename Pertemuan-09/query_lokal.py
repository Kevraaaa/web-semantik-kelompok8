from pathlib import Path
from rdflib import Graph

# Membaca data RDF dari Pertemuan 6
g = Graph()
g.parse(
    Path("../pertemuan-06/kampus_usu.ttl"), format="turtle")

# Prefix sesuai IRI graf Kampus USU
PREFIX = """
PREFIX ex: <https://contoh.github.io/web-semantik/251402095/kampus#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
"""

# SELECT: Menampilkan dosen dan mata kuliah

q_select = PREFIX + """
SELECT ?namaDosen ?namaMK WHERE {
    ?dosen ex:mengajar ?mk ;
           foaf:name ?namaDosen .
    ?mk foaf:name ?namaMK .
}
ORDER BY ?namaDosen
"""

print("\n[1] DAFTAR DOSEN DAN MATA KULIAH")
for row in g.query(q_select):
    print(f"{row.namaDosen} - {row.namaMK}")

# ASK: Memeriksa fakta sebelum INSERT

q_ask = PREFIX + """
ASK {
    ex:ida ex:mengajar ex:mk_baru .
}
"""

print("\n[2] PEMERIKSAAN FAKTA SEBELUM INSERT")
print("Sebelum INSERT:", g.query(q_ask).askAnswer)

# INSERT DATA: Menambahkan fakta baru

g.update(PREFIX + """
INSERT DATA {
    ex:ida ex:mengajar ex:mk_baru .
    ex:mk_baru foaf:name "Pemrograman Web"@id .
}
""")

print("\n[3] PEMERIKSAAN FAKTA SESUDAH INSERT")
print("Sesudah INSERT:", g.query(q_ask).askAnswer)

# Menampilkan jumlah triple akhir

print("\n[4] JUMLAH TRIPLE")
print("Jumlah triple awal : 26")
print("Jumlah triple akhir:", len(g))

# Menyimpan graf yang sudah diperbarui

g.serialize("kampus_usu_v2.ttl", format="turtle")

print("\nFile kampus_usu_v2.ttl berhasil dibuat.")