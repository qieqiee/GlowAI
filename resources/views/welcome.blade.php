<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GlowAI | AI-Powered Makeup Booking</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fffaf8;
            color: #211d1e;
        }

        /* NAVBAR */
        .navbar {
            height: 88px;
            padding: 0 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 250, 248, 0.96);
            border-bottom: 1px solid #eadfde;
        }

        .logo img {
            width: 65px;
            display: block;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .login-link {
            color: #8f5365;
            text-decoration: none;
            font-weight: 600;
            padding: 11px 20px;
        }

        .register-link {
            background: #98586b;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 7px;
            transition: 0.2s ease;
        }

        .register-link:hover {
            background: #81485a;
        }

        /* HERO */
        .hero {
            min-height: 680px;
            display: grid;
            grid-template-columns: 52% 48%;
        }

        .hero-content {
            padding: 70px 8% 70px 13%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transform: translateY(-25px);
        }

        .hero h1 {
            font-size: clamp(38px, 3.4vw, 54px);
            line-height: 1.03;
            letter-spacing: -2.5px;
            margin-bottom: 26px;
            max-width: 720px;
        }

        .hero h1 span {
            color: #98586b;
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 500;
            letter-spacing: -1px;
        }

        .hero-description {
            color: #685d60;
            font-size: 16px;
            line-height: 1.7;
            max-width: 610px;
            margin-bottom: 35px;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-block;
            text-decoration: none;
            font-weight: 700;
            padding: 15px 25px;
            border-radius: 7px;
            transition: 0.2s ease;
        }

        .btn-primary {
            background: #98586b;
            color: white;
            border: 2px solid #98586b;
        }

        .btn-primary:hover {
            background: #81485a;
            border-color: #81485a;
        }

        .btn-secondary {
            color: #98586b;
            border: 2px solid #98586b;
            background: transparent;
        }

        .btn-secondary:hover {
            background: #f4e9eb;
        }

        .hero-image {
            position: relative;
            min-height: 650px;
            overflow: hidden;
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            position: absolute;
            inset: 0;
        }

        .hero-image::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg,
                rgba(255,250,248,0.15),
                rgba(0,0,0,0.04)
            );
        }

        /* HOW IT WORKS */
        .how-it-works {
            padding: 65px 8%;
            background: #ffffff;
            text-align: center;
        }

        .section-label {
            color: #98586b;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 38px;
            margin-bottom: 13px;
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 500;
        }

        .section-description {
            color: #756a6c;
            max-width: 620px;
            margin: 0 auto 55px;
            line-height: 1.7;
        }

        .steps {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .step {
            padding: 36px 28px;
            border: 1px solid #eadfde;
            border-radius: 14px;
            text-align: left;
            background: #fffaf8;
        }

        .step-number {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #98586b;
            color: white;
            border-radius: 50%;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .step h3 {
            font-size: 20px;
            margin-bottom: 12px;
        }

        .step p {
            color: #756a6c;
            line-height: 1.65;
            font-size: 15px;
        }

        /* JOIN */
        .join {
            padding: 65px 8%;
            background: #f8eeee;
            text-align: center;
        }

        .join-options {
            max-width: 850px;
            margin: 45px auto 0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .join-card {
            background: white;
            padding: 40px 35px;
            border-radius: 14px;
            border: 1px solid #eadfde;
        }

        .join-card h3 {
            font-size: 23px;
            margin-bottom: 12px;
        }

        .join-card p {
            color: #756a6c;
            line-height: 1.6;
            margin-bottom: 25px;
            min-height: 52px;
        }

        .join-card a {
            display: inline-block;
            background: #98586b;
            color: white;
            text-decoration: none;
            font-weight: 700;
            padding: 13px 23px;
            border-radius: 7px;
        }

        /* FOOTER */
        footer {
            background: #2b2225;
            color: #ddd1d4;
            padding: 25px 8%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
        }

        footer strong {
            color: white;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-content {
                padding: 65px 8%;
            }

            .hero-image {
                min-height: 520px;
            }

            .steps {
                grid-template-columns: 1fr;
            }

            .join-options {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .navbar {
                height: 75px;
                padding: 0 5%;
            }

            .logo img {
                width: 55px;
            }

            .login-link {
                padding: 9px 8px;
            }

            .register-link {
                padding: 10px 13px;
                font-size: 14px;
            }

            .hero h1 {
                font-size: clamp(42px, 4vw, 64px);
                line-height: 1.08;
                letter-spacing: -1.5px;
                margin-bottom: 22px;
                max-width: 650px;
            }

            .hero-description {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                text-align: center;
            }

            footer {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="{{ url('/') }}" class="logo">
        <img src="{{ asset('images/glowai-logo.png') }}" alt="GlowAI Logo">
        </a>

        <div class="nav-right">
            <a href="{{ route('login') }}" class="login-link">Log In</a>
            <a href="#join" class="register-link">Register</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">

            <h1>
                Your Beauty,<br>
                Your <span>Perfect Match.</span>
            </h1>

            <p class="hero-description">
                Discover professional makeup artists, book your appointments,
                and receive AI-powered makeup recommendations tailored to your
                facial features and skin tone.
            </p>

            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn-primary">
                    Get Started
                </a>

                <a href="#how-it-works" class="btn-secondary">
                    Discover GlowAI
                </a>
            </div>
        </div>

        <div class="hero-image">
            <img src="{{ asset('images/glowai-landing.jpg') }}"
                 alt="Makeup shades and lipstick swatches">
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <p class="section-label">How It Works</p>
        <h2 class="section-title">Beauty made simpler.</h2>

        <p class="section-description">
            GlowAI brings makeup discovery, appointment booking and
            personalised recommendations together in one platform.
        </p>

        <div class="steps">
            <div class="step">
                <div class="step-number">01</div>
                <h3>Discover Makeup Artists</h3>
                <p>
                    Explore professional makeup artists and find services
                    that suit your occasion and preferences.
                </p>
            </div>

            <div class="step">
                <div class="step-number">02</div>
                <h3>Book Your Appointment</h3>
                <p>
                    Choose your preferred service, date and time through
                    a simple and organised booking process.
                </p>
            </div>

            <div class="step">
                <div class="step-number">03</div>
                <h3>Get AI Recommendations</h3>
                <p>
                    Receive personalised makeup recommendations based on
                    your facial features and skin tone.
                </p>
            </div>
        </div>
    </section>

    <!-- Join -->
    <section class="join" id="join">
        <p class="section-label">Join GlowAI</p>
        <h2 class="section-title">Choose how you want to get started.</h2>

        <div class="join-options">
            <div class="join-card">
                <h3>I'm a Customer</h3>
                <p>
                    Discover makeup artists, manage your bookings and
                    explore makeup recommendations made for you.
                </p>

                <a href="{{ route('register') }}">
                    Register as Customer
                </a>
            </div>

            <div class="join-card">
                <h3>I'm a Makeup Artist</h3>
                <p>
                    Showcase your services, manage appointments and
                    connect with clients through GlowAI.
                </p>

                <a href="{{ route('register') }}">
                    Register as Makeup Artist
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <strong>GlowAI</strong>
        <span>&copy; {{ date('Y') }} GlowAI. All rights reserved.</span>
    </footer>

</body>
</html>