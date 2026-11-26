import { getFaunaDetail, getFilterOptions, getGraph } from './api.js';

const colors = {
    pusat: { background: '#eff4bd', border: '#b5c27c', font: { color: '#25321f' } },
    fauna: { background: '#556f30', border: '#8fa65d', font: { color: '#f0f0e5' } },
    kelompok: { background: '#70743a', border: '#a2a964', font: { color: '#f0f0e5' } },
    habitat: { background: '#516f7a', border: '#85a5ac', font: { color: '#f0f0e5' } },
    ekosistem: { background: '#3e6870', border: '#80a6a6', font: { color: '#f0f0e5' } },
    makanan: { background: '#855f2d', border: '#b18a51', font: { color: '#fff3d4' } },
    ancaman: { background: '#713d36', border: '#b16d5c', font: { color: '#ffe6d8' } },
};

const fallbackColor = { background: '#58605a', border: '#89938b', font: { color: '#f0f0e5' } };

function styleNodes(nodes, mini = false) {
    return nodes.map((node) => {
        const color = colors[node.type] ?? fallbackColor;

        return {
            ...node,
            color,
            shape: 'dot',
            size: node.type === 'pusat' ? 34 : (node.type === 'fauna' ? 23 : 16),
            font: { ...color.font, size: mini ? 10 : 13 },
            borderWidth: 1.5,
        };
    });
}

function styleEdges(edges) {
    return edges.map((edge) => ({
        ...edge,
        arrows: 'to',
        color: { color: '#91a181', opacity: .48, highlight: '#eff4bd' },
        font: { color: '#c4cbb8', size: 10, align: 'middle', strokeWidth: 3, strokeColor: '#1b2a1b' },
        smooth: { type: 'dynamic' },
    }));
}

function populateSelect(select, options, placeholder) {
    if (!select) {
        return;
    }

    select.replaceChildren(new Option(placeholder, ''));
    options.forEach(([value, label]) => select.add(new Option(label, value)));
}

async function mountGraph(container, mini = false) {
    const { Network } = await import('vis-network/standalone');
    const relationTypes = [...document.querySelectorAll('[data-graph-relation]:checked')].map((input) => input.value);
    let fullGraph = await getGraph({ relasi: relationTypes });
    const nodes = styleNodes(fullGraph.nodes, mini);
    const edges = styleEdges(fullGraph.edges);
    const network = new Network(container, { nodes, edges }, {
        autoResize: true,
        interaction: { hover: true, navigationButtons: false, keyboard: true, zoomView: true },
        physics: { stabilization: { iterations: mini ? 80 : 160 }, barnesHut: { gravitationalConstant: -3700, springLength: mini ? 130 : 170, springConstant: .04 } },
        nodes: { borderWidth: 1, shape: 'dot', font: { face: 'Inter' } },
        edges: { width: 1, selectionWidth: 2 },
    });
    network.on('stabilizationIterationsDone', () => network.fit({ animation: false }));

    if (mini) {
        return network;
    }

    const options = await getFilterOptions();
    const groupFilter = document.querySelector('[data-graph-group]');
    const habitatFilter = document.querySelector('[data-graph-habitat]');
    populateSelect(groupFilter, options.kelompok.map((group) => [group, group]), 'Semua kelompok');
    populateSelect(habitatFilter, Object.entries(options.habitat), 'Semua habitat');

    const selected = document.querySelector('[data-graph-detail]');
    network.on('click', async (event) => {
        const id = event.nodes[0];
        if (!id) return;
        const node = fullGraph.nodes.find((item) => item.id === id);
        if (!node) return;
        const neighbors = new Set(fullGraph.edges.filter((edge) => edge.from === id || edge.to === id).flatMap((edge) => [edge.from, edge.to]));
        network.selectNodes([...neighbors]);
        network.focus(id, { scale: 1.1, animation: { duration: 450 } });
        selected.querySelector('[data-graph-detail-title]').textContent = node.label;
        selected.querySelector('[data-graph-detail-text]').textContent = `Terhubung dengan ${Math.max(0, neighbors.size - 1)} simpul lain melalui relasi yang tersedia.`;
        const link = selected.querySelector('[data-graph-detail-link]');
        if (node.slug) {
            try {
                const animal = await getFaunaDetail(node.slug);
                selected.querySelector('[data-graph-detail-text]').textContent = `${animal.nama_ilmiah} · ${animal.status_iucn}. Relasi diturunkan dari dataset RDF.`;
                link.href = `/eksplorasi/${encodeURIComponent(node.slug)}`;
                link.classList.remove('hidden');
            } catch (error) {
                console.error(error);
            }
        } else {
            link.classList.add('hidden');
        }
    });

    document.querySelector('[data-graph-zoom="in"]')?.addEventListener('click', () => network.moveTo({ scale: network.getScale() * 1.2, animation: true }));
    document.querySelector('[data-graph-zoom="out"]')?.addEventListener('click', () => network.moveTo({ scale: network.getScale() / 1.2, animation: true }));
    document.querySelector('[data-graph-reset]')?.addEventListener('click', () => network.fit({ animation: true }));
    document.querySelector('[data-graph-search]')?.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLocaleLowerCase('id');
        if (!query) {
            network.unselectAll();
            return;
        }
        const match = fullGraph.nodes.find((node) => node.label.toLocaleLowerCase('id').includes(query));
        if (match) {
            network.selectNodes([match.id]);
            network.focus(match.id, { scale: 1.15, animation: true });
        }
    });

    const applyRelations = async () => {
        const types = [...document.querySelectorAll('[data-graph-relation]:checked')].map((input) => input.value);
        const graph = await getGraph({
            relasi: types,
            kelompok: groupFilter?.value || undefined,
            habitat: habitatFilter?.value || undefined,
        });
        fullGraph = graph;
        network.setData({ nodes: styleNodes(graph.nodes), edges: styleEdges(graph.edges) });
    };
    document.querySelectorAll('[data-graph-relation]').forEach((input) => input.addEventListener('change', applyRelations));
    groupFilter?.addEventListener('change', applyRelations);
    habitatFilter?.addEventListener('change', applyRelations);
    return network;
}

document.querySelectorAll('[data-graph]').forEach((node) => {
    mountGraph(node).catch((error) => {
        console.error(error);
        node.innerHTML = '<p class="p-5 text-sm text-rose-200">Knowledge graph belum dapat dimuat.</p>';
    });
});
document.querySelectorAll('[data-mini-graph]').forEach((node) => {
    mountGraph(node, true).catch((error) => {
        console.error(error);
        node.innerHTML = '<p class="p-4 text-xs text-muted">Pratinjau graf belum dapat dimuat.</p>';
    });
});
