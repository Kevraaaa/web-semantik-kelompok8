#!/usr/bin/env python
"""SPARQL operations and OWL/XML validation for Jejak Fauna."""

from __future__ import annotations

import difflib
import json
import math
import re
import sys
import xml.etree.ElementTree as ET
from collections import defaultdict
from pathlib import Path

from rdflib import Graph, Literal, RDF, RDFS, URIRef, XSD


JF = "https://jejakfauna.org/ontology#"
OWL_NS = "http://www.w3.org/2002/07/owl#"
LABEL_PROPERTIES = {
    "habitat": "namaHabitat",
    "wilayah": "namaWilayah",
    "ancaman": "deskripsiAncaman",
    "upaya_konservasi": "deskripsiKonservasi",
}
STATUS_LABELS = {
    "CR": "Kritis (CR)",
    "EN": "Terancam (EN)",
    "VU": "Rentan (VU)",
    "NT": "Hampir Terancam (NT)",
    "LC": "Risiko Rendah (LC)",
    "DD": "Data Kurang (DD)",
    "NE": "Belum Dievaluasi (NE)",
}
STOPWORDS = {
    "ada", "atau", "bagaimana", "berdasarkan", "berhabitat", "cari", "dan",
    "dari", "di", "fauna", "hewan", "hidup", "ingin", "jenis", "yang",
    "makan", "memakan", "pemakan", "satwa", "saya", "spesies", "untuk",
}
GROUP_CLASSES = (
    "Mamalia", "Burung", "Reptil", "Amfibi", "Ikan",
    "Krustasea", "Serangga", "Moluska", "Echinodermata",
)
DESCRIPTION_GROUPS = {
    "mammal": "Mamalia",
    "mammalia": "Mamalia",
    "primate": "Mamalia",
    "primata": "Mamalia",
    "rodent": "Mamalia",
    "burung": "Burung",
    "bird": "Burung",
    "amfibi": "Amfibi",
    "amphibian": "Amfibi",
    "amphibia": "Amfibi",
    "reptil": "Reptil",
    "reptile": "Reptil",
    "reptilia": "Reptil",
    "turtle": "Reptil",
    "snake": "Reptil",
    "ular": "Reptil",
    "crocodyl": "Reptil",
    "ikan": "Ikan",
    "fish": "Ikan",
    "fishes": "Ikan",
    "actinopterygii": "Ikan",
    "chondrichthyes": "Ikan",
    "elasmobranchii": "Ikan",
    "ray": "Ikan",
    "shark": "Ikan",
    "stingray": "Ikan",
    "crustacean": "Krustasea",
    "serangga": "Serangga",
    "insect": "Serangga",
    "mollusc": "Moluska",
    "mollusk": "Moluska",
    "bivalvia": "Moluska",
    "gastropoda": "Moluska",
    "holothuroidea": "Echinodermata",
}

PREFIXES = """
PREFIX jf: <https://jejakfauna.org/ontology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
"""
GROUP_VALUES = " ".join(f"jf:{group}" for group in GROUP_CLASSES)

FAUNA_QUERY = (PREFIXES + """
SELECT DISTINCT ?fauna ?localName ?scientificName ?description ?image
       ?iucnUrl ?wikidata ?group ?status ?habitat ?food ?threat
       ?taxonomy ?region ?effort ?latitude ?longitude
WHERE {
    ?fauna a jf:Fauna ;
        jf:namaLokal ?localName ;
        jf:namaIlmiah ?scientificName .
    OPTIONAL { ?fauna jf:deskripsi ?description }
    OPTIONAL { ?fauna jf:urlGambar ?image }
    OPTIONAL { ?fauna rdfs:seeAlso ?iucnUrl }
    OPTIONAL { ?fauna owl:sameAs ?wikidata }
    OPTIONAL {
        ?fauna rdf:type ?group .
        VALUES ?group { __GROUP_VALUES__ }
    }
    OPTIONAL { ?fauna jf:berstatusKonservasi ?status }
    OPTIONAL { ?fauna jf:mempunyaiHabitat ?habitat }
    OPTIONAL { ?fauna jf:memakan ?food }
    OPTIONAL { ?fauna jf:mempunyaiAncaman ?threat }
    OPTIONAL { ?fauna jf:termasukTaksonomi ?taxonomy }
    OPTIONAL { ?fauna jf:tersebarDi ?region }
    OPTIONAL { ?fauna jf:mempunyaiUpayaKonservasi ?effort }
    OPTIONAL { ?fauna geo:lat ?latitude ; geo:long ?longitude }
}
ORDER BY ?localName
""").replace("__GROUP_VALUES__", GROUP_VALUES)


