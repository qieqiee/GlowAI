@extends('layouts.mua')
@section('title', 'MUA Dashboard | GlowAI')
@section('page-assets')
@vite([
        'resources/css/app.css',
        'resources/css/mua-dashboard.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')
@php
    $mua = auth()->user()->makeupArtist;

    $serviceCount = $mua
        ? $mua->services()->count()
        : 0;

    $today = now()->startOfDay();

    $allBookings = $mua
        ? $mua->bookings()
            ->with(['customer', 'service'])
            ->get()
        : collect();

    // Pending bookings - SAME logic as Manage Bookings
    $pendingBookings = $allBookings->filter(function ($booking) use ($today) {
        return $booking->status === 'pending'
            && $booking->booking_date->copy()->startOfDay()->gte($today);
    });

    // Accepted upcoming bookings
    $upcomingBookings = $allBookings->filter(function ($booking) use ($today) {
        return $booking->status === 'accepted'
            && $booking->booking_date->copy()->startOfDay()->gte($today);
    });

    // Statistics
    $pendingCount = $pendingBookings->count();

    $bookedDatesCount = $upcomingBookings
        ->pluck('booking_date')
        ->map(fn ($date) => $date->format('Y-m-d'))
        ->unique()
        ->count();

    $confirmedRevenue = $allBookings
        ->where('status', 'accepted')
        ->sum('service_price');

    // Sort nearest booking first
    $pendingBookings = $pendingBookings
        ->sortBy(function ($booking) {
            return $booking->booking_date->format('Y-m-d')
                . ' '
                . $booking->booking_time;
        })
        ->values();

    $upcomingBookings = $upcomingBookings
        ->sortBy(function ($booking) {
            return $booking->booking_date->format('Y-m-d')
                . ' '
                . $booking->booking_time;
        })
        ->values();
@endphp




    {{-- MAIN CONTENT --}}
    <main class="mua-dashboard-content">


        {{-- PROFILE --}}
        <section class="mua-profile-card">

            <div class="mua-profile-photo">

                @if($mua && $mua->profile_picture)

                    <img
                        src="{{ asset('storage/' . $mua->profile_picture) }}"
                        alt="{{ auth()->user()->name }}"
                    >

                @else

                    <div class="mua-profile-placeholder">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                @endif

            </div>


            <div class="mua-profile-info">

                <h1>{{ auth()->user()->name }}</h1>

                @if($mua && $mua->studio_brand_name)
                    <p class="mua-studio">
                        {{ $mua->studio_brand_name }}
                    </p>
                @endif


                <div class="mua-profile-meta">

                    @if($mua && $mua->studio_address)
                        <span>
                            {{ $mua->studio_address }}
                        </span>
                    @endif

                    @if($mua)
                        <span class="meta-dot">•</span>

                        <span>
                            {{ $mua->years_experience }}
                            {{ $mua->years_experience == 1 ? 'year' : 'years' }}
                            experience
                        </span>
                    @endif

                    @if($mua && $mua->specialized_makeup_look)
                        <span class="meta-dot">•</span>

                        <span>
                            {{ $mua->specialized_makeup_look }}
                        </span>
                    @endif

                </div>

            </div>

        </section>


        {{-- STATISTIC CARDS --}}
        <section class="mua-stats-grid">

            <div class="mua-stat-card">

                <div class="stat-top">
                    <span>Confirmed Booking Revenue</span>
                </div>

                <strong>
                    RM {{ number_format($confirmedRevenue, 2) }}
                </strong>

                <small>
                    From accepted bookings
                </small>

            </div>


            <div class="mua-stat-card">

                <div class="stat-top">
                    <span>Pending Action</span>
                </div>

                <strong>{{ $pendingCount }}</strong>

                <small>
                    Bookings awaiting your review
                </small>

            </div>


            <div class="mua-stat-card">

                <div class="stat-top">
                    <span>Booked Dates</span>
                </div>

                <strong>{{ $bookedDatesCount }}</strong>

                <small>
                    Confirmed appointment dates
                </small>

            </div>


            <div class="mua-stat-card">

                <div class="stat-top">
                    <span>Makeup Services</span>
                </div>

                <strong>
                    {{ $serviceCount }}
                </strong>

                <small>
                    {{ $serviceCount == 1 ? 'Service offered' : 'Services offered' }}
                </small>

            </div>

        </section>


        {{-- BOOKINGS --}}
        <section class="mua-booking-grid">


            {{-- PENDING --}}
            <div class="dashboard-booking-section">

                <div class="dashboard-section-heading">

                    <div>

                        <h2>Pending Client Booking</h2>
                    </div>

                    <a href="{{ route('mua.bookings.index') }}">
                        View All Bookings →
                    </a>

                </div>


                @if($pendingBookings->isNotEmpty())

                @php
                    $booking = $pendingBookings->first();
                @endphp

                <div class="dashboard-booking-card">

                    <div class="dashboard-booking-top">
                        <div>
                            <span>Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span>

                            <h3>
                                {{ $booking->customer->name }}
                            </h3>
                        </div>

                        <strong>
                            RM{{ number_format($booking->service_price, 2) }}
                        </strong>
                    </div>

                    <div class="dashboard-booking-details">

                        <p>
                            <strong>{{ $booking->service->service_name }}</strong>
                        </p>

                        <p>
                            {{ $booking->event_type }}
                            ·
                            {{ $booking->booking_date->format('d M Y') }}
                            ·
                            {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                        </p>

                        <p>
                            @if($booking->appointment_location_type === 'studio')
                                MUA Studio
                            @else
                                {{ $booking->appointment_address }}
                            @endif
                        </p>

                    </div>

                    <div class="dashboard-booking-actions">

                        <form method="POST"
                            action="{{ route('mua.bookings.reject', $booking->id) }}">
                            @csrf

                            <button type="submit" class="dashboard-decline-btn">
                                Decline
                            </button>
                        </form>

                        <form method="POST"
                            action="{{ route('mua.bookings.accept', $booking->id) }}">
                            @csrf

                            <button type="submit" class="dashboard-accept-btn">
                                Accept & Lock Date
                            </button>
                        </form>

                    </div>

                </div>

            @else

                    <div class="empty-booking-card">

                        <div class="empty-booking-icon">♡</div>

                        <strong>Pending bookings will appear here</strong>

                        <p>
                            Review new client booking requests and accept or reject them.
                        </p>

                        <a href="{{ route('mua.bookings.index') }}">
                            Manage Bookings
                        </a>

                    </div>

                @endif
            </div>


            {{-- UPCOMING --}}
            <div class="dashboard-booking-section">

                <div class="dashboard-section-heading">

                    <div>
                        <h2>Upcoming Booking</h2>
                    </div>

                    <a href="{{ route('mua.bookings.index') }}">
                        View Calendar →
                    </a>

                </div>


                @if($upcomingBookings->isNotEmpty())

                @php
                    $booking = $upcomingBookings->first();
                @endphp

                <div class="dashboard-booking-card">

                    <div class="dashboard-booking-top">
                        <div>
                            <span>Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span>

                            <h3>
                                {{ $booking->customer->name }}
                            </h3>
                        </div>

                        <strong>
                            RM{{ number_format($booking->service_price, 2) }}
                        </strong>
                    </div>

                    <div class="dashboard-booking-details">

                        <p>
                            <strong>{{ $booking->service->service_name }}</strong>
                        </p>

                        <p>
                            {{ $booking->event_type }}
                            ·
                            {{ $booking->booking_date->format('d M Y') }}
                            ·
                            {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                        </p>

                        <p>
                            @if($booking->appointment_location_type === 'studio')
                                MUA Studio
                            @else
                                {{ $booking->appointment_address }}
                            @endif
                        </p>

                    </div>

                    <div class="dashboard-booking-actions">

                        <a href="{{ route('chat.show', $booking->id) }}"
                        class="dashboard-chat-btn">
                            Chat with Client
                        </a>

                    </div>

                </div>

                @else

                <div class="empty-booking-card">

                    <div class="empty-booking-icon">◇</div>

                    <strong>Upcoming bookings will appear here</strong>

                    <p>
                        Your accepted appointments will be displayed here.
                    </p>

                    <a href="{{ route('mua.bookings.index') }}">
                        View Bookings
                    </a>

                </div>

                @endif
            </div>

        </section>

    </main>

</div>
@endsection
