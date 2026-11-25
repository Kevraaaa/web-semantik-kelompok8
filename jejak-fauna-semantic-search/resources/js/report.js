import { getFauna, kirimLaporan } from './api.js';
import { showToast } from './toast.js';

const form = document.querySelector('[data-report-form]');
if (form) {
    const search = document.querySelector('[data-fauna-autocomplete]');
    const options = document.querySelector('[data-autocomplete-results]');
    const slugInput = document.querySelector('[data-fauna-slug]');
    const fileInput = document.querySelector('[data-report-photos]');
    const preview = document.querySelector('[data-photo-previews]');
    const photoError = document.querySelector('[data-photo-error]');
    const errors = [...form.querySelectorAll('[data-error-for]')];
    let availableFauna = [];

    getFauna().then((items) => { availableFauna = items; }).catch((error) => console.error(error));
    const requestedFauna = new URLSearchParams(location.search).get('fauna');
    if (requestedFauna) {
        getFauna().then((items) => {
            const animal = items.find((item) => item.slug === requestedFauna);
            if (animal) {
                search.value = animal.nama;
                slugInput.value = animal.slug;
                form.elements.nama_bebas.value = animal.nama;
            }
        }).catch((error) => console.error(error));
    }

    search.addEventListener('input', () => {
        slugInput.value = '';
        const query = search.value.toLocaleLowerCase('id').trim();
        const matches = availableFauna.filter((animal) => animal.nama.toLocaleLowerCase('id').includes(query)).slice(0, 6);
        options.innerHTML = matches.map((animal) => `<button class="block w-full rounded-lg px-3 py-2 text-left text-xs hover:bg-white/5" type="button" data-fauna-option="${escapeHtml(animal.slug)}"><strong>${escapeHtml(animal.nama)}</strong><span class="mt-1 block italic text-muted">${escapeHtml(animal.nama_ilmiah)}</span></button>`).join('');
        options.classList.toggle('hidden', !query || matches.length === 0);
    });

    options.addEventListener('click', (event) => {
        const option = event.target.closest('[data-fauna-option]');
        if (!option) return;
        const animal = availableFauna.find((item) => item.slug === option.dataset.faunaOption);
        search.value = animal.nama;
        slugInput.value = animal.slug;
        form.elements.nama_bebas.value = animal.nama;
        options.classList.add('hidden');
    });

    fileInput.addEventListener('change', () => {
        const files = [...fileInput.files];
        const invalid = files.length > 5 || files.some((file) => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024);
        photoError.textContent = files.length > 5
            ? 'Maksimal 5 foto.'
            : 'Gunakan JPG, PNG, atau WebP dengan ukuran maksimal 5 MB per foto.';
        photoError.classList.toggle('hidden', !invalid);
        preview.innerHTML = '';
        if (invalid) {
            fileInput.value = '';
            return;
        }

        function escapeHtml(value = '') {
            return String(value).replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
            }[char]));
        }
        files.forEach((file) => {
            const image = document.createElement('img');
            image.className = 'h-20 w-full rounded-lg object-cover';
            image.alt = `Pratinjau ${file.name}`;
            image.src = URL.createObjectURL(file);
            preview.append(image);
        });
    });

    form.addEventListener('submit', async (event) => {
        // TODO(backend): kirim FormData ke POST /api/laporan-penemuan setelah endpoint multipart tersedia.
        event.preventDefault();
        errors.forEach((error) => { error.textContent = ''; error.classList.add('hidden'); });
        let valid = true;
        const data = new FormData(form);
        const required = ['nama_bebas', 'deskripsi', 'tanggal', 'lokasi', 'latitude', 'longitude'];
        required.forEach((name) => {
            const value = String(data.get(name) || '').trim();
            if (!value) {
                const error = form.querySelector(`[data-error-for="${name}"]`);
                if (error) {
                    error.textContent = 'Bagian ini wajib diisi.';
                    error.classList.remove('hidden');
                }
                valid = false;
            }
        });
        if (String(data.get('deskripsi') || '').trim().length < 20) {
            const error = form.querySelector('[data-error-for="deskripsi"]');
            error.textContent = 'Ceritakan pengamatan minimal 20 karakter.';
            error.classList.remove('hidden');
            valid = false;
        }
        const foundDate = new Date(`${data.get('tanggal')}T00:00:00`);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (Number.isNaN(foundDate.getTime()) || foundDate > today) {
            const error = form.querySelector('[data-error-for="tanggal"]');
            error.textContent = 'Tanggal harus valid dan tidak boleh di masa depan.';
            error.classList.remove('hidden');
            valid = false;
        }
        const latitude = Number(data.get('latitude'));
        const longitude = Number(data.get('longitude'));
        if (!Number.isFinite(latitude) || latitude < -90 || latitude > 90) {
            const error = form.querySelector('[data-error-for="latitude"]');
            error.textContent = 'Latitude harus berada di antara -90 dan 90.';
            error.classList.remove('hidden');
            valid = false;
        }
        if (!Number.isFinite(longitude) || longitude < -180 || longitude > 180) {
            const error = form.querySelector('[data-error-for="longitude"]');
            error.textContent = 'Longitude harus berada di antara -180 dan 180.';
            error.classList.remove('hidden');
            valid = false;
        }
        if (fileInput.files.length < 1 || fileInput.files.length > 5 || !photoError.classList.contains('hidden')) {
            photoError.textContent = 'Unggah 1 sampai 5 foto yang valid.';
            photoError.classList.remove('hidden');
            valid = false;
        }
        if (!valid) {
            showToast('Periksa kembali isian yang ditandai.', 'error');
            return;
        }

        try {
            const payload = Object.fromEntries(data.entries());
            payload.foto = [...fileInput.files];
            await kirimLaporan(payload);
            const success = document.querySelector('[data-report-success]');
            success.classList.remove('hidden');
            success.classList.add('grid');
            success.querySelector('[role="dialog"]')?.focus();
        } catch (error) {
            console.error(error);
            showToast('Laporan belum dapat diproses.', 'error');
        }
    });

    document.querySelector('[data-report-success-close]')?.addEventListener('click', () => {
        const success = document.querySelector('[data-report-success]');
        success.classList.add('hidden');
        success.classList.remove('grid');
        form.querySelector('[type="submit"]')?.focus();
    });

    document.querySelector('[data-report-success]')?.addEventListener('jf:modal-escape', () => {
        const success = document.querySelector('[data-report-success]');
        if (success.classList.contains('hidden')) return;
        success.classList.add('hidden');
        success.classList.remove('grid');
        form.querySelector('[type="submit"]')?.focus();
    });

    document.addEventListener('keydown', (event) => {
        const success = document.querySelector('[data-report-success]');
        if (event.key !== 'Tab' || !success || success.classList.contains('hidden')) return;
        const focusable = [...success.querySelectorAll('a[href], button:not([disabled])')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });
}
