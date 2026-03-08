<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        Mail::raw(
            "Feedback from: {$user->name} <{$user->email}>\n\n{$validated['message']}",
            function ($mail) use ($user) {
                $mail->to('info@ezefone.co.uk')
                     ->subject("Ezefone App Feedback — {$user->name}")
                     ->replyTo($user->email, $user->name);
            }
        );

        return response()->json(['success' => true]);
    }
}
