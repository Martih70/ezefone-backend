<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        // Always save to database first — guaranteed path regardless of mail config
        $feedback = Feedback::create([
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        // Send email as best-effort — never fail the request if this throws
        try {
            Mail::raw(
                "Feedback from: {$user->name} <{$user->email}>\n\n{$validated['message']}",
                function ($mail) use ($user) {
                    $mail->to('info@ezefone.co.uk')
                         ->subject("Ezefone App Feedback — {$user->name}")
                         ->replyTo($user->email, $user->name);
                }
            );
            $feedback->update(['emailed' => true]);
        } catch (\Throwable $e) {
            Log::error('Feedback email failed', [
                'feedback_id' => $feedback->id,
                'error'       => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
