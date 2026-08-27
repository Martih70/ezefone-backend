<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentRegistrationController extends Controller
{
    /**
     * Show the account creation form after a verified payment.
     */
    public function show()
    {
        if (!session('payment_verified')) {
            return redirect('/');
        }

        return view('auth.create-account', [
            'email' => session('payment_email'),
        ]);
    }

    /**
     * Create the account, log the user in, and unlock the app.
     */
    public function store(Request $request)
    {
        if (!session('payment_verified')) {
            return redirect('/');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Ensure the submitted email matches the Stripe-verified email.
        if ($validated['email'] !== session('payment_email')) {
            return back()->withErrors(['email' => 'Email must match your Stripe payment email.'])->withInput();
        }

        // The Stripe webhook may have already created this account (race condition).
        // If so, update the placeholder record with the real name/password instead.
        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $user->update([
                'name'     => $validated['name'],
                'password' => Hash::make($validated['password']),
            ]);
        } else {
            $user = User::create([
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'password'     => Hash::make($validated['password']),
                'pricing_tier' => $this->pricingTierFromSession(),
            ]);
        }

        Auth::login($user);

        session()->forget(['payment_verified', 'payment_email', 'stripe_session_id']);

        $token = $user->createToken('pwa')->plainTextToken;

        return redirect('/?paid=1&token=' . $token);
    }

    /**
     * Look up which pricing tier the verified Stripe session paid for, in
     * case this account is being created before the webhook has landed.
     */
    protected function pricingTierFromSession(): ?string
    {
        $sessionId = session('stripe_session_id');

        if (!$sessionId) {
            return null;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        return Session::retrieve($sessionId)->metadata['pricing_tier'] ?? null;
    }
}
