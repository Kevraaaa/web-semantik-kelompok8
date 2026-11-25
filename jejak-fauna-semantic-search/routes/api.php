<?php

use App\Http\Controllers\Api\FaunaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Jejak Fauna — API Routes
|--------------------------------------------------------------------------
|
| Semua endpoint berawalan /api/ secara otomatis karena didaftarkan
| di bootstrap/app.php dengan ->withRouting(api: __DIR__.'/../routes/api.php').
|
| Data bersumber dari SparqlService yang mem-parse dataset RDF/Turtle:
|   dataset/fauna_kalimantan.ttl
|
*/

// ------------------------------------------------------------------
// Fauna — Katalog & Detail
// ------------------------------------------------------------------

// GET /api/fauna?query=&status[]=&habitat[]=&page=&per_page=
Route::get('/fauna', [FaunaController::class, 'index'])->name('api.fauna.index');

// GET /api/fauna/filter-options  (harus sebelum {slug} agar tidak terambiguasi)
Route::get('/fauna/filter-options', [FaunaController::class, 'filterOptions'])->name('api.fauna.filter-options');

// GET /api/fauna/{slug}
Route::get('/fauna/{slug}', [FaunaController::class, 'show'])->name('api.fauna.show');

// GET /api/fauna/{slug}/rekomendasi?limit=6
Route::get('/fauna/{slug}/rekomendasi', [FaunaController::class, 'rekomendasi'])->name('api.fauna.rekomendasi');

// ------------------------------------------------------------------
// Pencarian Semantik
// ------------------------------------------------------------------

// POST /api/pencarian-semantik
// Body: { "kalimat": "satwa pemakan buah di hutan yang kritis" }
Route::post('/pencarian-semantik', [FaunaController::class, 'pencarianSemantik'])->name('api.pencarian-semantik');

// GET /api/ontology/validation
Route::get('/ontology/validation', [FaunaController::class, 'ontologyValidation'])->name('api.ontology.validation');

// ------------------------------------------------------------------
// Knowledge Graph
// ------------------------------------------------------------------

// GET /api/knowledge-graph?relasi[]=hidupDi&limit=50
Route::get('/knowledge-graph', [FaunaController::class, 'knowledgeGraph'])->name('api.knowledge-graph');
