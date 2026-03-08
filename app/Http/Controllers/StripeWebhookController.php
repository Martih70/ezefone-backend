<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->payment_status !== 'paid') {
                return response('Not paid', 200);
            }

            $email = $session->customer_details?->email;
            $name  = $session->customer_details?->name ?? 'Ezefone User';

            if (!$email) {
                return response('No email', 200);
            }

            // If user already exists (normal redirect flow completed), nothing to do
            if (User::where('email', $email)->exists()) {
                return response('OK', 200);
            }

            // Create account with random password — user sets their own via reset email
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make(Str::random(32)),
            ]);

            // Send password reset email so user can set their password and sign in
            Password::sendResetLink(['email' => $email]);
        }

        return response('OK', 200);
    }
}
