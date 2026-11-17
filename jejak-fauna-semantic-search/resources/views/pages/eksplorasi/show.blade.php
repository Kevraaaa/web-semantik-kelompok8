@extends('layouts.app')
@section('title', 'Detail Fauna — Jejak Fauna Kalimantan')
@section('page', 'detail')
@section('content')
<section class="container-page pb-20 pt-8" data-detail-slug="{{ request()->route('slug') }}">
    <a class="mb-6 inline-flex items-center gap-2 text-sm text-muted hover:text-fresh-grown" href="{{ route('explore') }}"><span aria-hidden="true">←</span> Kembali ke eksplorasi</a>
    <div data-detail-content aria-live="polite">
        <div class="grid gap-8 lg:grid-cols-[1.05fr_.95fr]">
            <div class="fauna-visual h-80 animate-pulse rounded-3xl lg:h-[31rem]"></div>
            <div class="grid content-center gap-4"><div class="skeleton h-7 w-32 rounded-full"></div><div class="skeleton h-12 w-4/5 rounded"></div><div class="skeleton h-5 w-1/2 rounded"></div><div class="skeleton mt-4 h-28 rounded"></div></div>
        </div>
    </div>
    <div class="mt-16 grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <section class="rounded-3xl border border-soft surface p-6 sm:p-8"><p class="eyebrow">Ruang hidup</p><h2 class="font-display mt-2 text-2xl">Jejak persebaran</h2><div class="mt-5 h-72" data-detail-map aria-label="Peta persebaran fauna"></div></section>
        <section class="rounded-3xl border border-soft surface p-6 sm:p-8"><p class="eyebrow">Satu ekosistem</p><h2 class="font-display mt-2 text-2xl">Fauna yang serupa</h2><div class="mt-5 flex gap-4 overflow-x-auto pb-2" data-detail-recommendations></div></section>
    </div>
    <section class="mt-6 rounded-3xl border border-soft surface p-6 sm:p-8"><p class="eyebrow">Bacaan lanjutan</p><h2 class="font-display mt-2 text-2xl">Sumber dan referensi</h2><ul class="mt-5 grid gap-3 text-sm text-muted sm:grid-cols-2" data-detail-sources></ul></section>
</section>
<x-compare-bar />
@endsection