def load_graph(path: str) -> Graph:
    dataset_path = Path(path)
    if not dataset_path.is_file():
        raise FileNotFoundError(f"RDF dataset not found: {dataset_path}")

    graph = Graph()
    graph.parse(dataset_path, format="turtle")
    return graph


def local_name(value: URIRef | str) -> str:
    text = str(value)
    if "#" in text:
        return text.rsplit("#", 1)[1]
    return text.rstrip("/").rsplit("/", 1)[-1]


def humanize(value: str) -> str:
    text = re.sub(r"(?<=[a-z0-9])(?=[A-Z])", " ", value)
    return re.sub(r"\s+", " ", text).strip()


def resource_label(graph: Graph, resource: URIRef, kind: str | None = None) -> str:
    if kind == "status":
        code = next(iter(graph.objects(resource, URIRef(JF + "kodeIUCN"))), None)
        if code is not None:
            return STATUS_LABELS.get(str(code).upper(), str(code))
        code = {
            "StatusKritis": "CR",
            "StatusTerancam": "EN",
            "StatusRentan": "VU",
        }.get(local_name(resource))
        if code:
            return STATUS_LABELS[code]

    labels = list(graph.objects(resource, RDFS.label))
    if labels:
        return str(labels[0])

    property_name = LABEL_PROPERTIES.get(kind or "")
    if property_name:
        values = list(graph.objects(resource, URIRef(JF + property_name)))
        if values:
            return str(values[0])

    return humanize(local_name(resource))


def slug_from_uri(resource: URIRef) -> str:
    subject = local_name(resource)
    kebab = re.sub(r"([A-Z])", r"-\1", subject)
    return kebab.lstrip("-").lower()


