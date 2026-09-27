<nav class="glowai-navbar">

    <div class="nav-container">

        {{-- LOGO --}}
        <a
            href="{{ route('mua.dashboard') }}"
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
            id="muaNavToggle"
            aria-label="Open navigation menu"
            aria-expanded="false"
            aria-controls="muaNavMenu"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>


        {{-- NAVIGATION MENU --}}
        <div
            class="nav-menu"
            id="muaNavMenu"
        >

            <div class="nav-links">

                <a
                    href="{{ route('mua.dashboard') }}"
                    class="{{ request()->routeIs('mua.dashboard', 'dashboard') ? 'active' : '' }}"
                    @if(request()->routeIs('mua.dashboard', 'dashboard')) aria-current="page" @endif
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('mua.availability') }}"
                    class="{{ request()->routeIs('mua.availability*') ? 'active' : '' }}"
                    @if(request()->routeIs('mua.availability*')) aria-current="page" @endif
                >
                    Calendar & Availability
                </a>

                <a
                    href="{{ route('mua.bookings.index') }}"
                    class="{{ request()->routeIs('mua.bookings.*') ? 'active' : '' }}"
                    @if(request()->routeIs('mua.bookings.*')) aria-current="page" @endif
                >
                    Bookings
                </a>

                <a
                    href="{{ route('mua.services') }}"
                    class="{{ request()->routeIs('mua.services*') ? 'active' : '' }}"
                    @if(request()->routeIs('mua.services*')) aria-current="page" @endif
                >
                    Services
                </a>

                <a
                    href="{{ route('mua.profile') }}"
                    class="{{ request()->routeIs('mua.profile*', 'mua.portfolio.*', 'mua.review', 'makeup-artist.register*') ? 'active' : '' }}"
                    @if(request()->routeIs('mua.profile*', 'mua.portfolio.*', 'mua.review', 'makeup-artist.register*')) aria-current="page" @endif
                >
                    Profile
                </a>

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