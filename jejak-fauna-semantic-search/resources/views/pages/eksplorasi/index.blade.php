@extends('layouts.app')
@section('title', 'Eksplorasi Fauna — Jejak Fauna Kalimantan')
@section('page', 'explore')
@section('content')
<section class="container-page pb-20 pt-12">
    <div class="mb-10 flex flex-col justify-between gap-5 md:flex-row md:items-end"><div><p class="eyebrow">Katalog keanekaragaman</p><h1 class="font-display mt-2 text-4xl sm:text-5xl">Eksplorasi Fauna</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Cari spesies dari nama, lalu persempit perjalananmu berdasarkan status konservasi, habitat, makanan, kelompok, famili, dan ancaman.</p></div><a class="btn-secondary" href="{{ route('map') }}">⌖ Lihat persebaran</a></div>
    <div class="grid gap-6 lg:grid-cols-[260px_1fr]">
        <aside id="filter-fauna" class="hidden h-fit rounded-2xl border border-soft surface p-5 lg:block">
            <div class="flex items-center justify-between"><h2 class="font-display text-xl">Filter fauna</h2><button class="text-xs text-fresh-grown hover:underline" type="button" data-filter-reset>Reset</button></div>
            <div class="mt-5 grid gap-5" data-filter-panel>
                <fieldset><legend class="mb-2 text-xs font-semibold">Status konservasi</legend><div class="grid gap-2 text-xs text-muted" data-filter-options="status"></div></fieldset>
                <fieldset><legend class="mb-2 text-xs font-semibold">Habitat</legend><div class="grid gap-2 text-xs text-muted" data-filter-options="habitat"></div></fieldset>
                <label class="grid gap-2 text-xs font-semibold">Jenis makanan<select class="field text-xs" data-filter-select="makanan"><option value="">Semua jenis</option></select></label>
                <label class="grid gap-2 text-xs font-semibold">Kelompok<select class="field text-xs" data-filter-select="kelompok"><option value="">Semua kelompok</option></select></label>
                <label class="grid gap-2 text-xs font-semibold">Famili<select class="field text-xs" data-filter-select="famili"><option value="">Semua famili</option></select></label>
                <label class="grid gap-2 text-xs font-semibold">Ancaman<select class="field text-xs" data-filter-select="ancaman"><option value="">Semua ancaman</option></select></label>
            </div>
        </aside>
        <div>
            <div class="rounded-2xl border border-soft surface p-4 sm:p-5" id="pencarian">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center">
                    <label class="relative flex-1"><span class="sr-only">Kata pencarian fauna</span><span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-fresh-grown">⌕</span><input class="field pl-12!" type="search" data-search-input placeholder="Cari nama atau nama ilmiah..." aria-label="Cari fauna"></label>
                    <label id="pencarian-semantik" class="semantic-toggle inline-flex cursor-pointer items-center gap-3 rounded-xl px-2 py-2 text-sm" title="Aktifkan pencarian berdasarkan ciri, habitat, atau makanan fauna"><input type="checkbox" class="peer sr-only" data-semantic-toggle aria-label="Aktifkan pencarian semantik"><span class="semantic-switch-track relative h-6 w-11 rounded-full transition peer-checked:bg-fig-leaf"><span class="absolute left-1 top-1 size-4 rounded-full bg-white transition peer-checked:translate-x-5"></span></span><span>Pencarian Semantik</span></label>
                </div>
                <p class="mt-2 hidden text-xs font-semibold text-fresh-grown" data-semantic-status aria-live="polite">Mode pencarian semantik aktif</p>
                <div class="mt-4 hidden rounded-xl border border-azul-petroleo/30 bg-azul-petroleo/10 p-4" data-semantic-help>
                    <p class="text-xs leading-5 text-muted">Tuliskan ciri atau habitat yang kamu ingat—kami akan mencocokkan kata kunci dengan data fauna.</p>
                    <div class="mt-3 flex flex-wrap gap-2"><button class="query-example" data-query-example="hewan pemakan buah di hutan" type="button">hewan pemakan buah di hutan</button><button class="query-example" data-query-example="satwa air tawar di sungai" type="button">satwa air tawar di sungai</button><button class="query-example" data-query-example="primata yang hidup di rawa" type="button">primata yang hidup di rawa</button></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2" data-active-filters></div>
            </div>
            <div class="mt-7 flex items-center justify-between gap-3"><p class="text-sm text-muted" data-result-count>Memuat fauna...</p><button class="btn-secondary min-h-9 px-3 py-2 text-xs lg:hidden" type="button" data-mobile-filter>☷ Filter</button></div>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-fauna-results aria-live="polite">
                @for($i=0; $i<6; $i++)<x-skeleton-card />@endfor
            </div>
            <button class="btn-secondary mx-auto mt-7 hidden" type="button" data-fauna-load-more>Muat lebih banyak</button>
            <div class="mt-5 hidden" data-fauna-empty><x-empty-state title="Belum ada fauna yang cocok" description="Coba ubah kata pencarian atau longgarkan pilihan filter." action="Lihat semua fauna" href="{{ route('explore') }}" /></div>
            <div class="mt-5 hidden rounded-2xl border border-rose-300/20 bg-rose-950/20 p-5 text-sm text-rose-100" data-fauna-error role="alert">Data fauna belum dapat dimuat. Coba muat ulang halaman.</div>
        </div>
    </div>
</section>
<x-compare-bar />
<div class="fixed inset-0 z-[60] hidden bg-black/70 p-4 lg:hidden" data-filter-drawer>
    <div class="ml-auto h-full w-full max-w-sm overflow-y-auto rounded-2xl border border-soft surface p-5">
        <div class="flex items-center justify-between"><h2 class="font-display text-xl">Filter fauna</h2><button type="button" class="text-2xl" data-filter-close aria-label="Tutup filter">×</button></div>
        <div class="mt-4 grid gap-5" data-filter-drawer-content></div>
        <button class="mt-4 w-full text-left text-xs text-fresh-grown" type="button" data-filter-reset-mobile>Reset filter</button>
        <button class="btn-primary mt-5 w-full" type="button" data-filter-close>Lihat hasil</button>
    </div>
</div>
@endsection
