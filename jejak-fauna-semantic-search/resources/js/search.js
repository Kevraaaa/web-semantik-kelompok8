import { cariSemantik, getFauna, getFaunaDetail, getRekomendasi } from './api.js';

const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[char]));

function cardMarkup(animal, semanticReasons = []) {
    return `<article class="fauna-card group overflow-hidden rounded-2xl border border-soft surface card-hover" data-fauna-card data-slug="${escapeHtml(animal.slug)}">
        <div class="fauna-visual relative h-52">
            <img class="absolute inset-0 size-full object-cover opacity-55 mix-blend-screen transition duration-500 group-hover:scale-105" src="${escapeHtml(animal.gambar)}" alt="Placeholder foto ${escapeHtml(animal.nama)}" loading="lazy">
            <label class="absolute left-3 top-3 z-10 flex cursor-pointer items-center gap-2 rounded-full bg-black/35 px-3 py-1.5 text-xs text-white backdrop-blur"><input type="checkbox" class="accent-fresh-grown" data-compare-check value="${escapeHtml(animal.slug)}"><span>Bandingkan</span></label>
            <div class="absolute inset-x-4 bottom-4 z-10 flex items-end justify-between"><div><p class="text-xs text-white/75">${escapeHtml(animal.kelompok)}</p><p class="mt-1 text-2xl" aria-hidden="true">${animal.ikon || '🌿'}</p></div><button type="button" class="grid size-9 place-items-center rounded-full border border-white/20 bg-black/25 text-lg text-white" data-favorite="${escapeHtml(animal.slug)}" aria-label="Tambahkan ${escapeHtml(animal.nama)} ke favorit" aria-pressed="false">♡</button></div>
        </div>
        <div class="p-5">
            <div class="flex items-start justify-between gap-3"><div><h3 class="font-display text-xl">${escapeHtml(animal.nama)}</h3><p class="mt-1 text-xs italic text-muted">${escapeHtml(animal.nama_ilmiah)}</p></div><a class="grid size-9 shrink-0 place-items-center rounded-full border border-soft" href="/eksplorasi/${encodeURIComponent(animal.slug)}" aria-label="Lihat detail ${escapeHtml(animal.nama)}">↗</a></div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2"><span class="conservation-chip">${escapeHtml(animal.status_iucn)}</span><span class="text-[.68rem] text-muted">${escapeHtml(animal.ekosistem)}</span></div>
            ${semanticReasons.length ? `<div class="mt-4 rounded-xl border border-azul-petroleo/25 bg-azul-petroleo/10 p-3"><p class="text-[.65rem] font-semibold uppercase tracking-wide text-sky-100">Alasan kecocokan</p><ul class="mt-2 grid gap-1 text-xs text-muted">${semanticReasons.map((reason) => `<li>• ${escapeHtml(reason)}</li>`).join('')}</ul></div>` : ''}
        </div>
    </article>`;
}

