import { getLaporanAdmin, verifikasiLaporan } from './api.js';
import { showToast } from './toast.js';

const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[char]));

const tableBody = document.querySelector('[data-admin-rows]');
if (tableBody) {
    const statusSelect = document.querySelector('[data-admin-status]');
    const storageKey = 'jf_admin_reports';
    let reports = [];

    try {
        reports = JSON.parse(localStorage.getItem(storageKey) || 'null') || await getLaporanAdmin();
    } catch (error) {
        console.error(error);
        tableBody.innerHTML = '<tr><td class="p-5 text-rose-200" colspan="5">Daftar laporan belum dapat dimuat.</td></tr>';
    }

    const persist = () => localStorage.setItem(storageKey, JSON.stringify(reports));
    const badge = (status) => {
        const colors = { menunggu: 'bg-amber-900/35 text-amber-100', diverifikasi: 'bg-emerald-900/35 text-emerald-100', ditolak: 'bg-rose-900/35 text-rose-100' };
        return `<span class="rounded-full px-2.5 py-1 text-[.68rem] ${colors[status] || ''}">${escapeHtml(status)}</span>`;
    };

    const renderRows = () => {
        const status = statusSelect.value;
        const visible = reports.filter((report) => !status || report.status === status);
        tableBody.innerHTML = visible.map((report) => `<tr class="cursor-pointer transition hover:bg-white/[.025]" data-admin-row="${escapeHtml(report.id)}" tabindex="0" role="button" aria-label="Tinjau laporan ${escapeHtml(report.nama)}"><td class="px-4 py-4"><span class="block font-medium">${escapeHtml(report.nama)}</span><span class="mt-1 block text-xs text-muted">${escapeHtml(report.lokasi.teks)}</span></td><td class="px-4 py-4 text-xs text-muted">${escapeHtml(report.pelapor)}</td><td class="px-4 py-4 text-xs text-muted">${escapeHtml(report.tanggal)}</td><td class="px-4 py-4">${badge(report.status)}</td><td class="px-4 py-4 text-fresh-grown">Tinjau →</td></tr>`).join('') || '<tr><td class="p-5 text-sm text-muted" colspan="5">Tidak ada laporan untuk filter ini.</td></tr>';
        tableBody.querySelectorAll('[data-admin-row]').forEach((row) => {
            const open = () => renderDetail(reports.find((report) => report.id === row.dataset.adminRow));
            row.addEventListener('click', open);
            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    open();
                }
            });
        });
    };

    const renderDetail = (report) => {
        if (!report) return;
        const panel = document.querySelector('[data-admin-detail]');
        panel.innerHTML = `<p class="eyebrow">Detail laporan</p><h2 class="font-display mt-2 text-2xl">${escapeHtml(report.nama)}</h2><p class="mt-1 text-xs text-muted">${escapeHtml(report.tanggal)} · ${badge(report.status)}</p><img class="mt-4 h-44 w-full rounded-xl object-cover" src="${escapeHtml(report.foto?.[0] || '/images/fauna-placeholder.svg')}" alt="Foto laporan ${escapeHtml(report.nama)}"><div class="mt-4 grid gap-2 text-xs text-muted"><p><strong class="text-white">Pelapor:</strong> ${escapeHtml(report.pelapor)}</p><p><strong class="text-white">Lokasi:</strong> ${escapeHtml(report.lokasi.teks)}</p><p><strong class="text-white">Koordinat:</strong> ${escapeHtml(report.lokasi.lat)}, ${escapeHtml(report.lokasi.lng)}</p>${report.catatan ? `<p><strong class="text-white">Catatan:</strong> ${escapeHtml(report.catatan)}</p>` : ''}</div><div class="mt-4 h-44 rounded-xl" data-admin-map data-lat="${escapeHtml(report.lokasi.lat)}" data-lng="${escapeHtml(report.lokasi.lng)}" aria-label="Lokasi laporan pada peta mini"></div><label class="mt-4 block"><span class="field-label">Catatan verifikator</span><textarea class="field min-h-20" data-admin-note placeholder="Tambahkan catatan untuk pelapor...">${escapeHtml(report.catatan || '')}</textarea></label><div class="mt-4 grid grid-cols-2 gap-2"><button class="btn-primary" type="button" data-admin-decision="diverifikasi">Setujui</button><button class="btn-secondary" type="button" data-admin-decision="ditolak">Tolak</button></div><p class="mt-3 text-[.68rem] leading-5 text-muted">Mock lokal: keputusan hanya tersimpan di localStorage browser.</p>`;
        panel.querySelectorAll('[data-admin-decision]').forEach((button) => button.addEventListener('click', async () => {
            // TODO(backend): kirim keputusan dan catatan ke POST /api/admin/laporan/{id}/verifikasi.
            const note = panel.querySelector('[data-admin-note]').value.trim();
            try {
                await verifikasiLaporan(report.id, button.dataset.adminDecision, note);
                report.status = button.dataset.adminDecision;
                report.catatan = note;
                persist();
                renderRows();
                renderDetail(report);
                showToast(`Laporan ditandai ${report.status} pada tampilan demo.`);
            } catch (error) {
                console.error(error);
                showToast('Keputusan belum dapat diterapkan.', 'error');
            }
        }));
        document.dispatchEvent(new CustomEvent('jf:admin-map-ready'));
    };

    statusSelect.addEventListener('change', renderRows);
    renderRows();
}
