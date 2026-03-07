<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Serve the PWA
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

// Stripe Checkout
Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');

// Stripe Customer Portal
Route::get('/customer-portal', [CustomerPortalController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('customer.portal');

// Breeze profile management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth routes (login, register, password reset) — added by Breeze
require __DIR__.'/auth.php';
