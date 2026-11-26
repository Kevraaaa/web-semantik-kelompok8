<?php

namespace Tests\Feature\Services;

use App\Services\SparqlService;
use Tests\TestCase;

class SparqlServiceTest extends TestCase
{
    public function test_comma_separated_objects_and_multiline_types_are_parsed(): void
    {
        $service = new class extends SparqlService
        {
            public function parseFixture(string $path): array
            {
                $this->ttlPath = $path;

                return $this->parseTtl();
            }
        };

        $fauna = $service->parseFixture(base_path('tests/Fixtures/fauna-multiple-objects.ttl'))[0];

        $this->assertSame('Mamalia', $fauna['kelompok']);
        $this->assertSame(
            ['HutanHujanTropis', 'SungaiDanRawaAirTawarKalimantan'],
            $fauna['habitat_keys'],
        );
        $this->assertSame(['BuahbuahanDaunDanSerangga', 'SeranggaKecil'], $fauna['makanan_keys']);
        $this->assertSame(['AncamanSatu', 'AncamanDua'], $fauna['ancaman_keys']);
        $this->assertSame('Muridae', $fauna['famili_key']);
        $this->assertSame('Muridae', $fauna['famili']);
    }
}
