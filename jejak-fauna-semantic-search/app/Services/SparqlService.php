<?php

namespace App\Services;

use App\Exceptions\RdfEngineException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class SparqlService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, meta: array{page: int, per_page: int, total: int}}
     */
    public function listFauna(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $fauna = $this->getAllFauna();

        foreach ([
            'status' => ['status_iucn_key', 'status_iucn'],
            'habitat' => ['habitat_keys', 'habitat'],
            'makanan' => ['makanan_keys', 'makanan'],
            'kelompok' => ['kelompok_key', 'kelompok'],
            'famili' => ['famili_key', 'famili'],
            'ancaman' => ['ancaman_keys', 'ancaman'],
        ] as $filter => [$keyField, $labelField]) {
            if (! empty($filters[$filter])) {
                $selected = array_map(
                    fn ($value) => $this->normalize((string) $value),
                    (array) $filters[$filter],
                );
                $fauna = array_filter($fauna, fn (array $item) => $this->containsSelected(
                    $selected,
                    array_merge(
                        (array) ($item[$keyField] ?? []),
                        (array) ($item[$labelField] ?? []),
                    ),
                    $filter !== 'status',
                ));
            }
        }

        if (! empty($filters['query'])) {
            $query = $this->normalize((string) $filters['query']);
            $filteredFauna = $fauna;
            $matches = array_filter($filteredFauna, fn (array $item) => str_contains(
                $this->normalize(implode(' ', array_filter([
                    $item['nama'] ?? '',
                    $item['nama_ilmiah'] ?? '',
                    $item['deskripsi'] ?? '',
                    implode(' ', $item['habitat'] ?? []),
                    implode(' ', $item['makanan'] ?? []),
                    implode(' ', $item['ancaman'] ?? []),
                ]))),
                $query,
            ));

            if ($matches === []) {
                $terms = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $matches = array_filter($filteredFauna, function (array $item) use ($terms): bool {
                    $names = $this->normalize(($item['nama'] ?? '').' '.($item['nama_ilmiah'] ?? ''));
                    $nameTerms = preg_split('/[^a-z0-9]+/', $names, -1, PREG_SPLIT_NO_EMPTY) ?: [];

                    foreach ($terms as $term) {
                        if (mb_strlen($term) < 4) {
                            continue;
                        }
                        foreach ($nameTerms as $nameTerm) {
                            similar_text($term, $nameTerm, $similarity);
                            if ($similarity >= 75) {
                                return true;
                            }
                        }
                    }

                    return false;
                });
            }

            $fauna = $matches;
        }

        $fauna = array_values($fauna);
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        return [
            'data' => array_map(
                fn (array $item) => $this->toPublicFauna($item),
                array_slice($fauna, ($page - 1) * $perPage, $perPage),
            ),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => count($fauna),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFaunaBySlug(string $slug): ?array
    {
        foreach ($this->getAllFauna() as $fauna) {
            if ($fauna['slug'] === $slug) {
                return $this->toPublicFauna($fauna);
            }
        }

        return null;
    }

    /**
     * @return array{data: array<int, array{fauna: array<string, mixed>, skor: int, alasan_kecocokan: array<int, string>}>}
     */
    public function pencarianSemantik(string $kalimat): array
    {
        return $this->runPython('semantic', ['query' => $kalimat]);
    }

    /**
     * @return array{data: array<int, array<string, mixed>>}
     */
    public function getRekomendasiBySlug(string $slug, int $limit = 6): array
    {
        return $this->runPython('recommendations', [
            'slug' => $slug,
            'limit' => max(1, min(20, $limit)),
        ]);
    }

    /**
     * @param  array<int, string>  $relasiFilter
     * @return array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>}
     */
    public function getKnowledgeGraph(
        array $relasiFilter = [],
        int $limit = 50,
        ?string $kelompok = null,
        ?string $habitat = null,
    ): array {
        return $this->runPython('graph', [
            'relations' => array_values($relasiFilter),
            'limit' => max(1, min(200, $limit)),
            'group' => $kelompok,
            'habitat' => $habitat,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return $this->runPython('filters');
    }

    /**
     * @return array<string, mixed>
     */
    public function validateRdfAgainstOntology(): array
    {
        return $this->runPython('validate');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAllFauna(): array
    {
        $datasetPath = (string) config('services.rdf.dataset_path');
        $scriptPath = (string) config('services.rdf.script_path');

        if (! is_file($datasetPath) || ! is_file($scriptPath)) {
            return $this->runPython('fauna')['data'];
        }

        $cacheKey = 'jejak_fauna_sparql_'.md5(implode('|', [
            $datasetPath,
            (string) filemtime($datasetPath),
            (string) filemtime($scriptPath),
            (string) config('services.rdf.python_binary'),
        ]));

        return Cache::store('file')->remember(
            $cacheKey,
            3600,
            fn () => $this->runPython('fauna')['data'],
        );
    }

    /**
     * @param  array<int, string>  $selected
     * @param  array<int, string>  $values
     */
    private function containsSelected(array $selected, array $values, bool $partial): bool
    {
        foreach ($values as $value) {
            $readableValue = preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', (string) $value);
            $normalizedValue = $this->normalize($readableValue);

            foreach ($selected as $selectedValue) {
                if ($normalizedValue === $selectedValue) {
                    return true;
                }

                if ($partial && str_contains($normalizedValue, $selectedValue)) {
                    return true;
                }

                if ($partial) {
                    $tokens = preg_split('/\s+/', $selectedValue, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    if ($tokens !== [] && count(array_filter(
                        $tokens,
                        fn (string $token) => str_contains($normalizedValue, $token),
                    )) === count($tokens)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }

    /**
     * @param  array<string, mixed>  $fauna
     * @return array<string, mixed>
     */
    private function toPublicFauna(array $fauna): array
    {
        $publicFauna = array_intersect_key($fauna, array_flip([
            'slug',
            'nama',
            'nama_ilmiah',
            'kelompok',
            'deskripsi',
            'status_iucn',
            'habitat',
            'makanan',
            'ancaman',
            'taksonomi',
            'famili_key',
            'famili',
            'wilayah',
            'upaya_konservasi',
            'gambar',
            'latitude',
            'longitude',
            'url_iucn',
            'wikidata',
            'sumber',
        ]));
        $publicFauna['sumber'] = array_values(array_filter([
            $fauna['url_iucn'] ?? null,
            $fauna['wikidata'] ?? null,
        ]));

        return $publicFauna;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function runPython(string $operation, array $input = []): array
    {
        $pythonBinary = (string) config('services.rdf.python_binary');
        $scriptPath = (string) config('services.rdf.script_path');

        if (! is_file($scriptPath)) {
            Log::error('RDF semantic engine script is missing.', ['script' => $scriptPath]);

            throw new RdfEngineException('RDF semantic engine is unavailable.');
        }

        $process = new Process([$pythonBinary, $scriptPath, $operation]);
        $process->setTimeout((float) config('services.rdf.timeout', 30));
        $process->setInput(json_encode([
            'dataset_path' => (string) config('services.rdf.dataset_path'),
            'ontology_path' => (string) config('services.rdf.ontology_path'),
            ...$input,
        ], JSON_THROW_ON_ERROR));

        try {
            $process->run();
        } catch (ProcessStartFailedException|ProcessTimedOutException $exception) {
            Log::error('RDF semantic engine could not complete its operation.', [
                'operation' => $operation,
                'exception' => $exception->getMessage(),
            ]);

            throw new RdfEngineException('RDF semantic engine is unavailable.', previous: $exception);
        }

        if (! $process->isSuccessful()) {
            Log::error('RDF semantic engine returned a failure.', [
                'operation' => $operation,
                'exit_code' => $process->getExitCode(),
                'error_output' => trim($process->getErrorOutput()),
            ]);

            throw new RdfEngineException('RDF semantic engine is unavailable.');
        }

        try {
            $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            Log::error('RDF semantic engine returned invalid JSON.', [
                'operation' => $operation,
                'exception' => $exception->getMessage(),
            ]);

            throw new RdfEngineException('RDF semantic engine returned an invalid response.', previous: $exception);
        }

        if (! is_array($result)) {
            Log::error('RDF semantic engine returned an unexpected response type.', ['operation' => $operation]);

            throw new RdfEngineException('RDF semantic engine returned an invalid response.');
        }

        return $result;
    }
}
