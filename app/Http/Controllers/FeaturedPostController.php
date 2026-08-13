<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class FeaturedPostController extends Controller
{
    /**
     * Key used to store this setting's value.
     */
    protected const SETTING_KEY = 'featured_facebook_post_url';

    /**
     * Public, unauthenticated JSON endpoint the static frontend fetches
     * at runtime. Returns null when nothing's been configured yet so the
     * frontend can hide the embed instead of showing a broken one.
     */
    public function show()
    {
        return response()->json([
            'url' => Setting::get(self::SETTING_KEY),
        ]);
    }

    /**
     * Admin form — shows the current value.
     */
    public function edit(Request $request)
    {
        return view('admin.featured-post', [
            'url' => Setting::get(self::SETTING_KEY, ''),
        ]);
    }

    /**
     * Admin form — save the new value.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'url' => [
                'nullable',
                'url',
                'starts_with:https://www.facebook.com/,https://facebook.com/,https://m.facebook.com/',
            ],
        ], [
            'url.starts_with' => 'Please enter a facebook.com post URL.',
        ]);

        Setting::set(self::SETTING_KEY, $validated['url'] ?? null);

        return redirect()
            ->route('admin.featured-post.edit')
            ->with('status', 'featured-post-updated');
    }
}
