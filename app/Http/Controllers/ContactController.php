<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    private function userId(): int
    {
        return Auth::id();
    }

    public function index()
    {
        $userId = $this->userId();

        $contacts = Contact::where('user_id', $userId)
            ->orderBy('name')
            ->get();

        $favorites = Favorite::whereHas('contact', fn($q) => $q->where('user_id', $userId))
            ->with('contact')
            ->orderBy('order')
            ->get();

        return response()->json([
            'contacts' => $contacts,
            'favorites' => $favorites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
        ]);

        $contact = Contact::create(array_merge($validated, [
            'user_id' => $this->userId(),
        ]));

        return response()->json($contact, 201);
    }

    public function addFavorite($contactId)
    {
        $userId = $this->userId();

        $contact = Contact::where('id', $contactId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $maxOrder = Favorite::whereHas('contact', fn($q) => $q->where('user_id', $userId))
            ->max('order') ?? 0;

        $favorite = Favorite::create([
            'contact_id' => $contact->id,
            'order'      => $maxOrder + 1,
        ]);

        $favorite->load('contact');

        return response()->json($favorite, 201);
    }

    public function removeFavorite($contactId)
    {
        $userId = $this->userId();

        Contact::where('id', $contactId)
            ->where('user_id', $userId)
            ->firstOrFail();

        Favorite::where('contact_id', $contactId)->delete();

        return response()->json(null, 204);
    }

    public function destroy($contactId)
    {
        $contact = Contact::where('id', $contactId)
            ->where('user_id', $this->userId())
            ->firstOrFail();

        Favorite::where('contact_id', $contactId)->delete();
        $contact->delete();

        return response()->json(null, 204);
    }
}
