@extends('layouts.mua')
@section('title', 'Calendar & Availability | GlowAI')
@section('page-assets')
@vite([
        'resources/css/app.css',
        'resources/css/mua-bookings.css',
        'resources/css/mua-availability.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')
@php
    $days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    ];

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

    $today = now()->format('Y-m-d');
@endphp


{{-- =========================================
     NAVBAR
========================================= --}}



<main class="availability-page">

    {{-- =========================================
         PAGE HEADING
    ========================================= --}}

    <div class="page-heading">
        <div>
            <h1>Calendar & Availability</h1>
        </div>
    </div>


    {{-- SUCCESS MESSAGE --}}

    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif


    {{-- VALIDATION ERRORS --}}

    @if($errors->any())
        <div class="error-message">
            <strong>Please check the following:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- =========================================
         WEEKLY AVAILABILITY
    ========================================= --}}

    <section class="availability-card weekly-card">

        <div class="section-heading">
            <div>
                <h2>Operating Hours & Working Days</h2>
            </div>
        </div>


        <form method="POST"
              action="{{ route('mua.availability.update') }}">

            @csrf
            @method('PUT')


            <div class="weekly-list">

                @foreach($days as $day)

                    @php
                        $availability = $availabilities->get($day);
                    @endphp


                    <div class="weekly-row">

                        <div class="day-name">
                            {{ $day }}
                        </div>


                        <label class="working-toggle">

                            <input
                                type="checkbox"
                                name="working[{{ $day }}]"
                                value="1"
                                {{ $availability && $availability->is_working ? 'checked' : '' }}
                            >

                            <span class="toggle-switch"></span>

                            <span class="working-text">
                                Working Day
                            </span>

                        </label>


                        <div class="time-field">

                            <label>
                                Start Time
                            </label>

                            <input
                                type="time"
                                name="start_time[{{ $day }}]"
                                value="{{ $availability?->start_time ? substr($availability->start_time, 0, 5) : '' }}"
                            >

                        </div>


                        <span class="time-separator">
                            to
                        </span>


                        <div class="time-field">

                            <label>
                                End Time
                            </label>

                            <input
                                type="time"
                                name="end_time[{{ $day }}]"
                                value="{{ $availability?->end_time ? substr($availability->end_time, 0, 5) : '' }}"
                            >

                        </div>

                    </div>

                @endforeach

            </div>


            <div class="weekly-actions">

                <button type="submit" class="primary-button">
                    Save Availability
                </button>

            </div>

        </form>

    </section>


    {{-- =========================================
         CALENDAR + BLOCK DATE
    ========================================= --}}

    <div class="calendar-management-grid">


        {{-- CALENDAR --}}

        <section class="availability-card calendar-card">

            <div class="calendar-navigation">

                <div class="calendar-title">

                    <span class="section-label">
                        MONTHLY SCHEDULE
                    </span>

                    <strong>
                        {{ $currentMonth->format('F Y') }}
                    </strong>

                </div>


                <div class="calendar-arrows">

                    <a
                        href="{{ route('mua.availability', ['month' => $previousMonth]) }}"
                        aria-label="Previous month"
                    >
                        &lsaquo;
                    </a>

                    <a
                        href="{{ route('mua.availability', ['month' => $nextMonth]) }}"
                        aria-label="Next month"
                    >
                        &rsaquo;
                    </a>

                </div>

            </div>


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

                        {{-- Empty cells before first date --}}
                        @for($i = 1; $i < $firstDayOfWeek; $i++)
                            <td></td>
                        @endfor


                        @for($day = 1; $day <= $daysInMonth; $day++)

                            @php
                                $date = $currentMonth->copy()->day($day);

                                $dateString = $date->format('Y-m-d');

                                $isBlocked = in_array(
                                    $dateString,
                                    $blockedDateStrings
                                );

                                $override = $overrideDates->get($dateString);

                                $isToday = $dateString === $today;
                            @endphp


                            <td
                                class="
                                    calendar-day
                                    {{ $isBlocked ? 'blocked' : '' }}
                                    {{ $override ? 'custom-hours' : '' }}
                                    {{ $isToday ? 'today' : '' }}
                                "
                            >

                                <div class="calendar-date">
                                    {{ $day }}
                                </div>


                                @if($isBlocked)

                                    <span class="calendar-status blocked-status">
                                        Blocked
                                    </span>

                                @elseif($override)

                                    <span class="calendar-status custom-status">
                                        Custom
                                    </span>

                                @endif

                            </td>


                            @if($date->dayOfWeekIso == 7 && $day != $daysInMonth)
                                </tr>
                                <tr>
                            @endif

                        @endfor


                        {{-- Empty cells after final date --}}
                        @php
                            $lastDate = $currentMonth
                                ->copy()
                                ->endOfMonth();
                        @endphp


                        @if($lastDate->dayOfWeekIso != 7)

                            @for(
                                $i = $lastDate->dayOfWeekIso;
                                $i < 7;
                                $i++
                            )
                                <td></td>
                            @endfor

                        @endif

                    </tr>

                </tbody>

            </table>


            <div class="calendar-legend">

                <span>
                    <i class="legend-dot available-dot"></i>
                    Available
                </span>

                <span>
                    <i class="legend-dot custom-dot"></i>
                    Custom Hours
                </span>

                <span>
                    <i class="legend-dot blocked-dot"></i>
                    Blocked
                </span>

            </div>

        </section>


        {{-- BLOCK DATE --}}

        <section class="availability-card block-date-card">

            <div class="section-heading">

                <div>
                    <h2>Block Custom Date</h2>
                </div>

            </div>


            <form
                    method="POST"
                    action="{{ route('mua.availability.block') }}"
                >
                    @csrf

                    <div class="form-group">
                        <label>Date to Block</label>

                        <input
                            type="date"
                            name="blocked_date"
                            value="{{ old('blocked_date') }}"
                            min="{{ now()->format('Y-m-d') }}"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="primary-button full-button"
                    >
                        Block Date
                    </button>
                </form>


            {{-- BLOCKED DATE LIST --}}

            <div class="existing-section">

                <h3>
                    Blocked Dates
                </h3>


                @if($blockedDates->count() > 0)

                    <div class="existing-list">

                        @foreach($blockedDates as $blockedDate)

                            <div class="existing-item">

                                <div>

                                    <strong>
                                        {{ $blockedDate->blocked_date->format('d M Y') }}
                                    </strong>

                                </div>


                                <form
                                    method="POST"
                                    action="{{ route(
                                        'mua.availability.block.remove',
                                        $blockedDate->id
                                    ) }}"
                                >

                                    @csrf
                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="remove-button"
                                        onclick="return confirm('Remove this blocked date?')"
                                    >
                                        Remove
                                    </button>

                                </form>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="empty-state-small">
                        No blocked dates for
                        {{ $currentMonth->format('F Y') }}.
                    </div>

                @endif

            </div>

        </section>

    </div>


    {{-- =========================================
         CUSTOM WORKING HOURS
    ========================================= --}}

    <section class="availability-card custom-hours-card">

        <div class="section-heading">

            <div>
                <h2>Custom Working Hours</h2>
            </div>

        </div>


        <form
            method="POST"
            action="{{ route('mua.availability.override.store') }}"
            class="custom-hours-form"
        >

            @csrf


            <div class="form-group">

                <label>
                    Date
                </label>

                <input
                    type="date"
                    name="override_date"
                    value="{{ old('override_date') }}"
                    min="{{ date('Y-m-d') }}"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Start Time
                </label>

                <input
                    type="time"
                    name="override_start_time"
                    value="{{ old('override_start_time') }}"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    End Time
                </label>

                <input
                    type="time"
                    name="override_end_time"
                    value="{{ old('override_end_time') }}"
                    required
                >

            </div>

            <div class="custom-submit">

                <button
                    type="submit"
                    class="primary-button"
                >
                    Save Custom Hours
                </button>

            </div>

        </form>


        {{-- EXISTING CUSTOM HOURS --}}

        <div class="existing-section custom-existing">

            <h3>
                Custom Hours for
                {{ $currentMonth->format('F Y') }}
            </h3>


            @if($availabilityOverrides->count() > 0)

                <div class="override-list">

                @foreach($availabilityOverrides as $override)

                    <div class="override-item">

                        <div class="override-date">
                            <span>DATE</span>

                            <strong>
                                {{ $override->override_date->format('d M Y') }}
                            </strong>
                        </div>


                        <div class="override-info">
                            <span>WORKING HOURS</span>

                            <strong>
                                {{ substr($override->start_time, 0, 5) }}
                                –
                                {{ substr($override->end_time, 0, 5) }}
                            </strong>
                        </div>


                        <form
                            method="POST"
                            action="{{ route(
                                'mua.availability.override.remove',
                                $override->id
                            ) }}"
                            class="override-remove-form"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="remove-button"
                                onclick="return confirm('Remove these custom working hours?')"
                            >
                                Remove
                            </button>
                        </form>

                    </div>

                    @endforeach

                </div>

            @else

                <div class="empty-state-small">
                    No custom working hours for
                    {{ $currentMonth->format('F Y') }}.
                </div>

            @endif

        </div>

    </section>

</main>
@endsection
