<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ShortUrl;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shortens_a_valid_url()
    {
        $response = $this->postJson('/shorten', ['url' => 'https://example.com']);

        $response->assertStatus(200);
        $response->assertJsonStructure(['short_url']);
        $this->assertDatabaseHas('short_urls', [
            'original_url' => 'https://example.com',
        ]);
    }

    /** @test */
    public function it_fails_to_shorten_an_invalid_url()
    {
        $response = $this->postJson('/shorten', ['url' => 'invalid-url']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function it_rejects_non_http_urls()
    {
        $response = $this->postJson('/shorten', ['url' => 'javascript:alert(1)']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function it_returns_the_same_short_url_for_an_existing_long_url()
    {
        $originalUrl = 'https://example.com';

        $response1 = $this->postJson('/shorten', ['url' => $originalUrl]);
        $shortUrl1 = $response1->json('short_url');

        $response2 = $this->postJson('/shorten', ['url' => $originalUrl]);
        $shortUrl2 = $response2->json('short_url');

        $this->assertEquals($shortUrl1, $shortUrl2);
    }

    /** @test */
    public function it_generates_unique_short_urls_for_different_long_urls()
    {
        $response1 = $this->postJson('/shorten', ['url' => 'https://example1.com']);
        $response2 = $this->postJson('/shorten', ['url' => 'https://example2.com']);

        $shortUrl1 = $response1->json('short_url');
        $shortUrl2 = $response2->json('short_url');

        $this->assertNotEquals($shortUrl1, $shortUrl2);
    }

    /** @test */
    public function it_redirects_to_the_original_url_from_a_short_url()
    {
        $response = $this->postJson('/shorten', ['url' => 'https://example.com']);
        $shortCode = str_replace(url('/'), '', $response->json('short_url'));

        $redirectResponse = $this->get("/{$shortCode}");
        $redirectResponse->assertRedirect('https://example.com');
    }

    /** @test */
    public function it_handles_non_existent_short_codes()
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
    }
}
