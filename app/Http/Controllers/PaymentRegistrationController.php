<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user, remember: true);

        session()->forget(['payment_verified', 'payment_email', 'stripe_session_id']);

        return redirect('/?paid=1');
    }
}
