@extends('layouts.customer')

@section('title', 'Payment | GlowAI')

@section('content')

<div class="payment-page">

    <a
        href="javascript:history.back()"
        class="payment-back"
    >
        ← Back to booking
    </a>


    <div class="payment-heading">
        <h1>Payment</h1>

        <p>
            Review your appointment details and deposit before proceeding to payment.
        </p>
    </div>


    @if($errors->any())
        <div class="payment-errors">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif


    <div class="payment-layout">

        {{-- LEFT: BOOKING DETAILS --}}
        <div class="payment-card">

            <div class="payment-card-heading">
                <h2>Booking details</h2>
                <p>Please make sure your appointment information is correct.</p>
            </div>


            <div class="payment-service">

                <div>
                    <span>Makeup artist</span>

                    <strong>
                        {{ $service->makeupArtist->user->name }}
                    </strong>

                    @if($service->makeupArtist->studio_brand_name)
                        <small>
                            {{ $service->makeupArtist->studio_brand_name }}
                        </small>
                    @endif
                </div>


                <div>
                    <span>Service</span>

                    <strong>
                        {{ $service->service_name }}
                    </strong>

                    <small>
                        {{ $service->category }}
                    </small>
                </div>

            </div>


            <div class="payment-booking-grid">

                <div class="payment-detail">
                    <span>Date</span>

                    <strong>
                        {{ \Carbon\Carbon::parse($validated['booking_date'])->format('d M Y') }}
                    </strong>
                </div>


                <div class="payment-detail">
                    <span>Time</span>

                    <strong>
                        {{ \Carbon\Carbon::createFromFormat(
                            'H:i',
                            $validated['booking_time']
                        )->format('g:i A') }}
                    </strong>
                </div>


                <div class="payment-detail">
                    <span>Event type</span>

                    <strong>
                        {{ $validated['event_type'] }}
                    </strong>
                </div>


                <div class="payment-detail">
                    <span>Appointment location</span>

                    <strong>
                        @if($validated['appointment_location_type'] === 'studio')
                            MUA Studio
                        @else
                            My Location
                        @endif
                    </strong>
                </div>

            </div>


            @if(
                $validated['appointment_location_type'] === 'customer_location'
                && !empty($validated['appointment_address'])
            )

                <div class="payment-address">

                    <span>Appointment address</span>

                    <strong>
                        {{ $validated['appointment_address'] }}
                    </strong>

                </div>

            @endif

        </div>


        {{-- RIGHT: PAYMENT SUMMARY --}}
        <aside class="payment-summary-card">

            <div class="payment-card-heading">
                <h2>Payment summary</h2>
                <p>Your deposit is required to proceed with the booking.</p>
            </div>


            <div class="payment-price-row">
                <span>Service price</span>

                <strong>
                    RM{{ number_format($servicePrice, 2) }}
                </strong>
            </div>


            <div class="payment-price-row">
                <span>Deposit</span>

                <strong>
                    RM{{ number_format($depositAmount, 2) }}
                </strong>
            </div>


            <div class="payment-total">

                <span>Payable now</span>

                <strong>
                    RM{{ number_format($depositAmount, 2) }}
                </strong>

            </div>


            <div class="payment-balance">

                <span>Remaining balance</span>

                <strong>
                    RM{{ number_format($balanceAmount, 2) }}
                </strong>

                <p>
                    The remaining balance will be paid directly to the
                    makeup artist after the appointment.
                </p>

            </div>


            <form
                method="POST"
                action="{{ route('customer.booking.payment.process') }}"
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
                    value="{{ $validated['booking_date'] }}"
                >

                <input
                    type="hidden"
                    name="booking_time"
                    value="{{ $validated['booking_time'] }}"
                >

                <input
                    type="hidden"
                    name="event_type"
                    value="{{ $validated['event_type'] }}"
                >

                <input
                    type="hidden"
                    name="appointment_location_type"
                    value="{{ $validated['appointment_location_type'] }}"
                >

                <input
                    type="hidden"
                    name="appointment_address"
                    value="{{ $validated['appointment_address'] ?? '' }}"
                >

                <input
                    type="hidden"
                    name="customer_phone"
                    value="{{ $validated['customer_phone'] }}"
                >

                <input
                    type="hidden"
                    name="additional_notes"
                    value="{{ $validated['additional_notes'] ?? '' }}"
                >


                <button
                    type="submit"
                    class="payment-button"
                >
                    Proceed to Payment →
                </button>

            </form>


            <div class="payment-secure-note">
                <span>◇</span>
                Secure payment via ToyyibPay
            </div>

        </aside>

    </div>

</div>

@endsection