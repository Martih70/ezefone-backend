<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response(file_get_contents(public_path('index.html')), 200)
        ->header('Content-Type', 'text/html; charset=utf-8');
});

// API Routes
Route::prefix('api')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::delete('/contacts/{contactId}', [ContactController::class, 'destroy']);
    Route::post('/contacts/{contactId}/favorite', [ContactController::class, 'addFavorite']);
    Route::delete('/contacts/{contactId}/favorite', [ContactController::class, 'removeFavorite']);
    Route::get('/test', function () {
        return ['message' => 'test works'];
    });
});
