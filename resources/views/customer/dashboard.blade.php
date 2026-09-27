@extends('layouts.customer')

@section('title', 'Home | GlowAI')

@section('content')

<section class="customer-hero">
    <div class="hero-content">
        <span class="hero-label">BEAUTY MEETS TECHNOLOGY</span>

        <h1>
            Find Your Perfect Look<br>
            with <em>GlowAI</em>
        </h1>

        <p>
            Discover professional makeup artists, book your preferred
            services, and get personalized AI-powered makeup recommendations.
        </p>

        <div class="hero-actions">
            <a href="{{ route('customer.mua.index') }}" class="btn-primary">
                Find Makeup Artist
            </a>

            <a href="{{ route('customer.ai.index') }}" class="btn-secondary">
                Try AI Recommendation
            </a>
        </div>
    </div>

    <div class="hero-image-wrapper">
    <img
        src="{{ asset('images/glowai-hero.jpg') }}"
        alt="Makeup artist applying makeup"
        class="hero-image"
        >
    </div>
</section>


<section class="dashboard-section">

    <div class="section-heading">
        <span>EXPLORE GLOWAI</span>
        <h2>What would you like to do?</h2>
        <p>
            Everything you need for your makeup journey in one place.
        </p>
    </div>

    <div class="quick-grid">

        <a href="{{ route('customer.mua.index') }}" class="quick-card">
            <div class="quick-icon">♡</div>

            <h3>Find a Makeup Artist</h3>

            <p>
                Browse makeup artists, explore their portfolios
                and compare available services.
            </p>

            <span class="card-link">
                Explore Artists →
            </span>
        </a>


        <a href="{{ route('customer.ai.index') }}" class="quick-card">
            <div class="quick-icon">✦</div>

            <h3>AI Makeup Recommendation</h3>

            <p>
                Analyze your facial features and discover
                makeup recommendations personalized for you.
            </p>

            <span class="card-link">
                Try AI Analysis →
            </span>
        </a>


        <a href="{{ route('customer.bookings.index') }}" class="quick-card">
            <div class="quick-icon">◷</div>

            <h3>My Bookings</h3>

            <p>
                Manage your appointments, check booking status
                and communicate with your makeup artist.
            </p>

            <span class="card-link">
                View Bookings →
            </span>
        </a>

    </div>

</section>

@endsection