<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\MakeupArtist;

class AdminController extends Controller
{
    public function dashboard()
{
    // Dashboard totals
    $totalUsers = User::count();
    $totalCustomers = User::where('role', 'customer')->count();
    $totalMuas = User::where('role', 'makeup_artist')->count();
    $totalBookings = Booking::count();


    // =========================================
    // BOOKINGS BY MONTH
    // =========================================

    $monthlyBookings = Booking::selectRaw(
        'MONTH(booking_date) as month, COUNT(*) as total'
    )
        ->whereYear('booking_date', now()->year)
        ->groupByRaw('MONTH(booking_date)')
        ->orderByRaw('MONTH(booking_date)')
        ->pluck('total', 'month');


    $bookingMonths = [];
    $bookingMonthTotals = [];

    for ($month = 1; $month <= 12; $month++) {

        $bookingMonths[] = date('M', mktime(0, 0, 0, $month, 1));

        $bookingMonthTotals[] = $monthlyBookings->get($month, 0);
    }


        // =========================================
        // BOOKING STATUS
        // =========================================

        $bookingStatusLabels = [
            'Pending',
            'Accepted',
            'Rejected'
        ];

        $bookingStatusTotals = [
            Booking::where('status', 'pending')->count(),
            Booking::where('status', 'accepted')->count(),
            Booking::where('status', 'rejected')->count()
        ];


        return view('admin.dashboard', compact(
            'totalUsers',
            'totalCustomers',
            'totalMuas',
            'totalBookings',
            'bookingMonths',
            'bookingMonthTotals',
            'bookingStatusLabels',
            'bookingStatusTotals'
        ));
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return view('admin.users.index', compact('users'));
    }

    public function muas()
    {
        $makeupArtists = MakeupArtist::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.mua.index', compact('makeupArtists'));
    }

    public function bookings()
    {
        $bookings = Booking::with([
            'customer',
            'makeupArtist.user',
            'service'
        ])
        ->orderBy('booking_date', 'desc')
        ->orderBy('booking_time', 'desc')
        ->get();

        return view('admin.booking.index', compact('bookings'));
    }

    public function showMua(MakeupArtist $makeupArtist)
    {
        $makeupArtist->load([
            'user',
            'services',
            'portfolios'
        ]);
    
        return view('admin.mua.show', compact('makeupArtist'));
    }
    
        public function toggleMuaPublish(MakeupArtist $makeupArtist)
    {
        $makeupArtist->update([
            'is_published' => !$makeupArtist->is_published,
        ]);

        return redirect()
            ->route('admin.muas.show', $makeupArtist->id)
            ->with(
                'success',
                $makeupArtist->is_published
                    ? 'MUA profile published successfully.'
                    : 'MUA profile unpublished successfully.'
            );
    }

        public function showBooking(Booking $booking)
    {
        $booking->load([
            'customer',
            'makeupArtist.user',
            'service',
        ]);

        return view('admin.booking.show', compact('booking'));
    }

    public function showUser(User $user)
    {
        $user->load('makeupArtist');
    
        return view('admin.users.show', compact('user'));
    }
}