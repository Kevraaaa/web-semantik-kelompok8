@extends('layouts.app')
@section('title', 'Peta Persebaran — Jejak Fauna Kalimantan')
@section('page', 'map')
@section('content')
<section class="container-page pb-20 pt-10">
    <div class="mb-7"><p class="eyebrow">Menyusuri ruang hidup</p><h1 class="font-display mt-2 text-4xl sm:text-5xl">Peta Persebaran</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Jelajahi titik sebaran ilustratif fauna Kalimantan. Lokasi dan kelimpahan dummy bukan data survei.</p></div>
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
            <div class="rounded-2xl border border-soft surface p-5"><h2 class="font-display text-xl">Legenda kelimpahan</h2><div class="mt-4 grid grid-cols-2 gap-3 text-xs text-muted"><span>● <i class="text-emerald-300">Sangat Umum</i></span><span>● <i class="text-green-200">Umum</i></span><span>● <i class="text-amber-200">Jarang</i></span><span>● <i class="text-rose-200">Sangat Jarang</i></span></div></div>
            <div class="rounded-2xl border border-soft surface p-5"><div class="flex items-center justify-between"><h2 class="font-display text-xl">Titik ditemukan</h2><span class="pill text-xs" data-map-count>0</span></div><div class="mt-3 grid max-h-80 gap-2 overflow-y-auto" data-map-results></div></div>
        </aside>
    </div>
</section>
@endsection
