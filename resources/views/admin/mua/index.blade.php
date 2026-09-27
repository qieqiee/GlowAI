@extends('layouts.admin')

@section('title', 'Manage Makeup Artists | GlowAI')

@section('content')

<div class="admin-page">

    {{-- PAGE HEADING --}}
    <div class="admin-section-heading">
        <span>Management</span>

        <h1>Manage Makeup Artists</h1>

        <p>
            Review registered makeup artists and manage their profiles.
        </p>
    </div>


    {{-- SUMMARY --}}
    <div class="admin-mua-summary">

        <div class="admin-mini-stat">
            <span>Total Makeup Artists</span>
            <strong>{{ $makeupArtists->count() }}</strong>
        </div>

        <div class="admin-mini-stat">
            <span>Published</span>
            <strong>
                {{ $makeupArtists->where('is_published', true)->count() }}
            </strong>
        </div>

        <div class="admin-mini-stat">
            <span>Unpublished</span>
            <strong>
                {{ $makeupArtists->where('is_published', false)->count() }}
            </strong>
        </div>

    </div>


    {{-- TABLE CARD --}}
    <section class="admin-table-card">

        <div class="admin-table-header">

            <div>
                <h2>Makeup Artist List</h2>

                <p>
                    All makeup artists registered on GlowAI.
                </p>
            </div>

            <div class="admin-table-search">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />

                    <path
                        d="M20 20L16.5 16.5"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>

                <input
                    type="text"
                    id="muaSearch"
                    placeholder="Search makeup artist..."
                >
            </div>

        </div>


        @if($makeupArtists->isEmpty())

            <div class="admin-empty-state">
                <h3>No makeup artists found</h3>

                <p>
                    There are currently no registered makeup artists.
                </p>
            </div>

        @else

            <div class="admin-table-wrapper">

                <table class="admin-data-table" id="muaTable">

                    <thead>
                        <tr>
                            <th>Makeup Artist</th>
                            <th>Studio / Brand</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($makeupArtists as $mua)

                            <tr>

                                {{-- MUA --}}
                                <td>

                                    <div class="admin-mua-person">

                                        <div class="admin-mua-avatar">

                                            @if($mua->profile_picture)

                                                <img
                                                    src="{{ asset('storage/' . $mua->profile_picture) }}"
                                                    alt="{{ $mua->user->name }}"
                                                >

                                            @else

                                                <span>
                                                    {{ strtoupper(substr($mua->user->name, 0, 1)) }}
                                                </span>

                                            @endif

                                        </div>


                                        <div>
                                            <strong>
                                                {{ $mua->user->name }}
                                            </strong>

                                            <span>
                                                {{ $mua->user->email }}
                                            </span>
                                        </div>

                                    </div>

                                </td>


                                {{-- BRAND --}}
                                <td>
                                    {{ $mua->studio_brand_name ?: '-' }}
                                </td>


                                {{-- EXPERIENCE --}}
                                <td>
                                    {{ $mua->years_experience }}
                                    {{ $mua->years_experience == 1 ? 'year' : 'years' }}
                                </td>


                                {{-- STATUS --}}
                                <td>

                                    @if($mua->is_published)

                                        <span class="admin-status-badge published">
                                            Published
                                        </span>

                                    @else

                                        <span class="admin-status-badge unpublished">
                                            Unpublished
                                        </span>

                                    @endif

                                </td>


                                {{-- ACTION --}}
                                <td>

                                    <a
                                        href="{{ route('admin.muas.show', $mua->id) }}"
                                        class="admin-view-button"
                                    >
                                        View Details
                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </section>

</div>


{{-- SEARCH --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput =
        document.getElementById('muaSearch');

    const rows =
        document.querySelectorAll('#muaTable tbody tr');

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', function () {

        const keyword =
            this.value.toLowerCase().trim();

        rows.forEach(function (row) {

            const text =
                row.textContent.toLowerCase();

            row.style.display =
                text.includes(keyword) ? '' : 'none';

        });

    });

});
</script>

@endsection