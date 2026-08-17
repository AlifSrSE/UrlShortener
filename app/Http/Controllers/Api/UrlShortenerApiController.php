<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UrlShortenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UrlShortenerApiController extends Controller
{
    public function __construct(private UrlShortenerService $urlShortener) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'url', 'max:2048', 'regex:/^https?:\/\//i'],
            'alias' => ['nullable', 'string', 'alpha_dash', 'min:3', 'max:30', 'unique:short_urls,alias'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
        ]);

        $shortUrl = $this->urlShortener->shorten(
            $request->input('url'),
            $request->input('alias'),
            $request->input('expires_at')
        );

        if ($request->filled('password')) {
            $shortUrl->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->input('password')),
            ]);
        }

        return response()->json([
            'short_url' => $shortUrl->short_url,
        ], 201);
    }

    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'urls' => ['required', 'array', 'min:1', 'max:10'],
            'urls.*' => ['required', 'url', 'max:2048', 'regex:/^https?:\/\//i'],
        ]);

        $results = [];

        foreach ($request->input('urls', []) as $url) {
            $shortUrl = $this->urlShortener->shorten($url);
            $results[] = [
                'original_url' => $url,
                'short_url' => $shortUrl->short_url,
            ];
        }

        return response()->json([
            'data' => $results,
        ], 201);
    }

    public function qrCode($code): \Symfony\Component\HttpFoundation\Response
    {
        $shortUrl = $this->urlShortener->findForRedirect($code);

        return redirect()->away('https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($shortUrl->short_url));
    }
}
