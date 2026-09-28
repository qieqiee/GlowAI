@extends('layouts.mua')
@section('title', 'Manage Bookings | GlowAI')
@section('page-assets')
@vite([
        'resources/css/app.css',
        'resources/css/mua-bookings.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')
@php
    $today = now()->startOfDay();

    $pendingBookings = $bookings->filter(function ($booking) use ($today) {
        return $booking->status === 'pending'
            && $booking->payment_status === 'paid'
            && $booking->booking_date->copy()->startOfDay()->gte($today);
    });

    $acceptedBookings = $bookings->filter(function ($booking) use ($today) {
        return $booking->status === 'accepted'
            && $booking->booking_date->copy()->startOfDay()->gte($today);
    });

    $rejectedBookings = $bookings->filter(function ($booking) use ($today) {
        return $booking->status === 'rejected'
            && $booking->booking_date->copy()->startOfDay()->gte($today);
    });

    $pastBookings = $bookings->filter(function ($booking) use ($today) {
        return $booking->booking_date->copy()->startOfDay()->lt($today);
    });

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


    // PENDING - sama logic dengan Manage Bookings
    $pendingBookings = $allBookings->filter(function ($booking) use ($today) {
        return $booking->status === 'pending'
            && $booking->booking_date->copy()->startOfDay()->gte($today);
    });


    // UPCOMING / ACCEPTED
    $upcomingBookings = $allBookings->filter(function ($booking) use ($today) {
        return $booking->status === 'accepted'
            && $booking->booking_date->copy()->startOfDay()->gte($today);
    });


    // PENDING COUNT
    $pendingCount = $pendingBookings->count();


    // BOOKED DATES
    $bookedDatesCount = $upcomingBookings
        ->pluck('booking_date')
        ->map(fn ($date) => $date->format('Y-m-d'))
        ->unique()
        ->count();


    // CONFIRMED BOOKING REVENUE
    $confirmedRevenue = $allBookings
        ->where('status', 'accepted')
        ->sum('service_price');


    // Susun booking paling dekat dahulu
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


{{-- =========================================
     PAGE
========================================= --}}

<main class="bookings-page">

    <div class="bookings-heading">

        <h1>Manage Bookings</h1>

        <p>
            Review and manage your client appointments.
        </p>

    </div>


    {{-- SUCCESS MESSAGE --}}

    @if(session('success'))

        <div class="booking-alert booking-alert-success">
            {{ session('success') }}
        </div>

    @endif


    {{-- ERROR MESSAGE --}}

    @if(session('error'))

        <div class="booking-alert booking-alert-error">
            {{ session('error') }}
        </div>

    @endif


    {{-- =========================================
         TABS
    ========================================= --}}

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


    {{-- =========================================
         PENDING
    ========================================= --}}

    <section
        class="booking-section active"
        id="pending"
    >

        @forelse($pendingBookings as $booking)

            @include('mua.bookings.partials.booking-card', [
                'booking' => $booking,
                'displayStatus' => 'pending'
            ])

        @empty

            <div class="booking-empty">
                <h3>No pending bookings</h3>
                <p>You have no booking requests waiting for approval.</p>
            </div>

        @endforelse

    </section>


    {{-- =========================================
         ACCEPTED
    ========================================= --}}

    <section
        class="booking-section"
        id="accepted"
    >

        @forelse($acceptedBookings as $booking)

            @include('mua.bookings.partials.booking-card', [
                'booking' => $booking,
                'displayStatus' => 'accepted'
            ])

        @empty

            <div class="booking-empty">
                <h3>No accepted bookings</h3>
                <p>Your confirmed upcoming appointments will appear here.</p>
            </div>

        @endforelse

    </section>


    {{-- =========================================
         REJECTED
    ========================================= --}}

    <section
        class="booking-section"
        id="rejected"
    >

        @forelse($rejectedBookings as $booking)

            @include('mua.bookings.partials.booking-card', [
                'booking' => $booking,
                'displayStatus' => 'rejected'
            ])

        @empty

            <div class="booking-empty">
                <h3>No rejected bookings</h3>
                <p>You have no rejected upcoming appointments.</p>
            </div>

        @endforelse

    </section>


    {{-- =========================================
         PAST
    ========================================= --}}

    <section
        class="booking-section"
        id="past"
    >

        @forelse($pastBookings as $booking)

            @include('mua.bookings.partials.booking-card', [
                'booking' => $booking,
                'displayStatus' => 'past'
            ])

        @empty

            <div class="booking-empty">
                <h3>No past bookings</h3>
                <p>Your previous appointments will appear here.</p>
            </div>

        @endforelse

    </section>

</main>
{{-- =========================================
     FLOATING BOOKING CHAT
========================================= --}}

<div
    id="bookingChatPanel"
    class="floating-chat-panel"
    style="display: none;"
>

    <div class="floating-chat-header">

        <div>
            <strong id="floatingChatTitle">
                Chat
            </strong>

            <span id="floatingChatSubtitle">
                Customer
            </span>
        </div>

        <div class="floating-chat-controls">

            <button
                type="button"
                id="minimizeChatBtn"
                onclick="toggleBookingChat()"
                title="Minimize"
            >
                —
            </button>

            <button
                type="button"
                onclick="closeBookingChat()"
                title="Close"
            >
                ×
            </button>

        </div>

    </div>


    <div
        id="bookingChatContent"
        class="floating-chat-content"
    >
        <div class="chat-loading">
            Loading conversation...
        </div>
    </div>

</div>

{{-- =========================================
     TAB SCRIPT
========================================= --}}

<script>
    let chatMinimized = false;

    async function openBookingChat(bookingId) {

        const panel = document.getElementById('bookingChatPanel');
        const content = document.getElementById('bookingChatContent');

        panel.style.display = 'flex';

        chatMinimized = false;
        content.style.display = 'block';

        content.innerHTML = `
            <div class="chat-loading">
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
                chatbox.dataset.chatPerson || 'User';

            const serviceName =
                chatbox.dataset.serviceName || '';

            document.getElementById(
                'floatingChatTitle'
            ).textContent = chatPerson;

            document.getElementById(
                'floatingChatSubtitle'
            ).textContent = serviceName;

            content.innerHTML = chatbox.outerHTML;
            const messages = content.querySelector(
                '#chatMessages'
            );

            if (messages) {
                messages.scrollTop = messages.scrollHeight;
            }

        } catch (error) {

            content.innerHTML = `
                <div class="chat-loading">
                    Unable to load conversation.
                </div>
            `;

            console.error(error);
        }
    }


    function toggleBookingChat() {

        const content = document.getElementById(
            'bookingChatContent'
        );

        const button = document.getElementById(
            'minimizeChatBtn'
        );

        chatMinimized = !chatMinimized;

        if (chatMinimized) {

            content.style.display = 'none';

            button.textContent = '□';

        } else {

            content.style.display = 'block';

            button.textContent = '—';

        }
    }


    function closeBookingChat() {

        const panel = document.getElementById(
            'bookingChatPanel'
        );

        panel.style.display = 'none';

        chatMinimized = false;
    }


    document.addEventListener('DOMContentLoaded', function () {

        const tabs = document.querySelectorAll('.booking-tab');
        const sections = document.querySelectorAll('.booking-section');

        tabs.forEach(function (tab) {

            tab.addEventListener('click', function () {

                const target = this.dataset.tab;

                tabs.forEach(function (item) {
                    item.classList.remove('active');
                });

                sections.forEach(function (section) {
                    section.classList.remove('active');
                });

                this.classList.add('active');

                document
                    .getElementById(target)
                    .classList
                    .add('active');

            });

        });

    });

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
     * Reload current conversation inside the
     * floating chatbox after sending.
     */
    const bookingId = form.dataset.bookingId;

    await openBookingChat(bookingId);

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
