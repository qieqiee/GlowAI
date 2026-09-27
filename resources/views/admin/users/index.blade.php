@extends('layouts.admin')

@section('title', 'Manage Users | GlowAI')

@section('content')

<div class="admin-page">

    {{-- PAGE HEADING --}}
    <div class="admin-section-heading">
        <span>Management</span>

        <h1>Manage Users</h1>

        <p>
            View and manage all registered GlowAI user accounts.
        </p>
    </div>


    {{-- SUMMARY --}}
    <div class="admin-mua-summary">

        <div class="admin-mini-stat">
            <span>Total Users</span>
            <strong>{{ $users->count() }}</strong>
        </div>

        <div class="admin-mini-stat">
            <span>Customers</span>
            <strong>
                {{ $users->where('role', 'customer')->count() }}
            </strong>
        </div>

        <div class="admin-mini-stat">
            <span>Makeup Artists</span>
            <strong>
                {{ $users->where('role', 'makeup_artist')->count() }}
            </strong>
        </div>

    </div>


    {{-- TABLE --}}
    <section class="admin-table-card">

        <div class="admin-table-header">

            <div>
                <h2>User List</h2>

                <p>
                    All registered accounts on GlowAI.
                </p>
            </div>


            {{-- SEARCH --}}
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
                    id="userSearch"
                    placeholder="Search users..."
                >

            </div>

        </div>


        @if($users->isEmpty())

            <div class="admin-empty-state">

                <h3>No users found</h3>

                <p>
                    There are currently no registered users.
                </p>

            </div>

        @else

            <div class="admin-table-wrapper">

                <table class="admin-data-table" id="userTable">

                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>


                    <tbody>

                        @foreach($users as $user)

                            <tr>

                                {{-- USER --}}
                                <td>

                                    <div class="admin-mua-person">

                                        <div class="admin-user-avatar">

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


                                        <div>
                                            <strong>
                                                {{ $user->name }}
                                            </strong>

                                            <span>
                                                ID #{{ $user->id }}
                                            </span>
                                        </div>

                                    </div>

                                </td>


                                {{-- EMAIL --}}
                                <td>
                                    {{ $user->email }}
                                </td>


                                {{-- ROLE --}}
                                <td>

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

                                </td>


                                {{-- REGISTERED --}}
                                <td>
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>


                                {{-- ACTION --}}
                                <td>

                                    <a
                                        href="{{ route('admin.users.show', $user->id) }}"
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
        document.getElementById('userSearch');

    const rows =
        document.querySelectorAll('#userTable tbody tr');

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