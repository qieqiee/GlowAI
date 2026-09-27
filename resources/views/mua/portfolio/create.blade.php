@extends('layouts.mua')

@section('title', 'Add Portfolio | GlowAI')

@section('page-assets')
    @vite([
        'resources/css/app.css',
        'resources/css/mua-profile.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')

<div class="portfolio-form-page">

    <a href="{{ route('mua.profile') }}" class="portfolio-form-back">
        ← Back to Profile
    </a>

    <div class="portfolio-form-heading">
        <span>Manage Portfolio</span>
        <h1>Add Portfolio</h1>
        <p>Add a new makeup look to showcase your work.</p>
    </div>


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


    <div class="portfolio-form-card">

        <div class="portfolio-form-card-header">
            <h2>Portfolio Information</h2>
            <p>
                Add the makeup look details, image and products used.
            </p>
        </div>


        <form
            method="POST"
            action="{{ route('mua.portfolio.store') }}"
            enctype="multipart/form-data"
            class="portfolio-edit-form"
        >
            @csrf


            {{-- LOOK TITLE + CATEGORY --}}
            <div class="portfolio-form-grid">

                <div class="portfolio-form-field">
                    <label for="look_title">
                        Look Title
                    </label>

                    <input
                        type="text"
                        id="look_title"
                        name="look_title"
                        value="{{ old('look_title') }}"
                        placeholder="e.g. Soft Glam Bridal Look"
                        required
                    >
                </div>


                <div class="portfolio-form-field">
                    <label for="category">
                        Category
                    </label>

                    <select
                        id="category"
                        name="category"
                        required
                    >
                        <option value="">
                            Select Category
                        </option>

                        @foreach([
                            'Bridal',
                            'Engagement',
                            'Event',
                            'Photoshoot',
                            'Natural',
                            'Glam'
                        ] as $category)

                            <option
                                value="{{ $category }}"
                                {{ old('category') == $category ? 'selected' : '' }}
                            >
                                {{ $category }}
                            </option>

                        @endforeach
                    </select>
                </div>

            </div>


            {{-- PICTURE --}}
            <div class="portfolio-picture-section">

                <label>Portfolio Picture</label>

                <div class="portfolio-picture-box">

                    <div class="portfolio-picture-preview">

                        <img
                            id="portfolioPicturePreview"
                            src=""
                            alt="Portfolio Picture Preview"
                            style="display: none;"
                        >

                        <div
                            id="portfolioPicturePlaceholder"
                            class="portfolio-picture-placeholder"
                        >
                            No Image Selected
                        </div>

                    </div>


                    <div class="portfolio-picture-controls">

                        <strong>Upload Portfolio Picture</strong>

                        <p>
                            Choose a clear photo that best represents
                            this makeup look.
                        </p>

                        <label
                            for="picture"
                            class="portfolio-picture-button"
                        >
                            Choose Picture
                        </label>

                        <input
                            type="file"
                            id="picture"
                            name="picture"
                            accept="image/*"
                            required
                        >

                    </div>

                </div>

            </div>


            {{-- DESCRIPTION --}}
            <div class="portfolio-form-field portfolio-full-field">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Describe this makeup look..."
                >{{ old('description') }}</textarea>

            </div>


            {{-- PRODUCTS USED --}}
            <div class="portfolio-form-field portfolio-full-field">

                <label for="products_used">
                    Products Used
                </label>

                <textarea
                    id="products_used"
                    name="products_used"
                    rows="4"
                    placeholder="List the main products used for this look..."
                >{{ old('products_used') }}</textarea>

                <small>
                    You can list makeup products, lashes, contact lenses
                    or other products used for this look.
                </small>

            </div>


            {{-- ACTIONS --}}
            <div class="portfolio-form-actions">

                <a
                    href="{{ route('mua.profile') }}"
                    class="portfolio-form-cancel"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="portfolio-form-save"
                >
                    Save Portfolio
                </button>

            </div>

        </form>

    </div>

</div>


{{-- PICTURE PREVIEW --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const pictureInput =
        document.getElementById('picture');

    const picturePreview =
        document.getElementById('portfolioPicturePreview');

    const picturePlaceholder =
        document.getElementById('portfolioPicturePlaceholder');

    pictureInput.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) {
            picturePreview.src = '';
            picturePreview.style.display = 'none';
            picturePlaceholder.style.display = 'flex';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {

            picturePreview.src = event.target.result;
            picturePreview.style.display = 'block';

            picturePlaceholder.style.display = 'none';
        };

        reader.readAsDataURL(file);
    });

});
</script>

@endsection