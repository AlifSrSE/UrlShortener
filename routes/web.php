<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [UrlShortenerController::class, 'index'])->name('home');
Route::post('/shorten', [UrlShortenerController::class, 'create'])->middleware('throttle:10,1')->name('shorten');
Route::get('/{code}', [UrlShortenerController::class, 'redirect'])->where('code', '[A-Za-z0-9]{6}');
