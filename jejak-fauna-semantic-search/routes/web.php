<?php

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
// TODO(backend): ganti dengan controller sesi; POST kredensial dan response token/session setelah autentikasi tersedia.
Route::view('/masuk', 'pages.auth.login')->name('login');
// TODO(backend): ganti dengan controller pendaftaran; POST payload profil setelah autentikasi tersedia.
Route::view('/daftar', 'pages.auth.register')->name('register');
// TODO(backend): ganti dengan controller profil; kembalikan favorit, riwayat, laporan, dan aktivitas pengguna.
Route::view('/profil', 'pages.profil')->name('profile');
// TODO(backend): ganti dengan controller laporan penemuan; POST metadata dan foto, response laporan tersimpan.
Route::view('/lapor', 'pages.lapor')->name('report.create');
// TODO(backend): ganti dengan controller moderasi; GET daftar laporan dan POST keputusan verifikasi.
Route::view('/admin/laporan', 'pages.admin.laporan')->name('admin.reports');
