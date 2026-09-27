@php
    $isMua = auth()->user()->role === 'makeup_artist';

    $chatPerson = $isMua
        ? $booking->customer->name
        : $booking->makeupArtist->user->name;
@endphp

<div
    class="embedded-chat"
    data-chat-person="{{ $chatPerson }}"
    data-service-name="{{ $booking->service->service_name }}"
>

    {{-- BOOKING INFO --}}
    <div class="embedded-booking-info">
        <strong>
            Booking #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
        </strong>

        <span>·</span>

        <span>
            {{ $booking->booking_date->format('d M Y') }}
        </span>

        <span>·</span>

        <span>
            {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
        </span>
    </div>


    {{-- MESSAGES --}}
    <div class="embedded-chat-messages" id="chatMessages">

        @if ($booking->messages->isEmpty())

            <div class="embedded-chat-empty">
                <strong>No messages yet</strong>

                <p>
                    Start the conversation with
                    {{ $chatPerson }}.
                </p>
            </div>

        @else

            @foreach ($booking->messages as $message)

                <div class="embedded-message-row
                    {{ $message->sender_id === auth()->id() ? 'mine' : 'theirs' }}">

                    @if ($message->sender_id !== auth()->id())
                        <div class="embedded-message-sender">
                            {{ $message->sender->name }}
                        </div>
                    @endif


                    <div class="embedded-message-bubble">
                        {{ $message->message }}
                    </div>


                    <div class="embedded-message-time">

                        {{ $message->created_at->format('h:i A') }}

                        @if ($message->sender_id === auth()->id())
                            · {{ $message->is_read ? 'Read' : 'Sent' }}
                        @endif

                    </div>

                </div>

            @endforeach

        @endif

    </div>


    {{-- MESSAGE FORM --}}
    <div class="embedded-chat-footer">

        <form
            action="{{ route('chat.send', $booking->id) }}"
            method="POST"
            class="embedded-chat-form"
            data-booking-id="{{ $booking->id }}"
        >
            @csrf

            <textarea
                name="message"
                rows="1"
                class="embedded-chat-input"
                placeholder="Type a message..."
                required
            ></textarea>

            <button
                type="submit"
                class="embedded-chat-send"
            >
                Send
            </button>

        </form>

        @error('message')
            <p class="embedded-chat-error">
                {{ $message }}
            </p>
        @enderror

    </div>

</div>