@extends('layouts.app')
@section('title', 'Daftar — Jejak Fauna Kalimantan')
@section('page', 'register')
@section('content')
<section class="container-page flex min-h-[76vh] items-center justify-center py-12">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl border border-soft surface shadow-2xl md:grid-cols-[.9fr_1.1fr]">
        <aside class="hero-image relative hidden min-h-[630px] flex-col justify-end p-10 md:flex"><div class="hero-copy relative z-10"><p class="eyebrow">Mari jaga bersama</p><h1 class="font-display mt-3 text-4xl text-white">Kenali lebih dekat. Jaga lebih lama.</h1><p class="mt-4 text-sm leading-6 text-white/70">Jadilah bagian dari komunitas yang menghargai alam liar dan pengetahuan lokal.</p></div></aside>
        <div class="p-6 sm:p-10">
            <a class="text-sm text-muted hover:text-fresh-grown" href="{{ route('home') }}">← Kembali ke beranda</a>
            <div class="mt-8"><p class="eyebrow">Buat akun</p><h2 class="font-display mt-2 text-3xl">Mulai perjalananmu</h2><p class="mt-2 text-sm text-muted">Pendaftaran pada demo ini hanya mengubah state di browser.</p></div>
            <form class="mt-6 grid gap-4" data-register-form novalidate>
                <label><span class="field-label">Nama lengkap</span><input class="field" type="text" name="name" placeholder="Nama kamu" autocomplete="name" minlength="2" required><x-field-error name="name" /></label>
                <label><span class="field-label">Alamat email</span><input class="field" type="email" name="email" placeholder="nama@email.com" autocomplete="email" required><x-field-error name="email" /></label>
                <label><span class="field-label">Kata sandi</span><input class="field" type="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" minlength="8" required><x-field-error name="password" /></label>
                <label><span class="field-label">Ulangi kata sandi</span><input class="field" type="password" name="password_confirmation" placeholder="Ulangi kata sandi" autocomplete="new-password" required><x-field-error name="password_confirmation" /></label>
                <label class="flex items-start gap-2 text-xs leading-5 text-muted"><input class="mt-1 accent-fresh-grown" type="checkbox" name="consent" required><span>Saya setuju menggunakan platform ini dengan bertanggung jawab dan menjaga keselamatan satwa.</span></label>
                <p class="hidden text-xs text-rose-300" data-error-for="consent"></p>
                <button class="btn-primary w-full" type="submit">Buat akun <span aria-hidden="true">→</span></button>
                <p class="text-center text-xs text-muted">Sudah memiliki akun? <a class="font-semibold text-fresh-grown hover:underline" href="{{ route('login') }}">Masuk</a></p>
            </form>
        </div>
    </div>
</section>
@endsection
