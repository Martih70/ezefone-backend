<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Save to database first — guaranteed regardless of mail config
        $feedbackId = null;
        try {
            $feedbackId = DB::table('feedback')->insertGetId([
                'user_id'    => $user->id,
                'message'    => $validated['message'],
                'emailed'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Feedback DB save failed', ['error' => $e->getMessage()]);
        }

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
            if ($feedbackId) {
                DB::table('feedback')->where('id', $feedbackId)->update(['emailed' => true]);
            }
        } catch (\Throwable $e) {
            Log::error('Feedback email failed', [
                'feedback_id' => $feedbackId,
                'error'       => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
