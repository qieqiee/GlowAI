@extends('layouts.customer')

@section('title', 'Booking Details | GlowAI')

@section('content')

<div class="booking-details-page">

    {{-- BACK --}}
    <a href="{{ route('customer.bookings.index') }}" class="back-link">
        ← Back to My Bookings
    </a>


    {{-- HEADER --}}
    <div class="booking-details-header">

        <div>
            <p class="booking-label">Booking Details</p>

            <h1>
                Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
            </h1>

            <p class="booking-subtitle">
                View your appointment and payment information.
            </p>
        </div>


        {{-- STATUS --}}
        <span class="booking-status status-{{ $booking->status }}">
            @if($booking->status === 'pending')
                Pending Approval
            @elseif($booking->status === 'accepted')
                Accepted
            @elseif($booking->status === 'rejected')
                Rejected
            @else
                {{ ucfirst($booking->status) }}
            @endif
        </span>

    </div>


    {{-- APPOINTMENT DETAILS --}}
    <section class="details-card">

        <div class="section-heading">
            <h2>Appointment Details</h2>
            <p>Your makeup appointment information.</p>
        </div>


        <div class="details-grid">

            <div class="detail-box">
                <span>Makeup Artist</span>
                <strong>
                    {{ $booking->makeupArtist->user->name }}
                </strong>
            </div>


            <div class="detail-box">
                <span>Service</span>
                <strong>
                    {{ $booking->service->service_name }}
                </strong>
            </div>


            <div class="detail-box">
                <span>Event Type</span>
                <strong>
                    {{ $booking->event_type }}
                </strong>
            </div>

        </div>


        <div class="appointment-row">

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
        <div class="location-box">

            <span>Appointment Location</span>

            <strong>
                @if($booking->appointment_location_type === 'studio')
                    MUA Studio
                @else
                    Customer Location
                @endif
            </strong>


            <p>
                @if($booking->appointment_location_type === 'customer_location')
                    {{ $booking->appointment_address }}
                @else
                    {{ $booking->makeupArtist->studio_address }}
                @endif
            </p>

        </div>


        {{-- CUSTOMER CONTACT --}}
        <div class="extra-details">

            <div>
                <span>Contact Number</span>
                <strong>{{ $booking->customer_phone }}</strong>
            </div>


            @if($booking->additional_notes)

                <div>
                    <span>Additional Notes</span>
                    <strong>{{ $booking->additional_notes }}</strong>
                </div>

            @endif

        </div>

    </section>



    {{-- PAYMENT --}}
    <section class="details-card payment-card">

        <div class="section-heading">
            <h2>Payment Summary</h2>
            <p>Payment information for this booking.</p>
        </div>


        <div class="payment-row">

            <span>Service Price</span>

            <strong>
                RM{{ number_format($booking->service_price, 2) }}
            </strong>

        </div>


        <div class="payment-row deposit">

            <span>Deposit Paid</span>

            <strong>
                RM{{ number_format($booking->deposit_amount, 2) }}
            </strong>

        </div>


        <div class="payment-row remaining">

            <span>Remaining Balance</span>

            <strong>
                RM{{ number_format($booking->balance_amount, 2) }}
            </strong>

        </div>


        <div class="payment-status-row">

            <span>Payment Status</span>

            <span class="payment-status">
                {{ ucfirst($booking->payment_status) }}
            </span>

        </div>

    </section>



    {{-- CHAT --}}
    @if ($booking->status === 'accepted')

        @php
            $unreadCount = $booking
                ->unreadMessagesFor(auth()->id())
                ->count();
        @endphp

        <div class="chat-section">

            <div>
                <h3>Need to discuss your appointment?</h3>

                <p>
                    Chat directly with your makeup artist about your booking.
                </p>
            </div>

            <button
                type="button"
                class="chat-button"
                onclick="openCustomerChat({{ $booking->id }})"
            >
                Chat with MUA

                @if($unreadCount > 0)
                    <span class="meessage-count">
                        {{ $unreadCount }}
                    </span>
                @endif
            </button>

        </div>

    @endif

            <div id="customerChatPanel" class="customer-floating-chat" style="display: none;">

            <div class="customer-chat-header">
                <div>
                    <strong id="customerChatTitle">Makeup Artist</strong>
                    <span id="customerChatSubtitle">Service</span>
                </div>

                <div class="customer-chat-controls">
                    <button
                        type="button"
                        id="customerMinimizeChatBtn"
                        onclick="toggleCustomerChat()"
                        title="Minimize"
                    >
                        —
                    </button>

                    <button
                        type="button"
                        onclick="closeCustomerChat()"
                        title="Close"
                    >
                        ×
                    </button>
                </div>
            </div>

            <div id="customerChatContent" class="customer-chat-content">
                <div class="customer-chat-loading">
                    Loading conversation...
                </div>
            </div>

        </div>

        <script>
let customerChatMinimized = false;

async function openCustomerChat(bookingId) {
    const panel = document.getElementById('customerChatPanel');
    const content = document.getElementById('customerChatContent');

    panel.style.display = 'flex';
    customerChatMinimized = false;
    content.style.display = 'block';

    content.innerHTML = `
        <div class="customer-chat-loading">
            Loading conversation...
        </div>
    `;

    try {
        const response = await fetch(`/chat/${bookingId}`);

        if (!response.ok) {
            throw new Error('Unable to load chat.');
        }

        const html = await response.text();

        const parser = new DOMParser();
        const documentResponse = parser.parseFromString(
            html,
            'text/html'
        );

        const chatbox = documentResponse.querySelector(
            '.embedded-chat'
        );

        if (!chatbox) {
            throw new Error('Chat content not found.');
        }

        const chatPerson =
            chatbox.dataset.chatPerson || 'Makeup Artist';

        const serviceName =
            chatbox.dataset.serviceName || '';

        document.getElementById(
            'customerChatTitle'
        ).textContent = chatPerson;

        document.getElementById(
            'customerChatSubtitle'
        ).textContent = serviceName;

        content.innerHTML = chatbox.outerHTML;

        const messages = content.querySelector('#chatMessages');

        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }

    } catch (error) {
        console.error(error);

        content.innerHTML = `
            <div class="customer-chat-loading">
                Unable to load conversation.
            </div>
        `;
    }
}

function toggleCustomerChat() {
    const content =
        document.getElementById('customerChatContent');

    const button =
        document.getElementById('customerMinimizeChatBtn');

    customerChatMinimized = !customerChatMinimized;

    if (customerChatMinimized) {
        content.style.display = 'none';
        button.textContent = '□';
    } else {
        content.style.display = 'block';
        button.textContent = '—';
    }
}

function closeCustomerChat() {
    document.getElementById(
        'customerChatPanel'
    ).style.display = 'none';

    customerChatMinimized = false;
}


/* SEND WITHOUT LEAVING BOOKING DETAILS */
document.addEventListener('submit', async function (event) {
    const form = event.target.closest('.embedded-chat-form');

    if (!form) return;

    event.preventDefault();

    const textarea = form.querySelector('.embedded-chat-input');
    const sendButton = form.querySelector('.embedded-chat-send');

    if (!textarea.value.trim()) return;

    sendButton.disabled = true;
    sendButton.textContent = 'Sending...';

    try {
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to send message.');
        }

        textarea.value = '';

        const bookingId = form.dataset.bookingId;

        await openCustomerChat(bookingId);

            } catch (error) {
                console.error(error);

                alert(
                    'Message could not be sent. Please try again.'
                );

            } finally {
                sendButton.disabled = false;
                sendButton.textContent = 'Send';
            }
        });
        </script>
@endsection