<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function show(Booking $booking)
    {
        $user = Auth::user();

        // Chat only available for accepted bookings
        if ($booking->status !== 'accepted') {
            abort(403, 'Chat is only available for accepted bookings.');
        }

        // Check whether logged-in user is the customer
        $isCustomer = $booking->customer_id === $user->id;

        // Check whether logged-in user is the MUA
        $isMua = $booking->makeupArtist
            && $booking->makeupArtist->user_id === $user->id;

        // Other users cannot access this conversation
        if (!$isCustomer && !$isMua) {
            abort(403);
        }

        $booking->load([
            'customer',
            'makeupArtist.user',
            'service',
            'messages.sender',
        ]);

        // Mark messages sent by the other person as read
        Message::where('booking_id', $booking->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('chat.show', compact('booking'));
    }

    public function send(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($booking->status !== 'accepted') {
            abort(403, 'Chat is only available for accepted bookings.');
        }

        $isCustomer = $booking->customer_id === $user->id;

        $isMua = $booking->makeupArtist
            && $booking->makeupArtist->user_id === $user->id;

        if (!$isCustomer && !$isMua) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        Message::create([
            'booking_id' => $booking->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return redirect()
            ->route('chat.show', $booking->id);
    }
}