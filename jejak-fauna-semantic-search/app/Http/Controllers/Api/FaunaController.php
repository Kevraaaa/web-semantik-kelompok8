<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SparqlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * FaunaController
 *
 * Menyediakan endpoint REST API untuk data fauna Kalimantan.
 * Semua data berasal dari SparqlService yang mengquery dataset RDF/Turtle.
 *
 * Endpoint:
 *   GET  /api/fauna                      – Daftar fauna (filter + paginasi)
 *   GET  /api/fauna/filter-options        – Enum nilai untuk filter UI
 *   GET  /api/fauna/{slug}               – Detail satu fauna
 *   GET  /api/fauna/{slug}/rekomendasi   – Fauna serupa
 *   POST /api/pencarian-semantik         – Pencarian kalimat bebas
 *   GET  /api/ontology/validation        – Validasi dataset terhadap OWL/XML
 *   GET  /api/knowledge-graph            – Node dan edge graf pengetahuan
 */
class FaunaController extends Controller
{
    public function __construct(protected SparqlService $sparql) {}

    // ------------------------------------------------------------------
    // GET /api/fauna
    // ------------------------------------------------------------------

    /**
     * Daftar fauna dengan filter opsional dan paginasi.
     *
     * Query parameters:
     *   query    (string)        – kata kunci teks bebas
     *   status[] (string[])      – StatusKritis | StatusTerancam | StatusRentan
     *   habitat[] (string[])     – key habitat dari dataset
     *   makanan[] (string[])     – key makanan dari dataset
     *   kelompok[] (string[])    – kelompok fauna dari dataset
     *   famili[] (string[])      – key famili dari dataset
     *   ancaman[] (string[])     – key ancaman dari dataset
     *   page     (int, default 1)
     *   per_page (int, default 12, maks 100)
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'array'],
            'status.*' => ['string'],
            'habitat' => ['nullable', 'array'],
            'habitat.*' => ['string'],
            'makanan' => ['nullable', 'array'],
            'makanan.*' => ['string'],
            'kelompok' => ['nullable', 'array'],
            'kelompok.*' => ['string'],
            'famili' => ['nullable', 'array'],
            'famili.*' => ['string'],
            'ancaman' => ['nullable', 'array'],
            'ancaman.*' => ['string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $result = $this->sparql->listFauna(
            filters: array_filter($validated),
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 12),
        );

        return response()->json($result);
    }

    // ------------------------------------------------------------------
    // GET /api/fauna/filter-options
    // ------------------------------------------------------------------

    /**
     * Kembalikan semua nilai enum yang tersedia untuk filter UI.
     * Berguna agar frontend tidak hardcode daftar status/habitat/dsb.
     */
    public function filterOptions(): JsonResponse
    {
        return response()->json([
            'data' => $this->sparql->getFilterOptions(),
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/fauna/{slug}
    // ------------------------------------------------------------------

    /**
     * Detail lengkap satu fauna berdasarkan slug.
     *
     * Slug berasal dari CamelCase subject TTL yang dikonversi ke kebab-case:
     *   "BettaAlbimarginata" → "betta-albimarginata"
     */
    public function show(string $slug): JsonResponse
    {
        $fauna = $this->sparql->getFaunaBySlug($slug);

        if (! $fauna) {
            return response()->json([
                'message' => 'Fauna tidak ditemukan.',
                'slug' => $slug,
            ], 404);
        }

        return response()->json(['data' => $fauna]);
    }

    // ------------------------------------------------------------------
    // GET /api/fauna/{slug}/rekomendasi
    // ------------------------------------------------------------------

    /**
     * Fauna serupa berdasarkan kesamaan kelompok, status, dan habitat.
     *
     * Query parameters:
     *   limit (int, default 6, maks 20)
     */
    public function rekomendasi(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        // Pastikan fauna dengan slug ini memang ada
        if (! $this->sparql->getFaunaBySlug($slug)) {
            return response()->json([
                'message' => 'Fauna tidak ditemukan.',
                'slug' => $slug,
            ], 404);
        }

        $result = $this->sparql->getRekomendasiBySlug(
            slug: $slug,
            limit: (int) ($validated['limit'] ?? 6),
        );

        return response()->json($result);
    }

    // ------------------------------------------------------------------
    // POST /api/pencarian-semantik
    // ------------------------------------------------------------------

    /**
     * Pencarian semantik menggunakan kalimat bahasa alami.
     *
     * Request body (JSON):
     *   { "kalimat": "satwa pemakan buah di hutan tropis yang kritis" }
     *
     * Response:
     *   { "data": [{ "fauna": {...}, "skor": 3, "alasan_kecocokan": [...] }] }
     */
    public function pencarianSemantik(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kalimat' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $result = $this->sparql->pencarianSemantik($validated['kalimat']);

        return response()->json($result);
    }

    public function ontologyValidation(): JsonResponse
    {
        return response()->json([
            'data' => $this->sparql->validateRdfAgainstOntology(),
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/knowledge-graph
    // ------------------------------------------------------------------

    /**
     * Data knowledge-graph (node & edge) dari dataset ontologi.
     *
     * Query parameters:
     *   relasi[] – filter tipe relasi: hidupDi | memakan | terancamOleh | bagianDari
     *   limit    – maks fauna yang di-generate ke graph (default 50, maks 200)
     *   kelompok – filter berdasarkan kelompok fauna
     *   habitat  – filter berdasarkan key habitat dari dataset
     */
    public function knowledgeGraph(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'relasi' => ['nullable', 'array'],
            'relasi.*' => ['string', 'in:hidupDi,memakan,terancamOleh,bagianDari'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'kelompok' => ['nullable', 'string', 'max:100'],
            'habitat' => ['nullable', 'string', 'max:200'],
        ]);

        $result = $this->sparql->getKnowledgeGraph(
            relasiFilter: $validated['relasi'] ?? [],
            limit: (int) ($validated['limit'] ?? 50),
            kelompok: $validated['kelompok'] ?? null,
            habitat: $validated['habitat'] ?? null,
        );

        return response()->json($result);
    }
}
