<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\PaymentRegistrationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// In-app login for PWA (returns JSON token so the PWA never leaves the app shell)
Route::post('/api/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);
    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Incorrect email or password.'], 401);
    }
    $token = Auth::user()->createToken('pwa')->plainTextToken;
    return response()->json(['token' => $token]);
})->middleware('throttle:5,1');

// Serve the PWA
Route::get('/', function () {
    return response(file_get_contents(public_path('index.html')), 200)
        ->header('Content-Type', 'text/html; charset=utf-8');
});

// API Routes — auth required so contacts are per-user
Route::prefix('api')->middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::delete('/contacts/{contactId}', [ContactController::class, 'destroy']);
    Route::post('/contacts/{contactId}/favorite', [ContactController::class, 'addFavorite']);
    Route::delete('/contacts/{contactId}/favorite', [ContactController::class, 'removeFavorite']);
    Route::post('/feedback', [FeedbackController::class, 'store']);
});

// Stripe Checkout
Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Post-payment account creation
Route::get('/create-account', [PaymentRegistrationController::class, 'show'])->name('create.account');
Route::post('/create-account', [PaymentRegistrationController::class, 'store']);

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

// Printable A5 guide
Route::get('/guide', function () {
    return response(file_get_contents(public_path('guide.html')), 200)
        ->header('Content-Type', 'text/html; charset=utf-8');
});

// Simple GET logout for PWA (no CSRF token available in static HTML)
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth');

// Auth routes (login, register, password reset) — added by Breeze
require __DIR__.'/auth.php';
