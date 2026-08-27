<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    /**
     * Create a Stripe Checkout session and redirect to Stripe.
     */
    public function checkout()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        [$tier, $priceId] = $this->resolvePricingTier();

        $session = Session::create([
            'mode'                 => 'payment',
            'line_items'           => [[
                'price'    => $priceId,
                'quantity' => 1,
            ]],
            'success_url'          => url('/payment/success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'            => 'https://ezefone.co.uk',
            'payment_method_types'  => ['card'],
            'allow_promotion_codes' => true,
            'metadata'              => ['pricing_tier' => $tier],
        ]);

        return redirect($session->url);
    }

    /**
     * Decide whether this checkout should use the Early Adopter or Standard
     * price: the first N (config('services.stripe.early_adopter_limit'))
     * early-adopter purchases get the discounted price, after which every
     * new checkout switches to Standard automatically.
     *
     * @return array{0: string, 1: string} [tier, priceId]
     */
    protected function resolvePricingTier(): array
    {
        $limit = (int) config('services.stripe.early_adopter_limit');

        $earlyAdopterCount = User::where('pricing_tier', 'early_adopter')->count();

        if ($earlyAdopterCount < $limit) {
            return ['early_adopter', config('services.stripe.price_early_adopter')];
        }

        return ['standard', config('services.stripe.price_standard')];
    }

    /**
     * Verify payment and redirect to the PWA with access granted.
     */
    public function success(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect('/');
        }

        $session = Session::retrieve($sessionId, ['expand' => ['customer_details']]);

        if ($session->payment_status !== 'paid') {
            return redirect('/');
        }

        // Store verified payment info in session for account creation
        session([
            'payment_verified'  => true,
            'payment_email'     => $session->customer_details?->email,
            'stripe_session_id' => $sessionId,
        ]);

        return redirect('/create-account');
    }
}