def query_fauna(graph: Graph) -> list[dict]:
    records: dict[str, dict] = {}
    relation_fields = {
        "habitat": "habitat",
        "food": "makanan",
        "threat": "ancaman",
        "taxonomy": "taksonomi",
        "region": "wilayah",
        "effort": "upaya_konservasi",
    }

    for row in graph.query(FAUNA_QUERY):
        fauna_uri = row.fauna
        key = str(fauna_uri)
        record = records.setdefault(key, {
            "iri": key,
            "slug": slug_from_uri(fauna_uri),
            "nama": str(row.localName),
            "nama_ilmiah": str(row.scientificName),
            "deskripsi": "",
            "gambar": "/images/fauna-placeholder.svg",
            "url_iucn": None,
            "wikidata": None,
            "kelompok": "Lainnya",
            "kelompok_key": "Lainnya",
            "status_iucn": None,
            "status_iucn_key": None,
            "latitude": None,
            "longitude": None,
            "habitat": [],
            "habitat_keys": [],
            "makanan": [],
            "makanan_keys": [],
            "ancaman": [],
            "ancaman_keys": [],
            "taksonomi": [],
            "taksonomi_keys": [],
            "famili_key": None,
            "famili": None,
            "wilayah": [],
            "wilayah_keys": [],
            "upaya_konservasi": [],
            "upaya_konservasi_keys": [],
        })

        if row.description is not None:
            record["deskripsi"] = str(row.description)
        if row.image is not None:
            record["gambar"] = str(row.image)
        if row.iucnUrl is not None:
            record["url_iucn"] = str(row.iucnUrl)
        if row.wikidata is not None:
            record["wikidata"] = str(row.wikidata)
        if row.group is not None:
            group = local_name(row.group)
            record["kelompok"] = group
            record["kelompok_key"] = group
        if row.status is not None:
            record["status_iucn_key"] = local_name(row.status)
            record["status_iucn"] = resource_label(graph, row.status, "status")
        if row.latitude is not None and row.longitude is not None:
            try:
                latitude = float(row.latitude)
                longitude = float(row.longitude)
            except (TypeError, ValueError, OverflowError):
                pass
            else:
                if (
                    math.isfinite(latitude)
                    and math.isfinite(longitude)
                    and -90 <= latitude <= 90
                    and -180 <= longitude <= 180
                ):
                    record["latitude"] = latitude
                    record["longitude"] = longitude

        for row_field, public_field in relation_fields.items():
            value = getattr(row, row_field)
            if value is not None:
                kind = public_field if public_field in LABEL_PROPERTIES else None
                pair = (local_name(value), resource_label(graph, value, kind))
                if pair not in record.setdefault(f"{public_field}_pairs", []):
                    record[f"{public_field}_pairs"].append(pair)

    results = []
    for record in records.values():
        for field in ("habitat", "makanan", "ancaman", "taksonomi", "wilayah", "upaya_konservasi"):
            pairs = record.pop(f"{field}_pairs", [])
            record[field] = sorted({label for _, label in pairs}, key=str.casefold)
            key_field = {
                "habitat": "habitat_keys",
                "makanan": "makanan_keys",
                "ancaman": "ancaman_keys",
                "wilayah": "wilayah_keys",
                "upaya_konservasi": "upaya_konservasi_keys",
            }.get(field)
            if field == "taksonomi":
                record["taksonomi_keys"] = sorted({key for key, _ in pairs}, key=str.casefold)
                family_key = next(
                    (key for key in record["taksonomi_keys"] if key.casefold().endswith("idae")),
                    None,
                )
                if family_key:
                    record["famili_key"] = family_key
                    record["famili"] = next(
                        (label for key, label in pairs if key == family_key),
                        humanize(family_key),
                    )
            elif key_field:
                record[key_field] = sorted({key for key, _ in pairs}, key=str.casefold)

        if record["kelompok"] == "Lainnya":
            description = record["deskripsi"].casefold()
            for term, group in DESCRIPTION_GROUPS.items():
                if re.search(r"(?<![\w])" + re.escape(term) + r"(?![\w])", description):
                    record["kelompok"] = group
                    record["kelompok_key"] = group
                    break

        results.append(record)

    return results


def public_fauna(fauna: dict) -> dict:
    public_fields = (
        "slug", "nama", "nama_ilmiah", "kelompok", "deskripsi", "status_iucn",
        "habitat", "makanan", "ancaman", "taksonomi", "wilayah",
        "upaya_konservasi", "gambar", "url_iucn", "wikidata", "famili_key", "famili",
        "latitude", "longitude",
    )
    result = {field: fauna.get(field) for field in public_fields}
    result["sumber"] = [url for url in (fauna.get("url_iucn"), fauna.get("wikidata")) if url]
    return result


def filters(graph: Graph) -> dict:
    fauna = query_fauna(graph)
    options: dict[str, dict] = {
        "status": {},
        "habitat": {},
        "makanan": {},
        "kelompok": set(),
        "famili": {},
        "ancaman": {},
    }
    for item in fauna:
        if item["status_iucn_key"]:
            options["status"][item["status_iucn_key"]] = item["status_iucn"]
        if item["kelompok"] != "Lainnya":
            options["kelompok"].add(item["kelompok"])
        if item["famili_key"]:
            options["famili"][item["famili_key"]] = item["famili"]

    for field, predicate, kind in (
        ("habitat", "mempunyaiHabitat", "habitat"),
        ("makanan", "memakan", None),
        ("ancaman", "mempunyaiAncaman", None),
    ):
        for target in graph.objects(None, URIRef(JF + predicate)):
            if isinstance(target, URIRef):
                options[field][local_name(target)] = resource_label(graph, target, kind)

    options["kelompok"] = sorted(options["kelompok"])
    return options


