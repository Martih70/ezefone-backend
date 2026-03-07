<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Customer;

class CustomerPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect the authenticated user to their Stripe Customer Portal.
     * Creates a Stripe customer record on first visit if one doesn't exist.
     */
    public function redirect(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $user = Auth::user();

        if (!$user->stripe_customer_id) {
            $customer = Customer::create([
                'email'    => $user->email,
                'name'     => $user->name,
                'metadata' => ['user_id' => $user->id],
            ]);

            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        $session = PortalSession::create([
            'customer'   => $user->stripe_customer_id,
            'return_url' => url('/'),
        ]);

        return redirect($session->url);
    }
}
