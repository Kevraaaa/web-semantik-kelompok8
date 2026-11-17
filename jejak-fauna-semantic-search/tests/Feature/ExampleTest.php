<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_mock_pages_render_without_backend_services(): void
    {
        $paths = [
            '/eksplorasi',
            '/eksplorasi/orangutan-kalimantan',
            '/bandingkan',
            '/peta',
            '/knowledge-graph',
            '/masuk',
            '/daftar',
            '/profil',
            '/lapor',
            '/admin/laporan',
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
        }
    }
}
