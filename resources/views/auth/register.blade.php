<x-guest-layout>

    <div class="glowai-auth-page glowai-register-page">

        {{-- LEFT: IMAGE --}}
        <div class="glowai-auth-image-side glowai-register-image">

            <img
                src="{{ asset('images/auth/signup.jpg') }}"
                alt="Makeup collection"
            >

            <div class="glowai-auth-overlay"></div>

            <div class="glowai-image-content">
                <h2>
                    Let's get<br>
                    <em>started.</em>
                </h2>

                <span>
                    Create your GlowAI account and begin your
                    personalised beauty journey.
                </span>
            </div>

        </div>


        {{-- RIGHT: REGISTER FORM --}}
        <div class="glowai-auth-form-side">

            <div class="glowai-auth-form glowai-register-form">

                {{-- Logo --}}
                <div class="glowai-auth-brand">
                    <img
                        src="{{ asset('images/glowai-logo.png') }}"
                        alt="GlowAI Logo"
                    >
                </div>

                <div class="glowai-auth-heading">
                    <h1>
                        Create your<br>
                        <em>account.</em>
                    </h1>

                </div>


                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    {{-- Full Name --}}
                    <div class="glowai-field">
                        <label for="name">Name</label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Enter your name"
                        >

                        <x-input-error
                            :messages="$errors->get('name')"
                            class="mt-2"
                        />
                    </div>


                    {{-- Email --}}
                    <div class="glowai-field">
                        <label for="email">Email Address</label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            placeholder="Enter your email"
                        >

                        <x-input-error
                            :messages="$errors->get('email')"
                            class="mt-2"
                        />
                    </div>

                    {{-- Register As --}}
                    <div class="glowai-field">
                        <label for="role">Register As</label>

                        <select
                            id="role"
                            name="role"
                            class="glowai-select"
                            required
                        >
                            <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>
                                Customer
                            </option>

                            <option value="makeup_artist" {{ old('role') === 'makeup_artist' ? 'selected' : '' }}>
                                Makeup Artist
                            </option>
                        </select>

                        <x-input-error
                            :messages="$errors->get('role')"
                            class="mt-2"
                        />
                    </div>               

                    {{-- Passwords --}}
                    <div class="glowai-field">
                    <label for="password">Password</label>

                    <div class="password-input-wrap">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Enter password"
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

                <div class="glowai-field">
                        <label for="password_confirmation">Confirm Password</label>

                        <div class="password-input-wrap">
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-target="password_confirmation"
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
                            :messages="$errors->get('password_confirmation')"
                            class="mt-2"
                        />
                    </div>


                    <button
                        type="submit"
                        class="glowai-login-btn"
                    >
                        Create Account
                    </button>

                </form>


                <p class="glowai-auth-switch">
                    Already have an account?
                    <a href="{{ route('login') }}">Log in</a>
                </p>

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