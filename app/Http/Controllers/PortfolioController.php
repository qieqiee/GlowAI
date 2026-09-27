<?php

namespace App\Http\Controllers;

use App\Models\MakeupArtist;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfolioController extends Controller
{
    public function create()
    {
        return view('mua.portfolio.create');
    }

    public function store(Request $request)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'look_title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'picture' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'description' => 'required|string',
            'products_used' => 'nullable|string',
        ]);

        $validated['picture'] =
            $request->file('picture')
                ->store('portfolios', 'public');

        $validated['makeup_artist_id'] = $makeupArtist->id;

        Portfolio::create($validated);

        return redirect()
            ->route('mua.profile')
            ->with('success', 'Portfolio added successfully.');
    }

    public function edit(Portfolio $portfolio)
    {
        return view('mua.portfolio.edit', compact('portfolio'));
    }
    
    public function update(Request $request, Portfolio $portfolio)
    {
        $validated = $request->validate([
            'look_title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'picture' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'description' => 'required|string',
            'products_used' => 'nullable|string',
        ]);
    
        if ($request->hasFile('picture')) {
            $validated['picture'] =
                $request->file('picture')->store('portfolios', 'public');
        }
    
        $portfolio->update($validated);
    
        return redirect()
            ->route('mua.profile')
            ->with('success', 'Portfolio updated successfully.');
    }
    
    public function destroy(Portfolio $portfolio)
    {
        $portfolio->delete();
    
        return redirect()
            ->route('mua.profile')
            ->with('success', 'Portfolio deleted successfully.');
    }
}