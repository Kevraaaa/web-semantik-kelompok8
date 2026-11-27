import { cariSemantik, getFauna, getFaunaDetail, getFaunaPage, getFilterOptions, getRekomendasi } from './api.js';

const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[char]));

function commonsFilePageUrl(imageUrl) {
    const prefix = 'https://commons.wikimedia.org/wiki/Special:FilePath/';
    if (!imageUrl.startsWith(prefix)) {
        return null;
    }

    const filename = decodeURIComponent(imageUrl.slice(prefix.length).split('?')[0]);
    const pageTitle = encodeURIComponent(`File:${filename}`)
        .replace('%3A', ':')
        .replace(/%20/g, '_');

    return `https://commons.wikimedia.org/wiki/${pageTitle}`;
}

function cardMarkup(animal, semanticReasons = []) {
    const habitat = animal.habitat?.slice(0, 2).join(' · ') || 'Habitat belum tercatat';
    return `<article class="fauna-card group overflow-hidden rounded-2xl border border-soft surface card-hover" data-fauna-card data-slug="${escapeHtml(animal.slug)}">
        <div class="fauna-visual relative h-52">
            <img class="absolute inset-0 size-full object-cover opacity-55 mix-blend-screen transition duration-500 group-hover:scale-105" src="${escapeHtml(animal.gambar)}" alt="Gambar ${escapeHtml(animal.nama)}" data-fauna-image loading="lazy" decoding="async">
            <label class="absolute left-3 top-3 z-10 flex cursor-pointer items-center gap-2 rounded-full bg-black/35 px-3 py-1.5 text-xs text-white backdrop-blur"><input type="checkbox" class="accent-fresh-grown" data-compare-check value="${escapeHtml(animal.slug)}"><span>Bandingkan</span></label>
            <div class="absolute inset-x-4 bottom-4 z-10 flex items-end justify-between"><div><p class="text-xs text-white/75">${escapeHtml(animal.kelompok)}</p><p class="mt-1 text-2xl" aria-hidden="true">${animal.ikon || '🌿'}</p></div><button type="button" class="grid size-9 place-items-center rounded-full border border-white/20 bg-black/25 text-lg text-white" data-favorite="${escapeHtml(animal.slug)}" aria-label="Tambahkan ${escapeHtml(animal.nama)} ke favorit" aria-pressed="false">♡</button></div>
        </div>
        <div class="p-5">
            <div class="flex items-start justify-between gap-3"><div><h3 class="font-display text-xl">${escapeHtml(animal.nama)}</h3><p class="mt-1 text-xs italic text-muted">${escapeHtml(animal.nama_ilmiah)}</p></div><a class="grid size-9 shrink-0 place-items-center rounded-full border border-soft" href="/eksplorasi/${encodeURIComponent(animal.slug)}" aria-label="Lihat detail ${escapeHtml(animal.nama)}">↗</a></div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2"><span class="conservation-chip">${escapeHtml(animal.status_iucn || 'Status konservasi belum tercatat')}</span><span class="text-[.68rem] text-muted">${escapeHtml(habitat)}</span></div>
            ${semanticReasons.length ? `<div class="semantic-reasons mt-4 rounded-xl border border-azul-petroleo/25 bg-azul-petroleo/10 p-3"><p class="semantic-reasons-title text-[.65rem] font-semibold uppercase tracking-wide">Alasan kecocokan</p><ul class="mt-2 grid gap-1 text-xs text-muted">${semanticReasons.map((reason) => `<li>• ${escapeHtml(reason)}</li>`).join('')}</ul></div>` : ''}
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
    const semanticStatus = document.querySelector('[data-semantic-status]');
    const resultCount = document.querySelector('[data-result-count]');
    const emptyState = document.querySelector('[data-fauna-empty]');
    const errorState = document.querySelector('[data-fauna-error]');
    const activeFilters = document.querySelector('[data-active-filters]');
    const loadMore = document.querySelector('[data-fauna-load-more]');
    const sidebar = document.querySelector('#filter-fauna');
    const drawer = document.querySelector('[data-filter-drawer]');
    const drawerContent = drawer?.querySelector('[data-filter-drawer-content]');
    const pageSize = 12;
    let page = 1;
    let semanticResults = null;
    let renderSequence = 0;

    const entriesFor = (options) => Array.isArray(options)
        ? options.map((value) => [value, value])
        : Object.entries(options || {});

    const renderOptions = (options) => {
        for (const key of ['status', 'habitat']) {
            const container = sidebar.querySelector(`[data-filter-options="${key}"]`);
            container.innerHTML = entriesFor(options[key]).map(([value, label]) => `<label class="flex items-center gap-2"><input class="accent-fresh-grown" type="checkbox" name="${key}" value="${escapeHtml(value)}"><span>${escapeHtml(label)}</span></label>`).join('');
        }

        for (const key of ['makanan', 'kelompok', 'famili', 'ancaman']) {
            const select = sidebar.querySelector(`[data-filter-select="${key}"]`);
            const defaultLabel = select.options[0].textContent;
            select.innerHTML = `<option value="">${escapeHtml(defaultLabel)}</option>${entriesFor(options[key]).map(([value, label]) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`).join('')}`;
        }
    };

    const selectedFilters = () => {
        const selected = (name) => [...new Set([...sidebar.querySelectorAll(`[name="${name}"]:checked`)].map((input) => input.value))];
        const selects = Object.fromEntries([...sidebar.querySelectorAll('[data-filter-select]')].map((select) => [select.dataset.filterSelect, select.value]));

        return {
            status: selected('status'),
            habitat: selected('habitat'),
            ...Object.fromEntries(Object.entries(selects).map(([key, value]) => [key, value ? [value] : []])),
        };
    };

    const selectedLabels = () => [
        ...[...sidebar.querySelectorAll('input[type="checkbox"]:checked')].map((input) => input.closest('label').textContent.trim()),
        ...[...sidebar.querySelectorAll('[data-filter-select]')].filter((select) => select.value).map((select) => select.options[select.selectedIndex].textContent),
    ];

    const originalFilter = (input) => input.dataset.filterSelect
        ? sidebar.querySelector(`[data-filter-select="${input.dataset.filterSelect}"]`)
        : [...sidebar.querySelectorAll(`input[name="${input.name}"]`)].find((item) => item.value === input.value);

    const setupFilterDrawer = () => {
        if (!drawerContent || !sidebar) {
            return;
        }

        drawerContent.replaceChildren(sidebar.querySelector('[data-filter-panel]').cloneNode(true));
        drawerContent.querySelectorAll('input, select').forEach((input) => {
            input.addEventListener('change', () => {
                const original = originalFilter(input);
                if (original) {
                    if (input.type === 'checkbox') {
                        original.checked = input.checked;
                    } else {
                        original.value = input.value;
                    }
                    original.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    };

    const syncFilterDrawer = () => {
        drawerContent?.querySelectorAll('input, select').forEach((input) => {
            const original = originalFilter(input);
            if (original) {
                if (input.type === 'checkbox') {
                    input.checked = original.checked;
                } else {
                    input.value = original.value;
                }
            }
        });
    };

    try {
        renderOptions(await getFilterOptions());
        setupFilterDrawer();
    } catch (error) {
        console.error(error);
        resultGrid.innerHTML = '';
        errorState.classList.remove('hidden');
        resultCount.textContent = 'Filter fauna tidak tersedia';
        return;
    }

    const render = async (append = false) => {
        const requestId = ++renderSequence;
        const filters = selectedFilters();
        const query = queryInput.value.trim();
        const semantic = semanticToggle.checked && query;
        const targetPage = append ? page + 1 : 1;

        if (!append) {
            page = 1;
            semanticResults = null;
            resultGrid.innerHTML = Array.from({ length: 6 }, () => '<div class="skeleton h-80 rounded-2xl" aria-hidden="true"></div>').join('');
            emptyState.classList.add('hidden');
            errorState.classList.add('hidden');
            loadMore.classList.add('hidden');
        }
        loadMore.disabled = true;
        activeFilters.innerHTML = selectedLabels()
            .map((item) => `<span class="pill text-xs">${escapeHtml(item)} <span aria-hidden="true">×</span></span>`).join('');

        try {
            let results;
            let total;
            if (semantic) {
                if (!semanticResults) {
                    const [matches, filteredFauna] = await Promise.all([cariSemantik(query), getFauna(filters)]);
                    if (requestId !== renderSequence) {
                        return;
                    }
                    const allowed = new Set(filteredFauna.map((item) => item.slug));
                    semanticResults = matches.filter((item) => allowed.has(item.fauna.slug));
                }

                total = semanticResults.length;
                results = semanticResults.slice((targetPage - 1) * pageSize, targetPage * pageSize);
            } else {
                const response = await getFaunaPage({
                    ...filters,
                    query,
                    page: targetPage,
                    per_page: pageSize,
                });
                if (requestId !== renderSequence) {
                    return;
                }
                results = response.data;
                total = response.meta.total;
            }

            if (requestId !== renderSequence) {
                return;
            }

            page = targetPage;
            const markup = results.map((item) => semantic
                ? cardMarkup(item.fauna, item.alasan_kecocokan)
                : cardMarkup(item)).join('');
            if (append) {
                resultGrid.insertAdjacentHTML('beforeend', markup);
            } else {
                resultGrid.innerHTML = markup;
            }
            resultCount.textContent = `${total} fauna ditemukan${semantic ? ' · diurutkan menurut kecocokan semantik' : ''}`;
            emptyState.classList.toggle('hidden', total > 0);
            resultGrid.classList.toggle('hidden', results.length === 0 && !append);
            loadMore.classList.toggle('hidden', page * pageSize >= total);
            loadMore.disabled = false;
            document.dispatchEvent(new CustomEvent('jf:results-rendered'));
        } catch (error) {
            if (requestId !== renderSequence) {
                return;
            }
            console.error(error);
            if (!append) {
                resultGrid.innerHTML = '';
            }
            errorState.classList.remove('hidden');
            resultCount.textContent = 'Data fauna tidak tersedia';
            loadMore.disabled = false;
        }
    };

    const debounceRender = (() => {
        let timer;
        return () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(render, 180);
        };
    })();

    const searchParams = new URLSearchParams(location.search);
    if (searchParams.has('q')) queryInput.value = searchParams.get('q');

    const syncSemanticMode = () => {
        const active = semanticToggle.checked;
        semanticHelp.classList.toggle('hidden', !active);
        semanticStatus?.classList.toggle('hidden', !active);
        semanticToggle.setAttribute('aria-checked', String(active));
        queryInput.placeholder = active
            ? 'Ceritakan fauna yang kamu cari, misalnya pemakan buah di hutan...'
            : 'Cari nama atau nama ilmiah...';
    };

    const semanticRequested = searchParams.get('mode') === 'semantic';
    semanticToggle.checked = semanticRequested;
    syncSemanticMode();
    if (semanticRequested) {
        requestAnimationFrame(() => queryInput.focus({ preventScroll: true }));
    }
    queryInput.addEventListener('input', debounceRender);
    semanticToggle.addEventListener('change', () => {
        syncSemanticMode();
        render();
    });
    document.addEventListener('change', (event) => {
        if (event.target.matches('#filter-fauna [name="status"], #filter-fauna [name="habitat"], #filter-fauna [data-filter-select]')) {
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

    loadMore.addEventListener('click', () => render(true));
    document.querySelector('[data-mobile-filter]')?.addEventListener('click', () => {
        syncFilterDrawer();
        drawer?.classList.remove('hidden');
    });
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
            const picks = ['orangutan-kalimantan', 'nasalis-larvatus', 'batagur-borneoensis', 'betta-albimarginata'];
            const faunaBySlug = new Map(fauna.map((animal) => [animal.slug, animal]));
            featured.innerHTML = picks.map((slug) => faunaBySlug.get(slug)).filter(Boolean).map((animal) => cardMarkup(animal, [])).join('');
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
            recommendations.innerHTML = items.slice(0, 4).map((animal) => `<a href="/eksplorasi/${encodeURIComponent(animal.slug)}" class="group grid min-w-20 shrink-0 justify-items-center gap-2 text-center"><span class="fauna-visual relative grid size-20 place-items-center overflow-hidden rounded-full border border-soft text-3xl transition group-hover:-translate-y-1"><img class="absolute inset-0 size-full object-cover opacity-60" src="${escapeHtml(animal.gambar)}" alt="" data-fauna-image loading="lazy" decoding="async"><span class="relative z-10">${animal.ikon || '🌿'}</span></span><span class="text-xs text-muted group-hover:text-fresh-grown">${escapeHtml(animal.nama)}</span></a>`).join('');
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
        const conservationStatus = animal.status_iucn || 'Status konservasi belum tercatat';
        const imageSource = commonsFilePageUrl(animal.gambar);
        container.innerHTML = `<div class="grid gap-8 lg:grid-cols-[1.05fr_.95fr]">
            <div><div class="fauna-visual relative grid h-80 place-items-center overflow-hidden rounded-3xl text-8xl lg:h-[31rem]" role="img" aria-label="Ilustrasi ${escapeHtml(animal.nama)}"><img class="absolute inset-0 size-full object-cover opacity-50 mix-blend-screen" src="${escapeHtml(animal.gambar)}" alt="" data-fauna-image decoding="async"><span class="relative z-10">${animal.ikon || '🌿'}</span></div>${imageSource ? `<p class="mt-2 text-xs text-muted"><a href="${escapeHtml(imageSource)}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2">Sumber dan lisensi gambar di Wikimedia Commons</a></p>` : ''}</div>
            <div class="grid content-center gap-4"><p class="eyebrow">${escapeHtml(animal.kelompok)}</p><h1 class="font-display text-4xl leading-tight sm:text-5xl">${escapeHtml(animal.nama)}</h1><p class="text-sm italic text-muted">${escapeHtml(animal.nama_ilmiah)}</p><div><span class="conservation-chip">${escapeHtml(conservationStatus)}</span></div><p class="mt-2 text-sm leading-7 text-muted">${escapeHtml(animal.deskripsi)}</p>
            <div class="flex flex-wrap gap-2"><button class="btn-secondary" data-favorite="${escapeHtml(animal.slug)}" type="button" aria-pressed="false">♡ Simpan favorit</button></div>
            <div class="mt-3 grid grid-cols-2 gap-3">${[['Habitat', animal.habitat], ['Makanan', animal.makanan], ['Ancaman', animal.ancaman], ['Wilayah', animal.wilayah]].map(([label, items]) => `<section class="rounded-xl border border-soft bg-[var(--page)] p-4"><h2 class="text-xs font-semibold text-fresh-grown">${label}</h2><ul class="mt-2 grid gap-1 text-xs leading-5 text-muted">${(items || []).length ? items.map((item) => `<li>• ${escapeHtml(item)}</li>`).join('') : '<li>Belum tercatat</li>'}</ul></section>`).join('')}</div>
            <section class="rounded-xl border border-soft bg-[var(--page)] p-4"><h2 class="text-xs font-semibold text-fresh-grown">Status konservasi & ancaman utama</h2><p class="mt-2 text-xs text-muted">Kategori saat ini: ${escapeHtml(conservationStatus)} <span class="text-amber-200">· Perlu verifikasi sumber IUCN.</span></p><p class="mt-2 text-xs leading-5 text-muted">${animal.ancaman.map(escapeHtml).join(' · ')}</p></section>
            </div></div>`;
        document.querySelector('[data-detail-sources]').innerHTML = (animal.sumber || []).map((source) => `<li class="rounded-xl border border-soft bg-[var(--page)] p-3"><a class="break-all" href="${escapeHtml(source)}" target="_blank" rel="noopener noreferrer">${escapeHtml(source)}</a></li>`).join('');
        const mapNode = document.querySelector('[data-detail-map]');
        if (mapNode) mapNode.dataset.mapSlug = animal.slug;
        const suggestions = await getRekomendasi(slug);
        document.querySelector('[data-detail-recommendations]').innerHTML = suggestions.length
            ? suggestions.map((item) => `<a class="grid min-w-16 shrink-0 justify-items-center gap-2 text-center" href="/eksplorasi/${encodeURIComponent(item.slug)}"><span class="fauna-visual relative grid size-16 place-items-center overflow-hidden rounded-full text-2xl"><img class="absolute inset-0 size-full object-cover opacity-60" src="${escapeHtml(item.gambar)}" alt="" data-fauna-image loading="lazy" decoding="async"><span class="relative z-10">${item.ikon || '🌿'}</span></span><span class="text-xs text-muted">${escapeHtml(item.nama)}</span></a>`).join('')
            : '<p class="text-sm text-muted">Belum ada fauna terkait yang dapat ditampilkan.</p>';
        document.dispatchEvent(new CustomEvent('jf:results-rendered'));
    } catch (error) {
        console.error(error);
        container.innerHTML = '<div role="alert" class="rounded-2xl border border-rose-300/20 p-6 text-sm text-rose-100">Detail fauna tidak ditemukan atau belum dapat dimuat. <a class="underline" href="/eksplorasi">Kembali ke katalog</a>.</div>';
    }
}

setupDiscovery();
setupHome();
setupDetail();
