import { getProfil, toggleFavorit } from './api.js';
import { showToast } from './toast.js';

const storageKey = 'jf_favorites';

function favoriteSet() {
    try {
        return new Set(JSON.parse(localStorage.getItem(storageKey) || '[]'));
    } catch (error) {
        console.error('Favorit lokal tidak dapat dibaca.', error);
        return new Set();
    }
}

function paintFavorites() {
    const saved = favoriteSet();
    document.querySelectorAll('[data-favorite]').forEach((button) => {
        const active = saved.has(button.dataset.favorite);
        button.textContent = active ? '♥' : '♡';
        button.classList.toggle('text-rose-200', active);
        button.setAttribute('aria-pressed', String(active));
        button.setAttribute('aria-label', `${active ? 'Hapus dari' : 'Tambahkan ke'} favorit`);
    });
}

if (!localStorage.getItem(storageKey)) {
    getProfil().then((profile) => {
        localStorage.setItem(storageKey, JSON.stringify(profile.favorit));
        paintFavorites();
    }).catch((error) => console.error(error));
}

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-favorite]');
    if (!button) return;

    try {
        const active = await toggleFavorit(button.dataset.favorite);
        paintFavorites();
        if (document.body.dataset.page === 'profile') {
            button.closest('[data-profile-favorite-card]')?.remove();
            const count = document.querySelector('[data-favorite-count]');
            const remaining = document.querySelectorAll('[data-profile-favorite-card]').length;
            if (count) count.textContent = `${remaining} tersimpan`;
        }
        showToast(active ? 'Fauna ditambahkan ke favorit demo.' : 'Fauna dihapus dari favorit demo.');
    } catch (error) {
        console.error(error);
        showToast('Favorit belum dapat diperbarui.', 'error');
    }
});

document.addEventListener('jf:results-rendered', paintFavorites);
paintFavorites();
