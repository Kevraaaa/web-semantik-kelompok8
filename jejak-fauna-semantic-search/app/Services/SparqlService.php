<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * SparqlService
 *
 * Menyediakan query semantik terhadap dataset RDF/Turtle fauna Kalimantan
 * secara in-process (tanpa Fuseki / library RDF eksternal).
 *
 * Strategi:
 *   - Parse file TTL sekali → cache via Laravel Cache (file driver, 1 jam)
 *   - Semua filter, paginasi, dan pencarian semantik dijalankan di PHP
 *
 * Dataset: dataset/fauna_kalimantan.ttl
 * Namespace: https://jejakfauna.org/ontology#  (prefix jf:)
 */
class SparqlService
{
    // ------------------------------------------------------------------
    // Konfigurasi
    // ------------------------------------------------------------------

    /** Cache key untuk hasil parsing TTL. */
    protected const CACHE_KEY = 'jejak_fauna_parsed_ttl_v2';

    /** Durasi cache dalam detik (1 jam). Dataset bersifat statis. */
    protected const CACHE_TTL = 3600;

    /** Mapping kode status IUCN ke label tampilan. */
    protected const STATUS_MAP = [
        'StatusKritis' => 'Kritis (CR)',
        'StatusTerancam' => 'Terancam (EN)',
        'StatusRentan' => 'Rentan (VU)',
    ];

    /** Kelompok fauna yang dikenali dari tipe RDF. */
    protected const KELOMPOK_CLASS = [
        'Mamalia', 'Burung', 'Reptil', 'Amfibi', 'Ikan',
        'Krustasea', 'Serangga', 'Moluska', 'Echinodermata',
    ];

    /** Label cadangan untuk deskripsi dataset yang menyebut kelas fauna. */
    protected const DESKRIPSI_KELOMPOK = [
        'mammal' => 'Mamalia',
        'mammalia' => 'Mamalia',
        'primate' => 'Mamalia',
        'primata' => 'Mamalia',
        'rodent' => 'Mamalia',
        'burung' => 'Burung',
        'bird' => 'Burung',
        'amfibi' => 'Amfibi',
        'amphibian' => 'Amfibi',
        'amphibia' => 'Amfibi',
        'reptil' => 'Reptil',
        'reptile' => 'Reptil',
        'reptilia' => 'Reptil',
        'turtle' => 'Reptil',
        'snake' => 'Reptil',
        'ular' => 'Reptil',
        'crocodyl' => 'Reptil',
        'ikan' => 'Ikan',
        'fish' => 'Ikan',
        'fishes' => 'Ikan',
        'actinopterygii' => 'Ikan',
        'chondrichthyes' => 'Ikan',
        'elasmobranchii' => 'Ikan',
        'ray' => 'Ikan',
        'shark' => 'Ikan',
        'stingray' => 'Ikan',
        'crustacean' => 'Krustasea',
        'serangga' => 'Serangga',
        'insect' => 'Serangga',
        'mollusc' => 'Moluska',
        'bivalvia' => 'Moluska',
        'gastropoda' => 'Moluska',
        'holothuroidea' => 'Echinodermata',
    ];

    /** Path ke file dataset TTL. */
    protected string $ttlPath;

    public function __construct()
    {
        $this->ttlPath = base_path('dataset/fauna_kalimantan.ttl');
    }

    // ==================================================================
    // PUBLIC API
    // ==================================================================

