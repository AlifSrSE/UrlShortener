<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Click;
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
        $shortUrl = $response->json('short_url');
        $shortCode = parse_url($shortUrl, PHP_URL_PATH);
        $shortCode = ltrim($shortCode, '/');

        $previewResponse = $this->get("/{$shortCode}");
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('You are leaving this site');
        $previewResponse->assertSee('https://example.com');

        $confirmResponse = $this->post("/confirm/{$shortCode}");
        $confirmResponse->assertRedirect('https://example.com');
    }

    /** @test */
    public function it_handles_non_existent_short_codes()
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_records_a_click_when_short_url_is_confirmed()
    {
        $response = $this->postJson('/shorten', ['url' => 'https://example.com']);
        $shortUrl = $response->json('short_url');
        $shortCode = parse_url($shortUrl, PHP_URL_PATH);
        $shortCode = ltrim($shortCode, '/');

        $this->get("/{$shortCode}");
        $this->assertDatabaseMissing('clicks', [
            'ip_address' => '127.0.0.1',
        ]);

        $this->post("/confirm/{$shortCode}");
        $this->assertDatabaseHas('clicks', [
            'ip_address' => '127.0.0.1',
        ]);
    }

    /** @test */
    public function it_records_multiple_clicks_for_multiple_confirmations()
    {
        $response = $this->postJson('/shorten', ['url' => 'https://example.com']);
        $shortUrl = $response->json('short_url');
        $shortCode = parse_url($shortUrl, PHP_URL_PATH);
        $shortCode = ltrim($shortCode, '/');

        $this->post("/confirm/{$shortCode}");
        $this->post("/confirm/{$shortCode}");

        $this->assertEquals(2, Click::count());
    }

    /** @test */
    public function it_creates_a_short_url_with_custom_alias()
    {
        $response = $this->postJson('/shorten', [
            'url' => 'https://example.com',
            'alias' => 'my-custom-link',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['short_url']);
        $this->assertDatabaseHas('short_urls', [
            'alias' => 'my-custom-link',
        ]);
    }

    /** @test */
    public function it_redirects_using_custom_alias()
    {
        $response = $this->postJson('/shorten', [
            'url' => 'https://example.com',
            'alias' => 'my-custom-link',
        ]);
        $shortUrl = $response->json('short_url');
        $shortCode = parse_url($shortUrl, PHP_URL_PATH);
        $shortCode = ltrim($shortCode, '/');

        $previewResponse = $this->get("/{$shortCode}");
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('You are leaving this site');

        $confirmResponse = $this->post("/confirm/{$shortCode}");
        $confirmResponse->assertRedirect('https://example.com');
    }

    /** @test */
    public function it_rejects_duplicate_aliases()
    {
        $this->postJson('/shorten', [
            'url' => 'https://example1.com',
            'alias' => 'my-custom-link',
        ]);

        $response = $this->postJson('/shorten', [
            'url' => 'https://example2.com',
            'alias' => 'my-custom-link',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['alias']);
    }

    /** @test */
    public function it_creates_a_short_url_with_expiration()
    {
        $expiresAt = now()->addDays(7)->format('Y-m-d\TH:i');

        $response = $this->postJson('/shorten', [
            'url' => 'https://example.com',
            'expires_at' => $expiresAt,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('short_urls', [
            'original_url' => 'https://example.com',
        ]);
    }

    /** @test */
    public function it_returns_410_for_expired_short_url()
    {
        $shortUrl = ShortUrl::create([
            'original_url' => 'https://example.com',
            'short_code' => 'expired1',
            'expires_at' => now()->subDay(),
        ]);
        $shortCode = $shortUrl->short_code;

        $redirectResponse = $this->get("/{$shortCode}");
        $redirectResponse->assertStatus(410);
    }

    /** @test */
    public function it_creates_a_short_url_with_password()
    {
        $response = $this->postJson('/shorten', [
            'url' => 'https://example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('short_urls', [
            'original_url' => 'https://example.com',
        ]);
    }

    /** @test */
    public function it_shows_password_form_for_protected_url()
    {
        $shortUrl = ShortUrl::create([
            'original_url' => 'https://example.com',
            'short_code' => 'protected1',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
        ]);

        $response = $this->get("/{$shortUrl->short_code}");

        $response->assertStatus(200);
        $response->assertSee('Password Required');
    }

    /** @test */
    public function it_redirects_after_correct_password()
    {
        $shortUrl = ShortUrl::create([
            'original_url' => 'https://example.com',
            'short_code' => 'protected2',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
        ]);

        $response = $this->post("/password/{$shortUrl->short_code}", [
            'password' => 'secret123',
        ]);

        $response->assertRedirect("/{$shortUrl->short_code}");
    }

    /** @test */
    public function it_rejects_wrong_password()
    {
        $shortUrl = ShortUrl::create([
            'original_url' => 'https://example.com',
            'short_code' => 'protected3',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
        ]);

        $response = $this->post("/password/{$shortUrl->short_code}", [
            'password' => 'wrongpass',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function it_generates_qr_code_for_short_url()
    {
        $response = $this->postJson('/shorten', ['url' => 'https://example.com']);
        $shortUrl = $response->json('short_url');
        $shortCode = parse_url($shortUrl, PHP_URL_PATH);
        $shortCode = ltrim($shortCode, '/');

        $qrResponse = $this->get("/qr/{$shortCode}");
        $qrResponse->assertStatus(302);
        $qrResponse->assertRedirect('https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($shortUrl));
    }
}
