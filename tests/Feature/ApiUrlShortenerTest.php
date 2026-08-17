<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => 'test-api-key']);
    }

    /** @test */
    public function it_rejects_api_requests_without_key()
    {
        $response = $this->postJson('/api/shorten', ['url' => 'https://example.com']);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_shortens_url_via_api()
    {
        $response = $this->withHeaders(['X-API-Key' => 'test-api-key'])
            ->postJson('/api/shorten', ['url' => 'https://example.com']);

        $response->assertStatus(201);
        $response->assertJsonStructure(['short_url']);
    }

    /** @test */
    public function it_supports_bulk_shortening_via_api()
    {
        $response = $this->withHeaders(['X-API-Key' => 'test-api-key'])
            ->postJson('/api/shorten/bulk', [
                'urls' => ['https://example1.com', 'https://example2.com'],
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => [
            ['original_url', 'short_url'],
            ['original_url', 'short_url'],
        ]]);
    }

    /** @test */
    public function it_rejects_bulk_requests_with_too_many_urls()
    {
        $response = $this->withHeaders(['X-API-Key' => 'test-api-key'])
            ->postJson('/api/shorten/bulk', [
                'urls' => array_fill(0, 11, 'https://example.com'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['urls']);
    }
}
