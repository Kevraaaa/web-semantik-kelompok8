# Semantic Web Layer Cake

| Lapis               | Peran                                      | Contoh Anda                                                                    |
| ------------------- | ------------------------------------------ | ------------------------------------------------------------------------------ |
| URI dan Unicode     | Identitas global dan representasi karakter | URI `https://satusu.ac.id` sebagai identitas unik sumber daya di web.          |
| XML                 | Sintaks pertukaran data                    | Dokumen XML yang menyimpan informasi Universitas Sumatera Utara.               |
| RDF dan RDFS        | Pernyataan graph dan kosakata dasar        | RDF/RDFS yang mendefinisikan class `Universitas` dan labelnya.                 |
| Ontology / OWL      | Makna domain dan penalaran lebih kaya      | OWL yang mendefinisikan class `Dosen` dan individual `Aliyah` bertipe `Dosen`. |
| SPARQL              | Query graph RDF                            | Query SPARQL untuk mengambil seluruh properti dan nilai dari resource USU.     |
| Rules, Proof, Trust | Aturan, pembuktian, dan kepercayaan        | Aturan inferensi bahwa dosen yang bekerja di USU terafiliasi dengan USU.       |

## Contoh Sintaks

### URI dan Unicode

```text
https://satusu.ac.id
```

URI digunakan sebagai identitas unik suatu sumber daya di web.

### XML

```xml
<?xml version="1.0" encoding="UTF-8"?>
<universitas>
    <nama>Universitas Sumatera Utara</nama>
    <singkatan>USU</singkatan>
    <lokasi>Medan</lokasi>
    <akreditasi>Unggul</akreditasi>
    <situs_web>www.usu.ac.id</situs_web>
</universitas>
```

XML digunakan untuk menyusun dan menukar data dalam format yang terstruktur.

### RDF dan RDFS

```xml
<rdf:RDF xmlns:rdf="http://w3.org" xmlns:rdfs="http://w3.org">
    <rdfs:Class rdf:about="univ:USU">
        <rdfs:label>Universitas Sumatera Utara</rdfs:label>
    </rdfs:Class>
</rdf:RDF>
```

RDF/RDFS digunakan untuk merepresentasikan data sebagai graph dan mendefinisikan kosakata dasar.

### Ontology / OWL

```xml
<rdf:RDF xmlns:rdf="http://w3.org" xmlns:owl="http://w3.org">
    <owl:Class rdf:about="Dosen"/>
    <owl:NamedIndividual rdf:about="Aliyah">
        <rdf:type rdf:resource="Dosen"/>
    </owl:NamedIndividual>
</rdf:RDF>
```

OWL digunakan untuk mendefinisikan konsep, individu, relasi, dan aturan yang lebih kaya dibanding RDF/RDFS.

### SPARQL

```sparql
SELECT ?properti ?nilai
WHERE {
    <http://usu.ac.id> ?properti ?nilai .
}
```

SPARQL digunakan untuk melakukan query terhadap data RDF.

### Rules, Proof, Trust

```text
Dosen(?d) ^ bekerjaDi(?d, USU)
    -> terafiliasiDengan(?d, USU)
```

Aturan ini menyatakan bahwa jika seseorang adalah dosen dan bekerja di USU, maka dapat disimpulkan bahwa orang tersebut terafiliasi dengan USU. 
Pada bagian ini kelompok kami mencari referensi dari berbagai sumber.

## Mengapa ontology berada di atas RDF/RDFS dan di bawah SPARQL?

Ontology berada di atas RDF/RDFS karena ontology memperluas RDF/RDFS dengan menambahkan definisi konsep, hubungan, dan aturan yang lebih kaya sehingga makna data dapat dipahami dengan lebih baik. Ontology berada di bawah SPARQL karena SPARQL digunakan untuk melakukan query terhadap data dan ontology yang telah dimodelkan sebelumnya.