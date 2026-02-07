<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Favorite;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::orderBy('name')->get();
        $favorites = Favorite::with('contact')->orderBy('order')->get();

        return response()->json([
            'contacts' => $contacts,
            'favorites' => $favorites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);

        return response()->json($contact, 201);
    }

    public function addFavorite($contactId)
    {
        $contact = Contact::findOrFail($contactId);
        $maxOrder = Favorite::max('order') ?? 0;

        $favorite = Favorite::create([
            'contact_id' => $contactId,
            'order' => $maxOrder + 1,
        ]);

        // Load the contact relationship
        $favorite->load('contact');

        return response()->json($favorite, 201);
    }

    public function removeFavorite($contactId)
    {
        Favorite::where('contact_id', $contactId)->delete();

        return response()->json(null, 204);
    }
}
