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
        foreach ($response->json('data') as $fauna) {
            $this->assertSame('Kritis (CR)', $fauna['status_iucn']);
        }
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
