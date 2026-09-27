@extends('layouts.customer')

@section('title', 'Booking Submitted | GlowAI')

@section('content')

<div class="confirmation-page">

    {{-- SUCCESS HEADER --}}
    <div class="confirmation-header">

        <div class="success-icon">
            ✓
        </div>

        <h1>Booking Submitted!</h1>

        <p>
            Your deposit payment was successful and your booking request
            has been sent to the makeup artist.
        </p>


        <div class="confirmation-number">

            <span>Booking number</span>

            <strong>
                #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
            </strong>

        </div>


        <span class="confirmation-status status-{{ $booking->status }}">
            {{ $booking->status === 'pending' ? 'Pending approval' : ucfirst($booking->status) }}
        </span>

    </div>


    {{-- BOOKING DETAILS --}}
    <div class="confirmation-card">

        <div class="confirmation-card-heading">

            <h2>Booking details</h2>

            <p>
                Keep these details for your appointment reference.
            </p>

        </div>


        {{-- CUSTOMER + MUA --}}
        <div class="confirmation-person-grid">

            <div class="confirmation-person">

                <span>Customer</span>

                <strong>
                    {{ $booking->customer->name }}
                </strong>

                <small>
                    {{ $booking->customer_phone }}
                </small>

            </div>


            <div class="confirmation-person">

                <span>Makeup artist</span>

                <strong>
                    {{ $booking->makeupArtist->user->name }}
                </strong>

                <small>
                    {{ $booking->makeupArtist->phone }}
                </small>

            </div>

        </div>


        {{-- SERVICE --}}
        <div class="confirmation-service">

            <span>Service</span>

            <strong>
                {{ $booking->service->service_name }}
            </strong>

            <small>
                {{ $booking->event_type }}
            </small>

        </div>


        {{-- DATE TIME DURATION --}}
        <div class="confirmation-info-grid">

            <div>
                <span>Date</span>

                <strong>
                    {{ $booking->booking_date->format('d M Y') }}
                </strong>
            </div>


            <div>
                <span>Time</span>

                <strong>
                    {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                </strong>
            </div>


            <div>
                <span>Duration</span>

                <strong>
                    {{ $booking->service->duration }} minutes
                </strong>
            </div>

        </div>


        {{-- LOCATION --}}
        <div class="confirmation-location">

            <span>Appointment location</span>

            @if($booking->appointment_location_type === 'studio')

                <strong>MUA Studio</strong>

                <p>
                    {{ $booking->makeupArtist->studio_address ?? 'Address not provided' }}
                </p>

            @else

                <strong>My Location</strong>

                <p>
                    {{ $booking->appointment_address }}
                </p>

            @endif

        </div>


        {{-- PAYMENT --}}
        <div class="confirmation-payment">

            <div class="confirmation-payment-row">

                <span>Service price</span>

                <strong>
                    RM{{ number_format($booking->service_price, 2) }}
                </strong>

            </div>


            <div class="confirmation-payment-row paid">

                <span>Deposit paid</span>

                <strong>
                    RM{{ number_format($booking->deposit_amount, 2) }}
                </strong>

            </div>


            <div class="confirmation-payment-row balance">

                <span>Remaining balance</span>

                <strong>
                    RM{{ number_format($booking->balance_amount, 2) }}
                </strong>

            </div>

        </div>

    </div>


    {{-- BOTTOM --}}
    <div class="confirmation-bottom">

        <p>
            The makeup artist will review your booking request.
            You can check the latest status from My Bookings.
        </p>

        <a
            href="{{ route('customer.bookings.index', $booking->id) }}"
            class="confirmation-button"
        >
            View My Booking →
        </a>

    </div>

</div>

@endsection