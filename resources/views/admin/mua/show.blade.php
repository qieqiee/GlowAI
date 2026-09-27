@extends('layouts.admin')

@section('title', 'Makeup Artist Details | GlowAI')

@section('content')

<div class="admin-page">

    {{-- BACK --}}
    <a href="{{ route('admin.muas.index') }}" class="admin-back-link">
        ← Back to Manage Makeup Artists
    </a>


    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="admin-success-message">
            {{ session('success') }}
        </div>
    @endif


    {{-- PAGE HEADING --}}
    <div class="admin-section-heading admin-detail-heading">
        <span>Makeup Artist Management</span>

        <h1>Makeup Artist Details</h1>

        <p>
            Review the makeup artist's profile, services and publication status.
        </p>
    </div>


    {{-- PROFILE OVERVIEW --}}
    <section class="admin-detail-card">

        <div class="admin-mua-profile-header">

            <div class="admin-detail-avatar">

                @if($makeupArtist->profile_picture)

                    <img
                        src="{{ asset('storage/' . $makeupArtist->profile_picture) }}"
                        alt="{{ $makeupArtist->user->name }}"
                    >

                @else

                    <span>
                        {{ strtoupper(substr($makeupArtist->user->name, 0, 1)) }}
                    </span>

                @endif

            </div>


            <div class="admin-mua-profile-info">

                <div class="admin-profile-title-row">

                    <div>
                        <h2>
                            {{ $makeupArtist->user->name }}
                        </h2>

                        <p>
                            {{ $makeupArtist->studio_brand_name ?: 'Independent Makeup Artist' }}
                        </p>
                    </div>


                    @if($makeupArtist->is_published)

                        <span class="admin-status-badge published">
                            Published
                        </span>

                    @else

                        <span class="admin-status-badge unpublished">
                            Unpublished
                        </span>

                    @endif

                </div>


                <div class="admin-profile-meta">

                    <span>
                        {{ $makeupArtist->user->email }}
                    </span>

                    <span>
                        {{ $makeupArtist->phone ?: 'No phone number' }}
                    </span>

                </div>

            </div>

        </div>

    </section>


    {{-- INFORMATION GRID --}}
    <div class="admin-detail-grid">

        {{-- PERSONAL / BUSINESS INFORMATION --}}
        <section class="admin-detail-card">

            <div class="admin-detail-card-header">
                <div>
                    <h2>Profile Information</h2>
                    <p>Personal and business information.</p>
                </div>
            </div>


            <div class="admin-info-list">

                <div class="admin-info-item">
                    <span>Name</span>
                    <strong>
                        {{ $makeupArtist->user->name }}
                    </strong>
                </div>


                <div class="admin-info-item">
                    <span>Email</span>
                    <strong>
                        {{ $makeupArtist->user->email }}
                    </strong>
                </div>


                <div class="admin-info-item">
                    <span>Studio / Brand</span>
                    <strong>
                        {{ $makeupArtist->studio_brand_name ?: '-' }}
                    </strong>
                </div>


                <div class="admin-info-item">
                    <span>Phone</span>
                    <strong>
                        {{ $makeupArtist->phone ?: '-' }}
                    </strong>
                </div>


                <div class="admin-info-item admin-info-full">
                    <span>Studio Address</span>
                    <strong>
                        {{ $makeupArtist->studio_address ?: '-' }}
                    </strong>
                </div>

            </div>

        </section>


        {{-- PROFESSIONAL INFORMATION --}}
        <section class="admin-detail-card">

            <div class="admin-detail-card-header">
                <div>
                    <h2>Professional Information</h2>
                    <p>Experience and service preferences.</p>
                </div>
            </div>


            <div class="admin-info-list">

                <div class="admin-info-item">
                    <span>Years Experience</span>
                    <strong>
                        {{ $makeupArtist->years_experience }}
                        {{ $makeupArtist->years_experience == 1 ? 'year' : 'years' }}
                    </strong>
                </div>


                <div class="admin-info-item">
                    <span>Specialization</span>
                    <strong>
                        {{ $makeupArtist->specialized_makeup_look ?: '-' }}
                    </strong>
                </div>


                <div class="admin-info-item">
                    <span>Willing to Travel</span>

                    <strong>
                        {{ $makeupArtist->willing_to_travel ? 'Yes' : 'No' }}
                    </strong>
                </div>


                <div class="admin-info-item">
                    <span>Travel Location</span>

                    <strong>
                        {{ $makeupArtist->travel_location ?: '-' }}
                    </strong>
                </div>


                <div class="admin-info-item admin-info-full">
                    <span>Description</span>

                    <strong class="admin-description-text">
                        {{ $makeupArtist->description ?: '-' }}
                    </strong>
                </div>

            </div>

        </section>

    </div>


    {{-- SERVICES + PORTFOLIO --}}
    <div class="admin-detail-grid admin-content-grid">

        {{-- SERVICES --}}
        <section class="admin-detail-card">

            <div class="admin-detail-card-header">

                <div>
                    <h2>Services</h2>
                    <p>Services currently offered by this makeup artist.</p>
                </div>

                <span class="admin-count-badge">
                    {{ $makeupArtist->services->count() }}
                </span>

            </div>


            <div class="admin-services-list">

                @forelse($makeupArtist->services as $service)

                    <div class="admin-service-item">

                        <div>
                            <strong>
                                {{ $service->service_name }}
                            </strong>

                            <span>
                                {{ $service->category ?: 'Makeup Service' }}
                            </span>
                        </div>

                        <strong class="admin-service-price">
                            RM {{ number_format($service->price, 2) }}
                        </strong>

                    </div>

                @empty

                    <div class="admin-detail-empty">
                        No services available.
                    </div>

                @endforelse

            </div>

        </section>


        {{-- PORTFOLIO --}}
        <section class="admin-detail-card">

            <div class="admin-detail-card-header">

                <div>
                    <h2>Portfolio</h2>
                    <p>Makeup work uploaded to the artist's profile.</p>
                </div>

                <span class="admin-count-badge">
                    {{ $makeupArtist->portfolios->count() }}
                </span>

            </div>


            @if($makeupArtist->portfolios->count() > 0)

                <div class="admin-portfolio-preview">

                    @foreach($makeupArtist->portfolios->take(4) as $portfolio)

                        <div class="admin-portfolio-image">

                            @if($portfolio->picture)

                                <img
                                    src="{{ asset('storage/' . $portfolio->picture) }}"
                                    alt="{{ $portfolio->look_title }}"
                                >

                            @else

                                <span>No Image</span>

                            @endif

                        </div>

                    @endforeach

                </div>


                <p class="admin-portfolio-total">
                    {{ $makeupArtist->portfolios->count() }}
                    portfolio
                    {{ $makeupArtist->portfolios->count() == 1 ? 'image' : 'images' }}
                    uploaded.
                </p>

            @else

                <div class="admin-detail-empty">
                    No portfolio images uploaded.
                </div>

            @endif

        </section>

    </div>


    {{-- PUBLICATION --}}
    <section class="admin-detail-card admin-publication-card">

        <div class="admin-publication-content">

            <div>

                <span class="admin-publication-label">
                    Profile Visibility
                </span>

                <h2>
                    {{ $makeupArtist->is_published
                        ? 'This makeup artist is currently published'
                        : 'This makeup artist is currently unpublished' }}
                </h2>

                <p>
                    @if($makeupArtist->is_published)
                        The profile is visible to customers on GlowAI.
                    @else
                        The profile is currently hidden from customers.
                    @endif
                </p>

            </div>


            <form
                method="POST"
                action="{{ route('admin.muas.togglePublish', $makeupArtist->id) }}"
            >
                @csrf

                <button
                    type="submit"
                    class="admin-publish-button {{ $makeupArtist->is_published ? 'unpublish' : 'publish' }}"
                    onclick="return confirm('{{ $makeupArtist->is_published
                        ? 'Are you sure you want to unpublish this makeup artist?'
                        : 'Are you sure you want to publish this makeup artist?' }}')"
                >

                    @if($makeupArtist->is_published)
                        Unpublish MUA
                    @else
                        Publish MUA
                    @endif

                </button>

            </form>

        </div>

    </section>

</div>

@endsection