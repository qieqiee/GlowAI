@extends('layouts.mua')

@section('title', 'MUA Profile | GlowAI')

@section('page-assets')
    @vite([
        'resources/css/app.css',
        'resources/css/mua-profile.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')

<div class="mua-profile-page">

    <div class="profile-page-heading">
        <span>Manage Profile</span>
        <h1>Makeup Artist Profile</h1>
        <p>
            Manage your professional information and showcase your makeup portfolio.
        </p>
    </div>


    @if(session('success'))
        <div class="profile-success-message">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="profile-error-message">
            <strong>Please check the following:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="profile-main-grid">

        {{-- ==============================
             PERSONAL INFORMATION
        =============================== --}}
        <section class="profile-card">

            <div class="profile-card-header">
                <div>
                    <h2>Personal Information</h2>
                    <p>Update your professional profile and contact details.</p>
                </div>
            </div>


            <form
                method="POST"
                action="{{ route('mua.profile.update') }}"
                enctype="multipart/form-data"
                class="profile-form"
            >
                @csrf
                @method('PUT')


                <div class="profile-form-grid">

                    <div class="profile-field">
                        <label>Name</label>

                        <input
                            type="text"
                            value="{{ auth()->user()->name }}"
                            disabled
                        >

                        <small>
                            Your account name cannot be changed here.
                        </small>
                    </div>


                    <div class="profile-field">
                        <label>Email</label>

                        <input
                            type="email"
                            value="{{ auth()->user()->email }}"
                            disabled
                        >
                    </div>


                    <div class="profile-field">
                        <label for="studio_brand_name">
                            Studio / Brand Name
                        </label>

                        <input
                            type="text"
                            id="studio_brand_name"
                            name="studio_brand_name"
                            value="{{ old('studio_brand_name', $makeupArtist->studio_brand_name) }}"
                        >
                    </div>


                    <div class="profile-field">
                        <label for="phone">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $makeupArtist->phone) }}"
                        >
                    </div>

                </div>


                <div class="profile-field">
                    <label for="studio_address">
                        Studio / Home Address
                    </label>

                    <textarea
                        id="studio_address"
                        name="studio_address"
                        rows="3"
                    >{{ old('studio_address', $makeupArtist->studio_address) }}</textarea>
                </div>

                                {{-- SERVICE LOCATIONS --}}
                    <div class="profile-section-divider">
                        <span>Service Locations</span>
                    </div>

                    <div class="profile-field">
                        <label>Service States</label>

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

                            $selectedStates = old(
                                'service_states',
                                $makeupArtist->service_states ?? []
                            );
                        @endphp

                        <div class="profile-state-grid">
                            @foreach ($states as $state)
                                <label class="profile-state-option">
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


                    <div class="profile-field">
                        <label>Service Areas</label>

                        <div id="profileServiceAreas">

                            @php
                                $serviceAreas = old(
                                    'service_areas',
                                    $makeupArtist->service_areas ?? ['']
                                );

                                if (empty($serviceAreas)) {
                                    $serviceAreas = [''];
                                }
                            @endphp

                            @foreach ($serviceAreas as $index => $area)
                            
                            <div class="profile-service-area-row">

                                <input
                                    type="text"
                                    name="service_areas[]"
                                    value="{{ $area }}"
                                    placeholder="e.g. Shah Alam"
                                >

                                @if ($index > 0)
                                    <button
                                        type="button"
                                        class="profile-remove-area"
                                    >
                                        ×
                                    </button>
                                @endif

                            </div>
                        @endforeach

                    </div>

                    <button
                        type="button"
                        id="profileAddArea"
                        class="profile-add-area"
                    >
                        + Add Another Area
                    </button>

                    <small>
                        Add all cities or areas where you provide your services.
                    </small>
                </div>


                <div class="profile-form-grid">

                    <div class="profile-field">
                        <label for="willing_to_travel">
                            Willing to Travel
                        </label>

                        <select
                            id="willing_to_travel"
                            name="willing_to_travel"
                        >
                            <option
                                value="1"
                                {{ old('willing_to_travel', $makeupArtist->willing_to_travel) == 1 ? 'selected' : '' }}
                            >
                                Yes
                            </option>

                            <option
                                value="0"
                                {{ old('willing_to_travel', $makeupArtist->willing_to_travel) == 0 ? 'selected' : '' }}
                            >
                                No
                            </option>
                        </select>
                    </div>

                </div>


                {{-- PROFILE PICTURE --}}
                <div class="profile-picture-section">

                    <label>Profile Picture</label>

                    <div class="profile-picture-row">

                    <div class="current-profile-picture">

                        <img
                            id="profilePicturePreview"
                            src="{{ $makeupArtist->profile_picture
                                ? asset('storage/' . $makeupArtist->profile_picture)
                                : '' }}"
                            alt="Profile Picture Preview"
                            style="{{ $makeupArtist->profile_picture ? '' : 'display: none;' }}"
                        >

                        <div
                            id="profilePicturePlaceholder"
                            class="profile-picture-placeholder"
                            style="{{ $makeupArtist->profile_picture ? 'display: none;' : '' }}"
                        >
                            No Photo
                        </div>

                        </div>


                        <div class="profile-picture-upload">

                            <label
                                for="profile_picture"
                                class="profile-upload-button"
                            >
                                Choose New Photo
                            </label>

                            <input
                                type="file"
                                id="profile_picture"
                                name="profile_picture"
                                accept="image/*"
                            >

                            <small>
                                Upload a professional profile photo.
                            </small>

                        </div>

                    </div>

                </div>


                {{-- SOCIAL --}}
                <div class="profile-section-divider">
                    <span>Social Media</span>
                </div>


                <div class="profile-form-grid">

                    <div class="profile-field">
                        <label for="instagram">
                            Instagram
                        </label>

                        <input
                            type="text"
                            id="instagram"
                            name="instagram"
                            value="{{ old('instagram', $makeupArtist->instagram) }}"
                            placeholder="@username"
                        >
                    </div>


                    <div class="profile-field">
                        <label for="tiktok">
                            TikTok
                        </label>

                        <input
                            type="text"
                            id="tiktok"
                            name="tiktok"
                            value="{{ old('tiktok', $makeupArtist->tiktok) }}"
                            placeholder="@username"
                        >
                    </div>

                </div>


                {{-- PROFESSIONAL INFO --}}
                <div class="profile-section-divider">
                    <span>Professional Information</span>
                </div>


                <div class="profile-form-grid">

                    <div class="profile-field">
                        <label for="years_experience">
                            Years of Makeup Experience
                        </label>

                        <input
                            type="number"
                            id="years_experience"
                            name="years_experience"
                            min="0"
                            value="{{ old('years_experience', $makeupArtist->years_experience) }}"
                        >
                    </div>


                    <div class="profile-field">
                        <label for="specialized_makeup_look">
                            Specialized Makeup Look
                        </label>

                        <select
                            id="specialized_makeup_look"
                            name="specialized_makeup_look"
                        >
                            @foreach([
                                'Bridal',
                                'Engagement',
                                'Event',
                                'Photoshoot',
                                'Natural',
                                'Glam'
                            ] as $look)

                                <option
                                    value="{{ $look }}"
                                    {{ old('specialized_makeup_look', $makeupArtist->specialized_makeup_look) == $look ? 'selected' : '' }}
                                >
                                    {{ $look }}
                                </option>

                            @endforeach
                        </select>
                    </div>

                </div>


                <div class="profile-field">
                    <label for="description">
                        Makeup Artist Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Tell clients about your makeup style and experience..."
                    >{{ old('description', $makeupArtist->description) }}</textarea>
                </div>


                <div class="profile-save-area">
                    <button
                        type="submit"
                        class="profile-save-button"
                    >
                        Save Changes
                    </button>
                </div>

            </form>

        </section>



        {{-- ==============================
             PORTFOLIO
        =============================== --}}
        <section class="portfolio-card">

            <div class="portfolio-card-header">

                <div>
                    <h2>Portfolio</h2>
                    <p>
                        Showcase your makeup looks to potential clients.
                    </p>
                </div>

                <a
                    href="{{ route('mua.portfolio.create') }}"
                    class="portfolio-add-button"
                >
                    + Add Portfolio
                </a>

            </div>


            @if($makeupArtist->portfolios->count() > 0)

                <div class="portfolio-grid">

                    @foreach($makeupArtist->portfolios as $portfolio)

                        <article class="portfolio-item">

                            <div class="portfolio-image">

                                @if($portfolio->picture)

                                    <img
                                        src="{{ asset('storage/' . $portfolio->picture) }}"
                                        alt="{{ $portfolio->look_title }}"
                                    >

                                @else

                                    <div class="portfolio-image-empty">
                                        No Image
                                    </div>

                                @endif

                            </div>


                            <div class="portfolio-item-body">

                                <span class="portfolio-category">
                                    {{ $portfolio->category }}
                                </span>

                                <h3>
                                    {{ $portfolio->look_title }}
                                </h3>


                                @if($portfolio->description)
                                    <p class="portfolio-description">
                                        {{ $portfolio->description }}
                                    </p>
                                @endif


                                <div class="portfolio-products">
                                    <span>Products Used</span>

                                    <p>
                                        {{ $portfolio->products_used ?? 'Not specified' }}
                                    </p>
                                </div>


                                <div class="portfolio-actions">

                                    <a
                                        href="{{ route('mua.portfolio.edit', $portfolio->id) }}"
                                        class="portfolio-edit-button"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        method="POST"
                                        action="{{ route('mua.portfolio.destroy', $portfolio->id) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="portfolio-delete-button"
                                            onclick="return confirm('Are you sure you want to delete this portfolio?')"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="portfolio-empty">

                    <div class="portfolio-empty-icon">
                        ♡
                    </div>

                    <h3>No portfolio yet</h3>

                    <p>
                        Add your first makeup look to showcase your work.
                    </p>

                    <a
                        href="{{ route('mua.portfolio.create') }}"
                        class="portfolio-add-button"
                    >
                        + Add Portfolio
                    </a>

                </div>

            @endif

        </section>

    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================
    // PROFILE PICTURE PREVIEW
    // =========================================
    const fileInput = document.getElementById('profile_picture');
    const preview = document.getElementById('profilePicturePreview');
    const placeholder = document.getElementById('profilePicturePlaceholder');

    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = 'block';

                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };

            reader.readAsDataURL(file);
        });
    }


    // =========================================
    // ADD SERVICE AREA
    // =========================================
    const addAreaButton = document.getElementById('profileAddArea');
    const serviceAreasContainer = document.getElementById('profileServiceAreas');

    if (addAreaButton && serviceAreasContainer) {

        addAreaButton.addEventListener('click', function () {

            const row = document.createElement('div');
            row.classList.add('profile-service-area-row');

            row.innerHTML = `
                <input
                    type="text"
                    name="service_areas[]"
                    placeholder="e.g. Bangi"
                >

                <button
                    type="button"
                    class="profile-remove-area"
                >
                    ×
                </button>
            `;

            row.querySelector('.profile-remove-area')
                .addEventListener('click', function () {
                    row.remove();
                });

            serviceAreasContainer.appendChild(row);
        });


        // Existing remove buttons
        document.querySelectorAll('.profile-remove-area')
            .forEach(function (button) {

                button.addEventListener('click', function () {
                    this.closest('.profile-service-area-row').remove();
                });

            });
    }

});
</script>
@endsection