def normalize(text: str) -> str:
    return re.sub(r"\s+", " ", text.casefold()).strip()


def query_terms(query: str) -> list[str]:
    return [
        term for term in re.findall(r"[^\W_]+", normalize(query), flags=re.UNICODE)
        if len(term) > 1 and term not in STOPWORDS
    ]


def matches_term(term: str, text: str) -> bool:
    words = re.findall(r"[^\W_]+", normalize(text), flags=re.UNICODE)
    if term in words:
        return True

    return any(
        len(word) >= 4 and difflib.SequenceMatcher(None, term, word).ratio() >= 0.82
        for word in words
    )


def semantic_search(graph: Graph, query: str) -> dict:
    terms = query_terms(query)
    if not terms:
        return {"data": []}

    results = []
    for fauna in query_fauna(graph):
        reasons: dict[str, set[str]] = defaultdict(set)
        matched_terms: set[str] = set()

        for field, label in (
            ("habitat", "habitat"),
            ("makanan", "makanan"),
            ("ancaman", "ancaman"),
            ("wilayah", "wilayah"),
            ("upaya_konservasi", "upaya konservasi"),
        ):
            for value in fauna[field]:
                matched = {term for term in terms if matches_term(term, value)}
                if matched:
                    reasons[field].add(value)
                    matched_terms.update(matched)

        if fauna["status_iucn"]:
            matched = {
                term for term in terms
                if matches_term(term, fauna["status_iucn"])
                or matches_term(term, fauna["status_iucn_key"] or "")
            }
            if matched:
                reasons["status"].add(fauna["status_iucn"])
                matched_terms.update(matched)

        if fauna["kelompok"] != "Lainnya":
            matched = {term for term in terms if matches_term(term, fauna["kelompok"])}
            if matched:
                reasons["kelompok"].add(fauna["kelompok"])
                matched_terms.update(matched)

        for field in ("nama", "nama_ilmiah", "deskripsi"):
            matched = {term for term in terms if matches_term(term, fauna[field] or "")}
            if matched:
                reasons["nama" if field != "deskripsi" else "deskripsi"].add(fauna[field])
                matched_terms.update(matched)

        if reasons:
            reason_strings = [
                f"{label}: {value}"
                for field, label in (
                    ("habitat", "habitat"),
                    ("makanan", "makanan"),
                    ("ancaman", "ancaman"),
                    ("wilayah", "wilayah"),
                    ("upaya_konservasi", "upaya konservasi"),
                    ("status", "status"),
                    ("kelompok", "kelompok"),
                    ("nama", "nama"),
                    ("deskripsi", "deskripsi"),
                )
                for value in sorted(reasons[field], key=str.casefold)
            ]
            results.append({
                "fauna": public_fauna(fauna),
                "skor": len(matched_terms) + len(reason_strings),
                "alasan_kecocokan": reason_strings,
            })

    results.sort(key=lambda result: (-result["skor"], result["fauna"]["nama"].casefold()))
    return {"data": results}


