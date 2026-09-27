@extends('layouts.admin')

@section('title', 'User Details | GlowAI')

@section('content')

<div class="admin-page">

    {{-- BACK --}}
    <a
        href="{{ route('admin.users.index') }}"
        class="admin-back-link"
    >
        ← Back to Manage Users
    </a>


    {{-- HEADING --}}
    <div class="admin-section-heading admin-detail-heading">

        <span>User Management</span>

        <h1>User Details</h1>

        <p>
            View account information and user details.
        </p>

    </div>


    {{-- USER OVERVIEW --}}
    <section class="admin-detail-card">

        <div class="admin-user-detail-header">

            <div class="admin-detail-avatar">

                @if(
                    $user->role === 'makeup_artist' &&
                    $user->makeupArtist &&
                    $user->makeupArtist->profile_picture
                )

                    <img
                        src="{{ asset('storage/' . $user->makeupArtist->profile_picture) }}"
                        alt="{{ $user->name }}"
                    >

                @else

                    <span>
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>

                @endif

            </div>


            <div class="admin-user-detail-info">

                <div class="admin-profile-title-row">

                    <div>

                        <h2>
                            {{ $user->name }}
                        </h2>

                        <p>
                            {{ $user->email }}
                        </p>

                    </div>


                    @if($user->role === 'admin')

                        <span class="admin-role-badge admin">
                            Admin
                        </span>

                    @elseif($user->role === 'makeup_artist')

                        <span class="admin-role-badge mua">
                            Makeup Artist
                        </span>

                    @else

                        <span class="admin-role-badge customer">
                            Customer
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </section>


    {{-- ACCOUNT INFORMATION --}}
    <section class="admin-detail-card">

        <div class="admin-detail-card-header">

            <div>
                <h2>Account Information</h2>

                <p>
                    Registered GlowAI account details.
                </p>
            </div>

        </div>


        <div class="admin-user-information-grid">

            <div class="admin-user-information-item">
                <span>User ID</span>

                <strong>
                    #{{ $user->id }}
                </strong>
            </div>


            <div class="admin-user-information-item">
                <span>Name</span>

                <strong>
                    {{ $user->name }}
                </strong>
            </div>


            <div class="admin-user-information-item">
                <span>Email</span>

                <strong>
                    {{ $user->email }}
                </strong>
            </div>


            <div class="admin-user-information-item">
                <span>Role</span>

                <strong>
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                </strong>
            </div>


            <div class="admin-user-information-item">
                <span>Registered Date</span>

                <strong>
                    {{ $user->created_at->format('d/m/Y') }}
                </strong>
            </div>


            <div class="admin-user-information-item">
                <span>Registered Time</span>

                <strong>
                    {{ $user->created_at->format('g:i A') }}
                </strong>
            </div>

        </div>

    </section>


    {{-- MUA PROFILE --}}
    @if($user->makeupArtist)

        <section class="admin-detail-card admin-user-mua-card">

            <div class="admin-detail-card-header">

                <div>
                    <h2>Makeup Artist Profile</h2>

                    <p>
                        Professional profile associated with this account.
                    </p>
                </div>


                @if($user->makeupArtist->is_published)

                    <span class="admin-status-badge published">
                        Published
                    </span>

                @else

                    <span class="admin-status-badge unpublished">
                        Unpublished
                    </span>

                @endif

            </div>


            <div class="admin-user-mua-content">

                <div class="admin-user-information-grid">

                    <div class="admin-user-information-item">

                        <span>Studio / Brand</span>

                        <strong>
                            {{ $user->makeupArtist->studio_brand_name ?: '-' }}
                        </strong>

                    </div>


                    <div class="admin-user-information-item">

                        <span>Phone</span>

                        <strong>
                            {{ $user->makeupArtist->phone ?: '-' }}
                        </strong>

                    </div>


                    <div class="admin-user-information-item">

                        <span>Profile Status</span>

                        <strong>
                            {{ $user->makeupArtist->is_published
                                ? 'Published'
                                : 'Unpublished' }}
                        </strong>

                    </div>

                </div>


                <div class="admin-user-mua-action">

                    <a
                        href="{{ route('admin.muas.show', $user->makeupArtist->id) }}"
                        class="admin-primary-link"
                    >
                        View MUA Profile
                    </a>

                </div>

            </div>

        </section>

    @endif

</div>

@endsection