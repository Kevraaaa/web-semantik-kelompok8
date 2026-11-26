import { getFavoriteSlugs, toggleFavorit } from './api.js';
import { showToast } from './toast.js';

function favoriteSet() {
    try {
        return new Set(getFavoriteSlugs());
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
        showToast(active ? 'Fauna ditambahkan ke favorit akun ini.' : 'Fauna dihapus dari favorit akun ini.');
    } catch (error) {
        console.error(error);
        showToast('Favorit belum dapat diperbarui.', 'error');
    }
});

document.addEventListener('jf:results-rendered', paintFavorites);
paintFavorites();
