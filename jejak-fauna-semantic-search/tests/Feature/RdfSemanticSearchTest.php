<?php

namespace Tests\Feature;

use Tests\TestCase;

class RdfSemanticSearchTest extends TestCase
{
    public function test_exact_name_search_returns_fauna_from_rdf(): void
    {
        $response = $this->getJson('/api/fauna?query=Betta%20albimarginata&per_page=5');

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'betta-albimarginata');
    }

    public function test_name_search_finds_fauna_with_a_typographical_error(): void
    {
        $response = $this->getJson('/api/fauna?query=oranutan&per_page=5');

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'orangutan-kalimantan');
    }

    public function test_food_filter_matches_related_rdf_labels(): void
    {
        $response = $this->getJson('/api/fauna?makanan%5B%5D=Ikan&per_page=5');

        $response->assertOk();
        $this->assertGreaterThan(0, $response->json('meta.total'));
        $this->assertStringContainsString('ikan', mb_strtolower(implode(
            ' ',
            $response->json('data.0.makanan'),
        )));
    }

    public function test_conservation_status_filter_matches_the_ontology_status(): void
    {
        $response = $this->getJson('/api/fauna?status%5B%5D=Kritis%20%28CR%29&query=orangutan');

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.status_iucn', 'Kritis (CR)');
    }

    public function test_habitat_filter_matches_the_rdf_relation_identifier(): void
    {
        $response = $this->getJson('/api/fauna?habitat%5B%5D=Hutan%20hujan%20tropis&query=orangutan');

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'orangutan-kalimantan');
    }

    public function test_filter_options_are_built_from_rdf_resources(): void
    {
        $response = $this->getJson('/api/fauna/filter-options');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['status', 'habitat', 'makanan', 'kelompok', 'ancaman'],
            ]);
        $this->assertNotEmpty($response->json('data.habitat'));
        $this->assertNotEmpty($response->json('data.makanan'));
    }

    public function test_taxonomically_verified_fauna_are_grouped_as_fish(): void
    {
        foreach ([
            'eusphyra-blochii',
            'leiocassis-rudicula',
            'pastinachus-solocirostris',
            'rhynchobatus-springeri',
        ] as $slug) {
            $this->getJson("/api/fauna/{$slug}")
                ->assertOk()
                ->assertJsonPath('data.kelompok', 'Ikan');
        }
    }

    public function test_fauna_with_misidentified_image_uses_the_placeholder(): void
    {
        $response = $this->getJson('/api/fauna?query=Ansonia%20latidisca&per_page=1');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.gambar', '/images/fauna-placeholder.svg');
    }

    public function test_unverified_commons_image_sources_use_the_placeholder(): void
    {
        foreach ([
            'Aonyx cinereus',
            'Batagur borneoensis',
            'Betta albimarginata',
            'Crocodylus siamensis',
            'Petinomys genibarbis',
            'Scleropages formosus',
            'Urogymnus polylepis',
        ] as $scientificName) {
            $this->getJson('/api/fauna?query='.urlencode($scientificName).'&per_page=1')
                ->assertOk()
                ->assertJsonPath('data.0.gambar', '/images/fauna-placeholder.svg');
        }
    }

    public function test_leiocassis_conservation_status_matches_iucn_assessment(): void
    {
        $response = $this->getJson('/api/fauna?query=Leiocassis%20rudicula&per_page=1');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.status_iucn', 'Terancam (EN)');
    }

    public function test_fauna_without_wgs84_data_has_no_generated_coordinates(): void
    {
        $response = $this->getJson('/api/fauna?query=orangutan&per_page=1');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.latitude', null)
            ->assertJsonPath('data.0.longitude', null);
    }

    public function test_semantic_search_matches_habitat_and_food_relations(): void
    {
        $habitatResponse = $this->postJson('/api/pencarian-semantik', [
            'kalimat' => 'fauna yang hidup di hutan hujan tropis',
        ]);
        $foodResponse = $this->postJson('/api/pencarian-semantik', [
            'kalimat' => 'fauna pemakan ikan',
        ]);

        $habitatResponse->assertOk();
        $foodResponse->assertOk();
        $this->assertNotEmpty($habitatResponse->json('data'));
        $this->assertNotEmpty($foodResponse->json('data'));
        $this->assertStringContainsString(
            'habitat:',
            implode(' ', $habitatResponse->json('data.0.alasan_kecocokan')),
        );
        $this->assertStringContainsString(
            'makanan:',
            implode(' ', $foodResponse->json('data.0.alasan_kecocokan')),
        );
    }

    public function test_fauna_detail_and_recommendations_use_rdf_relations(): void
    {
        $detailResponse = $this->getJson('/api/fauna/orangutan-kalimantan');
        $recommendationResponse = $this->getJson('/api/fauna/orangutan-kalimantan/rekomendasi');

        $detailResponse
            ->assertOk()
            ->assertJsonPath('data.nama_ilmiah', 'Pongo pygmaeus');
        $this->assertNotEmpty($detailResponse->json('data.habitat'));
        $this->assertNotEmpty($detailResponse->json('data.makanan'));
        $this->assertNotEmpty($detailResponse->json('data.wilayah'));
        $this->assertNotEmpty($detailResponse->json('data.sumber'));

        $recommendationResponse->assertOk();
        $this->assertNotEmpty($recommendationResponse->json('data'));
        $this->assertNotSame(
            'orangutan-kalimantan',
            $recommendationResponse->json('data.0.slug'),
        );
    }

    public function test_knowledge_graph_returns_filtered_rdf_edges(): void
    {
        $response = $this->getJson('/api/knowledge-graph?relasi%5B%5D=hidupDi&limit=5');

        $response->assertOk();
        $this->assertNotEmpty($response->json('nodes'));
        $this->assertNotEmpty($response->json('edges'));
        $this->assertSame(
            ['hidupDi'],
            array_values(array_unique(array_column($response->json('edges'), 'type'))),
        );
    }

    public function test_ontology_validation_reports_rdf_and_owl_vocabulary(): void
    {
        $response = $this->getJson('/api/ontology/validation');

        $response
            ->assertOk()
            ->assertJsonPath('data.triple_count', 3121)
            ->assertJsonPath('data.vocabulary.namespace_matches', true);
        $this->assertNotEmpty($response->json('data.classes.used'));
        $this->assertNotEmpty($response->json('data.properties.used'));
        $this->assertSame([], $response->json('data.errors'));
        $this->assertNotEmpty($response->json('data.warnings'));
    }

    public function test_empty_turtle_dataset_returns_empty_results_and_a_validation_warning(): void
    {
        $emptyDataset = tempnam(sys_get_temp_dir(), 'jejak-fauna-empty-');
        file_put_contents($emptyDataset, '');

        try {
            config(['services.rdf.dataset_path' => $emptyDataset]);

            $searchResponse = $this->postJson('/api/pencarian-semantik', [
                'kalimat' => 'fauna hutan',
            ]);
            $validationResponse = $this->getJson('/api/ontology/validation');

            $searchResponse->assertOk()->assertExactJson(['data' => []]);
            $validationResponse
                ->assertOk()
                ->assertJsonPath('data.triple_count', 0);
            $this->assertNotEmpty($validationResponse->json('data.warnings'));
        } finally {
            unlink($emptyDataset);
        }
    }

    public function test_validator_reports_domain_range_datatype_and_vocabulary_errors(): void
    {
        $dataset = tempnam(sys_get_temp_dir(), 'jejak-fauna-invalid-');
        file_put_contents($dataset, <<<'TURTLE'
@prefix jf: <https://jejakfauna.org/ontology#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .

jf:HabitatSubject a jf:Habitat ;
    jf:mempunyaiHabitat jf:StatusSubject ;
    jf:deskripsi "not a string datatype"^^xsd:anyURI ;
    jf:propertiTidakDikenal "value" .

jf:StatusSubject a jf:StatusKonservasi .
TURTLE);

        try {
            config(['services.rdf.dataset_path' => $dataset]);

            $response = $this->getJson('/api/ontology/validation');

            $response
                ->assertOk()
                ->assertJsonPath('data.properties.undefined.0', 'propertiTidakDikenal');
            $errors = implode(' ', $response->json('data.errors'));
            $this->assertStringContainsString('Domain mismatch for mempunyaiHabitat', $errors);
            $this->assertStringContainsString('Range mismatch for mempunyaiHabitat', $errors);
            $this->assertStringContainsString('Datatype mismatch for deskripsi', $errors);
        } finally {
            unlink($dataset);
        }
    }

    public function test_rdf_engine_failure_returns_service_unavailable(): void
    {
        config(['services.rdf.python_binary' => 'jejak-fauna-python-not-installed']);

        $response = $this->getJson('/api/fauna');

        $response
            ->assertStatus(503)
            ->assertJsonPath('message', 'RDF semantic engine is unavailable.');
    }
}
