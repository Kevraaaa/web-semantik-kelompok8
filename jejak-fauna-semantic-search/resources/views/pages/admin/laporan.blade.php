@extends('layouts.app')
@section('title', 'Verifikasi Laporan — Jejak Fauna Kalimantan')
@section('page', 'admin')
@section('content')
<section class="container-page pb-20 pt-10">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="eyebrow">Ruang moderasi demo</p><h1 class="font-display mt-2 text-4xl sm:text-5xl">Verifikasi Laporan</h1><p class="mt-3 text-sm text-muted">Aksi hanya mengubah tampilan secara lokal dan tidak melakukan otorisasi.</p></div><span class="pill border-amber-200/15 bg-amber-950/20 text-xs text-amber-100">Mode UI · Tanpa autentikasi admin</span></div>
    <div class="grid gap-6 xl:grid-cols-[1fr_390px]">
        <section class="overflow-hidden rounded-2xl border border-soft surface">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-soft p-4"><h2 class="font-display text-xl">Antrean laporan</h2><label class="flex items-center gap-2 text-xs text-muted">Status<select class="field min-w-40 py-2 text-xs" data-admin-status><option value="">Semua status</option><option value="menunggu">Menunggu</option><option value="diverifikasi">Diverifikasi</option><option value="ditolak">Ditolak</option></select></label></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[690px] text-left text-sm"><thead class="bg-white/[.025] text-[.68rem] uppercase tracking-wider text-muted"><tr><th class="px-4 py-3">Laporan</th><th class="px-4 py-3">Pelapor</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead><tbody class="divide-y divide-white/5" data-admin-rows></tbody></table></div>
        </section>
        <aside class="rounded-2xl border border-soft surface p-5" data-admin-detail><p class="eyebrow">Detail laporan</p><div class="mt-5 rounded-xl border border-dashed border-soft px-4 py-10 text-center text-sm text-muted">Pilih laporan untuk meninjau foto, lokasi, dan catatan.</div></aside>
    </div>
</section>
@endsection
