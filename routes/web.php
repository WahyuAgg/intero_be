<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\GoogleAuthController;

Route::get('/google/login', [GoogleAuthController::class, 'login']);
Route::get('/oauth2callback', [GoogleAuthController::class, 'callback']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});


Route::get('/google/example-redirect', [GoogleAuthController::class, 'exampleGoogleRedirect'])->name('google.exampleRedirect');
Route::get('/google/example-callback', [GoogleAuthController::class, 'exampleGoogleCallback'])->name('google.exampleCallback');
