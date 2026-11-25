@extends('layouts.app')
@section('title', 'Jejak Fauna Kalimantan — Jelajahi, Pelajari, Lindungi')
@section('page', 'home')
@section('content')
    <section class="hero-image hero-grain relative flex min-h-[min(760px,88svh)] items-center overflow-hidden">
        <div class="container-page relative z-10 grid w-full gap-12 py-16 lg:grid-cols-[1.15fr_.85fr] lg:items-center">
            <div class="max-w-3xl">
                <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-fresh-grown/20 bg-black/20 px-3 py-1.5 text-xs text-fresh-grown backdrop-blur"><span aria-hidden="true">❧</span> Pray for Kalimantan</div>
                <p class="eyebrow">Jelajah · Kenali · Lestarikan</p>
                <h1 class="font-display mt-5 text-5xl leading-[1.04] text-white sm:text-6xl lg:text-7xl">Jelajahi Keanekaragaman <span class="text-fresh-grown">Fauna Kalimantan</span></h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">Temukan, pelajari, dan lindungi kekayaan hayati Pulau Kalimantan melalui eksplorasi berbasis semantik.</p>
                <form class="mt-8 flex max-w-2xl flex-col gap-2 rounded-2xl border border-white/15 bg-black/35 p-2 backdrop-blur sm:flex-row" action="{{ route('explore') }}" method="get">
                    <label class="sr-only" for="hero-query">Cari fauna Kalimantan</label>
                    <span class="grid size-11 shrink-0 place-items-center text-xl text-fresh-grown" aria-hidden="true">⌕</span>
                    <input class="min-w-0 flex-1 bg-transparent px-1 text-sm text-white outline-none placeholder:text-white/50" id="hero-query" name="q" placeholder="Cari nama, habitat, makanan, atau ancaman..." autocomplete="off">
                    <button class="btn-primary" type="submit">Mulai jelajah <span aria-hidden="true">→</span></button>
                </form>
                <p class="mt-6 flex items-center gap-2 text-xs tracking-wide text-white/60"><span class="animate-bounce" aria-hidden="true">↓</span> Scroll untuk menjelajahi lebih lanjut</p>
            </div>
            <div class="hidden justify-self-end lg:block">
                <div class="glass w-72 rounded-2xl p-5">
                    <p class="eyebrow">Kekayaan yang kita jaga</p>
                    <div class="my-5 flex items-end gap-3"><span class="font-display text-6xl text-fresh-grown">10</span><span class="pb-2 text-sm text-muted">spesies pilihan<br>untuk dijelajahi</span></div>
                    <div class="h-px bg-white/10"></div>
                    <p class="mt-4 text-sm leading-6 text-muted">Setiap titik di peta menyimpan cerita tentang rumah, hubungan, dan masa depan satwa.</p>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-[var(--page)] to-transparent"></div>
    </section>

    <section class="container-page -mt-2 py-10" aria-label="Pintasan fitur">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-shortcut-card icon="⌕" title="Pencarian Fauna" description="Temukan spesies dari nama atau ciri yang kamu kenal." :href="route('explore')" />
            <x-shortcut-card icon="☷" title="Filter Fauna" description="Susuri fauna menurut habitat, makanan, status, dan kelompok." :href="route('explore').'#filter-fauna'" />
            <x-shortcut-card icon="⌖" title="Peta Persebaran" description="Lihat jejak persebaran dan laporan pengamatan warga." :href="route('map')" />
            <x-shortcut-card icon="⌘" title="Pencarian Semantik" description="Ceritakan satwa yang kamu cari dengan kalimat sederhana." :href="route('explore').'#pencarian'" />
        </div>
    </section>

    <section class="container-page section-space">
        <x-section-heading eyebrow="Kenali penghuni hutan" title="Fauna pilihan" description="Berkenalan lebih dekat dengan spesies yang menjadi bagian penting ekosistem Kalimantan." action="Lihat Semua" :href="route('explore')" />
        <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4" data-featured-fauna aria-live="polite">
            @for($i=0; $i<4; $i++)<x-skeleton-card />@endfor
        </div>
    </section>

    <section class="container-page grid gap-5 pb-10 lg:grid-cols-[.9fr_1.1fr]">
        <article class="rounded-3xl border border-soft surface p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Jalinan kehidupan</p><h2 class="font-display mt-2 text-3xl">Knowledge Graph</h2></div><span class="grid size-11 place-items-center rounded-full bg-azul-petroleo/25 text-lg text-sky-100">⌘</span></div>
            <p class="mt-3 max-w-lg text-sm leading-6 text-muted">Lihat bagaimana fauna terhubung dengan habitat, makanan, dan ekosistem tempatnya bergantung.</p>
            <div class="network-wrap mt-5 h-64 overflow-hidden rounded-2xl border border-soft bg-[#182719]" data-mini-graph aria-label="Pratinjau jejaring fauna"></div>
            <a class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-fresh-grown hover:gap-3" href="{{ route('graph') }}">Jelajahi relasi <span aria-hidden="true">→</span></a>
        </article>
        <article class="rounded-3xl border border-soft surface p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Kenali ruang hidup</p><h2 class="font-display mt-2 text-3xl">Jejak di peta</h2></div><span class="grid size-11 place-items-center rounded-full bg-azul-petroleo/25 text-lg text-sky-100">⌖</span></div>
            <p class="mt-3 max-w-lg text-sm leading-6 text-muted">Sebaran pada peta adalah gambaran awal untuk memahami luasnya lanskap yang perlu dijaga.</p>
            <div class="relative mt-5"><div class="h-64 rounded-2xl" data-mini-map aria-label="Peta mini persebaran fauna"></div><div class="absolute bottom-3 left-3 flex flex-wrap gap-2 rounded-xl bg-black/50 p-2 text-[.65rem] text-white backdrop-blur"><span>● Sangat Umum</span><span class="text-emerald-200">● Umum</span><span class="text-amber-200">● Jarang</span><span class="text-rose-200">● Sangat Jarang</span></div></div>
            <a class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-fresh-grown hover:gap-3" href="{{ route('map') }}">Lihat Peta <span aria-hidden="true">→</span></a>
        </article>
    </section>

    <section class="container-page grid gap-5 py-10 lg:grid-cols-2">
        <article class="relative overflow-hidden rounded-3xl border border-soft bg-wood-bark/60 p-7 sm:p-9">
            <div class="absolute -right-10 -top-10 size-56 rounded-full border border-fresh-grown/10"></div>
            <p class="eyebrow">Status konservasi</p><h2 class="font-display mt-2 max-w-md text-3xl">Mengenal status, memahami ancaman</h2>
            <div class="mt-7 flex items-center gap-4 rounded-2xl border border-soft bg-black/10 p-4">
                <div class="fauna-visual grid size-20 shrink-0 place-items-center rounded-xl text-3xl" aria-hidden="true">🦧</div>
                <div><h3 class="font-display text-xl">Orangutan Kalimantan</h3><p class="mt-1 text-xs italic text-muted">Pongo pygmaeus</p><x-status-badge status="Kritis (CR)" /></div>
            </div>
            <ul class="mt-5 grid gap-3 text-sm text-muted"><li>◉ Kehilangan dan fragmentasi habitat</li><li>◉ Perburuan dan perdagangan ilegal</li><li>◉ Kebakaran hutan</li></ul>
            <a class="btn-secondary mt-6" href="{{ route('explore.show', 'orangutan-kalimantan') }}">Pelajari Lebih Lanjut <span aria-hidden="true">→</span></a>
        </article>
        <article class="rounded-3xl border border-soft surface p-7 sm:p-9">
            <p class="eyebrow">Mungkin kamu suka</p><h2 class="font-display mt-2 text-3xl">Temukan yang serupa</h2>
            <p class="mt-3 text-sm leading-6 text-muted">Jelajah dari satu cerita menuju hubungan fauna yang lain.</p>
            <div class="mt-8 flex gap-4 overflow-x-auto pb-2" data-recommendations></div>
        </article>
    </section>

    <section class="container-page section-space" id="tentang">
        <div class="relative overflow-hidden rounded-[2rem] border border-soft bg-wood-bark/60 p-8 sm:p-12">
            <div class="absolute -right-12 -top-16 size-64 rounded-full border border-fresh-grown/10"></div>
            <div class="relative max-w-3xl"><p class="eyebrow">Tentang Jejak Fauna</p><h2 class="font-display mt-3 text-3xl sm:text-4xl">Satu langkah kecil untuk lebih mengenal rumah kita.</h2><p class="mt-5 text-sm leading-7 text-muted sm:text-base">Jejak Fauna Kalimantan adalah ruang eksplorasi digital untuk merangkai pengetahuan tentang satwa, habitat, dan ekosistem. Kenali hubungan di antara mereka, bagikan pengamatan dengan bertanggung jawab, dan temukan alasan untuk ikut menjaga hutan.</p><a class="btn-primary mt-7" href="{{ route('explore') }}">Mulai menjelajah <span aria-hidden="true">→</span></a></div>
        </div>
    </section>
    <x-compare-bar />
@endsection
