@extends('layouts.customer')

@section('title', $makeupArtist->user->name . ' | GlowAI')

@section('content')

<a href="{{ route('customer.mua.index') }}" class="profile-back">
    ← Back to Makeup Artists
</a>

<section class="artist-profile">

    {{-- LEFT: PORTFOLIO --}}
    <div class="portfolio-section">

        @if($makeupArtist->portfolios->count() > 0)

            <div class="portfolio-carousel">

                @foreach($makeupArtist->portfolios as $index => $portfolio)

                    <div
                        class="portfolio-slide {{ $index === 0 ? 'active' : '' }}"
                        data-slide="{{ $index }}"
                    >
                        <img
                            src="{{ asset('storage/' . $portfolio->picture) }}"
                            alt="{{ $portfolio->look_title ?? 'Portfolio Image' }}"
                        >

                        @if($portfolio->look_title || $portfolio->description)
                            <div class="portfolio-caption">

                                @if($portfolio->look_title)
                                    <strong>{{ $portfolio->look_title }}</strong>
                                @endif

                                @if($portfolio->description)
                                    <span>{{ $portfolio->description }}</span>
                                @endif

                            </div>
                        @endif
                    </div>

                @endforeach


                @if($makeupArtist->portfolios->count() > 1)

                    <button
                        type="button"
                        class="carousel-arrow carousel-prev"
                        aria-label="Previous portfolio image"
                    >
                        ‹
                    </button>

                    <button
                        type="button"
                        class="carousel-arrow carousel-next"
                        aria-label="Next portfolio image"
                    >
                        ›
                    </button>

                @endif

            </div>


            @if($makeupArtist->portfolios->count() > 1)

                <div class="carousel-dots">

                    @foreach($makeupArtist->portfolios as $index => $portfolio)

                        <button
                            type="button"
                            class="carousel-dot {{ $index === 0 ? 'active' : '' }}"
                            data-slide="{{ $index }}"
                            aria-label="View portfolio image {{ $index + 1 }}"
                        ></button>

                    @endforeach

                </div>

            @endif

        @else

            <div class="portfolio-empty">
                <span>✦</span>
                <p>No portfolio available.</p>
            </div>

        @endif

    </div>


    {{-- RIGHT --}}
    <div class="artist-information">

        <div class="artist-heading">

            <span class="page-eyebrow">
                MAKEUP ARTIST
            </span>

            <h1>
                {{ $makeupArtist->user->name }}
            </h1>

            @if($makeupArtist->studio_brand_name)
                <p class="artist-studio">
                    {{ $makeupArtist->studio_brand_name }}
                </p>
            @endif

        </div>


        <div class="artist-about">

            <h3>About the Artist</h3>

            <p>
                {{ $makeupArtist->description ?? 'No description provided.' }}
            </p>


            <div class="artist-meta-grid">

                <div class="artist-meta">
                    <span>LOCATION</span>

                    <strong>
                        {{ $makeupArtist->studio_address ?? 'Not specified' }}
                    </strong>

                    @if($makeupArtist->willing_to_travel)
                        <small>
                            Available for travel
                            @if($makeupArtist->travel_location)
                                · {{ $makeupArtist->travel_location }}
                            @endif
                        </small>
                    @else
                        <small>Studio appointment</small>
                    @endif
                </div>


                <div class="artist-meta">
                    <span>SPECIALIZATION</span>

                    <strong>
                        {{ $makeupArtist->specialized_makeup_look ?? 'Not specified' }}
                    </strong>

                    <small>
                        {{ $makeupArtist->years_experience ?? 0 }}
                        {{ $makeupArtist->years_experience == 1 ? 'year' : 'years' }}
                        experience
                    </small>
                </div>

            </div>


            @if(filled($makeupArtist->instagram) || filled($makeupArtist->tiktok))

                <div class="artist-socials">

                    @if(filled($makeupArtist->instagram))
                        @php
                            $instagram = trim($makeupArtist->instagram);

                            if (!str_starts_with($instagram, 'http')) {
                                $instagram = 'https://www.instagram.com/' . ltrim($instagram, '@');
                            }
                        @endphp

                        <a
                            href="{{ $instagram }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Instagram ↗
                        </a>
                    @endif


                    @if(filled($makeupArtist->tiktok))
                        @php
                            $tiktok = trim($makeupArtist->tiktok);

                            if (!str_starts_with($tiktok, 'http')) {
                                $tiktok = 'https://www.tiktok.com/@' . ltrim($tiktok, '@');
                            }
                        @endphp

                        <a
                            href="{{ $tiktok }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            TikTok ↗
                        </a>
                    @endif

                </div>

            @endif
        </div>


        {{-- SERVICES --}}
        <div class="artist-services">

            <div class="services-heading">
                <span class="page-eyebrow">SERVICES</span>
                <h2>Services Available</h2>
            </div>


            @if($makeupArtist->services->count() > 0)

                <div class="services-list">

                    @foreach($makeupArtist->services as $service)

                        <article class="service-card">

                            <div class="service-main">

                                <div class="service-title-row">

                                    <div>
                                        <h3>{{ $service->service_name }}</h3>

                                        <span>
                                            {{ $service->category }}
                                            ·
                                            {{ $service->duration }} min
                                        </span>
                                    </div>

                                    <strong class="service-price">
                                        RM{{ number_format($service->price, 2) }}
                                    </strong>

                                </div>


                                @if($service->description)
                                    <p>
                                        {{ $service->description }}
                                    </p>
                                @endif


                                @if($service->service_included)
                                    <div class="service-included">
                                        <span>Includes</span>
                                        <p>{{ $service->service_included }}</p>
                                    </div>
                                @endif

                            </div>


                            <div class="service-footer">

                                <div class="deposit-info">
                                    <span>Deposit</span>

                                    <strong>
                                        RM{{ number_format($service->deposit_amount ?? 0, 2) }}
                                    </strong>
                                </div>

                                <a
                                    href="{{ route('customer.booking.create', $service->id) }}"
                                    class="book-now-btn"
                                >
                                    Book Now
                                </a>

                            </div>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="services-empty">
                    No services are currently available.
                </div>

            @endif

        </div>

    </div>

</section>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const slides = document.querySelectorAll('.portfolio-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    const previousButton = document.querySelector('.carousel-prev');
    const nextButton = document.querySelector('.carousel-next');

    if (slides.length <= 1) {
        return;
    }

    let currentSlide = 0;

    function showSlide(index) {

        if (index < 0) {
            index = slides.length - 1;
        }

        if (index >= slides.length) {
            index = 0;
        }

        slides.forEach(slide => {
            slide.classList.remove('active');
        });

        dots.forEach(dot => {
            dot.classList.remove('active');
        });

        slides[index].classList.add('active');

        if (dots[index]) {
            dots[index].classList.add('active');
        }

        currentSlide = index;
    }


    previousButton?.addEventListener('click', function () {
        showSlide(currentSlide - 1);
    });


    nextButton?.addEventListener('click', function () {
        showSlide(currentSlide + 1);
    });


    dots.forEach(dot => {

        dot.addEventListener('click', function () {

            showSlide(
                parseInt(this.dataset.slide)
            );

        });

    });

});
</script>

@endpush