<article class="booking-card">

    {{-- =====================================
         TOP
    ====================================== --}}

    <div class="booking-card-top">

        <div>

            <span class="booking-number">
                Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
            </span>

            <h2>
                {{ $booking->service->service_name }}
            </h2>

        </div>


        @if($displayStatus === 'pending')

            <span class="booking-status booking-status-pending">
                Pending approval
            </span>

        @elseif($displayStatus === 'accepted')

            <span class="booking-status booking-status-accepted">
                Accepted
            </span>

        @elseif($displayStatus === 'rejected')

            <span class="booking-status booking-status-rejected">
                Rejected
            </span>

        @else

            <span class="booking-status booking-status-past">
                Past
            </span>

        @endif

    </div>


    {{-- =====================================
         INFORMATION
    ====================================== --}}

    <div class="booking-info-grid">

        <div class="booking-info">

            <span>Client</span>

            <strong>
                {{ $booking->customer->name }}
            </strong>

            <small>
                {{ $booking->customer_phone }}
            </small>

        </div>


        <div class="booking-info">

            <span>Date</span>

            <strong>
                {{ $booking->booking_date->format('d M Y') }}
            </strong>

            <small>
                {{ $booking->event_type }}
            </small>

        </div>


        <div class="booking-info">

            <span>Time</span>

            <strong>
                {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
            </strong>

        </div>


        <div class="booking-info">

            <span>Location</span>

            <strong>

                @if($booking->appointment_location_type === 'studio')
                    MUA Studio
                @else
                    Customer Location
                @endif

            </strong>

            @if(
                $booking->appointment_location_type === 'customer_location'
                && $booking->appointment_address
            )

                <small>
                    {{ $booking->appointment_address }}
                </small>

            @endif

        </div>

    </div>


    {{-- =====================================
         NOTES
    ====================================== --}}

    @if($booking->additional_notes)

        <div class="booking-notes">

            <span>Client notes</span>

            <p>
                {{ $booking->additional_notes }}
            </p>

        </div>

    @endif


    {{-- =====================================
         FOOTER
    ====================================== --}}

    <div class="booking-card-footer">

        <div class="booking-price">

            <span>Deposit paid</span>

            <strong>
                RM{{ number_format($booking->deposit_amount, 2) }}
            </strong>

        </div>


        <div class="booking-actions">

            {{-- PENDING --}}

            @if(
                $displayStatus === 'pending'
                && $booking->status === 'pending'
            )

                <form
                    method="POST"
                    action="{{ route('mua.bookings.reject', $booking->id) }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="booking-reject-button"
                        onclick="return confirm('Reject this booking?')"
                    >
                        Reject
                    </button>

                </form>


                <form
                    method="POST"
                    action="{{ route('mua.bookings.accept', $booking->id) }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="booking-accept-button"
                    >
                        Accept Booking
                    </button>

                </form>

            @endif


            {{-- ACCEPTED --}}

            @if(
                $displayStatus === 'accepted'
                && $booking->status === 'accepted'
            )

                @php

                    $unreadCount = $booking
                        ->unreadMessagesFor(auth()->id())
                        ->count();

                @endphp


                <button
                    type="button"
                    class="booking-chat-button"
                    onclick="openBookingChat({{ $booking->id }})"
                >
                    Chat with Customer

                    @if($unreadCount > 0)
                        <span class="chat-count">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </button>

            @endif

        </div>

    </div>

</article>