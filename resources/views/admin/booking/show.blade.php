@extends('layouts.admin')

@section('title', 'Booking Details | GlowAI')

@section('content')

<div class="admin-page">

    {{-- BACK --}}
    <a
        href="{{ route('admin.booking.index') }}"
        class="admin-back-link"
    >
        ← Back to Manage Bookings
    </a>


    {{-- PAGE HEADING --}}
    <div class="admin-section-heading admin-detail-heading">

        <span>Booking Management</span>

        <h1>Booking Details</h1>

        <p>
            Review appointment, customer and payment information.
        </p>

    </div>


    {{-- BOOKING OVERVIEW --}}
    <section class="admin-detail-card">

        <div class="admin-booking-overview">

            <div>

                <span class="admin-booking-overview-label">
                    Booking ID
                </span>

                <h2>
                    #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                </h2>

                <p>
                    {{ $booking->service->service_name }}
                    with
                    {{ $booking->makeupArtist->studio_brand_name ?: $booking->makeupArtist->user->name }}
                </p>

            </div>


            <div class="admin-booking-overview-status">

                <span class="admin-booking-status {{ strtolower($booking->status) }}">
                    {{ ucfirst($booking->status) }}
                </span>

                <span class="admin-payment-status {{ strtolower($booking->payment_status) }}">
                    Payment: {{ ucfirst($booking->payment_status) }}
                </span>

            </div>

        </div>

    </section>


    {{-- CUSTOMER + MUA --}}
    <div class="admin-detail-grid">

        {{-- CUSTOMER --}}
        <section class="admin-detail-card">

            <div class="admin-detail-card-header">
                <div>
                    <h2>Customer Information</h2>
                    <p>Customer contact details.</p>
                </div>
            </div>


            <div class="admin-info-list">

                <div class="admin-info-item">
                    <span>Name</span>
                    <strong>
                        {{ $booking->customer->name }}
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Email</span>
                    <strong>
                        {{ $booking->customer->email }}
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Phone</span>
                    <strong>
                        {{ $booking->customer_phone ?: '-' }}
                    </strong>
                </div>

            </div>

        </section>


        {{-- MUA & SERVICE --}}
        <section class="admin-detail-card">

            <div class="admin-detail-card-header">
                <div>
                    <h2>Makeup Artist & Service</h2>
                    <p>Selected artist and makeup service.</p>
                </div>
            </div>


            <div class="admin-info-list">

                <div class="admin-info-item">
                    <span>Makeup Artist</span>
                    <strong>
                        {{ $booking->makeupArtist->studio_brand_name ?: $booking->makeupArtist->user->name }}
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>MUA Email</span>
                    <strong>
                        {{ $booking->makeupArtist->user->email }}
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Service</span>
                    <strong>
                        {{ $booking->service->service_name }}
                    </strong>
                </div>

            </div>

        </section>

    </div>


    {{-- APPOINTMENT --}}
    <section class="admin-detail-card">

        <div class="admin-detail-card-header">

            <div>
                <h2>Appointment Information</h2>
                <p>Scheduled appointment and event details.</p>
            </div>

        </div>


        <div class="admin-booking-information-grid">

            <div class="admin-booking-information-item">
                <span>Date</span>

                <strong>
                    {{ $booking->booking_date->format('d/m/Y') }}
                </strong>
            </div>


            <div class="admin-booking-information-item">
                <span>Time</span>

                <strong>
                    {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                </strong>
            </div>


            <div class="admin-booking-information-item">
                <span>Event Type</span>

                <strong>
                    {{ $booking->event_type ?: '-' }}
                </strong>
            </div>


            <div class="admin-booking-information-item">
                <span>Location Type</span>

                <strong>
                    {{ ucfirst(str_replace('_', ' ', $booking->appointment_location_type)) }}
                </strong>
            </div>


            @if($booking->appointment_address)

                <div class="admin-booking-information-item admin-booking-info-wide">

                    <span>Appointment Address</span>

                    <strong>
                        {{ $booking->appointment_address }}
                    </strong>

                </div>

            @endif


            <div class="admin-booking-information-item admin-booking-info-wide">

                <span>Additional Notes</span>

                <strong class="admin-booking-notes">
                    {{ $booking->additional_notes ?: '-' }}
                </strong>

            </div>

        </div>

    </section>


    {{-- PAYMENT --}}
    <section class="admin-detail-card">

        <div class="admin-detail-card-header">

            <div>
                <h2>Payment Summary</h2>
                <p>Service price and payment breakdown.</p>
            </div>

            <span class="admin-payment-status {{ strtolower($booking->payment_status) }}">
                {{ ucfirst($booking->payment_status) }}
            </span>

        </div>


        <div class="admin-payment-summary">

            <div class="admin-payment-summary-item">
                <span>Service Price</span>

                <strong>
                    RM {{ number_format($booking->service_price, 2) }}
                </strong>
            </div>


            <div class="admin-payment-summary-item deposit">
                <span>Deposit Amount</span>

                <strong>
                    RM {{ number_format($booking->deposit_amount, 2) }}
                </strong>
            </div>


            <div class="admin-payment-summary-item balance">
                <span>Balance Amount</span>

                <strong>
                    RM {{ number_format($booking->balance_amount, 2) }}
                </strong>
            </div>

        </div>

    </section>


    {{-- STATUS --}}
    <section class="admin-detail-card admin-booking-status-card">

        <div>
            <span>Current Booking Status</span>

            <h2>
                {{ ucfirst($booking->status) }}
            </h2>

            <p>
                This booking is currently marked as
                {{ strtolower($booking->status) }}.
            </p>
        </div>


        <span class="admin-booking-status {{ strtolower($booking->status) }}">
            {{ ucfirst($booking->status) }}
        </span>

    </section>

</div>

@endsection