async function setupDiscovery() {
    const resultGrid = document.querySelector('[data-fauna-results]');
    if (!resultGrid) {
        return;
    }

    const queryInput = document.querySelector('[data-search-input]');
    const semanticToggle = document.querySelector('[data-semantic-toggle]');
    const semanticHelp = document.querySelector('[data-semantic-help]');
    const resultCount = document.querySelector('[data-result-count]');
    const emptyState = document.querySelector('[data-fauna-empty]');
    const errorState = document.querySelector('[data-fauna-error]');
    const activeFilters = document.querySelector('[data-active-filters]');

    const render = async () => {
        const selected = (name) => [...document.querySelectorAll(`[name="${name}"]:checked`)].map((input) => input.value);
        const selects = Object.fromEntries([...document.querySelectorAll('[data-filter-select]')].map((select) => [select.dataset.filterSelect, select.value]));
        const filters = { ...selects, status: selected('status'), habitat: selected('habitat') };
        const query = queryInput.value.trim();
        resultGrid.innerHTML = Array.from({ length: 6 }, () => '<div class="skeleton h-80 rounded-2xl" aria-hidden="true"></div>').join('');
        emptyState.classList.add('hidden');
        errorState.classList.add('hidden');
        activeFilters.innerHTML = [...filters.status, ...filters.habitat, ...Object.values(selects).filter(Boolean)]
            .map((item) => `<span class="pill text-xs">${escapeHtml(item)} <span aria-hidden="true">×</span></span>`).join('');

        try {
            let results;
            if (semanticToggle.checked && query) {
                results = await cariSemantik(query);
                const filtered = await getFauna(filters);
                const allowed = new Set(filtered.map((item) => item.slug));
                results = results.filter((item) => allowed.has(item.fauna.slug));
                resultGrid.innerHTML = results.map((item) => cardMarkup(item.fauna, item.alasan_kecocokan)).join('');
            } else {
                results = await getFauna({ ...filters, query });
                resultGrid.innerHTML = results.map((animal) => cardMarkup(animal)).join('');
            }
            const count = results.length;
            resultCount.textContent = `${count} fauna ditemukan${semanticToggle.checked && query ? ' · diurutkan menurut kecocokan semantik' : ''}`;
            emptyState.classList.toggle('hidden', count > 0);
            resultGrid.classList.toggle('hidden', count === 0);
            document.dispatchEvent(new CustomEvent('jf:results-rendered'));
        } catch (error) {
            console.error(error);
            resultGrid.innerHTML = '';
            errorState.classList.remove('hidden');
            resultCount.textContent = 'Data fauna tidak tersedia';
        }
    };

    const debounceRender = (() => {
        let timer;
        return () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(render, 180);
        };
    })();

    if (new URLSearchParams(location.search).has('q')) {
        queryInput.value = new URLSearchParams(location.search).get('q');
    }
    queryInput.addEventListener('input', debounceRender);
    semanticToggle.addEventListener('change', () => {
        semanticHelp.classList.toggle('hidden', !semanticToggle.checked);
        queryInput.placeholder = semanticToggle.checked
            ? 'Ceritakan fauna yang kamu cari, misalnya pemakan buah di hutan...'
            : 'Cari nama atau nama ilmiah...';
        render();
    });
    document.addEventListener('change', (event) => {
        if (event.target.matches('[name="status"], [name="habitat"], [data-filter-select]')) {
            render();
        }
    });
    const resetFilters = () => {
        document.querySelectorAll('[name="status"], [name="habitat"]').forEach((input) => { input.checked = false; });
        document.querySelectorAll('[data-filter-select]').forEach((select) => { select.value = ''; });
        queryInput.value = '';
        render();
    };
    document.querySelector('[data-filter-reset]')?.addEventListener('click', resetFilters);
    document.querySelector('[data-filter-reset-mobile]')?.addEventListener('click', resetFilters);
    document.querySelectorAll('[data-query-example]').forEach((button) => {
        button.addEventListener('click', () => {
            queryInput.value = button.dataset.queryExample;
            render();
            queryInput.focus();
        });
    });

    const drawer = document.querySelector('[data-filter-drawer]');
    const sidebar = document.querySelector('#filter-fauna');
    const drawerContent = drawer?.querySelector('[data-filter-drawer-content]');
    if (drawerContent && sidebar) {
        drawerContent.append(sidebar.querySelector('[data-filter-panel]').cloneNode(true));
        drawerContent.querySelectorAll('input, select').forEach((input) => {
            input.addEventListener('change', () => {
                const original = [...sidebar.querySelectorAll('input, select')].find((item) => item.name === input.name && item.value === input.value)
                    || sidebar.querySelector(`[data-filter-select="${input.dataset.filterSelect}"]`);
                if (original) {
                    if (input.type === 'checkbox') original.checked = input.checked;
                    else original.value = input.value;
                    original.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }
    document.querySelector('[data-mobile-filter]')?.addEventListener('click', () => drawer?.classList.remove('hidden'));
    document.querySelectorAll('[data-filter-close]').forEach((button) => button.addEventListener('click', () => drawer?.classList.add('hidden')));
    drawer?.addEventListener('click', (event) => {
        if (event.target === drawer) drawer.classList.add('hidden');
    });

    render();
}

async function setupHome() {
    const featured = document.querySelector('[data-featured-fauna]');
    if (featured) {
        try {
            const fauna = await getFauna();
            const picks = ['orangutan-kalimantan', 'bekantan', 'pesut-mahakam', 'beruang-madu'];
            featured.innerHTML = fauna.filter((animal) => picks.includes(animal.slug)).map((animal) => cardMarkup(animal, [])).join('');
            document.dispatchEvent(new CustomEvent('jf:results-rendered'));
        } catch (error) {
            console.error(error);
            featured.innerHTML = '<p class="col-span-full text-sm text-muted">Fauna pilihan belum dapat dimuat.</p>';
        }
    }

    const recommendations = document.querySelector('[data-recommendations]');
    if (recommendations) {
        try {
            const items = await getRekomendasi('orangutan-kalimantan');
            recommendations.innerHTML = items.slice(0, 4).map((animal) => `<a href="/eksplorasi/${encodeURIComponent(animal.slug)}" class="group grid min-w-20 shrink-0 justify-items-center gap-2 text-center"><span class="fauna-visual grid size-20 place-items-center rounded-full border border-soft text-3xl transition group-hover:-translate-y-1">${animal.ikon}</span><span class="text-xs text-muted group-hover:text-fresh-grown">${escapeHtml(animal.nama)}</span></a>`).join('');
        } catch (error) {
            console.error(error);
        }
    }
}

async function setupDetail() {
    const container = document.querySelector('[data-detail-content]');
    if (!container) return;
    const slug = document.querySelector('[data-detail-slug]').dataset.detailSlug;

    try {
        const animal = await getFaunaDetail(slug);
        container.innerHTML = `<div class="grid gap-8 lg:grid-cols-[1.05fr_.95fr]">
            <div class="fauna-visual relative grid h-80 place-items-center overflow-hidden rounded-3xl text-8xl lg:h-[31rem]" role="img" aria-label="Ilustrasi ${escapeHtml(animal.nama)}"><img class="absolute inset-0 size-full object-cover opacity-50 mix-blend-screen" src="${escapeHtml(animal.gambar)}" alt=""><span class="relative z-10">${animal.ikon}</span></div>
            <div class="grid content-center gap-4"><p class="eyebrow">${escapeHtml(animal.kelompok)}</p><h1 class="font-display text-4xl leading-tight sm:text-5xl">${escapeHtml(animal.nama)}</h1><p class="text-sm italic text-muted">${escapeHtml(animal.nama_ilmiah)}</p><div><span class="conservation-chip">${escapeHtml(animal.status_iucn)}</span></div><p class="mt-2 text-sm leading-7 text-muted">${escapeHtml(animal.deskripsi)}</p>
            <div class="flex flex-wrap gap-2"><button class="btn-secondary" data-favorite="${escapeHtml(animal.slug)}" type="button" aria-pressed="false">♡ Simpan favorit</button><a class="btn-secondary" href="/lapor?fauna=${encodeURIComponent(animal.slug)}">⌖ Laporkan Penemuan</a></div>
            <div class="mt-3 grid grid-cols-2 gap-3">${[['Habitat', animal.habitat], ['Makanan', animal.makanan], ['Karakteristik', animal.karakteristik], ['Ekosistem', [animal.ekosistem]]].map(([label, items]) => `<section class="rounded-xl border border-soft bg-[var(--page)] p-4"><h2 class="text-xs font-semibold text-fresh-grown">${label}</h2><ul class="mt-2 grid gap-1 text-xs leading-5 text-muted">${items.map((item) => `<li>• ${escapeHtml(item)}</li>`).join('')}</ul></section>`).join('')}</div>
            <section class="rounded-xl border border-soft bg-[var(--page)] p-4"><h2 class="text-xs font-semibold text-fresh-grown">Status konservasi & ancaman utama</h2><p class="mt-2 text-xs text-muted">Kategori saat ini: ${escapeHtml(animal.status_iucn)} <span class="text-amber-200">· Perlu verifikasi sumber IUCN.</span></p><p class="mt-2 text-xs leading-5 text-muted">${animal.ancaman.map(escapeHtml).join(' · ')}</p></section>
            </div></div>`;
        document.querySelector('[data-detail-sources]').innerHTML = animal.sumber.map((source) => `<li class="rounded-xl border border-soft bg-[var(--page)] p-3">↗ ${escapeHtml(source)}</li>`).join('');
        const mapNode = document.querySelector('[data-detail-map]');
        if (mapNode) mapNode.dataset.mapSlug = animal.slug;
        const suggestions = await getRekomendasi(slug);
        document.querySelector('[data-detail-recommendations]').innerHTML = suggestions.map((item) => `<a class="grid min-w-16 shrink-0 justify-items-center gap-2 text-center" href="/eksplorasi/${encodeURIComponent(item.slug)}"><span class="fauna-visual grid size-16 place-items-center rounded-full text-2xl">${item.ikon}</span><span class="text-xs text-muted">${escapeHtml(item.nama)}</span></a>`).join('');
        document.dispatchEvent(new CustomEvent('jf:results-rendered'));
    } catch (error) {
        console.error(error);
        container.innerHTML = '<div role="alert" class="rounded-2xl border border-rose-300/20 p-6 text-sm text-rose-100">Detail fauna tidak ditemukan atau belum dapat dimuat. <a class="underline" href="/eksplorasi">Kembali ke katalog</a>.</div>';
    }
}

setupDiscovery();
setupHome();
setupDetail();
