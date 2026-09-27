<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\MakeupArtist;
use App\Models\BlockedDate;
use App\Models\AvailabilityOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    public function index(Request $request)
{
    $makeupArtist = MakeupArtist::where('user_id', Auth::id())
        ->firstOrFail();

    $availabilities = Availability::where(
        'makeup_artist_id',
        $makeupArtist->id
    )
    ->get()
    ->keyBy('day_of_week');

    $month = $request->input('month', now()->format('Y-m'));

    $currentMonth = Carbon::createFromFormat('!Y-m', $month)
        ->startOfMonth();

    $blockedDates = BlockedDate::where(
        'makeup_artist_id',
        $makeupArtist->id
    )
    ->whereYear('blocked_date', $currentMonth->year)
    ->whereMonth('blocked_date', $currentMonth->month)
    ->orderBy('blocked_date')
    ->get();

    $availabilityOverrides = AvailabilityOverride::where(
        'makeup_artist_id',
        $makeupArtist->id
    )
    ->whereYear('override_date', $currentMonth->year)
    ->whereMonth('override_date', $currentMonth->month)
    ->orderBy('override_date')
    ->get();

    $previousMonth = $currentMonth->copy()
        ->subMonth()
        ->format('Y-m');

    $nextMonth = $currentMonth->copy()
        ->addMonth()
        ->format('Y-m');

    return view('mua.availability.index', compact(
        'makeupArtist',
        'availabilities',
        'blockedDates',
        'availabilityOverrides',
        'currentMonth',
        'previousMonth',
        'nextMonth'
    ));
}
    public function update(Request $request)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        $days = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];

        foreach ($days as $day) {

            $isWorking = $request->has("working.$day");

            Availability::updateOrCreate(
                [
                    'makeup_artist_id' => $makeupArtist->id,
                    'day_of_week' => $day,
                ],
                [
                    'is_working' => $isWorking,
                    'start_time' => $isWorking
                        ? $request->input("start_time.$day")
                        : null,
                    'end_time' => $isWorking
                        ? $request->input("end_time.$day")
                        : null,
                ]
            );
        }

        return redirect()
            ->route('mua.availability')
            ->with('success', 'Availability updated successfully.');
    }

    public function blockDate(Request $request)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();
    
        $validated = $request->validate([
            'blocked_date' => 'required|date|after_or_equal:today',
        ]);
    
        BlockedDate::updateOrCreate(
            [
                'makeup_artist_id' => $makeupArtist->id,
                'blocked_date' => $validated['blocked_date'],
            ],
        );
    
        // Ambil bulan daripada tarikh yang MUA baru block
        $month = \Carbon\Carbon::parse(
            $validated['blocked_date']
        )->format('Y-m');
    
        // Redirect terus ke bulan tersebut
        return redirect()
            ->route('mua.availability', ['month' => $month])
            ->with('success', 'Date blocked successfully.');
    }

        public function removeBlockedDate(BlockedDate $blockedDate)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        abort_if(
            $blockedDate->makeup_artist_id !== $makeupArtist->id,
            403
        );

        // Simpan month sebelum record dipadam
        $month = $blockedDate->blocked_date->format('Y-m');

        $blockedDate->delete();

        return redirect()
            ->route('mua.availability', ['month' => $month])
            ->with('success', 'Blocked date removed successfully.');
    }

    public function storeOverride(Request $request)
{
    $makeupArtist = MakeupArtist::where('user_id', Auth::id())
        ->firstOrFail();

    $validated = $request->validate([
        'override_date' => 'required|date|after_or_equal:today',
        'override_start_time' => 'required',
        'override_end_time' => 'required|after:override_start_time',
    ]);

    AvailabilityOverride::updateOrCreate(
        [
            'makeup_artist_id' => $makeupArtist->id,
            'override_date' => $validated['override_date'],
        ],
        [
            'start_time' => $validated['override_start_time'],
            'end_time' => $validated['override_end_time'],
        ]
    );

    $month = \Carbon\Carbon::parse(
        $validated['override_date']
    )->format('Y-m');

    return redirect()
        ->route('mua.availability', ['month' => $month])
        ->with('success', 'Custom working hours saved successfully.');
    }

    public function removeOverride(AvailabilityOverride $availabilityOverride)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();
    
        abort_if(
            $availabilityOverride->makeup_artist_id !== $makeupArtist->id,
            403
        );
    
        $month = $availabilityOverride->override_date->format('Y-m');
    
        $availabilityOverride->delete();
    
        return redirect()
            ->route('mua.availability', ['month' => $month])
            ->with('success', 'Custom working hours removed successfully.');
    }
}