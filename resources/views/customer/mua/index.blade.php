@extends('layouts.customer')

@section('title', 'Find Makeup Artist | GlowAI')

@section('content')

<section class="mua-page-header">

    <span class="page-eyebrow">FIND YOUR ARTIST</span>

    <h1>Discover Makeup Artists</h1>

    <p>
        Explore professional makeup artists, view their 
        portfolios and services to find the right artist for you.
    </p>

</section>

<section class="mua-location-filter">

    <form
        method="GET"
        action="{{ route('customer.mua.index') }}"
        class="location-filter-form"
    >
        <div class="location-filter-field">

            <label for="state">
                Filter by Location
            </label>

            <select
                id="state"
                name="state"
            >
                <option value="">All States</option>

                @foreach([
                    'Johor',
                    'Kedah',
                    'Kelantan',
                    'Melaka',
                    'Negeri Sembilan',
                    'Pahang',
                    'Perak',
                    'Perlis',
                    'Pulau Pinang',
                    'Sabah',
                    'Sarawak',
                    'Selangor',
                    'Terengganu',
                    'Kuala Lumpur',
                    'Labuan',
                    'Putrajaya'
                ] as $state)

                    <option
                        value="{{ $state }}"
                        {{ ($selectedState ?? '') === $state ? 'selected' : '' }}
                    >
                        {{ $state }}
                    </option>

                @endforeach
            </select>

        </div>

        <button type="submit" class="location-filter-button">
            Filter
        </button>

        @if(!empty($selectedState))
            <a
                href="{{ route('customer.mua.index') }}"
                class="location-filter-clear"
            >
                Clear
            </a>
        @endif

    </form>

</section>

@if($makeupArtists->count() > 0)

    <section class="mua-grid">

        @foreach($makeupArtists as $makeupArtist)

            <article class="mua-card">

                <div class="mua-card-image">

                    @if($makeupArtist->profile_picture)

                        <img
                            src="{{ asset('storage/' . $makeupArtist->profile_picture) }}"
                            alt="{{ $makeupArtist->user->name }}"
                        >

                    @elseif($makeupArtist->portfolios->count() > 0)

                        <img
                            src="{{ asset('storage/' . $makeupArtist->portfolios->first()->image_path) }}"
                            alt="{{ $makeupArtist->user->name }}"
                        >

                    @else

                        <div class="mua-image-placeholder">
                            <span>GlowAI</span>
                        </div>

                    @endif

                </div>


                <div class="mua-card-content">

                    <div class="mua-card-heading">

                        <div>
                            <h2>
                                {{ $makeupArtist->user->name }}
                            </h2>

                            @if($makeupArtist->studio_brand_name)
                                <span class="studio-name">
                                    {{ $makeupArtist->studio_brand_name }}
                                </span>
                            @endif
                        </div>

                        @if($makeupArtist->instagram)

                        @php
                            $instagram = $makeupArtist->instagram;

                            // If database stores only username, create full Instagram URL
                            if (!str_starts_with($instagram, 'http')) {
                                $instagram = 'https://www.instagram.com/' . ltrim($instagram, '@');
                            }
                        @endphp

                        <a
                            href="{{ $instagram }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="instagram-btn"
                            aria-label="Visit {{ $makeupArtist->user->name }} on Instagram"
                            title="View Instagram"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="5"
                                ></rect>

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="4"
                                ></circle>

                                <circle
                                    cx="17.5"
                                    cy="6.5"
                                    r="1"
                                    class="instagram-dot"
                                ></circle>
                            </svg>
                        </a>

                    @endif

                    </div>


                    <div class="mua-details">

                        <div class="mua-detail">
                            <span>Specialization</span>

                            <strong>
                                {{ $makeupArtist->specialized_makeup_look ?? 'Not specified' }}
                            </strong>
                        </div>


                        <div class="mua-detail">
                            <span>Experience</span>

                            <strong>
                                @if($makeupArtist->years_experience)
                                    {{ $makeupArtist->years_experience }}
                                    {{ $makeupArtist->years_experience == 1 ? 'year' : 'years' }}
                                @else
                                    Not specified
                                @endif
                            </strong>
                        </div>

                        <div class="mua-detail mua-location-detail">
                            <span>Location</span>

                            <p class="mua-location-text">
                                @if(!empty($makeupArtist->service_areas) || !empty($makeupArtist->service_states))

                                    @if(!empty($makeupArtist->service_areas))
                                        {{ implode(', ', $makeupArtist->service_areas) }}
                                    @endif

                                    @if(!empty($makeupArtist->service_areas) && !empty($makeupArtist->service_states))
                                        <span class="location-separator"> · </span>
                                    @endif

                                    @if(!empty($makeupArtist->service_states))
                                        {{ implode(', ', $makeupArtist->service_states) }}
                                    @endif

                                @else
                                    <span class="location-not-specified">Not specified</span>
                                @endif
                            </p>
                        </div>

                    </div>


                    <div class="mua-card-footer">

                        <div class="mua-services-info">

                            @if($makeupArtist->services->count() > 0)

                                <span>Services from</span>

                                <strong>
                                    RM{{ number_format($makeupArtist->services->min('price'), 2) }}
                                </strong>

                            @else

                                <span>No services listed yet</span>

                            @endif

                        </div>


                        <a
                            href="{{ route('customer.mua.show', $makeupArtist->id) }}"
                            class="view-profile-btn"
                        >
                            View Profile
                        </a>

                    </div>

                </div>

            </article>

        @endforeach

    </section>

@else

    <div class="empty-state">

        <span>✦</span>

        <h2>No Makeup Artists Available</h2>

        <p>
            @if(!empty($selectedState))
                No makeup artists are currently available in {{ $selectedState }}.
                Try selecting another location.
            @else
                There are currently no published makeup artists.
                Please check again later.
            @endif
        </p>

    </div>

@endif

@endsection