@extends('layouts.mua')
@section('title', 'Review MUA Profile | GlowAI')
@section('body-class', 'review-body')
@section('page-assets')
@vite([
        'resources/css/app.css',
        'resources/css/mua-review.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')
<div class="review-page">

    {{-- HEADER --}}
    <div class="review-header">

        <a href="{{ url('/') }}" class="review-logo">
            <img
                src="{{ asset('images/glowai-logo.png') }}"
                alt="GlowAI Logo"
            >
        </a>

        <div class="review-heading">
            <h1>
                Review your <em>profile.</em>
            </h1>

        </div>

    </div>


    {{-- SUCCESS MESSAGE --}}
    <div class="review-notice">
        <span>✓</span>

        <div>
            <strong>Your makeup artist profile has been created!</strong>
        </div>
    </div>


    {{-- PROFILE CARD --}}
    <div class="review-card">

        <div class="review-profile-top">

            {{-- PROFILE PHOTO --}}
            <div class="review-photo">

                @if($makeupArtist->profile_picture)

                    <img
                        src="{{ asset('storage/' . $makeupArtist->profile_picture) }}"
                        alt="{{ auth()->user()->name }}"
                    >

                @else

                    <div class="review-photo-placeholder">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                @endif

            </div>


            {{-- MAIN INFO --}}
            <div class="review-main-info">

                <span class="review-label">MAKEUP ARTIST</span>

                <h2>{{ auth()->user()->name }}</h2>

                @if($makeupArtist->studio_brand_name)
                    <p class="review-studio">
                        {{ $makeupArtist->studio_brand_name }}
                    </p>
                @endif

                <div class="review-tags">

                    @if($makeupArtist->specialized_makeup_look)
                        <span>
                            {{ $makeupArtist->specialized_makeup_look }}
                        </span>
                    @endif

                    <span>
                        {{ $makeupArtist->years_experience }}
                        {{ $makeupArtist->years_experience == 1 ? 'year' : 'years' }}
                        experience
                    </span>

                </div>

            </div>

        </div>


        {{-- PROFILE DETAILS --}}
        <div class="review-details">

            <div class="review-detail">
                <span>Location</span>
                <strong>
                    {{ $makeupArtist->studio_address ?: 'Not provided' }}
                </strong>
            </div>

            <div class="review-detail">
                <span>Phone Number</span>
                <strong>
                    {{ $makeupArtist->phone ?: 'Not provided' }}
                </strong>
            </div>

            <div class="review-detail">
                <span>Specialization</span>
                <strong>
                    {{ $makeupArtist->specialized_makeup_look ?: 'Not provided' }}
                </strong>
            </div>

            <div class="review-detail">
                <span>Experience</span>
                <strong>
                    {{ $makeupArtist->years_experience }}
                    {{ $makeupArtist->years_experience == 1 ? 'year' : 'years' }}
                </strong>
            </div>

            <div class="review-detail">
                <span>Willing to Travel</span>
                <strong>
                    {{ $makeupArtist->willing_to_travel ? 'Yes' : 'No' }}
                </strong>
            </div>

            <div class="review-detail">
                <span>Travel Location</span>
                <strong>
                    {{ $makeupArtist->travel_location ?: 'Not provided' }}
                </strong>
            </div>

        </div>


        {{-- ABOUT --}}
        <div class="review-about">

            <span class="review-section-label">About the Artist</span>

            <p>
                {{ $makeupArtist->description ?: 'No description provided.' }}
            </p>

        </div>


        {{-- SOCIAL MEDIA --}}
@if($makeupArtist->instagram || $makeupArtist->tiktok)

    <div class="review-socials">

        <span class="review-section-label">
            Social Media
        </span>

        <div class="review-social-links">

            {{-- INSTAGRAM --}}
            @if($makeupArtist->instagram)

                <div class="social-item">

                    <svg
                        class="social-icon instagram-icon"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <defs>
                            <radialGradient
                                id="instagramGradient"
                                cx="30%"
                                cy="107%"
                                r="150%"
                            >
                                <stop offset="0%" stop-color="#ffd600"/>
                                <stop offset="25%" stop-color="#ff7a00"/>
                                <stop offset="50%" stop-color="#ff0169"/>
                                <stop offset="75%" stop-color="#d300c5"/>
                                <stop offset="100%" stop-color="#7638fa"/>
                            </radialGradient>
                        </defs>

                        <rect
                            x="2"
                            y="2"
                            width="20"
                            height="20"
                            rx="5"
                            fill="url(#instagramGradient)"
                        />

                        <rect
                            x="6.5"
                            y="6.5"
                            width="11"
                            height="11"
                            rx="3.5"
                            fill="none"
                            stroke="white"
                            stroke-width="1.8"
                        />

                        <circle
                            cx="12"
                            cy="12"
                            r="2.8"
                            fill="none"
                            stroke="white"
                            stroke-width="1.8"
                        />

                        <circle
                            cx="17.2"
                            cy="6.9"
                            r="1"
                            fill="white"
                        />
                    </svg>

                    <span class="social-divider"></span>

                    <span>
                        Instagram · {{ $makeupArtist->instagram }}
                    </span>

                </div>

            @endif


            {{-- TIKTOK --}}
            @if($makeupArtist->tiktok)

                <div class="social-item">

                    <svg
                        class="social-icon tiktok-icon"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            d="M14 3v10.2a4.2 4.2 0 1 1-3.4-4.1"
                            fill="none"
                            stroke="#25F4EE"
                            stroke-width="3.4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            transform="translate(-1 1)"
                        />

                        <path
                            d="M14 3c.4 3 2.1 4.7 5 5"
                            fill="none"
                            stroke="#FE2C55"
                            stroke-width="3.4"
                            stroke-linecap="round"
                        />

                        <path
                            d="M14 3v10.2a4.2 4.2 0 1 1-3.4-4.1M14 3c.4 3 2.1 4.7 5 5"
                            fill="none"
                            stroke="#111111"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <span class="social-divider"></span>

                    <span>
                        TikTok · {{ $makeupArtist->tiktok }}
                    </span>

                </div>

            @endif

        </div>

    </div>

@endif
    </div>


    {{-- BOTTOM --}}
    <div class="review-actions">

        <p>
            Once published, clients will be able to discover your profile on GlowAI.
        </p>

        <form
            method="POST"
            action="{{ route('makeup-artist.publish') }}"
        >
            @csrf

            <button type="submit" class="publish-btn">
                Publish Profile
                <span>→</span>
            </button>

        </form>

    </div>

</div>
@endsection
