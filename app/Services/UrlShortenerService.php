<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UrlShortenerService
{
    public function shorten(string $url, ?string $alias = null, ?string $expiresAt = null): ShortUrl
    {
        $existing = ShortUrl::where('original_url', $url)->first();

        if ($existing) {
            return $existing;
        }

        if ($alias) {
            $shortCode = $alias;
        } else {
            $maxAttempts = 5;
            $attempt = 0;

            do {
                $shortCode = Str::random(6);
                $attempt++;
            } while (ShortUrl::where('short_code', $shortCode)->exists() && $attempt < $maxAttempts);
        }

        $shortUrl = ShortUrl::create([
            'original_url' => $url,
            'short_code' => $shortCode,
            'alias' => $alias,
            'expires_at' => $expiresAt,
        ]);

        Cache::forever("short_url:{$shortUrl->short_code}", $shortUrl);

        return $shortUrl;
    }

    public function findForRedirect(string $code): ShortUrl
    {
        return Cache::remember("short_url:{$code}", 3600, function () use ($code) {
            return ShortUrl::where('alias', $code)
                ->orWhere('short_code', $code)
                ->firstOrFail();
        });
    }
}

