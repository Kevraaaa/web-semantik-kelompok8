<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class FaunaApiTest extends TestCase
{
    public function test_fauna_index_returns_paginated_dataset_data(): void
    {
        $response = $this->getJson('/api/fauna?per_page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['slug', 'nama', 'status_iucn']],
                'meta' => ['page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_fauna_search_and_status_filter_are_applied(): void
    {
        $this->getJson('/api/fauna?query=orangutan&per_page=10')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'orangutan-kalimantan');

        $response = $this->getJson('/api/fauna?status[]=StatusKritis&per_page=10');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
        foreach ($response->json('data') as $fauna) {
            $this->assertSame('Kritis (CR)', $fauna['status_iucn']);
        }
    }

    public function test_fauna_group_and_family_filters_return_parsed_values(): void
    {
        $familyOptions = $this->getJson('/api/fauna/filter-options');

        $familyOptions->assertOk()
            ->assertJsonPath('data.famili.Bufonidae', 'Bufonidae');
        $this->assertContains('Ikan', $familyOptions->json('data.kelompok'));

        $familyResponse = $this->getJson('/api/fauna?famili[]=Bufonidae&per_page=100');

        $familyResponse->assertOk();
        $this->assertNotEmpty($familyResponse->json('data'));
        foreach ($familyResponse->json('data') as $fauna) {
            $this->assertSame('Bufonidae', $fauna['famili_key']);
            $this->assertSame('Bufonidae', $fauna['famili']);
        }

        $this->getJson('/api/fauna?kelompok[]=Mamalia&per_page=100')
            ->assertOk()
            ->assertJsonPath('data.0.kelompok', 'Mamalia');

        $fishResponse = $this->getJson('/api/fauna?kelompok[]=Ikan&per_page=100');

        $fishResponse->assertOk();
        $this->assertGreaterThan(0, $fishResponse->json('meta.total'));
        foreach ($fishResponse->json('data') as $fauna) {
            $this->assertSame('Ikan', $fauna['kelompok']);
        }

        $this->getJson('/api/fauna?famili=Bufonidae')
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['famili']]);
    }

    public function test_filter_option_camel_case_keys_match_rdf_relation_values(): void
    {
        $options = $this->getJson('/api/fauna/filter-options')
            ->assertOk()
            ->json('data');

        foreach ([
            'habitat' => 'habitat',
            'makanan' => 'makanan',
            'ancaman' => 'ancaman',
        ] as $filter => $field) {
            $key = array_key_first($options[$filter]);
            $label = $options[$filter][$key];
            $response = $this->getJson('/api/fauna?'.$filter.'[]='.urlencode($key).'&per_page=100');

            $response->assertOk();
            $fauna = $response->json('data');
            $this->assertNotEmpty($fauna, "The {$filter} option key should match RDF data.");

            foreach ($fauna as $animal) {
                $this->assertContains(
                    mb_strtolower($label),
                    array_map('mb_strtolower', $animal[$field]),
                );
            }
        }
    }

    public function test_semantic_search_does_not_match_short_synonyms_inside_other_words(): void
    {
        $response = $this->postJson('/api/pencarian-semantik', [
            'kalimat' => 'green crystal vulture fairy',
        ]);

        $response->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_detail_recommendation_semantic_search_and_graph_return_json(): void
    {
        $this->getJson('/api/fauna/orangutan-kalimantan')
            ->assertOk()
            ->assertJsonPath('data.slug', 'orangutan-kalimantan');

        $this->getJson('/api/fauna/orangutan-kalimantan/rekomendasi?limit=2')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->postJson('/api/pencarian-semantik', [
            'kalimat' => 'satwa pemakan buah di hutan',
        ])->assertOk()->assertJsonStructure([
            'data' => [['fauna', 'skor', 'alasan_kecocokan']],
        ]);

        $this->getJson('/api/knowledge-graph?limit=2')
            ->assertOk()
            ->assertJsonStructure([
                'nodes' => [['id', 'label', 'type']],
                'edges' => [['from', 'to', 'label', 'type']],
            ]);
    }

    public function test_knowledge_graph_default_is_varied_and_focus_filters_are_applied(): void
    {
        $defaultGraph = $this->getJson('/api/knowledge-graph?limit=50');

        $defaultGraph->assertOk();
        $defaultGroups = array_values(array_unique(array_column(
            array_filter($defaultGraph->json('nodes'), fn ($node) => $node['type'] === 'kelompok'),
            'label',
        )));
        $this->assertGreaterThan(1, count($defaultGroups));

        $focusedGraph = $this->getJson('/api/knowledge-graph?kelompok=Amfibi&limit=10');

        $focusedGraph->assertOk();
        $focusedGroups = array_values(array_unique(array_column(
            array_filter($focusedGraph->json('nodes'), fn ($node) => $node['type'] === 'kelompok'),
            'label',
        )));
        $this->assertSame(['Amfibi'], $focusedGroups);

        $habitatGraph = $this->getJson('/api/knowledge-graph?habitat=SungaiDanRawaAirTawarKalimantan&relasi[]=hidupDi');

        $habitatGraph->assertOk();
        $this->assertNotEmpty($habitatGraph->json('nodes'));
        $habitatNodes = array_values(array_filter(
            $habitatGraph->json('nodes'),
            fn ($node) => $node['type'] === 'habitat',
        ));
        $this->assertSame(
            ['Sungai dan Rawa Air Tawar Kalimantan'],
            array_column($habitatNodes, 'label'),
        );
        foreach ($habitatGraph->json('edges') as $edge) {
            $this->assertSame('hidupDi', $edge['type']);
        }
    }

    public function test_invalid_and_missing_api_inputs_return_structured_errors(): void
    {
        $this->getJson('/api/fauna/tidak-ada')
            ->assertNotFound()
            ->assertJsonPath('slug', 'tidak-ada');

        $this->postJson('/api/pencarian-semantik', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['kalimat']]);

        $this->getJson('/api/knowledge-graph?limit=999')
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['limit']]);
    }
}
