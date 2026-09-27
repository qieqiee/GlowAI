<?php

namespace App\Http\Controllers;

use App\Models\MakeupArtist;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{

    public function index()
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->with('services')
            ->firstOrFail();

        return view('mua.service.index', compact('makeupArtist'));
    }

    public function create()
    {
        return view('mua.service.create');
    }

    public function store(Request $request)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'service_included' => 'nullable|string',
        ]);

        $validated['makeup_artist_id'] = $makeupArtist->id;

        Service::create($validated);

        return redirect()
            ->route('mua.services')
            ->with('success', 'Service added successfully.');
    }

        public function edit(Service $service)
    {
        return view('mua.service.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'service_included' => 'nullable|string',
        ]);

        $service->update($validated);

        return redirect()
            ->route('mua.services')
            ->with('success', 'Service updated successfully.');
        }

        public function destroy(Service $service)
        {
            $service->delete();

            return redirect()
                ->route('mua.services')
                ->with('success', 'Service deleted successfully.');
        }
    }
