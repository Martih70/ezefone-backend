<?php

namespace App\Http\Controllers;

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

        $session = Session::create([
            'mode'                 => 'payment',
            'line_items'           => [[
                'price'    => config('services.stripe.price_id'),
                'quantity' => 1,
            ]],
            'success_url'          => url('/payment/success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => 'https://ezefone.co.uk',
            'payment_method_types' => ['card'],
        ]);

        return redirect($session->url);
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

        $session = Session::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            return redirect('/');
        }

        // Redirect to the PWA with a paid flag — JS stores it in localStorage
        return redirect('/?paid=1');
    }
}
