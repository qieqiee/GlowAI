<?php

namespace App\Http\Controllers;

use App\Models\MakeupArtist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MakeupArtistController extends Controller
{
    public function create()
    {
        if (MakeupArtist::where('user_id', Auth::id())->exists()) {
            return redirect()
                ->route('mua.dashboard')
                ->with('error', 'You already have a MUA profile.');
        }

        return view('mua.register');
    }

    public function store(Request $request)
    {

        if (MakeupArtist::where('user_id', Auth::id())->exists()) {
            return redirect()
                ->route('mua.dashboard')
                ->with('error', 'You already have a MUA profile.');
        }

        $validated = $request->validate([
            'studio_brand_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'studio_address' => 'required|string',
            'service_states' => 'required|array|min:1',
            'service_states.*' => 'string|max:100',

            'service_areas' => 'required|array|min:1',
            'service_areas.*' => 'required|string|max:100',
            'willing_to_travel' => 'required|boolean',
        
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',

            'years_experience' => 'required|integer|min:0',

            'specialized_makeup_look' => 'required|string|max:255',

            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request
                ->file('profile_picture')
                ->store('makeup-artists', 'public');
        }

        $validated['user_id'] = Auth::id();

        MakeupArtist::create($validated);

        $user = Auth::user();
        $user->role = 'makeup_artist';
        $user->save();

        return redirect()
            ->route('mua.review')
            ->with('success', 'MUA profile created successfully.');
    }

        public function review()
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())->firstOrFail();

        return view('mua.review', compact('makeupArtist'));
    }

        public function publish()
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        $makeupArtist->is_published = true;
        $makeupArtist->save();

        return redirect()
            ->route('mua.dashboard')
            ->with('success', 'Your MUA profile has been published successfully.');
    }

        public function profile()
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        return view('mua.profile', compact('makeupArtist'));
    }

        public function updateProfile(Request $request)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->with('portfolios')
            ->firstOrFail();
    
        $validated = $request->validate([
            'studio_brand_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'studio_address' => 'required|string',
            'service_states' => 'required|array|min:1',
            'service_states.*' => 'string|max:100',

            'service_areas' => 'required|array|min:1',
            'service_areas.*' => 'required|string|max:100',
            'willing_to_travel' => 'required|boolean',
    
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
    
            'years_experience' => 'required|integer|min:0',
    
            'specialized_makeup_look' => 'required|string|max:255',
    
            'description' => 'nullable|string',
        ]);
    
        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] =
                $request->file('profile_picture')
                    ->store('makeup-artists', 'public');
        }
    
        $makeupArtist->update($validated);
    
        return redirect()
            ->route('mua.profile')
            ->with('success', 'Profile updated successfully.');
    }
}