def recommendations(graph: Graph, slug: str, limit: int) -> dict:
    fauna = query_fauna(graph)
    target = next((item for item in fauna if item["slug"] == slug), None)
    if target is None:
        return {"data": []}

    target_uri = URIRef(target["iri"])
    scores: dict[str, int] = defaultdict(int)
    patterns = (
        URIRef(JF + "mempunyaiHabitat"),
        URIRef(JF + "memakan"),
        URIRef(JF + "termasukTaksonomi"),
        URIRef(JF + "berstatusKonservasi"),
    )
    for relation in patterns:
        query = PREFIXES + f"""
        SELECT DISTINCT ?candidate
        WHERE {{
            ?target <{relation}> ?concept .
            ?candidate <{relation}> ?concept ;
                a jf:Fauna .
            FILTER (?candidate != ?target)
        }}
        """
        for row in graph.query(query, initBindings={"target": target_uri}):
            scores[str(row.candidate)] += 1

    group_query = PREFIXES + """
    SELECT DISTINCT ?candidate
    WHERE {
        ?target rdf:type ?group .
        VALUES ?group { __GROUP_VALUES__ }
        ?candidate rdf:type ?group, jf:Fauna .
        FILTER (?candidate != ?target)
    }
    """.replace("__GROUP_VALUES__", GROUP_VALUES)
    for row in graph.query(group_query, initBindings={"target": target_uri}):
        scores[str(row.candidate)] += 1

    indexed = {item["iri"]: item for item in fauna}
    for candidate in fauna:
        if candidate["kelompok"] == target["kelompok"] and candidate["kelompok"] != "Lainnya":
            scores[candidate["iri"]] += 1

    ranked = sorted(
        ((indexed[iri], score) for iri, score in scores.items() if iri in indexed),
        key=lambda pair: (-pair[1], pair[0]["nama"].casefold()),
    )
    return {"data": [public_fauna(item) for item, _ in ranked[:max(1, limit)]]}


def knowledge_graph(
    graph: Graph,
    limit: int,
    relation_filter: list[str],
    group_filter: str | None = None,
    habitat_filter: str | None = None,
) -> dict:
    all_fauna = query_fauna(graph)
    if group_filter:
        all_fauna = [item for item in all_fauna if item["kelompok"] == group_filter]
    if habitat_filter:
        all_fauna = [item for item in all_fauna if habitat_filter in item["habitat_keys"]]

    if group_filter or habitat_filter:
        fauna = all_fauna[:max(1, limit)]
    else:
        by_group: dict[str, list[dict]] = defaultdict(list)
        for item in all_fauna:
            by_group[item["kelompok"]].append(item)

        fauna = []
        for index in range(max((len(items) for items in by_group.values()), default=0)):
            for group_items in by_group.values():
                if index < len(group_items):
                    fauna.append(group_items[index])
                    if len(fauna) >= max(1, limit):
                        break
            if len(fauna) >= max(1, limit):
                break

    if not fauna:
        return {"nodes": [], "edges": []}

    fauna_by_slug = {item["slug"]: item for item in fauna}
    values = " ".join(f"<{item['iri']}>" for item in fauna)
    query = PREFIXES + f"""
    SELECT DISTINCT ?fauna ?name ?group ?habitat ?food ?threat
    WHERE {{
        VALUES ?fauna {{ {values} }}
        ?fauna jf:namaLokal ?name .
        OPTIONAL {{
            ?fauna rdf:type ?group .
            VALUES ?group {{ {GROUP_VALUES} }}
        }}
        OPTIONAL {{ ?fauna jf:mempunyaiHabitat ?habitat }}
        OPTIONAL {{ ?fauna jf:memakan ?food }}
        OPTIONAL {{ ?fauna jf:mempunyaiAncaman ?threat }}
    }}
    """
    nodes: dict[str, dict] = {}
    edges = set()
    for row in graph.query(query):
        slug = slug_from_uri(row.fauna)
        fauna_id = f"fauna:{slug}"
        nodes[fauna_id] = {
            "id": fauna_id,
            "label": str(row.name),
            "type": "fauna",
            "slug": slug,
        }
        group = local_name(row.group) if row.group is not None else fauna_by_slug[slug]["kelompok"]
        if group != "Lainnya":
            group_id = f"kelompok:{group}"
            nodes[group_id] = {"id": group_id, "label": group, "type": "kelompok"}
            edges.add((fauna_id, group_id, "bagian dari", "bagianDari"))
        for term, kind in (
            (row.habitat, "habitat"),
            (row.food, "makanan"),
            (row.threat, "ancaman"),
        ):
            if term is None:
                continue
            label = resource_label(graph, term, kind if kind == "habitat" else None)
            concept_id = f"{kind}:{local_name(term)}"
            nodes[concept_id] = {"id": concept_id, "label": label, "type": kind}
            edge_label = {
                "habitat": "hidup di",
                "makanan": "memakan",
                "ancaman": "terancam oleh",
            }[kind]
            relation_type = {
                "habitat": "hidupDi",
                "makanan": "memakan",
                "ancaman": "terancamOleh",
            }[kind]
            edges.add((fauna_id, concept_id, edge_label, relation_type))

    edge_data = [
        {"from": source, "to": target, "label": label, "type": relation}
        for source, target, label, relation in sorted(edges)
        if not relation_filter or relation in relation_filter
    ]
    connected = {node_id for edge in edge_data for node_id in (edge["from"], edge["to"])}
    if relation_filter:
        nodes = {key: value for key, value in nodes.items() if key in connected}

    return {"nodes": list(nodes.values()), "edges": edge_data}


