@extends('layouts.customer')

@section('title', 'My Bookings | GlowAI')

@section('content')

<div class="bookings-page">

    <div class="bookings-heading">
        <h1>My Bookings</h1>
        <p>View and manage your makeup appointments.</p>
    </div>


    @php
        $today = now()->startOfDay();

        $pendingBookings = $bookings->filter(function ($booking) use ($today) {
            return $booking->status === 'pending'
                && $booking->booking_date->gte($today);
        });

        $acceptedBookings = $bookings->filter(function ($booking) use ($today) {
            return $booking->status === 'accepted'
                && $booking->booking_date->gte($today);
        });

        $rejectedBookings = $bookings->where('status', 'rejected');

        $pastBookings = $bookings->filter(function ($booking) use ($today) {
            return $booking->booking_date->lt($today);
        });
    @endphp


    {{-- TABS --}}
    <div class="booking-tabs">

        <button
            type="button"
            class="booking-tab active"
            data-tab="pending"
        >
            Pending
            <span>{{ $pendingBookings->count() }}</span>
        </button>

        <button
            type="button"
            class="booking-tab"
            data-tab="accepted"
        >
            Accepted
            <span>{{ $acceptedBookings->count() }}</span>
        </button>

        <button
            type="button"
            class="booking-tab"
            data-tab="rejected"
        >
            Rejected
            <span>{{ $rejectedBookings->count() }}</span>
        </button>

        <button
            type="button"
            class="booking-tab"
            data-tab="past"
        >
            Past
            <span>{{ $pastBookings->count() }}</span>
        </button>

    </div>


    {{-- ===============================
         PENDING
    ================================ --}}
    <div class="booking-section active" id="pending">

        @forelse($pendingBookings as $booking)

            <div class="booking-card">

                <div class="booking-card-top">

                    <div>
                        <span class="booking-number">
                            Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                        </span>

                        <h2>
                            {{ $booking->service->service_name }}
                        </h2>
                    </div>

                    <span class="booking-status booking-status-pending">
                        Pending approval
                    </span>

                </div>


                <div class="booking-info-grid">

                    <div class="booking-info">
                        <span>Makeup artist</span>

                        <strong>
                            {{ $booking->makeupArtist->user->name }}
                        </strong>

                        <small>
                            {{ $booking->makeupArtist->studio_brand_name ?? '' }}
                        </small>
                    </div>


                    <div class="booking-info">
                        <span>Date</span>

                        <strong>
                            {{ $booking->booking_date->format('d M Y') }}
                        </strong>
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
                            {{ $booking->appointment_location_type === 'studio'
                                ? 'MUA Studio'
                                : 'My Location' }}
                        </strong>
                    </div>

                </div>


                <div class="booking-card-footer">

                    <div class="booking-price">
                        <span>Deposit paid</span>

                        <strong>
                            RM{{ number_format($booking->deposit_amount, 2) }}
                        </strong>
                    </div>


                    <div class="booking-card-actions">

                        <a
                            href="{{ route('customer.bookings.show', $booking->id) }}"
                            class="booking-view-button"
                        >
                            View Booking →
                        </a>

                        @if($booking->status === 'accepted')

                            @php
                                $unreadCount = $booking
                                    ->unreadMessagesFor(auth()->id())
                                    ->count();
                            @endphp

                            <button
                                type="button"
                                class="booking-customer-chat-button"
                                onclick="openCustomerChat({{ $booking->id }})"
                            >
                                Chat with MUA

                                @if($unreadCount > 0)
                                    <span class="chat-count">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </button>

                        @endif

                    </div>
                </div>

            </div>

        @empty

            <div class="booking-empty">
                <h3>No pending bookings</h3>
                <p>You currently have no booking requests waiting for approval.</p>
            </div>

        @endforelse

    </div>


    {{-- ===============================
         ACCEPTED
    ================================ --}}
    <div class="booking-section" id="accepted">

        @forelse($acceptedBookings as $booking)

            <div class="booking-card">

                <div class="booking-card-top">

                    <div>
                        <span class="booking-number">
                            Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                        </span>

                        <h2>
                            {{ $booking->service->service_name }}
                        </h2>
                    </div>

                    <span class="booking-status booking-status-accepted">
                        Accepted
                    </span>

                </div>


                <div class="booking-info-grid">

                    <div class="booking-info">
                        <span>Makeup artist</span>

                        <strong>
                            {{ $booking->makeupArtist->user->name }}
                        </strong>

                        <small>
                            {{ $booking->makeupArtist->studio_brand_name ?? '' }}
                        </small>
                    </div>


                    <div class="booking-info">
                        <span>Date</span>

                        <strong>
                            {{ $booking->booking_date->format('d M Y') }}
                        </strong>
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
                            {{ $booking->appointment_location_type === 'studio'
                                ? 'MUA Studio'
                                : 'My Location' }}
                        </strong>
                    </div>

                </div>


                <div class="booking-card-footer">

                    <div class="booking-price">
                        <span>Deposit paid</span>

                        <strong>
                            RM{{ number_format($booking->deposit_amount, 2) }}
                        </strong>
                    </div>


                    <a
                        href="{{ route('customer.bookings.show', $booking->id) }}"
                        class="booking-view-button"
                    >
                        View Booking →
                    </a>

                </div>

            </div>

        @empty

            <div class="booking-empty">
                <h3>No accepted bookings</h3>
                <p>You currently have no upcoming accepted appointments.</p>
            </div>

        @endforelse

    </div>


    {{-- ===============================
         REJECTED
    ================================ --}}
    <div class="booking-section" id="rejected">

        @forelse($rejectedBookings as $booking)

            <div class="booking-card">

                <div class="booking-card-top">

                    <div>
                        <span class="booking-number">
                            Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                        </span>

                        <h2>
                            {{ $booking->service->service_name }}
                        </h2>
                    </div>

                    <span class="booking-status booking-status-rejected">
                        Rejected
                    </span>

                </div>


                <div class="booking-info-grid">

                    <div class="booking-info">
                        <span>Makeup artist</span>

                        <strong>
                            {{ $booking->makeupArtist->user->name }}
                        </strong>

                        <small>
                            {{ $booking->makeupArtist->studio_brand_name ?? '' }}
                        </small>
                    </div>


                    <div class="booking-info">
                        <span>Date</span>

                        <strong>
                            {{ $booking->booking_date->format('d M Y') }}
                        </strong>
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
                            {{ $booking->appointment_location_type === 'studio'
                                ? 'MUA Studio'
                                : 'My Location' }}
                        </strong>
                    </div>

                </div>


                <div class="booking-card-footer">

                    <div class="booking-price">
                        <span>Deposit</span>

                        <strong>
                            RM{{ number_format($booking->deposit_amount, 2) }}
                        </strong>
                    </div>


                    <a
                        href="{{ route('customer.bookings.show', $booking->id) }}"
                        class="booking-view-button"
                    >
                        View Booking →
                    </a>

                </div>

            </div>

        @empty

            <div class="booking-empty">
                <h3>No rejected bookings</h3>
                <p>You currently have no rejected booking requests.</p>
            </div>

        @endforelse

    </div>


    {{-- ===============================
         PAST
    ================================ --}}
    <div class="booking-section" id="past">

        @forelse($pastBookings as $booking)

            <div class="booking-card">

                <div class="booking-card-top">

                    <div>
                        <span class="booking-number">
                            Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                        </span>

                        <h2>
                            {{ $booking->service->service_name }}
                        </h2>
                    </div>

                    <span class="booking-status booking-status-past">
                        Past
                    </span>

                </div>


                <div class="booking-info-grid">

                    <div class="booking-info">
                        <span>Makeup artist</span>

                        <strong>
                            {{ $booking->makeupArtist->user->name }}
                        </strong>

                        <small>
                            {{ $booking->makeupArtist->studio_brand_name ?? '' }}
                        </small>
                    </div>


                    <div class="booking-info">
                        <span>Date</span>

                        <strong>
                            {{ $booking->booking_date->format('d M Y') }}
                        </strong>
                    </div>


                    <div class="booking-info">
                        <span>Time</span>

                        <strong>
                            {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                        </strong>
                    </div>


                    <div class="booking-info">
                        <span>Final status</span>

                        <strong>
                            {{ ucfirst($booking->status) }}
                        </strong>
                    </div>

                </div>


                <div class="booking-card-footer">

                    <div class="booking-price">
                        <span>Service price</span>

                        <strong>
                            RM{{ number_format($booking->service_price, 2) }}
                        </strong>
                    </div>


                    <a
                        href="{{ route('customer.bookings.show', $booking->id) }}"
                        class="booking-view-button"
                    >
                        View Booking →
                    </a>

                </div>

            </div>

        @empty

            <div class="booking-empty">
                <h3>No past bookings</h3>
                <p>Your completed or previous appointments will appear here.</p>
            </div>

            @endforelse

        </div>

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

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const tabs = document.querySelectorAll('.booking-tab');
    const sections = document.querySelectorAll('.booking-section');

    tabs.forEach(tab => {

        tab.addEventListener('click', function () {

            const target = this.dataset.tab;

            tabs.forEach(button => {
                button.classList.remove('active');
            });

            sections.forEach(section => {
                section.classList.remove('active');
            });

            this.classList.add('active');

            document
                .getElementById(target)
                .classList.add('active');
        });

    });

});


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

        const documentResponse =
            parser.parseFromString(html, 'text/html');

        const chatbox =
            documentResponse.querySelector('.embedded-chat');

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

        const messages =
            content.querySelector('#chatMessages');

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

    const panel =
        document.getElementById('customerChatPanel');

    panel.style.display = 'none';

    customerChatMinimized = false;
}


/* CUSTOMER CHAT - ENTER TO SEND */
document.addEventListener('keydown', function (event) {

const textarea = event.target.closest('.embedded-chat-input');

if (!textarea) {
    return;
}

// Enter = send
// Shift + Enter = new line
if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();

    const form = textarea.closest('.embedded-chat-form');

    if (form && textarea.value.trim() !== '') {
        form.requestSubmit();
    }
}
});


/* SEND MESSAGE WITHOUT LEAVING MY BOOKINGS */
document.addEventListener('submit', async function (event) {

const form = event.target.closest('.embedded-chat-form');

if (!form) {
    return;
}

event.preventDefault();

const textarea = form.querySelector('.embedded-chat-input');
const sendButton = form.querySelector('.embedded-chat-send');

const message = textarea.value.trim();

if (!message) {
    return;
}

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

    /*
     * Reload current conversation inside
     * the customer floating chat after sending.
     */
    const bookingId = form.dataset.bookingId;

    await openCustomerChat(bookingId);

} catch (error) {

    console.error(error);

    alert('Message could not be sent. Please try again.');

} finally {

    sendButton.disabled = false;
    sendButton.textContent = 'Send';
}
});

</script>

@endsection