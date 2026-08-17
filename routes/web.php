<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [UrlShortenerController::class, 'index'])->name('home');
Route::post('/shorten', [UrlShortenerController::class, 'create'])->middleware('throttle:10,1')->name('shorten');
Route::post('/password/{code}', [UrlShortenerController::class, 'verifyPassword'])->name('password.verify');
Route::get('/qr/{code}', [UrlShortenerController::class, 'qrCode'])->name('qr.code');
Route::post('/confirm/{code}', [UrlShortenerController::class, 'confirm'])->name('confirm');
Route::get('/{code}', [UrlShortenerController::class, 'redirect'])->where('code', '[A-Za-z0-9_-]{3,30}');
