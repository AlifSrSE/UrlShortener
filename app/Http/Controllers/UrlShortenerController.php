<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\ShortUrl;
use App\Services\UrlShortenerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UrlShortenerController extends Controller
{
    public function __construct(private UrlShortenerService $urlShortener) {}

    public function index()
    {
        return view('shortener');
    }

    public function create(Request $request)
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
        ]);
    }

    public function redirect(Request $request, $code)
    {
        $shortUrl = $this->urlShortener->findForRedirect($code);

        if ($shortUrl->expires_at && $shortUrl->expires_at->isPast()) {
            abort(410, 'This short URL has expired.');
        }

        if ($shortUrl->hasPassword() && !$request->session()->get("short_url_access_{$shortUrl->id}")) {
            return view('password', ['shortUrl' => $shortUrl]);
        }

        return view('preview', ['shortUrl' => $shortUrl]);
    }

    public function confirm(Request $request, $code)
    {
        $shortUrl = $this->urlShortener->findForRedirect($code);

        if ($shortUrl->expires_at && $shortUrl->expires_at->isPast()) {
            abort(410, 'This short URL has expired.');
        }

        if ($shortUrl->hasPassword() && !$request->session()->get("short_url_access_{$shortUrl->id}")) {
            return redirect()->to("/{$code}");
        }

        Click::create([
            'short_url_id' => $shortUrl->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'clicked_at' => now(),
        ]);

        return redirect($shortUrl->original_url);
    }

    public function verifyPassword(Request $request, $code)
    {
        $shortUrl = $this->urlShortener->findForRedirect($code);

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!$shortUrl->verifyPassword($request->input('password'))) {
            return back()->withErrors(['password' => 'Incorrect password.'])->withInput();
        }

        $request->session()->put("short_url_access_{$shortUrl->id}", true);

        return redirect()->to("/{$code}");
    }

    public function qrCode($code)
    {
        $shortUrl = $this->urlShortener->findForRedirect($code);

        return redirect()->away('https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($shortUrl->short_url));
    }
}
