@extends('layouts.mua')

@section('title', 'Edit Portfolio | GlowAI')

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
        <h1>Edit Portfolio</h1>
        <p>Update the details of your makeup look.</p>
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
                Update the makeup look, image and products used.
            </p>
        </div>


        <form
            method="POST"
            action="{{ route('mua.portfolio.update', $portfolio->id) }}"
            enctype="multipart/form-data"
            class="portfolio-edit-form"
        >
            @csrf
            @method('PUT')


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
                        value="{{ old('look_title', $portfolio->look_title) }}"
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
                                {{ old('category', $portfolio->category) == $category ? 'selected' : '' }}
                            >
                                {{ $category }}
                            </option>

                        @endforeach
                    </select>
                </div>

            </div>


            {{-- IMAGE --}}
            <div class="portfolio-picture-section">

                <label>Portfolio Picture</label>

                <div class="portfolio-picture-box">

                    <div class="portfolio-picture-preview">

                        <img
                            id="portfolioPicturePreview"
                            src="{{ $portfolio->picture
                                ? asset('storage/' . $portfolio->picture)
                                : '' }}"
                            alt="{{ $portfolio->look_title }}"
                            style="{{ $portfolio->picture ? '' : 'display:none;' }}"
                        >

                        <div
                            id="portfolioPicturePlaceholder"
                            class="portfolio-picture-placeholder"
                            style="{{ $portfolio->picture ? 'display:none;' : '' }}"
                        >
                            No Image
                        </div>

                    </div>


                    <div class="portfolio-picture-controls">

                        <strong>Current Portfolio Picture</strong>

                        <p>
                            Choose a new image only if you want to replace
                            the current portfolio picture.
                        </p>

                        <label
                            for="picture"
                            class="portfolio-picture-button"
                        >
                            Choose New Picture
                        </label>

                        <input
                            type="file"
                            id="picture"
                            name="picture"
                            accept="image/*"
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
                >{{ old('description', $portfolio->description) }}</textarea>

            </div>


            {{-- PRODUCTS --}}
            <div class="portfolio-form-field portfolio-full-field">

                <label for="products_used">
                    Products Used
                </label>

                <textarea
                    id="products_used"
                    name="products_used"
                    rows="4"
                    placeholder="List the main products used for this look..."
                >{{ old('products_used', $portfolio->products_used) }}</textarea>

                <small>
                    You can list makeup products, lashes, contact lenses or
                    other products used for this look.
                </small>

            </div>


            {{-- BUTTONS --}}
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
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


{{-- IMAGE PREVIEW --}}
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
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {

            picturePreview.src = event.target.result;
            picturePreview.style.display = 'block';

            if (picturePlaceholder) {
                picturePlaceholder.style.display = 'none';
            }
        };

        reader.readAsDataURL(file);
    });

});
</script>

@endsection