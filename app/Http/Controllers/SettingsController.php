<?php

namespace App\Http\Controllers;

use App\Mail\CheckinWelcome;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Touch last_opened_at on every app open — used by the daily check-in scheduler
        $user->update(['last_opened_at' => now()]);

        $sosContact = $user->sos_contact_id
            ? Contact::where('id', $user->sos_contact_id)
                     ->where('user_id', $user->id)
                     ->first()
            : null;

        return response()->json([
            'user_name'            => $user->name,
            'sos_contact_id'       => $user->sos_contact_id,
            'sos_contact_name'     => $sosContact?->name,
            'sos_contact_phone'    => $sosContact?->phone,
            'sos_location_sharing' => (bool) $user->sos_location_sharing,
            'checkin_enabled'      => (bool) $user->checkin_enabled,
            'checkin_time'         => $user->checkin_time ?? '11:00',
            'checkin_email'        => $user->checkin_email,
            'checkin_paused_until' => $user->checkin_paused_until?->toDateString(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sos_contact_id'       => 'sometimes|nullable|integer',
            'sos_location_sharing' => 'sometimes|boolean',
            'checkin_enabled'      => 'sometimes|boolean',
            'checkin_time'         => 'sometimes|nullable|string|regex:/^\d{2}:\d{2}$/',
            'checkin_email'        => 'sometimes|nullable|email|max:255',
            'checkin_paused_until' => 'sometimes|nullable|date',
        ]);

        $user = $request->user();

        if (!empty($validated['sos_contact_id'])) {
            Contact::where('id', $validated['sos_contact_id'])
                   ->where('user_id', $user->id)
                   ->firstOrFail();
        }

        $oldCheckinEmail = $user->checkin_email;

        $user->update($validated);

        // Send welcome email when checkin_email is first set or changed
        $newCheckinEmail = $user->fresh()->checkin_email;
        \Log::info('checkin_email_check', [
            'old'       => $oldCheckinEmail,
            'new'       => $newCheckinEmail,
            'mailer'    => config('mail.default'),
            'will_send' => !empty($newCheckinEmail) && $newCheckinEmail !== $oldCheckinEmail,
        ]);
        if (!empty($newCheckinEmail) && $newCheckinEmail !== $oldCheckinEmail) {
            Mail::to($newCheckinEmail)->send(new CheckinWelcome($user));
        }

        return response()->json(['success' => true]);
    }
}
