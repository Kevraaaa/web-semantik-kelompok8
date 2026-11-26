import { getFauna, getFavoriteSlugs } from './api.js';

const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[char]));

async function setupProfile() {
    const root = document.querySelector('[data-profile-panel]');
    if (!root) return;
    const panels = [...document.querySelectorAll('[data-profile-panel]')];
    const tabs = [...document.querySelectorAll('[data-profile-tab]')];

    tabs.forEach((tab) => tab.addEventListener('click', () => {
        tabs.forEach((item) => {
            const active = item === tab;
            item.classList.toggle('active', active);
            item.setAttribute('aria-selected', String(active));
        });
        panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.profilePanel !== tab.dataset.profileTab));
    }));

    try {
        const fauna = await getFauna();
        const favoriteSlugs = new Set(getFavoriteSlugs());
        const favorites = fauna.filter((animal) => favoriteSlugs.has(animal.slug));
        document.querySelector('[data-favorite-count]').textContent = `${favorites.length} tersimpan`;
        document.querySelector('[data-profile-favorites]').innerHTML = favorites.map((animal) => `<article class="rounded-2xl border border-soft surface p-4" data-profile-favorite-card><div class="flex items-center gap-3"><span class="fauna-visual grid size-14 shrink-0 place-items-center rounded-xl text-2xl">${animal.ikon || '🌿'}</span><div class="min-w-0 flex-1"><h3 class="font-display truncate">${escapeHtml(animal.nama)}</h3><p class="truncate text-xs italic text-muted">${escapeHtml(animal.nama_ilmiah)}</p></div><button class="rounded-full px-3 py-2 text-xs text-rose-200 hover:bg-rose-900/20" type="button" data-favorite="${escapeHtml(animal.slug)}" aria-label="Hapus ${escapeHtml(animal.nama)} dari favorit">Hapus</button></div><a class="mt-4 inline-flex text-xs font-semibold text-fresh-grown" href="/eksplorasi/${encodeURIComponent(animal.slug)}">Lihat detail →</a></article>`).join('') || '<p class="text-sm text-muted">Belum ada fauna favorit.</p>';
        document.querySelector('[data-profile-history]').innerHTML = '<p class="text-sm text-muted">Riwayat pencarian akun ini belum tersedia.</p>';
        document.querySelector('[data-profile-reports]').innerHTML = '<p class="text-sm text-muted">Belum ada laporan warga yang terhubung ke akun ini.</p>';
        document.querySelector('[data-profile-activity]').innerHTML = '<p class="text-sm text-muted">Aktivitas akun ini belum tersedia.</p>';
    } catch (error) {
        console.error(error);
        document.querySelector('[data-profile-favorites]').innerHTML = '<p class="text-sm text-rose-200">Data profil belum dapat dimuat.</p>';
    }
}

setupProfile();
