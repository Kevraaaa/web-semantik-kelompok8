@extends('layouts.app')
@section('title', 'Knowledge Graph — Jejak Fauna Kalimantan')
@section('page', 'graph')
@section('content')
<section class="container-page pb-20 pt-10">
    <p class="eyebrow">Jalinan ekosistem</p><h1 class="font-display mt-2 text-4xl sm:text-5xl">Knowledge Graph</h1><p class="mt-3 max-w-3xl text-sm leading-6 text-muted">Telusuri hubungan fauna dengan habitat, makanan, ekosistem, dan ancaman. Pilih sebuah simpul untuk melihat tetangganya.</p>
    <div class="mt-7 grid gap-5 xl:grid-cols-[1fr_320px]">
        <div class="overflow-hidden rounded-3xl border border-soft surface p-3">
            <div class="mb-3 flex flex-col gap-3 sm:flex-row">
                <label class="relative flex-1"><span class="sr-only">Cari node graph</span><span class="absolute left-3 top-1/2 -translate-y-1/2 text-fresh-grown">⌕</span><input class="field pl-9" data-graph-search placeholder="Cari fauna, habitat, atau makanan..."></label>
                <div class="flex gap-2"><button class="btn-secondary px-3 py-2" data-graph-zoom="in" type="button" aria-label="Perbesar">＋</button><button class="btn-secondary px-3 py-2" data-graph-zoom="out" type="button" aria-label="Perkecil">−</button><button class="btn-secondary px-3 py-2 text-xs" data-graph-reset type="button">Atur ulang</button></div>
            </div>
            <div class="network-wrap h-[620px] min-h-[420px] rounded-2xl bg-[#182719]" data-graph aria-label="Graf interaktif hubungan fauna" role="application"></div>
        </div>
        <aside class="grid content-start gap-4">
            <div class="rounded-2xl border border-soft surface p-5"><h2 class="font-display text-xl">Legenda node</h2><ul class="mt-4 grid gap-3 text-xs text-muted"><li><span class="legend-dot bg-fresh-grown"></span>Fauna Kalimantan</li><li><span class="legend-dot bg-fig-leaf"></span>Fauna</li><li><span class="legend-dot bg-[#70743a]"></span>Kelompok</li><li><span class="legend-dot bg-azul-petroleo"></span>Habitat / ekosistem</li><li><span class="legend-dot bg-amber-700"></span>Makanan</li><li><span class="legend-dot bg-rose-900"></span>Ancaman</li></ul></div>
            <div class="rounded-2xl border border-soft surface p-5"><h2 class="font-display text-xl">Filter relasi</h2><div class="mt-4 grid gap-2 text-xs text-muted">@foreach(['hidupDi'=>'hidupDi','memakan'=>'memakan','bagianDari'=>'bagianDari','terancamOleh'=>'terancamOleh'] as $type => $label)<label class="flex items-center gap-2"><input class="accent-fresh-grown" type="checkbox" value="{{ $type }}" data-graph-relation checked>{{ $label }}</label>@endforeach</div></div>
            <div class="rounded-2xl border border-soft surface p-5"><h2 class="font-display text-xl">Fokus graf</h2><div class="mt-4 grid gap-3"><label class="grid gap-1 text-xs text-muted">Kelompok<select class="field" data-graph-group><option value="">Semua kelompok</option></select></label><label class="grid gap-1 text-xs text-muted">Habitat<select class="field" data-graph-habitat><option value="">Semua habitat</option></select></label></div></div>
            <div class="rounded-2xl border border-soft surface p-5" data-graph-detail><p class="eyebrow">Detail simpul</p><h2 class="font-display mt-2 text-xl" data-graph-detail-title>Pilih sebuah simpul</h2><p class="mt-2 text-sm leading-6 text-muted" data-graph-detail-text>Relasi terdekat akan disorot di dalam graf.</p><a class="mt-4 hidden text-sm font-semibold text-fresh-grown" data-graph-detail-link href="#">Lihat detail fauna →</a></div>
        </aside>
    </div>
</section>
@endsection
