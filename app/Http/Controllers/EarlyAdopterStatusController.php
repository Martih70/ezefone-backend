<?php

namespace App\Http\Controllers;

use App\Models\User;

class EarlyAdopterStatusController extends Controller
{
    /**
     * Public, unauthenticated JSON endpoint the static ezefone-web frontend
     * fetches at runtime to show a live "X of 100 spots claimed" counter.
     * Reuses the same pricing_tier count CheckoutController checks before
     * deciding which Stripe price to use, so the two can never disagree.
     */
    public function show()
    {
        return response()->json([
            'claimed' => User::where('pricing_tier', 'early_adopter')->count(),
            'limit'   => (int) config('services.stripe.early_adopter_limit'),
        ]);
    }
}
