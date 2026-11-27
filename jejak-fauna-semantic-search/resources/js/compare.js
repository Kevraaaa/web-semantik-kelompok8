import { bandingkan } from './api.js';
import { showToast } from './toast.js';

const storageKey = 'jf_compare';
const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[char]));

function readSelection() {
    try {
        const fromUrl = new URLSearchParams(location.search).get('slugs');
        const slugs = fromUrl ? fromUrl.split(',').filter(Boolean) : JSON.parse(localStorage.getItem(storageKey) || '[]');
        return [...new Set(slugs)].slice(0, 3);
    } catch (error) {
        console.error('Pilihan perbandingan lokal tidak valid.', error);
        return [];
    }
}

let selected = readSelection();

function updateBar() {
    const bar = document.querySelector('[data-compare-bar]');
    if (!bar) return;
    bar.classList.toggle('hidden', selected.length === 0);
    bar.querySelector('[data-compare-count]').textContent = selected.length;
    const link = bar.querySelector('[data-compare-link]');
    if (link) link.href = `/bandingkan?slugs=${encodeURIComponent(selected.join(','))}`;
    localStorage.setItem(storageKey, JSON.stringify(selected));
    document.querySelectorAll('[data-compare-check]').forEach((input) => {
        input.checked = selected.includes(input.value);
        input.disabled = selected.length >= 3 && !input.checked;
    });
}

document.addEventListener('change', (event) => {
    const checkbox = event.target.closest('[data-compare-check]');
    if (!checkbox) return;
    if (checkbox.checked && !selected.includes(checkbox.value)) {
        if (selected.length >= 3) {
            checkbox.checked = false;
            showToast('Maksimal 3 fauna dapat dibandingkan.', 'error');
            return;
        }
        selected.push(checkbox.value);
    } else {
        selected = selected.filter((slug) => slug !== checkbox.value);
    }
    updateBar();
});

document.querySelector('[data-compare-clear]')?.addEventListener('click', () => {
    selected = [];
    updateBar();
});

async function renderComparison() {
    const container = document.querySelector('[data-compare-results]');
    const empty = document.querySelector('[data-compare-empty]');
    if (!container) return;
    if (selected.length < 2) {
        empty?.classList.remove('hidden');
        container.innerHTML = '';
        return;
    }
    empty?.classList.add('hidden');
    container.innerHTML = '<div class="skeleton h-64 rounded-2xl"></div>';
    try {
        const animals = await bandingkan(selected);
        const rows = [
            ['Nama ilmiah', (animal) => animal.nama_ilmiah],
            ['Kelompok', (animal) => animal.kelompok],
            ['Habitat', (animal) => animal.habitat.join(', ')],
            ['Makanan', (animal) => animal.makanan.join(', ')],
            ['Status konservasi', (animal) => animal.status_iucn],
            ['Ancaman', (animal) => animal.ancaman.join(', ')],
            ['Wilayah persebaran', (animal) => animal.sebaran.map((place) => `${place.lat.toFixed(2)}, ${place.lng.toFixed(2)}`).join(' · ')],
            ['Karakteristik', (animal) => animal.karakteristik.join(', ')],
        ];
        container.innerHTML = `<div class="overflow-x-auto rounded-2xl border border-soft surface"><table class="w-full min-w-[760px] border-collapse text-left text-sm"><thead><tr><th class="sticky left-0 z-10 w-40 bg-[var(--surface-raised)] p-4 text-xs uppercase tracking-wide text-muted">Aspek</th>${animals.map((animal) => `<th class="min-w-52 border-l border-soft bg-[var(--surface-raised)] p-4 align-top"><div class="fauna-visual mb-3 grid h-28 place-items-center rounded-xl text-4xl">${escapeHtml(animal.ikon)}</div><span class="font-display text-lg">${escapeHtml(animal.nama)}</span><div class="mt-1 text-xs italic text-muted">${escapeHtml(animal.nama_ilmiah)}</div></th>`).join('')}</tr></thead><tbody class="divide-y divide-white/5">${rows.map(([label, getter]) => {
            const values = animals.map(getter);
            const shared = new Set(values).size === 1;
            return `<tr><th class="sticky left-0 bg-[var(--surface)] p-4 text-xs font-semibold ${shared ? 'text-emerald-200' : 'text-fresh-grown'}"><span class="mb-1 block text-[.58rem] uppercase tracking-wide">${shared ? '✓ Sama' : '↗ Berbeda'}</span>${escapeHtml(label)}</th>${values.map((value) => `<td class="border-l border-soft p-4 text-xs leading-5 ${shared ? 'bg-emerald-400/[.035]' : 'bg-fresh-grown/[.035]'}">${escapeHtml(value)}</td>`).join('')}</tr>`;
        }).join('')}</tbody></table></div><p class="mt-3 text-xs text-muted">Sorotan lembut menandai informasi yang berbeda. Lokasi sebaran merupakan data ilustratif.</p>`;
    } catch (error) {
        console.error(error);
        container.innerHTML = '<p class="rounded-xl border border-rose-300/20 p-5 text-sm text-rose-100" role="alert">Data perbandingan belum dapat dimuat.</p>';
    }
}

updateBar();
renderComparison();
