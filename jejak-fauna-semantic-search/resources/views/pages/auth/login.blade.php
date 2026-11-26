@extends('layouts.app')
@section('title', 'Masuk — Jejak Fauna Kalimantan')
@section('page', 'login')
@section('content')
<section class="container-page flex min-h-[76vh] items-center justify-center py-12">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl border border-soft surface shadow-2xl md:grid-cols-[.9fr_1.1fr]">
        <aside class="hero-image relative hidden min-h-[590px] flex-col justify-end p-10 md:flex"><div class="hero-copy relative z-10"><p class="eyebrow">Jejak Fauna Kalimantan</p><h1 class="font-display mt-3 text-4xl text-white">Setiap perjalanan dimulai dari rasa ingin tahu.</h1><p class="mt-4 text-sm leading-6 text-white/70">Masuk untuk menyimpan spesies favorit dan mencatat cerita penemuanmu.</p></div></aside>
        <div class="p-6 sm:p-10">
            <a class="text-sm text-muted hover:text-fresh-grown" href="{{ route('home') }}">← Kembali ke beranda</a>
            <div class="mt-9"><p class="eyebrow">Selamat datang kembali</p><h2 class="font-display mt-2 text-3xl">Masuk ke akunmu</h2><p class="mt-2 text-sm text-muted">Gunakan email dan kata sandi akunmu.</p></div>
            <form class="mt-7 grid gap-4" method="POST" action="{{ route('login.store') }}">
                @csrf
                <label><span class="field-label">Alamat email</span><input class="field" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required><x-field-error name="email" /></label>
                <label><span class="field-label">Kata sandi</span><input class="field" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required><x-field-error name="password" /></label>
                <div class="flex justify-end"><a class="text-xs text-fresh-grown hover:underline" href="#lupa-password" data-toast="Fitur ini belum tersedia">Lupa kata sandi?</a></div>
                <button class="btn-primary mt-1 w-full" type="submit">Masuk <span aria-hidden="true">→</span></button>
                <p class="text-center text-xs text-muted">Belum punya akun? <a class="font-semibold text-fresh-grown hover:underline" href="{{ route('register') }}">Daftar sekarang</a></p>
            </form>
        </div>
    </div>
</section>
@endsection