def xml_iri(value: str | None, ontology_namespace: str, prefixes: dict[str, str]) -> str | None:
    if not value:
        return None
    if value.startswith("#"):
        return ontology_namespace + value[1:]
    if ":" in value:
        prefix, local = value.split(":", 1)
        if prefix in prefixes:
            return prefixes[prefix] + local
    return value


def entity_iri(element: ET.Element, ontology_namespace: str, prefixes: dict[str, str]) -> str | None:
    value = element.attrib.get("IRI") or element.attrib.get("abbreviatedIRI")
    return xml_iri(value, ontology_namespace, prefixes)


def parse_ontology(path: str) -> dict:
    ontology_path = Path(path)
    if not ontology_path.is_file():
        raise FileNotFoundError(f"OWL/XML ontology not found: {ontology_path}")

    root = ET.parse(ontology_path).getroot()
    prefixes = {
        entry.attrib["name"]: entry.attrib["IRI"]
        for entry in root.findall(f"{{{OWL_NS}}}Prefix")
        if "name" in entry.attrib and "IRI" in entry.attrib
    }
    namespace = prefixes.get("jf", "")
    base = root.attrib.get("{http://www.w3.org/XML/1998/namespace}base", "")
    if not namespace:
        namespace = base.rstrip("#") + "#"

    classes: set[str] = set()
    object_properties: set[str] = set()
    data_properties: set[str] = set()
    domains: dict[str, set[str]] = defaultdict(set)
    ranges: dict[str, set[str]] = defaultdict(set)
    datatypes: dict[str, set[str]] = defaultdict(set)
    subclasses: dict[str, set[str]] = defaultdict(set)

    declaration_types = {
        f"{{{OWL_NS}}}Class": classes,
        f"{{{OWL_NS}}}ObjectProperty": object_properties,
        f"{{{OWL_NS}}}DataProperty": data_properties,
    }
    for declaration in root.findall(f".//{{{OWL_NS}}}Declaration"):
        for entity in declaration:
            target_set = declaration_types.get(entity.tag)
            iri = entity_iri(entity, namespace, prefixes)
            if target_set is not None and iri:
                target_set.add(iri)

    for element_name, target in (
        ("ObjectPropertyDomain", domains),
        ("DataPropertyDomain", domains),
        ("ObjectPropertyRange", ranges),
    ):
        for statement in root.findall(f".//{{{OWL_NS}}}{element_name}"):
            entities = list(statement)
            if len(entities) >= 2:
                predicate = entity_iri(entities[0], namespace, prefixes)
                value = entity_iri(entities[1], namespace, prefixes)
                if predicate and value:
                    target[predicate].add(value)

    for statement in root.findall(f".//{{{OWL_NS}}}DataPropertyRange"):
        entities = list(statement)
        if len(entities) >= 2:
            predicate = entity_iri(entities[0], namespace, prefixes)
            datatype = entity_iri(entities[1], namespace, prefixes)
            if predicate and datatype:
                datatypes[predicate].add(datatype)

    for statement in root.findall(f".//{{{OWL_NS}}}SubClassOf"):
        entities = list(statement)
        if len(entities) >= 2:
            subclass = entity_iri(entities[0], namespace, prefixes)
            superclass = entity_iri(entities[1], namespace, prefixes)
            if subclass and superclass:
                subclasses[subclass].add(superclass)

    return {
        "namespace": namespace,
        "classes": classes,
        "object_properties": object_properties,
        "data_properties": data_properties,
        "domains": domains,
        "ranges": ranges,
        "datatypes": datatypes,
        "subclasses": subclasses,
    }


