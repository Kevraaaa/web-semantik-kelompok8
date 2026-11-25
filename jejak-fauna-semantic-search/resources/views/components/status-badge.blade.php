@props(['status' => 'Belum dinilai'])
@php
    $badgeColor = match ($status) {
        'Kritis (CR)' => 'bg-rose-900/40 text-rose-200',
        'Terancam (EN)' => 'bg-orange-900/40 text-orange-200',
        'Rentan (VU)' => 'bg-amber-900/35 text-amber-100',
        'Hampir Terancam (NT)' => 'bg-lime-900/35 text-lime-200',
        default => 'bg-azul-petroleo/30 text-sky-100',
    };
@endphp
<span data-conservation-status="{{ $status }}" {{ $attributes->merge(['class' => "status-badge inline-flex rounded-full px-2.5 py-1 text-[.67rem] font-semibold {$badgeColor}"]) }}>{{ $status }}</span>
