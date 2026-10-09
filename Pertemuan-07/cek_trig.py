
from rdflib import Dataset

dataset = Dataset()
dataset.parse("kampus_tergabung.trig", format="trig")

print("Jumlah triple:", len(dataset))
print("Jumlah named graph:")

for graph in dataset.graphs():
    if len(graph) > 0:
        print(graph.identifier, "-", len(graph), "triple")