    /**
     * Ambil semua fauna dengan filter opsional + paginasi.
     *
     * Filter keys yang didukung:
     *   query    (string)  – pencarian teks bebas (nama, nama ilmiah, deskripsi)
     *   status   (array)   – ['StatusKritis', 'StatusTerancam', 'StatusRentan']
     *   habitat  (array)   – key habitat, misal ['HutanHujanTropis']
     *   makanan  (array)   – key makanan, misal ['BuahbuahanDaunDanSerangga']
     *   kelompok (array)   – kelompok fauna yang terdeteksi
     *   famili   (array)   – key famili, misal ['Bufonidae']
     *   ancaman  (array)   – key ancaman
     *
     * @return array { data: array, meta: { page, per_page, total } }
     */
    public function listFauna(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $all = $this->getAllFauna();

        // Filter status konservasi
        if (! empty($filters['status'])) {
            $statusFilter = (array) $filters['status'];
            $all = array_filter($all, fn ($f) => in_array($f['status_iucn_key'], $statusFilter));
        }

        // Filter habitat
        if (! empty($filters['habitat'])) {
            $habitatFilter = (array) $filters['habitat'];
            $all = array_filter($all, fn ($f) => ! empty(array_intersect($f['habitat_keys'], $habitatFilter)));
        }

        // Filter makanan
        if (! empty($filters['makanan'])) {
            $makananFilter = (array) $filters['makanan'];
            $all = array_filter($all, fn ($f) => ! empty(array_intersect($f['makanan_keys'], $makananFilter)));
        }

        // Filter kelompok (class fauna)
        if (! empty($filters['kelompok'])) {
            $kelompokFilter = (array) $filters['kelompok'];
            $all = array_filter($all, fn ($f) => in_array($f['kelompok'], $kelompokFilter));
        }

        // Filter famili
        if (! empty($filters['famili'])) {
            $familiFilter = (array) $filters['famili'];
            $all = array_filter($all, fn ($f) => in_array($f['famili_key'], $familiFilter));
        }

        // Filter ancaman
        if (! empty($filters['ancaman'])) {
            $ancamanFilter = (array) $filters['ancaman'];
            $all = array_filter($all, fn ($f) => ! empty(array_intersect($f['ancaman_keys'], $ancamanFilter)));
        }

        // Pencarian teks bebas
        if (! empty($filters['query'])) {
            $q = mb_strtolower(trim($filters['query']));
            $all = array_filter($all, fn ($f) => str_contains(mb_strtolower($f['nama'] ?? ''), $q) ||
                str_contains(mb_strtolower($f['nama_ilmiah'] ?? ''), $q) ||
                str_contains(mb_strtolower($f['deskripsi'] ?? ''), $q)
            );
        }

        $all = array_values($all);
        $total = count($all);
        $data = array_slice($all, ($page - 1) * $perPage, $perPage);

        return [
            'data' => array_map(fn ($f) => $this->toPublicFauna($f), $data),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    /**
     * Ambil satu fauna berdasarkan slug.
     *
     * Slug dibentuk dari CamelCase subject TTL:
     *   "BettaAlbimarginata" → "betta-albimarginata"
     *
     * @return array|null Objek Fauna publik atau null jika tidak ditemukan.
     */
    public function getFaunaBySlug(string $slug): ?array
    {
        $item = collect($this->getAllFauna())->firstWhere('slug', $slug);

        return $item ? $this->toPublicFauna($item) : null;
    }

    /**
     * Pencarian semantik: cocokkan kalimat bebas ke properti ontologi.
     *
     * Algoritma:
     *  1. Tokenisasi kalimat (stopword removal Bahasa Indonesia)
     *  2. Cocokkan token ke sinonim habitat, makanan, status, kelompok, nama
     *  3. Hitung skor per fauna; kembalikan yang skor > 0 diurutkan DESC
     *
     * @param  string  $kalimat  Contoh: "satwa pemakan buah di hutan tropis"
     * @return array { data: [{ fauna, skor, alasan_kecocokan[] }] }
     */
    public function pencarianSemantik(string $kalimat): array
    {
        $tokens = $this->tokenize($kalimat);
        $results = [];

        foreach ($this->getAllFauna() as $fauna) {
            [$skor, $alasan] = $this->hitungSkor($fauna, $tokens);
            if ($skor > 0) {
                $results[] = [
                    'fauna' => $this->toPublicFauna($fauna),
                    'skor' => $skor,
                    'alasan_kecocokan' => array_values(array_unique($alasan)),
                ];
            }
        }

        usort($results, fn ($a, $b) => $b['skor'] <=> $a['skor']);

        return ['data' => array_values($results)];
    }

    /**
     * Ambil rekomendasi fauna berdasarkan kesamaan habitat & status.
     *
     * @param  string  $slug  Slug fauna acuan
     * @param  int  $limit  Jumlah rekomendasi (default 6)
     * @return array { data: Fauna[] }
     */
    public function getRekomendasiBySlug(string $slug, int $limit = 6): array
    {
        $all = $this->getAllFauna();
        $acuan = collect($all)->firstWhere('slug', $slug);

        if (! $acuan) {
            return ['data' => []];
        }

        $scored = [];
        foreach ($all as $f) {
            if ($f['slug'] === $slug) {
                continue;
            }

            $skor = 0;
            // Poin: kelompok sama
            if ($f['kelompok'] === $acuan['kelompok']) {
                $skor += 2;
            }
            // Poin: status sama
            if ($f['status_iucn_key'] === $acuan['status_iucn_key']) {
                $skor += 1;
            }
            // Poin: irisan habitat
            $skor += count(array_intersect($f['habitat_keys'], $acuan['habitat_keys']));

            if ($skor > 0) {
                $scored[] = ['fauna' => $f, 'skor' => $skor];
            }
        }

        usort($scored, fn ($a, $b) => $b['skor'] <=> $a['skor']);

        $data = array_map(
            fn ($s) => $this->toPublicFauna($s['fauna']),
            array_slice($scored, 0, $limit)
        );

        return ['data' => array_values($data)];
    }

    /**
     * Ambil data knowledge-graph (node & edge) dari dataset.
     *
     * Relasi yang di-generate:
     *   hidupDi     – Fauna → Habitat
     *   memakan     – Fauna → Makanan
     *   terancamOleh – Fauna → Ancaman
     *   kelompoknya – Fauna → Kelompok (Mamalia / Burung / …)
     *
     * @param  array  $relasiFilter  Filter tipe relasi (kosong = semua)
     * @param  int  $limit  Maks jumlah fauna (hindari graph terlalu besar)
     * @param  string|null  $kelompok  Filter kelompok fauna
     * @param  string|null  $habitat  Filter key habitat
     * @return array { nodes: [], edges: [] }
     */
    public function getKnowledgeGraph(
        array $relasiFilter = [],
        int $limit = 50,
        ?string $kelompok = null,
        ?string $habitat = null,
    ): array
    {
        $all = $this->getAllFauna();

        if ($kelompok !== null) {
            $all = array_filter($all, fn ($fauna) => $fauna['kelompok'] === $kelompok);
        }

        if ($habitat !== null) {
            $all = array_filter($all, fn ($fauna) => in_array($habitat, $fauna['habitat_keys'], true));
        }

        $all = array_values($all);

        if ($kelompok !== null || $habitat !== null) {
            $all = array_slice($all, 0, $limit);
        } else {
            $faunaByGroup = [];
            foreach ($all as $fauna) {
                $faunaByGroup[$fauna['kelompok']][] = $fauna;
            }

            $all = [];
            for ($round = 0; count($all) < $limit; $round++) {
                $addedFauna = false;

                foreach ($faunaByGroup as $groupFauna) {
                    if (isset($groupFauna[$round])) {
                        $all[] = $groupFauna[$round];
                        $addedFauna = true;
                    }

                    if (count($all) >= $limit) {
                        break;
                    }
                }

                if (! $addedFauna) {
                    break;
                }
            }
        }

        $nodes = [];
        $edges = [];
        $nodeIds = [];

        $addNode = function (string $id, string $label, string $type, ?string $slug = null) use (&$nodes, &$nodeIds) {
            if (! isset($nodeIds[$id])) {
                $nodeIds[$id] = true;
                $node = ['id' => $id, 'label' => $label, 'type' => $type];
                if ($slug) {
                    $node['slug'] = $slug;
                }
                $nodes[] = $node;
            }
        };

        $addEdge = function (string $from, string $to, string $label, string $type) use (&$edges, $relasiFilter) {
            if (empty($relasiFilter) || in_array($type, $relasiFilter)) {
                $edges[] = compact('from', 'to', 'label', 'type');
            }
        };

        foreach ($all as $f) {
            $fId = 'fauna:'.$f['slug'];
            $addNode($fId, $f['nama'] ?? $f['subject'], 'fauna', $f['slug']);

            // Kelompok
            if ($f['kelompok'] !== 'Lainnya') {
                $kId = 'kelompok:'.$f['kelompok'];
                $addNode($kId, $f['kelompok'], 'kelompok');
                $addEdge($fId, $kId, 'bagian dari', 'bagianDari');
            }

            // Habitat
            foreach ($f['habitat_keys'] as $i => $key) {
                $hId = 'habitat:'.$key;
                $addNode($hId, $f['habitat'][$i] ?? $key, 'habitat');
                $addEdge($fId, $hId, 'hidup di', 'hidupDi');
            }

            // Makanan
            foreach ($f['makanan_keys'] as $i => $key) {
                $mId = 'makanan:'.$key;
                $addNode($mId, $f['makanan'][$i] ?? $key, 'makanan');
                $addEdge($fId, $mId, 'memakan', 'memakan');
            }

            // Ancaman
            foreach ($f['ancaman_keys'] as $i => $key) {
                $aId = 'ancaman:'.$key;
                $addNode($aId, $f['ancaman'][$i] ?? $key, 'ancaman');
                $addEdge($fId, $aId, 'terancam oleh', 'terancamOleh');
            }
        }

        return [
            'nodes' => array_values($nodes),
            'edges' => $edges,
        ];
    }

    /**
     * Ambil semua nilai enum yang tersedia di dataset (untuk filter UI).
     *
     * @return array { status: [], habitat: [], makanan: [], kelompok: [], famili: [], ancaman: [] }
     */
    public function getFilterOptions(): array
    {
        $all = $this->getAllFauna();

        $statusList = [];
        $habitatList = [];
        $makananList = [];
        $kelompokList = [];
        $familiList = [];
        $ancamanList = [];

        foreach ($all as $f) {
            if ($f['status_iucn_key']) {
                $statusList[$f['status_iucn_key']] = $f['status_iucn'];
            }
            foreach ($f['habitat_keys'] as $i => $key) {
                $habitatList[$key] = $f['habitat'][$i] ?? $this->humanize($key);
            }
            foreach ($f['makanan_keys'] as $i => $key) {
                $makananList[$key] = $f['makanan'][$i] ?? $this->humanize($key);
            }
            if ($f['kelompok'] !== 'Lainnya') {
                $kelompokList[] = $f['kelompok'];
            }
            if ($f['famili_key']) {
                $familiList[$f['famili_key']] = $f['famili'];
            }
            foreach ($f['ancaman_keys'] as $i => $key) {
                $ancamanList[$key] = $f['ancaman'][$i] ?? $this->humanize($key);
            }
        }

        return [
            'status' => $statusList,
            'habitat' => $habitatList,
            'makanan' => $makananList,
            'kelompok' => array_values(array_unique($kelompokList)),
            'famili' => $familiList,
            'ancaman' => $ancamanList,
        ];
    }

    // ==================================================================
    // INTERNAL: PARSING TTL
    // ==================================================================

    /**
     * Ambil semua fauna: dari cache jika tersedia, atau parse ulang TTL.
     */
    protected function getAllFauna(): array
    {
        // Dataset RDF bersifat lokal dan API harus tetap dapat berjalan tanpa
        // koneksi database. Gunakan file cache secara eksplisit agar default
        // CACHE_STORE=database tidak membuat endpoint fauna gagal 500.
        return Cache::store('file')->remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->parseTtl());
    }

    /**
     * Parse file Turtle line-by-line menjadi array PHP.
     *
     * Format yang didukung:
     *
     *   @prefix jf: <...> .
     *   jf:SubjectName a jf:Fauna, jf:Mamalia ;
     *       jf:namaLokal "..."^^xsd:string ;
     *       ...
     *
     * Asumsi: setiap subjek diawali di kolom 0 (tidak indented),
     * setiap predikasi diindentasi dengan spasi/tab.
     */
    protected function parseTtl(): array
    {
        if (! file_exists($this->ttlPath)) {
            throw new \RuntimeException("Dataset tidak ditemukan: {$this->ttlPath}");
        }

        $lines = file($this->ttlPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $fauna = [];
        $current = null;
        $typeStatementPending = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip prefix declarations dan komentar
            if ($trimmed === '' || str_starts_with($trimmed, '@prefix') || str_starts_with($trimmed, '#')) {
                continue;
            }

            // Baris pembuka: "jf:SubjectName a jf:Fauna ..."
            if (preg_match('/^jf:(\w+)\s+a\s+(.+)/', $trimmed, $m)) {
                if ($current !== null) {
                    $fauna[] = $current;
                }
                $current = $this->newFaunaEntry($m[1]);
                $typeDeclaration = explode(';', $m[2], 2)[0];
                $this->parseTypes($current, $typeDeclaration);
                $typeStatementPending = ! str_contains($m[2], ';') && ! str_contains($m[2], '.');
                // Jika ada predikasi lain di baris yang sama setelah " ;"
                $this->parseProperty($current, $trimmed);

                continue;
            }

            if ($current !== null && $typeStatementPending && preg_match('/^\s+jf:\w+(?:\s*,)?\s*[;,]?\s*$/', $line)) {
                $this->parseTypes($current, $trimmed);
                $typeStatementPending = str_ends_with(trim($trimmed), ',');

                continue;
            }

            // Baris properti (indented)
            if ($current !== null && preg_match('/^\s+(jf:|rdfs:|owl:)/', $line)) {
                $this->parseProperty($current, $trimmed);
            }
        }

        // Simpan entri terakhir
        if ($current !== null) {
            $fauna[] = $current;
        }

        foreach ($fauna as &$item) {
            $this->inferKelompok($item);
        }
        unset($item);

        return $fauna;
    }

    /**
     * Buat array kosong untuk satu entri fauna.
     */
    protected function newFaunaEntry(string $subject): array
    {
        return [
            'subject' => $subject,
            'slug' => $this->subjectToSlug($subject),
            'nama' => null,
            'nama_ilmiah' => null,
            'deskripsi' => null,
            'kelompok' => 'Lainnya',
            'status_iucn' => null,
            'status_iucn_key' => null,
            'habitat' => [],
            'habitat_keys' => [],
            'makanan' => [],
            'makanan_keys' => [],
            'ancaman' => [],
            'ancaman_keys' => [],
            'taksonomi' => null,
            'famili_key' => null,
            'famili' => null,
            'url_gambar' => null,
            'url_iucn' => null,
            'wikidata_uri' => null,
        ];
    }

    /**
     * Parse deklarasi rdf:type untuk menentukan kelompok fauna.
     */
    protected function parseTypes(array &$item, string $typeStr): void
    {
        preg_match_all('/jf:(\w+)/', $typeStr, $matches);

        foreach (self::KELOMPOK_CLASS as $kelas) {
            if (in_array($kelas, $matches[1], true)) {
                $item['kelompok'] = $kelas;

                return; // ambil kelompok pertama yang cocok
            }
        }
    }

    /**
     * Ekstrak nilai dari satu baris predikasi TTL dan simpan ke $item.
     */
    protected function parseProperty(array &$item, string $line): void
    {
        // Datatype properties (literal string)
        if (preg_match('/jf:namaLokal\s+"([^"]+)"/', $line, $m)) {
            $item['nama'] = $m[1];
        }
        if (preg_match('/jf:namaIlmiah\s+"([^"]+)"/', $line, $m)) {
            $item['nama_ilmiah'] = $m[1];
        }
        if (preg_match('/jf:deskripsi\s+"([^"]+)"/', $line, $m)) {
            $item['deskripsi'] = $m[1];
        }
        if (preg_match('/jf:urlGambar\s+"([^"]+)"/', $line, $m)) {
            $item['url_gambar'] = $m[1];
        }

        // Object properties → status
        $statusKeys = $this->objectKeysForPredicate($line, 'berstatusKonservasi');
        if ($statusKeys !== []) {
            $statusKey = $statusKeys[0];
            $item['status_iucn_key'] = $statusKey;
            $item['status_iucn'] = self::STATUS_MAP[$statusKey] ?? $statusKey;
        }

        // Object properties → habitat (bisa lebih dari satu)
        $habitatKeys = $this->objectKeysForPredicate($line, 'mempunyaiHabitat');
        if ($habitatKeys !== []) {
            foreach ($habitatKeys as $key) {
                if (! in_array($key, $item['habitat_keys'])) {
                    $item['habitat_keys'][] = $key;
                    $item['habitat'][] = $this->humanize($key);
                }
            }
        }

        // Object properties → makanan
        $makananKeys = $this->objectKeysForPredicate($line, 'memakan');
        if ($makananKeys !== []) {
            foreach ($makananKeys as $key) {
                if (! in_array($key, $item['makanan_keys'])) {
                    $item['makanan_keys'][] = $key;
                    $item['makanan'][] = $this->humanize($key);
                }
            }
        }

        // Object properties → ancaman
        $ancamanKeys = $this->objectKeysForPredicate($line, 'mempunyaiAncaman');
        if ($ancamanKeys !== []) {
            foreach ($ancamanKeys as $key) {
                if (! in_array($key, $item['ancaman_keys'])) {
                    $item['ancaman_keys'][] = $key;
                    $item['ancaman'][] = $this->humanize($key);
                }
            }
        }

        // Object properties → taksonomi
        foreach ($this->objectKeysForPredicate($line, 'termasukTaksonomi') as $key) {
            $item['taksonomi'] = $this->humanize($key);

            if ($item['famili_key'] === null && preg_match('/idae$/', $key)) {
                $item['famili_key'] = $key;
                $item['famili'] = $this->humanize($key);
            }
        }

        // URI references
        if (preg_match('/rdfs:seeAlso\s+<([^>]+)>/', $line, $m)) {
            $item['url_iucn'] = $m[1];
        }
        if (preg_match('/owl:sameAs\s+<([^>]+)>/', $line, $m)) {
            $item['wikidata_uri'] = $m[1];
        }
    }

    /**
     * Ambil semua objek jf untuk satu predikat, termasuk nilai yang dipisahkan koma.
     *
     * @return string[]
     */
    protected function objectKeysForPredicate(string $line, string $predicate): array
    {
        if (! preg_match('/jf:'.preg_quote($predicate, '/').'\s+([^;.]+)/', $line, $propertyMatch)) {
            return [];
        }

        preg_match_all('/jf:(\w+)/', $propertyMatch[1], $objectMatches);

        return $objectMatches[1];
    }

    /**
     * Tentukan kelompok dari deskripsi hanya jika tipe RDF tidak menyediakannya.
     */
    protected function inferKelompok(array &$item): void
    {
        if ($item['kelompok'] !== 'Lainnya') {
            return;
        }

        $description = mb_strtolower($item['deskripsi'] ?? '');

        foreach (self::DESKRIPSI_KELOMPOK as $term => $kelompok) {
            if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($term, '/').'(?![\p{L}\p{N}])/u', $description)) {
                $item['kelompok'] = $kelompok;

                return;
            }
        }
    }

    // ==================================================================
    // INTERNAL: PENCARIAN SEMANTIK
    // ==================================================================

    /**
     * Tokenisasi kalimat query Bahasa Indonesia.
     * Hapus stopword umum dan karakter non-alfa.
     */
    protected function tokenize(string $kalimat): array
    {
        $stopwords = [
            'di', 'ke', 'dari', 'yang', 'dan', 'atau', 'dengan', 'untuk',
            'pada', 'ini', 'itu', 'adalah', 'satwa', 'fauna', 'kalimantan',
            'spesies', 'jenis', 'hewan', 'binatang', 'hidup', 'tinggal',
            'merupakan', 'termasuk', 'yaitu', 'seperti', 'ada', 'sangat',
        ];

        $kalimat = preg_replace('/[^a-zA-Z0-9\s]/u', ' ', mb_strtolower($kalimat));
        $words = preg_split('/\s+/', trim($kalimat), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_diff($words, $stopwords));
    }

    /**
     * Hitung skor kecocokan antara satu fauna dan daftar token.
     *
     * @return array [int $skor, string[] $alasan]
     */
    protected function hitungSkor(array $fauna, array $tokens): array
    {
        $skor = 0;
        $alasan = [];

        // Sinonim habitat: token → array key habitat yang cocok
        $habitatSinonim = [
            'hutan' => ['HutanHujanTropis', 'HutanLembapDanAliranSungaiKalimantan', 'Hutan', 'HutanRawaDanSungaiKalimantan'],
            'tropis' => ['HutanHujanTropis'],
            'sungai' => ['SungaiDanRawaAirTawarKalimantan', 'PerairanTawarDanSungaiKalimantan', 'HutanLembapDanAliranSungaiKalimantan'],
            'rawa' => ['Rawa', 'HutanRawaDanSungaiKalimantan', 'SungaiDanRawaAirTawarKalimantan'],
            'laut' => ['PerairanPesisirDanMuaraKalimantan', 'PerairanTerumbuKarangKalimantan'],
            'pesisir' => ['PerairanPesisirDanMuaraKalimantan'],
            'terumbu' => ['PerairanTerumbuKarangKalimantan'],
            'karang' => ['PerairanTerumbuKarangKalimantan'],
            'danau' => ['Danau'],
            'perairan' => ['PerairanTawarDanSungaiKalimantan', 'PerairanTawarDanPayauKalimantan', 'PerairanPesisirDanMuaraKalimantan'],
            'air' => ['PerairanTawarDanSungaiKalimantan', 'SungaiDanRawaAirTawarKalimantan', 'DasarSungaiAirTawarKalimantan'],
            'tawar' => ['PerairanTawarDanSungaiKalimantan', 'PerairanTawarDanPayauKalimantan'],
            'payau' => ['PerairanTawarDanPayauKalimantan'],
            'dasar' => ['DasarSungaiAirTawarKalimantan'],
            'perdu' => ['LahanPerdu'],
        ];

        // Sinonim makanan
        $makananSinonim = [
            'buah' => ['BuahbuahanDaunDanSerangga', 'SeranggaDanBuahHutan'],
            'daun' => ['BuahbuahanDaunDanSerangga', 'NektarDanDaun'],
            'nektar' => ['NektarDanDaun'],
            'ikan' => ['IkanDanKrustasea', 'PlanktonDanIkanKecil', 'IkanAmfibiDanMamaliaKecil'],
            'krustasea' => ['IkanDanKrustasea'],
            'udang' => ['IkanDanKrustasea'],
            'serangga' => ['SeranggaKecil', 'SeranggaDanBuahHutan', 'BuahbuahanDaunDanSerangga', 'DetritusOrganikDanSeranggaAir'],
            'plankton' => ['PlanktonDanIkanKecil', 'PlanktonDanPartikelOrganik'],
            'alga' => ['AlgaDanMikroorganisme'],
            'mikroba' => ['AlgaDanMikroorganisme'],
            'detritus' => ['DetritusOrganikDanSeranggaAir', 'DetritusOrganikDasarLaut'],
            'mamalia' => ['IkanAmfibiDanMamaliaKecil'],
            'katak' => ['IkanAmfibiDanMamaliaKecil'],
            'amfibi' => ['IkanAmfibiDanMamaliaKecil'],
            'telur' => ['TelurBurung'],
            'partikel' => ['PlanktonDanPartikelOrganik'],
        ];

        // Sinonim status
        $statusSinonim = [
            'kritis' => 'StatusKritis',
            'cr' => 'StatusKritis',
            'terancam' => 'StatusTerancam',
            'en' => 'StatusTerancam',
            'rentan' => 'StatusRentan',
            'vu' => 'StatusRentan',
            'langka' => null, // cocok semua status
        ];

        // Sinonim kelompok
        $kelompokSinonim = [
            'mamalia' => 'Mamalia',
            'menyusui' => 'Mamalia',
            'burung' => 'Burung',
            'unggas' => 'Burung',
            'reptil' => 'Reptil',
            'melata' => 'Reptil',
            'buaya' => 'Reptil',
            'ular' => 'Reptil',
            'amfibi' => 'Amfibi',
            'kodok' => 'Amfibi',
            'katak' => 'Amfibi',
        ];

        foreach ($tokens as $token) {
            // Cocokkan nama langsung (bobot tinggi)
            if (
                $this->containsWord($fauna['nama'] ?? '', $token) ||
                $this->containsWord($fauna['nama_ilmiah'] ?? '', $token)
            ) {
                $skor += 3;
                $alasan[] = 'nama: '.($fauna['nama'] ?? $fauna['subject']);
            }

            // Deskripsi (bobot rendah)
            if ($this->containsWord($fauna['deskripsi'] ?? '', $token)) {
                $skor += 1;
                $alasan[] = 'deskripsi cocok';
            }

            // Habitat
            foreach ($habitatSinonim as $kata => $keys) {
                if ($token === $kata) {
                    $matched = array_intersect($fauna['habitat_keys'], $keys);
                    if (! empty($matched)) {
                        $skor++;
                        $alasan[] = 'habitat: '.$this->humanize(reset($matched));
                    }
                }
            }

            // Makanan
            foreach ($makananSinonim as $kata => $keys) {
                if ($token === $kata) {
                    $matched = array_intersect($fauna['makanan_keys'], $keys);
                    if (! empty($matched)) {
                        $skor++;
                        $alasan[] = 'makanan: '.$this->humanize(reset($matched));
                    }
                }
            }

            // Status
            foreach ($statusSinonim as $kata => $key) {
                if ($token === $kata) {
                    if ($key === null || $fauna['status_iucn_key'] === $key) {
                        $skor++;
                        $alasan[] = 'status: '.($fauna['status_iucn'] ?? 'dilindungi');
                    }
                }
            }

            // Kelompok
            foreach ($kelompokSinonim as $kata => $kelompok) {
                if ($token === $kata && $fauna['kelompok'] === $kelompok) {
                    $skor++;
                    $alasan[] = 'kelompok: '.$kelompok;
                }
            }
        }

        return [$skor, $alasan];
    }

    // ==================================================================
    // HELPERS
    // ==================================================================

    /**
     * Konversi CamelCase subject TTL ke slug kebab-case.
     *
     * "BettaAlbimarginata" → "betta-albimarginata"
     * "AonyxCinereus"     → "aonyx-cinereus"
     */
    protected function subjectToSlug(string $subject): string
    {
        // Sisipkan tanda hubung sebelum huruf kapital, lalu lowercase
        $kebab = preg_replace('/([A-Z])/', '-$1', $subject);

        return strtolower(ltrim($kebab, '-'));
    }

    /**
     * CamelCase → human readable dengan spasi.
     *
     * "HutanHujanTropis" → "Hutan Hujan Tropis"
     */
    protected function humanize(string $value): string
    {
        return trim(preg_replace('/([A-Z])/', ' $1', $value));
    }

    /**
     * Periksa kata utuh pada field tampilan agar kata pendek tidak cocok sebagai bagian kata lain.
     */
    protected function containsWord(string $text, string $word): bool
    {
        return preg_match(
            '/(?<![\p{L}\p{N}])'.preg_quote($word, '/').'(?![\p{L}\p{N}])/u',
            mb_strtolower($text),
        ) === 1;
    }

    /**
     * Konversi internal fauna array ke bentuk response publik API.
     * Hanya field yang aman dan relevan untuk ditampilkan ke client.
     */
    protected function toPublicFauna(array $fauna): array
    {
        return [
            'slug' => $fauna['slug'],
            'nama' => $fauna['nama'],
            'nama_ilmiah' => $fauna['nama_ilmiah'],
            'kelompok' => $fauna['kelompok'],
            'deskripsi' => $fauna['deskripsi'],
            'status_iucn' => $fauna['status_iucn'],
            'habitat' => $fauna['habitat'],
            'makanan' => $fauna['makanan'],
            'ancaman' => $fauna['ancaman'],
            'taksonomi' => $fauna['taksonomi'],
            'famili_key' => $fauna['famili_key'],
            'famili' => $fauna['famili'],
            'gambar' => $fauna['url_gambar'] ?? '/images/fauna-placeholder.svg',
            'url_iucn' => $fauna['url_iucn'],
            'wikidata' => $fauna['wikidata_uri'],
        ];
    }
}
