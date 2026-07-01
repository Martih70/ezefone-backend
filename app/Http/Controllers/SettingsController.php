<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $sosContact = $user->sos_contact_id
            ? Contact::where('id', $user->sos_contact_id)
                     ->where('user_id', $user->id)
                     ->first()
            : null;

        return response()->json([
            'user_name'            => $user->name,
            'family_code_word'     => $user->family_code_word,
            'sos_contact_id'       => $user->sos_contact_id,
            'sos_contact_name'     => $sosContact?->name,
            'sos_contact_phone'    => $sosContact?->phone,
            'sos_location_sharing' => (bool) $user->sos_location_sharing,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'family_code_word'     => 'sometimes|nullable|string|max:100',
            'sos_contact_id'       => 'sometimes|nullable|integer',
            'sos_location_sharing' => 'sometimes|boolean',
        ]);

        // Ensure sos_contact_id belongs to this user
        if (!empty($validated['sos_contact_id'])) {
            Contact::where('id', $validated['sos_contact_id'])
                   ->where('user_id', $request->user()->id)
                   ->firstOrFail();
        }

        $request->user()->update($validated);

        return response()->json(['success' => true]);
    }
}
