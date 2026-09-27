<x-guest-layout>

    <div class="glowai-auth-page">

        {{-- LEFT: LOGIN FORM --}}
        <div class="glowai-auth-form-side">

            <div class="glowai-auth-form">

            <div class="glowai-auth-brand">
                <img src="{{ asset('images/glowai-logo.png') }}" alt="GlowAI Logo">
            </div>

                <div class="glowai-auth-heading">
                    <h1>Login to your<br><em>account.</em></h1>
                </div>

                {{-- Session Status --}}
                <x-auth-session-status
                    class="mb-4"
                    :status="session('status')"
                />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="glowai-field">
                        <label for="email">Email Address</label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Enter your email"
                        >

                        <x-input-error
                            :messages="$errors->get('email')"
                            class="mt-2"
                        />
                    </div>

                    {{-- Password --}}
                    <div class="glowai-field">

                    <div class="glowai-password-label">
                        <label for="password">Password</label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <div class="password-input-wrap">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="password"
                            aria-label="Show password"
                        >
                            <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>

                            <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18"/>
                                <path d="M10.6 6.2A9.7 9.7 0 0 1 12 6c6.5 0 10 6 10 6a17.8 17.8 0 0 1-2.1 2.8"/>
                                <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6a9.7 9.7 0 0 0 4.1-.9"/>
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                            </svg>
                        </button>
                    </div>

                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2"
                    />

                    </div>
                    {{-- Remember Me --}}
                    <label class="glowai-remember">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember_me"
                        >
                        <span>Remember me</span>
                    </label>

                    <button type="submit" class="glowai-login-btn">
                        Log In
                    </button>

                </form>

                <p class="glowai-auth-switch">
                    Don't have an account?
                    <a href="{{ route('register') }}">Create account</a>
                </p>

            </div>

        </div>


        {{-- RIGHT: IMAGE --}}
        <div class="glowai-auth-image-side">

            <img
                src="{{ asset('images/auth/login.jpg') }}"
                alt="Makeup artist applying makeup"
            >

            <div class="glowai-auth-overlay"></div>

            <div class="glowai-image-content">

                <h2>
                    Beauty begins<br>
                    <em>with you.</em>
                </h2>

                <span>
                    Discover artists, manage your bookings and
                    find makeup looks made for you.
                </span>
            </div>

        </div>

    </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.password-toggle').forEach(function (button) {

        button.addEventListener('click', function () {

            const input = document.getElementById(this.dataset.target);

            if (input.type === 'password') {
                input.type = 'text';
                this.classList.add('is-visible');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                this.classList.remove('is-visible');
                this.setAttribute('aria-label', 'Show password');
            }

        });

    });

});
</script>
</x-guest-layout>