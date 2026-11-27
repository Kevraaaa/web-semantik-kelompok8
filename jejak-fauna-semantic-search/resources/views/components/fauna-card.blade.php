@props(['fauna', 'showCompare' => true])
@php
    $imageUrl = $fauna['gambar'] ?? '/images/fauna-placeholder.svg';
    if (str_starts_with($imageUrl, 'http://commons.wikimedia.org/wiki/Special:FilePath/')) {
        $imageUrl = str_replace('http://', 'https://', $imageUrl);
    }
    if (str_starts_with($imageUrl, 'https://commons.wikimedia.org/wiki/Special:FilePath/') && !str_contains($imageUrl, 'width=')) {
        $imageUrl .= str_contains($imageUrl, '?') ? '&width=800' : '?width=800';
    }
@endphp
<article class="fauna-card group overflow-hidden rounded-2xl border border-soft surface card-hover" data-fauna-card data-slug="{{ $fauna['slug'] }}">
    <div class="fauna-visual relative h-52">
        <img class="absolute inset-0 size-full object-cover opacity-55 mix-blend-screen transition duration-500 group-hover:scale-105 group-hover:opacity-70" src="{{ $imageUrl }}" alt="{{ $fauna['alt_gambar'] ?? 'Ilustrasi '.$fauna['nama'] }}" data-fauna-image loading="lazy" decoding="async">
        <div class="absolute inset-x-4 bottom-4 z-10 flex items-end justify-between gap-3">
            <div><p class="text-xs text-white/75">{{ $fauna['kelompok'] }}</p><p class="mt-1 text-2xl" aria-hidden="true">{{ $fauna['ikon'] ?? '🌿' }}</p></div>
            <x-favorite-button :slug="$fauna['slug']" />
        </div>
        @if($showCompare)
            <label class="absolute left-3 top-3 z-10 flex cursor-pointer items-center gap-2 rounded-full bg-black/35 px-3 py-1.5 text-xs text-white backdrop-blur">
                <input type="checkbox" class="accent-fresh-grown" data-compare-check value="{{ $fauna['slug'] }}"><span>Bandingkan</span>
            </label>
        @endif
    </div>
    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div><h3 class="font-display text-xl">{{ $fauna['nama'] }}</h3><p class="mt-1 text-xs italic text-muted">{{ $fauna['nama_ilmiah'] }}</p></div>
            <a class="grid size-9 shrink-0 place-items-center rounded-full border border-soft transition hover:bg-white/5" href="{{ route('explore.show', $fauna['slug']) }}" aria-label="Lihat detail {{ $fauna['nama'] }}">↗</a>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <x-status-badge :status="$fauna['status_iucn']" />
            <span class="text-[.68rem] text-muted">{{ $fauna['ekosistem'] }}</span>
        </div>
    </div>
</article>
