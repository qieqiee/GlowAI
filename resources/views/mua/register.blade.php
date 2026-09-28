@extends('layouts.mua')
@section('title', 'Become a Makeup Artist | GlowAI')
@section('body-class', 'mua-register-body')
@section('page-assets')
@vite([
        'resources/css/app.css',
        'resources/css/mua-register.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')
<div class="mua-register-page">

    <div class="mua-register-header">
        <a href="{{ url('/') }}" class="mua-logo">
            <img src="{{ asset('images/glowai-logo.png') }}" alt="GlowAI Logo">
        </a>

        <div>
            <h1>Join GlowAI as a <em>Makeup Artist.</em></h1>
            <p>Create your professional profile and showcase your makeup services to clients.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mua-error-box">
            <strong>Please check the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form
        method="POST"
        action="{{ route('makeup-artist.register.store') }}"
        enctype="multipart/form-data"
        class="mua-register-form"
    >
        @csrf

        <div class="mua-form-grid">

            {{-- FULL NAME --}}
            <div class="mua-field">
                <label for="full_name">Name</label>

                <input
                    id="full_name"
                    type="text"
                    value="{{ auth()->user()->name }}"
                    disabled
                    class="readonly-field"
                >

                <small>Based on your GlowAI account.</small>
            </div>


            {{-- STUDIO / BRAND --}}
            <div class="mua-field">
                <label for="studio_brand_name">Studio / Brand Name</label>

                <input
                    id="studio_brand_name"
                    type="text"
                    name="studio_brand_name"
                    value="{{ old('studio_brand_name') }}"
                    placeholder="e.g. Fakhira Makeup Studio"
                >
            </div>


            {{-- EMAIL --}}
            <div class="mua-field">
                <label for="email">Email Address</label>

                <input
                    id="email"
                    type="email"
                    value="{{ auth()->user()->email }}"
                    disabled
                    class="readonly-field"
                >

                <small>Based on your GlowAI account.</small>
            </div>


            {{-- PHONE --}}
            <div class="mua-field">
                <label for="phone">Phone Number</label>

                <input
                    id="phone"
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="e.g. 0123456789"
                >

                <small>Use a number that is active on WhatsApp.</small>
            </div>


            {{-- STUDIO ADDRESS --}}
            <div class="mua-field">
                <label for="studio_address">Studio Address</label>

                <textarea
                    id="studio_address"
                    name="studio_address"
                    rows="3"
                    placeholder="Enter your studio or service address"
                >{{ old('studio_address') }}</textarea>
            </div>

            {{-- SERVICE STATES --}}
            <div class="mua-field mua-location-field">
                <label>Service States</label>

                <div class="state-checkbox-grid">
                    @php
                        $states = [
                            'Johor',
                            'Kedah',
                            'Kelantan',
                            'Melaka',
                            'Negeri Sembilan',
                            'Pahang',
                            'Perak',
                            'Perlis',
                            'Pulau Pinang',
                            'Sabah',
                            'Sarawak',
                            'Selangor',
                            'Terengganu',
                            'Kuala Lumpur',
                            'Labuan',
                            'Putrajaya'
                        ];

                        $selectedStates = old('service_states', []);
                    @endphp

                    @foreach ($states as $state)
                        <label class="state-checkbox">
                            <input
                                type="checkbox"
                                name="service_states[]"
                                value="{{ $state }}"
                                {{ in_array($state, $selectedStates) ? 'checked' : '' }}
                            >

                            <span>{{ $state }}</span>
                        </label>
                    @endforeach
                </div>

                <small>
                    Select all states where you provide makeup services.
                </small>
            </div>


            {{-- SERVICE AREAS --}}
            <div class="mua-field mua-location-field">
                <label>Service Areas</label>

                <div id="serviceAreasContainer">

                    @foreach (old('service_areas', ['']) as $index => $area)
                        <div class="service-area-row">
                            <input
                                type="text"
                                name="service_areas[]"
                                value="{{ $area }}"
                                placeholder="e.g. Shah Alam"
                            >

                            @if ($index > 0)
                                <button
                                    type="button"
                                    class="remove-area-btn"
                                    onclick="removeServiceArea(this)"
                                >
                                    ×
                                </button>
                            @endif
                        </div>
                    @endforeach

                </div>

                <button
                    type="button"
                    class="add-area-btn"
                    id="addServiceAreaBtn"
                >
                    + Add Another Area
                </button>

                <small>
                    Add the cities or areas where you provide your services.
                </small>
            </div>
                    
            {{-- TRAVEL --}}
            <div class="mua-field">
                <label for="willing_to_travel">Are you willing to travel?</label>

                <select
                    id="willing_to_travel"
                    name="willing_to_travel"
                >
                    <option value="">Select</option>

                    <option
                        value="1"
                        {{ old('willing_to_travel') === '1' ? 'selected' : '' }}
                    >
                        Yes
                    </option>

                    <option
                        value="0"
                        {{ old('willing_to_travel') === '0' ? 'selected' : '' }}
                    >
                        No
                    </option>
                </select>
            </div>


            {{-- PROFILE PICTURE --}}
            <div class="mua-field">
                <label>Profile Picture</label>

                <label for="profile_picture" class="mua-photo-upload">

                    <div id="muaPhotoPlaceholder">
                        <span class="photo-plus">+</span>
                        <strong>Choose Profile Photo</strong>
                        <small>JPG or PNG</small>
                    </div>

                    <img
                        id="muaPhotoPreview"
                        src=""
                        alt="Profile preview"
                    >

                    <input
                        id="profile_picture"
                        type="file"
                        name="profile_picture"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    >

                </label>
            </div>

            {{-- INSTAGRAM --}}
            <div class="mua-field">
                <label for="instagram">Instagram</label>

                <input
                    id="instagram"
                    type="text"
                    name="instagram"
                    value="{{ old('instagram') }}"
                    placeholder="Instagram username or link"
                >
            </div>


            {{-- TIKTOK --}}
            <div class="mua-field">
                <label for="tiktok">TikTok</label>

                <input
                    id="tiktok"
                    type="text"
                    name="tiktok"
                    value="{{ old('tiktok') }}"
                    placeholder="TikTok username or link"
                >
            </div>


            {{-- EXPERIENCE --}}
            <div class="mua-field">
                <label for="years_experience">Years of Makeup Experience</label>

                <input
                    id="years_experience"
                    type="number"
                    name="years_experience"
                    min="0"
                    value="{{ old('years_experience') }}"
                    placeholder="e.g. 3"
                >
            </div>


            {{-- SPECIALIZATION --}}
            <div class="mua-field">
                <label for="specialized_makeup_look">Specialized Makeup Look</label>

                <select
                    id="specialized_makeup_look"
                    name="specialized_makeup_look"
                >
                    <option value="">Select specialization</option>

                    <option value="Bridal"
                        {{ old('specialized_makeup_look') == 'Bridal' ? 'selected' : '' }}>
                        Bridal
                    </option>

                    <option value="Engagement"
                        {{ old('specialized_makeup_look') == 'Engagement' ? 'selected' : '' }}>
                        Engagement
                    </option>

                    <option value="Event"
                        {{ old('specialized_makeup_look') == 'Event' ? 'selected' : '' }}>
                        Event
                    </option>

                    <option value="Photoshoot"
                        {{ old('specialized_makeup_look') == 'Photoshoot' ? 'selected' : '' }}>
                        Photoshoot
                    </option>

                    <option value="Natural"
                        {{ old('specialized_makeup_look') == 'Natural' ? 'selected' : '' }}>
                        Natural
                    </option>

                    <option value="Glam"
                        {{ old('specialized_makeup_look') == 'Glam' ? 'selected' : '' }}>
                        Glam
                    </option>
                </select>
            </div>

        </div>


        {{-- DESCRIPTION --}}
        <div class="mua-field mua-description-field">
            <label for="description">Tell clients about yourself</label>

            <textarea
                id="description"
                name="description"
                rows="5"
                placeholder="Share your experience, makeup style and what clients can expect from your services..."
            >{{ old('description') }}</textarea>
        </div>


        <div class="mua-form-footer">
            <p>
                Your profile can be reviewed before it is published on GlowAI.
            </p>

            <button type="submit">
                Continue
                <span>→</span>
            </button>
        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================
    // PROFILE PICTURE PREVIEW
    // =========================================
    const input = document.getElementById('profile_picture');
    const preview = document.getElementById('muaPhotoPreview');
    const placeholder = document.getElementById('muaPhotoPlaceholder');

    if (input && preview && placeholder) {
        input.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                preview.src = '';
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };

            reader.readAsDataURL(file);
        });
    }


    // =========================================
    // ADD SERVICE AREA
    // =========================================
    const addAreaButton = document.getElementById('addServiceAreaBtn');
    const serviceAreasContainer = document.getElementById('serviceAreasContainer');

    if (addAreaButton && serviceAreasContainer) {
        addAreaButton.addEventListener('click', function () {

            const row = document.createElement('div');
            row.classList.add('service-area-row');

            row.innerHTML = `
                <input
                    type="text"
                    name="service_areas[]"
                    placeholder="e.g. Bangi"
                >

                <button
                    type="button"
                    class="remove-area-btn"
                >
                    ×
                </button>
            `;

            row.querySelector('.remove-area-btn').addEventListener('click', function () {
                row.remove();
            });

            serviceAreasContainer.appendChild(row);
        });
    }

});
</script>
@endsection
