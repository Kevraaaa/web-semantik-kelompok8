@extends('layouts.app')
@section('title', 'Bandingkan Fauna — Jejak Fauna Kalimantan')
@section('page', 'compare')
@section('content')
<section class="container-page min-h-[70vh] pb-20 pt-12">
    <p class="eyebrow">Lihat perbedaan dan persamaan</p><h1 class="font-display mt-2 text-4xl sm:text-5xl">Bandingkan Fauna</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Pilih 2–3 fauna di katalog untuk membandingkan habitat, makanan, ancaman, dan ciri khasnya.</p>
    <div class="mt-8" data-compare-results aria-live="polite"></div>
    <div class="mt-8 hidden" data-compare-empty><x-empty-state title="Pilih fauna yang ingin dibandingkan" description="Kembali ke katalog dan centang kotak Bandingkan pada dua atau tiga kartu fauna." action="Jelajahi fauna" href="{{ route('explore') }}" /></div>
</section>
@endsection
