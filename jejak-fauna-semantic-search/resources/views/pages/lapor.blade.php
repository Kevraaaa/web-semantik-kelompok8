@extends('layouts.app')
@section('title', 'Lapor Penemuan — Jejak Fauna Kalimantan')
@section('page', 'report')
@section('content')
<section class="container-page pb-20 pt-10">
    <div class="mb-8"><p class="eyebrow">Berbagi dengan penuh tanggung jawab</p><h1 class="font-display mt-2 text-4xl sm:text-5xl">Lapor Penemuan</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Catat pengamatan satwa liar. Hindari mendekati atau mengganggu satwa; lokasi tepat tidak akan ditampilkan publik pada tahap demo.</p></div>
    <div class="grid gap-6 lg:grid-cols-[1fr_390px]">
        <form class="grid gap-5 rounded-3xl border border-soft surface p-5 sm:p-8" data-report-form novalidate>
            <div><label class="field-label" for="fauna-search">Pilih fauna (opsional)</label><input id="fauna-search" class="field" type="search" data-fauna-autocomplete placeholder="Ketik nama fauna..." autocomplete="off"><input type="hidden" name="fauna_slug" data-fauna-slug><div class="relative"><div class="absolute z-20 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-xl border border-soft surface p-1 shadow-xl" data-autocomplete-results></div></div><p class="mt-1 text-xs text-muted">Atau tulis nama bebas bila belum ada di daftar.</p></div>
            <label><span class="field-label">Nama fauna yang ditemukan</span><input class="field" type="text" name="nama_bebas" placeholder="Contoh: burung enggang" minlength="2" required><x-field-error name="nama_bebas" /></label>
            <label><span class="field-label">Deskripsi pengamatan</span><textarea class="field min-h-28 resize-y" name="deskripsi" placeholder="Ceritakan ciri, perilaku, dan kondisi sekitar..." minlength="20" required></textarea><x-field-error name="deskripsi" /></label>
            <label><span class="field-label">Tanggal penemuan</span><input class="field" type="date" name="tanggal" max="{{ date('Y-m-d') }}" required><x-field-error name="tanggal" /></label>
            <div><label class="field-label" for="lokasi-teks">Lokasi pengamatan</label><input class="field" id="lokasi-teks" name="lokasi" placeholder="Nama kawasan atau desa" required><x-field-error name="lokasi" /><p class="mt-1 text-xs text-muted">Klik peta untuk memilih titik koordinat. Marker hanya bersifat lokal.</p></div>
            <div class="grid grid-cols-2 gap-3"><label><span class="field-label">Latitude</span><input class="field" type="number" step="any" name="latitude" data-lat placeholder="-0.1234" required><x-field-error name="latitude" /></label><label><span class="field-label">Longitude</span><input class="field" type="number" step="any" name="longitude" data-lng placeholder="114.1234" required><x-field-error name="longitude" /></label></div>
            <div><label class="field-label" for="report-photos">Foto pengamatan (1–5 foto, maksimal 5 MB/foto)</label><label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-soft bg-[var(--page)] px-4 py-8 text-center transition hover:border-fresh-grown/30"><span class="text-2xl text-fresh-grown" aria-hidden="true">⇧</span><span class="mt-2 text-sm font-medium">Pilih foto atau seret ke sini</span><span class="mt-1 text-xs text-muted">JPG, PNG, atau WebP</span><input class="sr-only" id="report-photos" type="file" name="foto" accept="image/jpeg,image/png,image/webp" multiple data-report-photos></label><div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5" data-photo-previews></div><p class="mt-1 hidden text-xs text-rose-300" data-photo-error></p></div>
            <button class="btn-primary w-full sm:w-fit" type="submit">Kirim laporan <span aria-hidden="true">→</span></button>
            <p class="text-xs leading-5 text-muted">Laporan demo tidak disimpan dan tidak dikirim. Backend perlu menambahkan moderasi dan kebijakan privasi data lokasi.</p>
        </form>
        <aside class="grid content-start gap-4">
            <section class="rounded-3xl border border-soft surface p-5"><h2 class="font-display text-xl">Tentukan titik</h2><div class="mt-4 h-[340px] rounded-2xl" data-picker-map aria-label="Pilih lokasi penemuan lewat peta"></div></section>
            <section class="rounded-2xl border border-amber-200/10 bg-amber-950/15 p-5"><h2 class="text-sm font-semibold text-amber-100">Panduan pengamatan aman</h2><ul class="mt-3 grid gap-2 text-xs leading-5 text-muted"><li>• Amati dari jarak aman dan jangan memberi makan.</li><li>• Jangan membuka lokasi sensitif satwa langka kepada publik.</li><li>• Jangan memasuki kawasan terbatas tanpa izin.</li></ul></section>
        </aside>
    </div>
    <div class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/70 p-4" data-report-success>
        <section class="modal-panel text-center" role="dialog" aria-modal="true" aria-labelledby="report-success-title" tabindex="-1"><span class="mx-auto grid size-14 place-items-center rounded-full bg-fig-leaf/30 text-2xl text-fresh-grown">✓</span><h2 class="font-display mt-4 text-2xl" id="report-success-title">Laporan siap ditinjau</h2><p class="mt-2 text-sm leading-6 text-muted">Ini simulasi sukses lokal. Setelah backend terhubung, laporan akan masuk ke antrean verifikasi.</p><a class="btn-primary mt-5" href="{{ route('profile') }}#laporan-saya">Lihat laporan saya</a><button class="mt-4 block w-full text-sm text-muted" type="button" data-report-success-close>Tutup</button></section>
    </div>
</section>
@endsection