def class_closure(types: set[str], subclasses: dict[str, set[str]]) -> set[str]:
    closure = set(types)
    pending = list(types)
    while pending:
        current = pending.pop()
        for parent in subclasses.get(current, set()):
            if parent not in closure:
                closure.add(parent)
                pending.append(parent)
    return closure


def validation(graph: Graph, ontology_path: str) -> dict:
    ontology = parse_ontology(ontology_path)
    namespace = ontology["namespace"]
    used_classes = {
        str(class_iri)
        for class_iri in graph.objects(None, RDF.type)
        if isinstance(class_iri, URIRef) and str(class_iri).startswith(JF)
    }
    used_properties = {
        str(predicate)
        for predicate in graph.predicates()
        if isinstance(predicate, URIRef) and str(predicate).startswith(JF)
    }
    classes = ontology["classes"]
    properties = ontology["object_properties"] | ontology["data_properties"]
    undefined_classes = used_classes - classes
    undefined_properties = used_properties - properties
    used_fauna = set(graph.subjects(RDF.type, URIRef(JF + "Fauna")))

    errors: list[str] = []
    warnings: list[str] = []
    invalid_triples: set[tuple] = set()
    untyped_range_objects: set[URIRef] = set()

    def report_mismatch(triple: tuple, message: str) -> None:
        errors.append(message)
        invalid_triples.add(triple)

    for class_iri in sorted(undefined_classes):
        errors.append(f"RDF uses an undeclared OWL class: {local_name(class_iri)}.")
    for property_iri in sorted(undefined_properties):
        errors.append(f"RDF uses an undeclared OWL property: {local_name(property_iri)}.")

    if namespace != JF:
        errors.append(f"Namespace mismatch: RDF uses {JF}, OWL/XML declares {namespace}.")

    for subject, predicate, obj in graph:
        predicate_iri = str(predicate) if isinstance(predicate, URIRef) else ""
        if not predicate_iri.startswith(JF):
            continue
        if predicate_iri not in properties:
            invalid_triples.add((subject, predicate, obj))
            continue

        domain_classes = ontology["domains"].get(predicate_iri, set())
        subject_types = {
            str(item) for item in graph.objects(subject, RDF.type) if isinstance(item, URIRef)
        }
        subject_closure = class_closure(subject_types, ontology["subclasses"])
        if domain_classes and subject_types and not domain_classes.intersection(subject_closure):
            report_mismatch(
                (subject, predicate, obj),
                f"Domain mismatch for {local_name(predicate_iri)} on {local_name(subject)}.",
            )
        elif domain_classes and not subject_types:
            warnings.append(f"Domain could not be checked for untyped subject {local_name(subject)}.")

        if predicate_iri in ontology["object_properties"]:
            range_classes = ontology["ranges"].get(predicate_iri, set())
            if isinstance(obj, URIRef) and range_classes:
                object_types = {
                    str(item) for item in graph.objects(obj, RDF.type) if isinstance(item, URIRef)
                }
                object_closure = class_closure(object_types, ontology["subclasses"])
                if object_types and not range_classes.intersection(object_closure):
                    report_mismatch(
                        (subject, predicate, obj),
                        f"Range mismatch for {local_name(predicate_iri)} target {local_name(obj)}.",
                    )
                elif not object_types:
                    untyped_range_objects.add(obj)
        elif predicate_iri in ontology["data_properties"]:
            expected_datatypes = ontology["datatypes"].get(predicate_iri, set())
            if not isinstance(obj, Literal):
                report_mismatch(
                    (subject, predicate, obj),
                    f"Expected a literal for data property {local_name(predicate_iri)}.",
                )
            elif expected_datatypes:
                actual_datatype = str(obj.datatype or XSD.string)
                if actual_datatype not in expected_datatypes:
                    report_mismatch(
                        (subject, predicate, obj),
                        f"Datatype mismatch for {local_name(predicate_iri)}: expected "
                        f"{', '.join(sorted(expected_datatypes))}, received {actual_datatype}.",
                    )

    if untyped_range_objects:
        warnings.append(
            f"{len(untyped_range_objects)} object-property targets have no explicit rdf:type; "
            "their range is not contradicted under OWL open-world semantics."
        )

    if not len(graph):
        warnings.append("RDF dataset is empty.")
    elif not used_fauna:
        warnings.append("RDF dataset has triples but no individuals typed as jf:Fauna.")
    else:
        missing_images = sum(
            1 for fauna in used_fauna
            if not any(graph.objects(fauna, URIRef(JF + "urlGambar")))
        )
        if missing_images:
            warnings.append(f"{missing_images} fauna individuals have no urlGambar value.")

        for predicate_name in (
            "mempunyaiHabitat",
            "memakan",
            "tersebarDi",
            "berstatusKonservasi",
        ):
            predicate = URIRef(JF + predicate_name)
            missing = sum(1 for fauna in used_fauna if not any(graph.objects(fauna, predicate)))
            if missing:
                warnings.append(f"{missing} fauna individuals have no {predicate_name} relation.")

    unused_classes = classes - used_classes
    unused_properties = properties - used_properties
    if unused_classes:
        warnings.append(
            "OWL classes defined but unused by RDF: "
            + ", ".join(sorted(local_name(value) for value in unused_classes))
            + "."
        )
    if unused_properties:
        warnings.append(
            "OWL properties defined but unused by RDF: "
            + ", ".join(sorted(local_name(value) for value in unused_properties))
            + "."
        )

    return {
        "triple_count": len(graph),
        "fauna_count": len(used_fauna),
        "class_count": len(used_classes),
        "property_count": len(used_properties),
        "valid_count": sum(
            1 for triple in graph
            if str(triple[1]).startswith(JF) and triple not in invalid_triples
        ),
        "vocabulary": {
            "namespace": namespace,
            "namespace_matches": namespace == JF,
        },
        "classes": {
            "used": sorted(local_name(value) for value in used_classes),
            "undefined": sorted(local_name(value) for value in undefined_classes),
            "unused": sorted(local_name(value) for value in unused_classes),
        },
        "properties": {
            "used": sorted(local_name(value) for value in used_properties),
            "undefined": sorted(local_name(value) for value in undefined_properties),
            "unused": sorted(local_name(value) for value in unused_properties),
        },
        "errors": sorted(set(errors)),
        "warnings": sorted(set(warnings)),
    }


def handle(operation: str, payload: dict) -> dict:
    graph = load_graph(payload["dataset_path"])

    if operation == "fauna":
        return {"data": query_fauna(graph)}
    if operation == "semantic":
        return semantic_search(graph, str(payload.get("query", "")))
    if operation == "recommendations":
        return recommendations(
            graph,
            str(payload.get("slug", "")),
            int(payload.get("limit", 6)),
        )
    if operation == "graph":
        return knowledge_graph(
            graph,
            int(payload.get("limit", 50)),
            list(payload.get("relations", [])),
            str(payload["group"]) if payload.get("group") else None,
            str(payload["habitat"]) if payload.get("habitat") else None,
        )
    if operation == "filters":
        return filters(graph)
    if operation == "validate":
        return validation(graph, str(payload["ontology_path"]))

    raise ValueError(f"Unsupported RDF operation: {operation}")


def main() -> None:
    if len(sys.argv) != 2:
        raise ValueError("An RDF operation is required.")

    payload = json.load(sys.stdin)
    result = handle(sys.argv[1], payload)
    json.dump(result, sys.stdout, ensure_ascii=False)


if __name__ == "__main__":
    main()
