@extends('layouts.app')
@section('title', 'Profil Saya — Jejak Fauna Kalimantan')
@section('page', 'profile')
@section('content')
<section class="container-page pb-20 pt-10">
    <div class="rounded-3xl border border-soft surface p-6 sm:p-9">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center"><div class="grid size-20 place-items-center rounded-full bg-fig-leaf/30 font-display text-3xl text-fresh-grown">A</div><div class="flex-1"><p class="eyebrow">Ruang pribadimu</p><h1 class="font-display mt-1 text-3xl" data-profile-name>Ayu Lestari</h1><p class="mt-1 text-sm text-muted" data-profile-email>ayu.lestari@example.test · Bergabung Januari 2026</p></div></div>
        <div class="mt-8 flex gap-2 overflow-x-auto border-b border-soft pb-3" role="tablist" aria-label="Menu profil">
            <button class="profile-tab active" type="button" role="tab" aria-selected="true" data-profile-tab="favorit">Favorit</button>
            <button class="profile-tab" type="button" role="tab" aria-selected="false" data-profile-tab="riwayat">Riwayat Pencarian</button>
            <button class="profile-tab" type="button" role="tab" aria-selected="false" data-profile-tab="laporan-saya">Laporan Saya</button>
            <button class="profile-tab" type="button" role="tab" aria-selected="false" data-profile-tab="aktivitas">Aktivitas</button>
        </div>
    </div>
    <section class="mt-8" data-profile-panel="favorit" role="tabpanel"><div class="mb-5 flex items-center justify-between"><h2 class="font-display text-2xl">Fauna favorit</h2><span class="pill text-xs" data-favorite-count>0 tersimpan</span></div><div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-profile-favorites></div></section>
    <section class="mt-8 hidden" data-profile-panel="riwayat" role="tabpanel"><h2 class="font-display text-2xl">Riwayat pencarian</h2><div class="mt-5 grid gap-3" data-profile-history></div></section>
    <section class="mt-8 hidden" data-profile-panel="laporan-saya" id="laporan-saya" role="tabpanel"><h2 class="font-display text-2xl">Laporan saya</h2><div class="mt-5 grid gap-3" data-profile-reports></div></section>
    <section class="mt-8 hidden" data-profile-panel="aktivitas" role="tabpanel"><h2 class="font-display text-2xl">Riwayat aktivitas</h2><div class="mt-5 rounded-2xl border border-soft surface p-5" data-profile-activity></div></section>
</section>
<x-compare-bar />
@endsection
