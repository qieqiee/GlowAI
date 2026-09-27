@extends('layouts.customer')

@section('title', 'Book Appointment | GlowAI')

@section('content')

<a
    href="{{ route('customer.mua.show', $service->makeupArtist->id) }}"
    class="booking-back"
>
    ← Back to Artist
</a>


<div class="booking-page-heading">
    <span class="booking-label">Book Your Appointment</span>

    <h1>Complete Your Booking</h1>

    <p>
        Choose your preferred appointment date, time and details.
    </p>
</div>


@if($errors->any())
    <div class="booking-errors">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif


<form
    method="POST"
    action="{{ route('customer.booking.payment') }}"
    class="booking-layout"
>
    @csrf

    <input
        type="hidden"
        name="service_id"
        value="{{ $service->id }}"
    >

    <input
        type="hidden"
        name="booking_date"
        value="{{ $selectedDate }}"
    >


    {{-- LEFT SIDE --}}
    <div class="booking-form-card">

        {{-- DATE & TIME --}}
        <section class="appointment-section">

            <div class="booking-section-heading">
                <span>01</span>

                <div>
                    <h2>Select Date & Time</h2>
                    <p>Choose an available appointment date and time.</p>
                </div>
            </div>


            {{-- MONTH NAVIGATION --}}
            <div class="calendar-navigation">

                <strong>
                    {{ $currentMonth->format('F Y') }}
                </strong>

                <div class="calendar-arrows">

                    <a
                        href="{{ route('customer.booking.create', [
                            'service' => $service->id,
                            'month' => $previousMonth
                        ]) }}"
                        aria-label="Previous month"
                    >
                        ‹
                    </a>

                    <a
                        href="{{ route('customer.booking.create', [
                            'service' => $service->id,
                            'month' => $nextMonth
                        ]) }}"
                        aria-label="Next month"
                    >
                        ›
                    </a>

                </div>

            </div>


            @php

                $startDay = $currentMonth->copy()->startOfMonth();
                $daysInMonth = $currentMonth->daysInMonth;
                $firstDayOfWeek = $startDay->dayOfWeekIso;

                $blockedDateStrings = $blockedDates
                    ->pluck('blocked_date')
                    ->map(fn($date) => $date->format('Y-m-d'))
                    ->toArray();

                $overrideDates = $availabilityOverrides->keyBy(
                    fn($override) => $override->override_date->format('Y-m-d')
                );

            @endphp


            {{-- CALENDAR --}}
            <table class="booking-calendar">

                <thead>
                    <tr>
                        <th>MON</th>
                        <th>TUE</th>
                        <th>WED</th>
                        <th>THU</th>
                        <th>FRI</th>
                        <th>SAT</th>
                        <th>SUN</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>

                        @for($i = 1; $i < $firstDayOfWeek; $i++)
                            <td class="calendar-empty"></td>
                        @endfor


                        @for($day = 1; $day <= $daysInMonth; $day++)

                            @php

                                $date = $currentMonth->copy()->day($day);
                                $dateString = $date->format('Y-m-d');
                                $dayName = $date->format('l');

                                $isBlocked = in_array(
                                    $dateString,
                                    $blockedDateStrings
                                );

                                $override = $overrideDates->get($dateString);

                                $weeklyAvailability =
                                    $availabilities->get($dayName);

                                $isWorkingDay =
                                    $weeklyAvailability &&
                                    $weeklyAvailability->is_working;

                                $isPast =
                                    $date->lt(now()->startOfDay());

                                $isAvailable =
                                    !$isPast &&
                                    !$isBlocked &&
                                    ($override || $isWorkingDay);

                                $isSelected =
                                    $selectedDate === $dateString;

                            @endphp


                            <td
                                class="
                                    calendar-day
                                    {{ !$isAvailable ? 'unavailable' : '' }}
                                    {{ $isSelected ? 'selected' : '' }}
                                "
                            >

                                @if($isAvailable)

                                    <a
                                        href="{{ route('customer.booking.create', [
                                            'service' => $service->id,
                                            'month' => $currentMonth->format('Y-m'),
                                            'date' => $dateString
                                        ]) }}"
                                    >
                                        {{ $day }}
                                    </a>

                                @else

                                    <span>
                                        {{ $day }}
                                    </span>

                                @endif

                            </td>


                            @if(
                                $date->dayOfWeekIso == 7 &&
                                $day != $daysInMonth
                            )

                                </tr>
                                <tr>

                            @endif

                        @endfor


                        @php
                            $lastDate =
                                $currentMonth->copy()->endOfMonth();
                        @endphp


                        @if($lastDate->dayOfWeekIso != 7)

                            @for(
                                $i = $lastDate->dayOfWeekIso;
                                $i < 7;
                                $i++
                            )

                                <td class="calendar-empty"></td>

                            @endfor

                        @endif

                    </tr>

                </tbody>

            </table>


            {{-- TIME --}}
            <div class="booking-times">

                @if($selectedDate)

                    <div class="available-time-heading">

                        <span>Available Times</span>

                        <strong>
                            {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M Y') }}
                        </strong>

                    </div>


                    @if(count($timeSlots) > 0)

                        <div class="time-slot-grid">

                            @foreach($timeSlots as $time)

                                <label class="time-slot">

                                    <input
                                        type="radio"
                                        name="booking_time"
                                        value="{{ $time }}"
                                        required
                                    >

                                    <span>
                                        {{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}
                                    </span>

                                </label>

                            @endforeach

                        </div>

                    @else

                        <div class="booking-notice">
                            No available time slots for this date.
                        </div>

                    @endif

                @else

                    <div class="booking-notice">
                        Select an available date above to view appointment times.
                    </div>

                @endif

            </div>

        </section>


        {{-- APPOINTMENT DETAILS --}}
        <section class="appointment-section booking-details-section">

            <div class="booking-section-heading">
                <span>02</span>

                <div>
                    <h2>Appointment Details</h2>
                    <p>Tell us a little more about your appointment.</p>
                </div>
            </div>


            <div class="booking-fields-grid">

                <div class="booking-field">

                    <label for="event_type">
                        Event Type 
                    </label>

                    <select
                        name="event_type"
                        id="event_type"
                        required
                    >
                        <option value="">Select event type</option>
                        <option value="Wedding">Wedding</option>
                        <option value="Engagement">Engagement</option>
                        <option value="Graduation">Graduation</option>
                        <option value="Corporate Event">Corporate Event</option>
                        <option value="Photoshoot">Photoshoot</option>
                        <option value="Other">Other</option>
                    </select>

                </div>


                <div class="booking-field">

                    <label for="customer_phone">
                        Contact Number
                    </label>

                    <input
                        type="text"
                        name="customer_phone"
                        id="customer_phone"
                        placeholder="e.g. 0123456789"
                        required
                    >

                    <small>
                        Please provide a number reachable by WhatsApp or phone call.
                    </small>

                </div>

            </div>


            {{-- LOCATION --}}
            <div class="location-section">

                <label class="field-title">
                    Appointment Location
                </label>


                @if($makeupArtist->willing_to_travel)

                    <div class="location-options">

                        <label class="location-option">

                            <input
                                type="radio"
                                name="appointment_location_type"
                                value="studio"
                                required
                            >

                            <span>
                                <strong>MUA Studio</strong>

                                <small>
                                    {{ $makeupArtist->studio_address ?? 'Address not provided' }}
                                </small>
                            </span>

                        </label>


                        <label class="location-option">

                            <input
                                type="radio"
                                name="appointment_location_type"
                                value="customer_location"
                                required
                            >

                            <span>
                                <strong>My Location</strong>

                                <small>
                                    The makeup artist will travel to you.
                                </small>
                            </span>

                        </label>

                    </div>


                    <div class="booking-field customer-address-field">

                        <label for="appointment_address">
                            Your Appointment Address
                        </label>

                        <textarea
                            name="appointment_address"
                            id="appointment_address"
                            placeholder="Enter your address if the makeup artist will travel to you"
                        ></textarea>

                    </div>

                @else

                    <input
                        type="hidden"
                        name="appointment_location_type"
                        value="studio"
                    >

                    <div class="studio-location-display">

                        <span>Studio Appointment</span>

                        <strong>
                            {{ $makeupArtist->studio_address ?? 'Address not provided' }}
                        </strong>

                        <small>
                            This makeup artist currently accepts studio appointments only.
                        </small>

                    </div>

                @endif

            </div>


            <div class="booking-field notes-field">

                <label for="additional_notes">
                    Additional Notes
                </label>

                <textarea
                    name="additional_notes"
                    id="additional_notes"
                    placeholder="Any additional requests or information for your makeup artist"
                ></textarea>

            </div>

        </section>


        <button
            type="submit"
            class="continue-payment-btn"
        >
            Continue to Payment →
        </button>

    </div>


    {{-- RIGHT SIDE: SUMMARY --}}
    <aside class="booking-summary">

        <span class="booking-label">
            Booking Summary
        </span>

        <h2>
            {{ $service->service_name }}
        </h2>

        <div class="summary-artist">

            <span>Makeup Artist</span>

            <strong>
                {{ $service->makeupArtist->user->name }}
            </strong>

        </div>


        <div class="summary-details">

            <div>
                <span>Category</span>
                <strong>{{ $service->category }}</strong>
            </div>

            <div>
                <span>Duration</span>
                <strong>{{ $service->duration }} min</strong>
            </div>

        </div>


        <div class="summary-price">

            <div>
                <span>Service Price</span>

                <strong>
                    RM{{ number_format($service->price, 2) }}
                </strong>
            </div>

            <div>
                <span>Deposit</span>

                <strong>
                    RM{{ number_format($service->deposit_amount ?? 0, 2) }}
                </strong>
            </div>

        </div>


        <div class="summary-note">
            <p>
                Your appointment slot will be reserved after
                successful deposit payment.
            </p>
        </div>

    </aside>

</form>

@endsection