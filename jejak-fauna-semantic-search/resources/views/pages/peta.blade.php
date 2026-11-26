@extends('layouts.app')
@section('title', 'Peta Persebaran — Jejak Fauna Kalimantan')
@section('page', 'map')
@section('content')
<section class="container-page pb-20 pt-10">
    <div class="mb-7"><p class="eyebrow">Menyusuri ruang hidup</p><h1 class="font-display mt-2 text-4xl sm:text-5xl">Peta Persebaran</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Marker fauna hanya ditampilkan jika koordinat valid tersedia. Garis provinsi adalah batas administrasi, bukan wilayah sebaran; polygon persebaran spesifik per spesies belum tersedia dari sumber terverifikasi.</p></div>
    <div class="grid gap-5 xl:grid-cols-[1fr_330px]">
        <div class="overflow-hidden rounded-3xl border border-soft surface p-2">
            <div class="h-[540px] min-h-[360px] rounded-2xl" data-map aria-label="Peta interaktif Pulau Kalimantan"></div>
        </div>
        <aside class="grid content-start gap-4">
            <div class="rounded-2xl border border-soft surface p-5">
                <h2 class="font-display text-xl">Filter peta</h2>
                <div class="mt-4 grid gap-3">
                    <label class="grid gap-1.5 text-xs font-semibold">Spesies<select class="field text-xs" data-map-filter="slug"><option value="">Semua fauna</option></select></label>
                    <label class="grid gap-1.5 text-xs font-semibold">Status konservasi<select class="field text-xs" data-map-filter="status"><option value="">Semua status</option><option>Kritis (CR)</option><option>Terancam (EN)</option><option>Rentan (VU)</option><option>Hampir Terancam (NT)</option></select></label>
                    <label class="grid gap-1.5 text-xs font-semibold">Habitat<select class="field text-xs" data-map-filter="habitat"><option value="">Semua habitat</option><option>Hutan hujan tropis</option><option>Hutan rawa gambut</option><option>Hutan mangrove</option><option>Sungai</option><option>Tepi sungai</option></select></label>
                    <label class="flex items-center gap-2 border-t border-soft pt-3 text-xs"><input type="checkbox" class="accent-fresh-grown" data-map-reports checked> Tampilkan laporan warga</label>
                </div>
            </div>
            <div class="rounded-2xl border border-soft surface p-5"><h2 class="font-display text-xl">Legenda peta</h2><div class="mt-4 grid gap-3 text-xs text-muted"><span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full border border-emerald-200 bg-emerald-700" aria-hidden="true"></span> Marker fauna — koordinat valid</span><span class="flex items-center gap-2"><span class="w-5 border-t border-dashed border-stone-300" aria-hidden="true"></span> Batas administrasi — bukan wilayah sebaran</span><span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full border border-cyan-100 bg-slate-500" aria-hidden="true"></span> Marker laporan warga</span><span>Polygon persebaran spesifik: belum tersedia dari sumber terverifikasi.</span></div><p class="mt-3 text-xs text-muted" data-map-distribution-status role="status">Memeriksa ketersediaan persebaran fauna.</p></div>
            <div class="rounded-2xl border border-soft surface p-5"><div class="flex items-center justify-between"><h2 class="font-display text-xl">Titik ditemukan</h2><span class="pill text-xs" data-map-count>0</span></div><div class="mt-3 grid max-h-80 gap-2 overflow-y-auto" data-map-results></div></div>
        </aside>
    </div>
</section>
@endsection
