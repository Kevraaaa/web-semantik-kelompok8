<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// TODO(backend): ganti dengan controller Beranda; response menyiapkan rekomendasi dan ringkasan konservasi.
Route::view('/', 'pages.beranda')->name('home');

// TODO(backend): ganti dengan controller katalog; dukung filter, kueri semantik, dan paginasi.
Route::view('/eksplorasi', 'pages.eksplorasi.index')->name('explore');
// TODO(backend): ganti dengan controller detail fauna; ambil detail, rekomendasi, dan sebaran berdasarkan slug.
Route::view('/eksplorasi/{slug}', 'pages.eksplorasi.show')->name('explore.show');
// TODO(backend): ganti dengan controller perbandingan fauna berdasarkan daftar slug.
Route::view('/bandingkan', 'pages.bandingkan')->name('compare');
// TODO(backend): ganti dengan controller titik sebaran dan laporan publik terfilter.
Route::view('/peta', 'pages.peta')->name('map');
// TODO(backend): ganti dengan controller data node dan edge knowledge graph.
Route::view('/knowledge-graph', 'pages.knowledge-graph')->name('graph');
Route::middleware('guest')->group(function () {
    Route::view('/masuk', 'pages.auth.login')->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::view('/daftar', 'pages.auth.register')->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::view('/profil', 'pages.profil')->name('profile');
    Route::view('/admin/laporan', 'pages.admin.laporan')->name('admin.reports');
});
