@extends('layouts.mua')
@section('title', 'Services Offered | GlowAI')
@section('page-assets')
@vite([
        'resources/css/app.css',
        'resources/css/mua-services.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')
{{-- =========================================
     NAVBAR
========================================= --}}



<main class="services-page">

    <div class="services-heading">

        <div>
            <h1>Services Offered</h1>
            <p>Manage the makeup services available for your clients.</p>
        </div>

        <a href="{{ route('mua.services.create') }}"
           class="add-service-btn">
            + Add Service
        </a>

    </div>


    @if(session('success'))
        <div class="service-alert">
            {{ session('success') }}
        </div>
    @endif


    <div class="services-grid">

        @forelse($makeupArtist->services as $service)

            <article class="service-card">

                <div class="service-card-top">

                    <div>

                        <span class="service-category">
                            {{ $service->category }}
                        </span>

                        <h2>
                            {{ $service->service_name }}
                        </h2>

                    </div>


                    <div class="service-price">

                        <strong>
                            RM{{ number_format($service->price, 2) }}
                        </strong>

                        <span>
                            Service price
                        </span>

                    </div>

                </div>


                <div class="service-description">

                    {{ $service->description ?? 'No description provided.' }}

                </div>


                <div class="service-details">

                    <div class="service-detail">

                        <span>Deposit</span>

                        <strong>
                            RM{{ number_format($service->deposit_amount ?? 0, 2) }}
                        </strong>

                    </div>


                    <div class="service-detail">

                        <span>Duration</span>

                        <strong>
                            {{ $service->duration }} minutes
                        </strong>

                    </div>

                </div>


                <div class="service-included">

                    <span>What's Included</span>

                    <p>
                        {{ $service->service_included ?? 'Not specified' }}
                    </p>

                </div>


                <div class="service-actions">

                    <a
                        href="{{ route('mua.services.edit', $service->id) }}"
                        class="service-edit-btn"
                    >
                        Edit Service
                    </a>


                    <form
                        method="POST"
                        action="{{ route('mua.services.destroy', $service->id) }}"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="service-delete-btn"
                            onclick="return confirm('Are you sure you want to delete this service?')"
                        >
                            Delete
                        </button>

                    </form>

                </div>

            </article>

        @empty

            <div class="services-empty">

                <h3>No services yet</h3>

                <p>
                    Add your first makeup service to start accepting bookings.
                </p>

            </div>

        @endforelse

    </div>

</main>
@endsection
