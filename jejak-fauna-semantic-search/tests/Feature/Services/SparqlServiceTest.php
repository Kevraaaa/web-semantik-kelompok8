<?php

namespace Tests\Feature\Services;

use Tests\TestCase;

class SparqlServiceTest extends TestCase
{
    public function test_rdf_lib_reads_multiple_relations_and_multiline_types(): void
    {
        config(['services.rdf.dataset_path' => base_path('tests/Fixtures/fauna-multiple-objects.ttl')]);

        $response = $this->getJson('/api/fauna?famili[]=Muridae&per_page=10');

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.kelompok', 'Mamalia')
            ->assertJsonPath('data.0.famili_key', 'Muridae')
            ->assertJsonPath('data.0.latitude', 0.8)
            ->assertJsonPath('data.0.longitude', 114);
        $this->assertSame(
            ['Hutan Hujan Tropis', 'Sungai Dan Rawa Air Tawar Kalimantan'],
            $response->json('data.0.habitat'),
        );
        $this->assertSame(
            ['Buahbuahan Daun Dan Serangga', 'Serangga Kecil'],
            $response->json('data.0.makanan'),
        );
        $this->assertSame(
            ['Ancaman Dua', 'Ancaman Satu'],
            $response->json('data.0.ancaman'),
        );
    }

    public function test_fauna_without_valid_wgs84_coordinates_returns_null_coordinates(): void
    {
        config(['services.rdf.dataset_path' => base_path('tests/Fixtures/fauna-multiple-objects.ttl')]);

        $response = $this->getJson('/api/fauna?query=Fauna%20Tanpa%20Koordinat');

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.latitude', null)
            ->assertJsonPath('data.0.longitude', null);
    }
}
