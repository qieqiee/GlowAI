<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\MakeupArtist;

class CustomerMuaController extends Controller
{
    public function index(Request $request)
    {
        $selectedState = $request->input('state');

        $makeupArtists = MakeupArtist::where('is_published', true)
            ->when($selectedState, function ($query) use ($selectedState) {
                $query->whereJsonContains('service_states', $selectedState);
            })
            ->with([
                'user',
                'portfolios',
                'services'
            ])
            ->get();

        return view(
            'customer.mua.index',
            compact('makeupArtists', 'selectedState')
        );
    }

        public function show(MakeupArtist $makeupArtist)
    {
        $makeupArtist->load([
            'user',
            'portfolios',
            'services'
        ]);

        return view(
            'customer.mua.show',
            compact('makeupArtist')
        );
    }
}