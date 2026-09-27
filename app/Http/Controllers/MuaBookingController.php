<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MakeupArtist;
use Illuminate\Support\Facades\Auth;

class MuaBookingController extends Controller
{
    public function index()
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        $bookings = Booking::where(
                'makeup_artist_id',
                $makeupArtist->id
            )
            ->with(['customer', 'service'])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get();

        return view(
            'mua.bookings.index',
            compact('bookings')
        );
    }

    public function accept(Booking $booking)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        if ($booking->makeup_artist_id !== $makeupArtist->id) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->route('mua.bookings.index')
                ->with('error', 'This booking is no longer pending.');
        }

        $booking->update([
            'status' => 'accepted',
        ]);

        return redirect()->route('mua.bookings.index')
            ->with('success', 'Booking accepted successfully.');
    }

    public function reject(Booking $booking)
    {
        $makeupArtist = MakeupArtist::where('user_id', Auth::id())
            ->firstOrFail();

        if ($booking->makeup_artist_id !== $makeupArtist->id) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->route('mua.bookings.index')
                ->with('error', 'This booking is no longer pending.');
        }

        $booking->update([
            'status' => 'rejected',
        ]);

        return redirect()->route('mua.bookings.index')
            ->with('success', 'Booking rejected successfully.');
    }
}