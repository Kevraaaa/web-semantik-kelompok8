import { daftar, getFauna, getLaporanSaya, getProfil, masuk } from './api.js';
import { showToast } from './toast.js';

const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[char]));

function setFieldError(form, name, message) {
    const error = form.querySelector(`[data-error-for="${name}"]`);
    if (!error) return;
    error.textContent = message;
    error.classList.toggle('hidden', !message);
}

function validateAuthForm(form, isRegister = false) {
    let valid = true;
    const data = new FormData(form);
    for (const name of ['name', 'email', 'password', 'password_confirmation']) setFieldError(form, name, '');
    const email = String(data.get('email') || '');
    const password = String(data.get('password') || '');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        setFieldError(form, 'email', 'Masukkan alamat email yang valid.');
        valid = false;
    }
    if (password.length < (isRegister ? 8 : 6)) {
        setFieldError(form, 'password', `Kata sandi minimal ${isRegister ? 8 : 6} karakter.`);
        valid = false;
    }
    if (isRegister && String(data.get('name') || '').trim().length < 2) {
        setFieldError(form, 'name', 'Nama minimal 2 karakter.');
        valid = false;
    }
    if (isRegister && password !== data.get('password_confirmation')) {
        setFieldError(form, 'password_confirmation', 'Ulangi kata sandi dengan benar.');
        valid = false;
    }
    if (isRegister && !data.get('consent')) {
        setFieldError(form, 'consent', 'Persetujuan diperlukan untuk melanjutkan.');
        valid = false;
    }
    return valid;
}

document.querySelector('[data-auth-form]')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validateAuthForm(event.currentTarget)) return;
    try {
        await masuk(Object.fromEntries(new FormData(event.currentTarget).entries()));
        window.__DUMMY_LOGGED_IN__ = true;
        showToast('Fitur ini akan aktif setelah backend terhubung. Kamu masuk dalam mode demo.');
        window.setTimeout(() => { window.location.href = '/profil'; }, 650);
    } catch (error) {
        console.error(error);
        showToast('Masuk belum dapat diproses.', 'error');
    }
});

document.querySelector('[data-register-form]')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validateAuthForm(event.currentTarget, true)) return;
    try {
        const { name, email, password, password_confirmation: passwordConfirmation } = Object.fromEntries(new FormData(event.currentTarget).entries());
        await daftar({ name, email, password, password_confirmation: passwordConfirmation });
        window.__DUMMY_LOGGED_IN__ = true;
        showToast('Fitur ini akan aktif setelah backend terhubung. Akun demo siap digunakan.');
        window.setTimeout(() => { window.location.href = '/profil'; }, 650);
    } catch (error) {
        console.error(error);
        showToast('Pendaftaran belum dapat diproses.', 'error');
    }
});

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
        const [profile, fauna, reports] = await Promise.all([getProfil(), getFauna(), getLaporanSaya()]);
        document.querySelector('[data-profile-name]').textContent = profile.nama;
        document.querySelector('[data-profile-email]').textContent = `${profile.email} · Bergabung ${profile.bergabung}`;
        const localFavorites = JSON.parse(localStorage.getItem('jf_favorites') || 'null');
        const favoriteSlugs = new Set(localFavorites || profile.favorit);
        const favorites = fauna.filter((animal) => favoriteSlugs.has(animal.slug));
        document.querySelector('[data-favorite-count]').textContent = `${favorites.length} tersimpan`;
        document.querySelector('[data-profile-favorites]').innerHTML = favorites.map((animal) => `<article class="rounded-2xl border border-soft surface p-4" data-profile-favorite-card><div class="flex items-center gap-3"><span class="fauna-visual grid size-14 shrink-0 place-items-center rounded-xl text-2xl">${animal.ikon || '🌿'}</span><div class="min-w-0 flex-1"><h3 class="font-display truncate">${escapeHtml(animal.nama)}</h3><p class="truncate text-xs italic text-muted">${escapeHtml(animal.nama_ilmiah)}</p></div><button class="rounded-full px-3 py-2 text-xs text-rose-200 hover:bg-rose-900/20" type="button" data-favorite="${escapeHtml(animal.slug)}" aria-label="Hapus ${escapeHtml(animal.nama)} dari favorit">Hapus</button></div><a class="mt-4 inline-flex text-xs font-semibold text-fresh-grown" href="/eksplorasi/${encodeURIComponent(animal.slug)}">Lihat detail →</a></article>`).join('') || '<p class="text-sm text-muted">Belum ada fauna favorit.</p>';

        document.querySelector('[data-profile-history]').innerHTML = profile.riwayat_pencarian.map((item) => `<article class="flex flex-col justify-between gap-3 rounded-2xl border border-soft surface p-4 sm:flex-row sm:items-center"><div><p class="text-sm font-medium">${escapeHtml(item.kata_kunci)}</p><p class="mt-1 text-xs text-muted">${escapeHtml(item.waktu)}</p></div><div class="flex gap-2"><a class="btn-secondary min-h-9 px-3 py-2 text-xs" href="/eksplorasi?q=${encodeURIComponent(item.kata_kunci)}">Ulangi pencarian</a><button class="btn-secondary min-h-9 px-3 py-2 text-xs" data-history-delete type="button">Hapus</button></div></article>`).join('');
        document.querySelectorAll('[data-history-delete]').forEach((button) => button.addEventListener('click', () => {
            button.closest('article').remove();
            showToast('Riwayat dihapus dari tampilan demo.');
        }));

        const statuses = { menunggu: 'bg-amber-900/35 text-amber-100', diverifikasi: 'bg-emerald-900/35 text-emerald-100', ditolak: 'bg-rose-900/35 text-rose-100' };
        document.querySelector('[data-profile-reports]').innerHTML = reports.map((report) => `<article class="grid gap-3 rounded-2xl border border-soft surface p-4 sm:grid-cols-[1fr_auto] sm:items-center"><div><h3 class="font-medium">${escapeHtml(report.nama)}</h3><p class="mt-1 text-xs text-muted">${escapeHtml(report.tanggal)} · ${escapeHtml(report.lokasi.teks)}</p>${report.catatan ? `<p class="mt-2 text-xs text-muted">Catatan: ${escapeHtml(report.catatan)}</p>` : ''}</div><span class="w-fit rounded-full px-3 py-1 text-xs ${statuses[report.status] || ''}">${escapeHtml(report.status)}</span></article>`).join('');
        document.querySelector('[data-profile-activity]').innerHTML = `<ol class="relative ml-2 grid gap-5 border-l border-soft pl-6">${profile.aktivitas.map((item) => `<li class="relative"><span class="absolute -left-[2rem] grid size-7 place-items-center rounded-full bg-fig-leaf/25 text-fresh-grown">${escapeHtml(item.ikon)}</span><p class="text-sm">${escapeHtml(item.judul)}</p><p class="mt-1 text-xs text-muted">${escapeHtml(item.waktu)}</p></li>`).join('')}</ol>`;
    } catch (error) {
        console.error(error);
        document.querySelector('[data-profile-favorites]').innerHTML = '<p class="text-sm text-rose-200">Data profil belum dapat dimuat.</p>';
    }
}

setupProfile();
