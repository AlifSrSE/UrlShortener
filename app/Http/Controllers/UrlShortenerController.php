<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UrlShortenerController extends Controller
{
    public function index()
    {
        return view('shortener');
    }

    public function create(Request $request)
    {
        $request->validate([
            'url' => ['required', 'url', 'max:2048', 'regex:/^https?:\/\//i'],
        ]);

        $originalUrl = $request->input('url');

        $existing = ShortUrl::where('original_url', $originalUrl)->first();

        if ($existing) {
            return response()->json([
                'short_url' => url($existing->short_code),
            ]);
        }

        $maxAttempts = 5;
        $attempt = 0;

        do {
            $shortCode = Str::random(6);
            $attempt++;
        } while (ShortUrl::where('short_code', $shortCode)->exists() && $attempt < $maxAttempts);

        $shortUrl = ShortUrl::create([
            'original_url' => $originalUrl,
            'short_code' => $shortCode,
        ]);

        return response()->json([
            'short_url' => url($shortUrl->short_code),
        ]);
    }

    public function redirect($code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();

        return redirect($shortUrl->original_url);
    }
}
