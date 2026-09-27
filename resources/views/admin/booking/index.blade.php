@extends('layouts.admin')

@section('title', 'Manage Bookings | GlowAI')

@section('content')

<div class="admin-page">

    {{-- PAGE HEADING --}}
    <div class="admin-section-heading">
        <span>Management</span>

        <h1>Manage Bookings</h1>

        <p>
            Monitor all customer bookings, appointment status and payments.
        </p>
    </div>


    {{-- SUMMARY --}}
    <div class="admin-booking-summary">

        <div class="admin-mini-stat">
            <span>Total Bookings</span>
            <strong>{{ $bookings->count() }}</strong>
        </div>

        <div class="admin-mini-stat">
            <span>Pending</span>
            <strong>
                {{ $bookings->where('status', 'pending')->count() }}
            </strong>
        </div>

        <div class="admin-mini-stat">
            <span>Accepted</span>
            <strong>
                {{ $bookings->where('status', 'accepted')->count() }}
            </strong>
        </div>

        <div class="admin-mini-stat">
            <span>Rejected</span>
            <strong>
                {{ $bookings->where('status', 'rejected')->count() }}
            </strong>
        </div>

    </div>


    {{-- TABLE CARD --}}
    <section class="admin-table-card">

        <div class="admin-table-header">

            <div>
                <h2>Booking List</h2>

                <p>
                    All booking records on GlowAI.
                </p>
            </div>


            {{-- SEARCH --}}
            <div class="admin-table-search">

                <svg viewBox="0 0 24 24" fill="none">
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />

                    <path
                        d="M20 20L16.5 16.5"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>

                <input
                    type="text"
                    id="bookingSearch"
                    placeholder="Search bookings..."
                >

            </div>

        </div>


        @if($bookings->isEmpty())

            <div class="admin-empty-state">

                <h3>No bookings found</h3>

                <p>
                    There are currently no booking records.
                </p>

            </div>

        @else

            <div class="admin-table-wrapper">

                <table
                    class="admin-data-table admin-booking-table"
                    id="bookingTable"
                >

                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Makeup Artist</th>
                            <th>Service</th>
                            <th>Appointment</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>


                    <tbody>

                        @foreach($bookings as $booking)

                            <tr>

                                {{-- ID --}}
                                <td>
                                    <strong class="admin-booking-id">
                                        #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                                    </strong>
                                </td>


                                {{-- CUSTOMER --}}
                                <td>
                                    <strong class="admin-table-primary">
                                        {{ $booking->customer->name }}
                                    </strong>
                                </td>


                                {{-- MUA --}}
                                <td>

                                    <strong class="admin-table-primary">
                                        {{ $booking->makeupArtist->studio_brand_name ?: $booking->makeupArtist->user->name }}
                                    </strong>

                                </td>


                                {{-- SERVICE --}}
                                <td>
                                    {{ $booking->service->service_name }}
                                </td>


                                {{-- APPOINTMENT --}}
                                <td>

                                    <div class="admin-booking-date">
                                        <strong>
                                            {{ $booking->booking_date->format('d/m/Y') }}
                                        </strong>

                                        <span>
                                            {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                                        </span>
                                    </div>

                                </td>


                                {{-- STATUS --}}
                                <td>

                                    <span class="admin-booking-status {{ strtolower($booking->status) }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>

                                </td>


                                {{-- PAYMENT --}}
                                <td>

                                    <div class="admin-payment-cell">

                                        <span class="admin-payment-status {{ strtolower($booking->payment_status) }}">
                                            {{ ucfirst($booking->payment_status) }}
                                        </span>

                                        <small>
                                            RM {{ number_format($booking->deposit_amount, 2) }}
                                        </small>

                                    </div>

                                </td>


                                {{-- ACTION --}}
                                <td>

                                    <a
                                        href="{{ route('admin.bookings.show', $booking->id) }}"
                                        class="admin-view-button"
                                    >
                                        View Details
                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </section>

</div>


{{-- SEARCH --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput =
        document.getElementById('bookingSearch');

    const rows =
        document.querySelectorAll('#bookingTable tbody tr');

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', function () {

        const keyword =
            this.value.toLowerCase().trim();

        rows.forEach(function (row) {

            const text =
                row.textContent.toLowerCase();

            row.style.display =
                text.includes(keyword) ? '' : 'none';

        });

    });

});
</script>

@endsection