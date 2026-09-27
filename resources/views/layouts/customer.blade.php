<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'GlowAI')
    </title>

    <link
        rel="stylesheet"
        href="{{ asset('css/glowai.css') }}"
    >

    @stack('styles')
</head>

<body>

<nav class="glowai-navbar">

<div class="nav-container">

    {{-- LOGO --}}
    <a
        href="{{ route('dashboard') }}"
        class="logo-link"
    >
        <img
            src="{{ asset('images/glowai-logo.png') }}"
            alt="GlowAI Logo"
            class="navbar-logo"
        >
    </a>


    {{-- HAMBURGER --}}
    <button
        type="button"
        class="nav-toggle"
        id="customerNavToggle"
        aria-label="Open navigation menu"
        aria-expanded="false"
        aria-controls="customerNavMenu"
    >
        <span></span>
        <span></span>
        <span></span>
    </button>


    {{-- NAVIGATION MENU --}}
    <div
        class="nav-menu"
        id="customerNavMenu"
    >

        <div class="nav-links">

            <a
                href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                Home
            </a>

            <a
                href="{{ route('customer.mua.index') }}"
                class="{{ request()->routeIs('customer.mua.*') ? 'active' : '' }}"
            >
                Find Makeup Artist
            </a>

            <a
                href="{{ route('customer.ai.index') }}"
                class="{{ request()->routeIs('customer.ai.*') ? 'active' : '' }}"
            >
                AI Recommendation
            </a>

            <a
                href="{{ route('customer.bookings.index') }}"
                class="{{ request()->routeIs('customer.bookings.*') ? 'active' : '' }}"
            >
                My Bookings
            </a>


            @if (!auth()->user()->makeupArtist)

                <a
                    href="{{ route('makeup-artist.register') }}"
                    class="mua-cta {{ request()->routeIs('makeup-artist.register') ? 'active' : '' }}"
                >
                    Become a Makeup Artist
                </a>

            @else

                <a
                    href="{{ route('mua.dashboard') }}"
                >
                    MUA Dashboard
                </a>

            @endif

        </div>


        {{-- USER --}}
        <div class="nav-user">

            <span class="user-name">
                {{ auth()->user()->name }}
            </span>

            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="logout-btn"
                >
                    Logout
                </button>

            </form>

        </div>

    </div>

</div>

</nav>

<main class="page-container">

@if (session('success'))
    <div>
        {{ session('success') }}
    </div>
@endif

@yield('content')

</main>

@stack('scripts')


<script>
document.addEventListener('DOMContentLoaded', function () {

const toggle = document.getElementById('customerNavToggle');
const menu = document.getElementById('customerNavMenu');

if (!toggle || !menu) {
    return;
}

toggle.addEventListener('click', function () {

    const isOpen = menu.classList.toggle('open');

    toggle.classList.toggle('open', isOpen);

    toggle.setAttribute(
        'aria-expanded',
        isOpen ? 'true' : 'false'
    );

});

});
</script>

</body>
</html>