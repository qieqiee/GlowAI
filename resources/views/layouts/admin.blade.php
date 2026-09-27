<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin | GlowAI')</title>

    @vite([
        'resources/css/app.css',
        'resources/css/admin.css',
        'resources/js/app.js'
    ])
</head>

<body class="admin-body">

<nav class="admin-navbar">

<div class="admin-nav-container">

    {{-- LOGO --}}
    <a href="{{ route('admin.dashboard') }}" class="admin-logo-link">
        <img
            src="{{ asset('images/glowai-logo.png') }}"
            alt="GlowAI"
            class="admin-navbar-logo"
        >
    </a>


    {{-- HAMBURGER --}}
    <button
        type="button"
        class="admin-nav-toggle"
        id="adminNavToggle"
        aria-label="Open navigation menu"
        aria-expanded="false"
        aria-controls="adminNavMenu"
    >
        <span></span>
        <span></span>
        <span></span>
    </button>


    {{-- MENU WRAPPER --}}
    <div class="admin-nav-menu" id="adminNavMenu">

        {{-- NAVIGATION --}}
        <div class="admin-nav-links">

            <a
                href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            >
                Dashboard
            </a>

            <a
                href="{{ route('admin.users.index') }}"
                class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
            >
                Manage Users
            </a>

            <a
                href="{{ route('admin.muas.index') }}"
                class="{{ request()->routeIs('admin.muas.*') ? 'active' : '' }}"
            >
                Manage Makeup Artists
            </a>

            <a
                href="{{ route('admin.booking.index') }}"
                class="{{ request()->routeIs('admin.booking.*') ? 'active' : '' }}"
            >
                Manage Bookings
            </a>

        </div>


        {{-- ADMIN + LOGOUT --}}
        <div class="admin-nav-user">

            <span class="admin-user-name">
                {{ auth()->user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="admin-logout-btn">
                    Log Out
                </button>
            </form>

        </div>

    </div>

</div>

</nav>


    <main class="admin-main">
        @yield('content')
    </main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('adminNavToggle');
    const menu = document.getElementById('adminNavMenu');

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