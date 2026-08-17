<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UrlShortenerApiController;

Route::post('/shorten', [UrlShortenerApiController::class, 'store']);
Route::post('/shorten/bulk', [UrlShortenerApiController::class, 'bulk']);
Route::get('/qr/{code}', [UrlShortenerApiController::class, 'qrCode']);
