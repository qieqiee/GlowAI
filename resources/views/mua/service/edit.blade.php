@extends('layouts.mua')

@section('title', 'Edit Service | GlowAI')

@section('page-assets')
    @vite([
        'resources/css/app.css',
        'resources/css/mua-services.css',
        'resources/js/app.js'
    ])
@endsection

@section('content')

<div class="service-edit-page">

    <a href="{{ route('mua.services') }}" class="service-back-link">
        ← Back to Services
    </a>

    <div class="service-edit-heading">
        <span class="service-page-label">Manage Services</span>
        <h1>Edit Service</h1>
        <p>Update your makeup service details and pricing.</p>
    </div>

    @if ($errors->any())
        <div class="service-form-errors">
            <strong>Please check the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="service-edit-card">

        <div class="service-card-heading">
            <div>
                <h2>Service Information</h2>
                <p>Keep your service information clear and up to date for clients.</p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('mua.services.update', $service->id) }}"
            class="service-edit-form"
        >
            @csrf
            @method('PUT')

            {{-- SERVICE NAME + CATEGORY --}}
            <div class="service-form-grid">

                <div class="service-form-group">
                    <label for="service_name">Service Name</label>

                    <input
                        type="text"
                        id="service_name"
                        name="service_name"
                        value="{{ old('service_name', $service->service_name) }}"
                        placeholder="e.g. Bridal Makeup"
                        required
                    >
                </div>

                <div class="service-form-group">
                    <label for="category">Category</label>

                    <select
                        id="category"
                        name="category"
                        required
                    >
                        <option value="">Select Category</option>

                        <option
                            value="Bridal"
                            {{ old('category', $service->category) == 'Bridal' ? 'selected' : '' }}
                        >
                            Bridal
                        </option>

                        <option
                            value="Engagement"
                            {{ old('category', $service->category) == 'Engagement' ? 'selected' : '' }}
                        >
                            Engagement
                        </option>

                        <option
                            value="Event"
                            {{ old('category', $service->category) == 'Event' ? 'selected' : '' }}
                        >
                            Event
                        </option>

                        <option
                            value="Photoshoot"
                            {{ old('category', $service->category) == 'Photoshoot' ? 'selected' : '' }}
                        >
                            Photoshoot
                        </option>
                    </select>
                </div>

            </div>


            {{-- DESCRIPTION --}}
            <div class="service-form-group service-full-width">
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Describe this makeup service"
                >{{ old('description', $service->description) }}</textarea>
            </div>


            {{-- PRICE / DEPOSIT / DURATION --}}
            <div class="service-form-grid service-form-grid-three">

                <div class="service-form-group">
                    <label for="price">Price (RM)</label>

                    <div class="service-input-prefix">
                        <span>RM</span>

                        <input
                            type="number"
                            id="price"
                            name="price"
                            step="0.01"
                            min="0"
                            value="{{ old('price', $service->price) }}"
                            required
                        >
                    </div>
                </div>

                <div class="service-form-group">
                    <label for="deposit_amount">Deposit Amount (RM)</label>

                    <div class="service-input-prefix">
                        <span>RM</span>

                        <input
                            type="number"
                            id="deposit_amount"
                            name="deposit_amount"
                            step="0.01"
                            min="0"
                            value="{{ old('deposit_amount', $service->deposit_amount) }}"
                            required
                        >
                    </div>
                </div>

                <div class="service-form-group">
                    <label for="duration">Duration</label>

                    <div class="service-input-suffix">
                        <input
                            type="number"
                            id="duration"
                            name="duration"
                            min="1"
                            value="{{ old('duration', $service->duration) }}"
                            required
                        >

                        <span>minutes</span>
                    </div>
                </div>

            </div>


            {{-- SERVICE INCLUDED --}}
            <div class="service-form-group service-full-width">
                <label for="service_included">Service Included</label>

                <textarea
                    id="service_included"
                    name="service_included"
                    rows="4"
                    placeholder="e.g. False lashes, touch-up, basic hairstyling"
                >{{ old('service_included', $service->service_included) }}</textarea>

                <small>
                    List what is included so clients know what to expect from this service.
                </small>
            </div>


            {{-- ACTIONS --}}
            <div class="service-form-actions">

                <a
                    href="{{ route('mua.services') }}"
                    class="service-cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="service-update-btn"
                >
                    Update Service
                </button>

            </div>

        </form>

    </div>

</div>

@endsection