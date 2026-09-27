@extends('layouts.admin')

@section('title', 'Admin Dashboard | GlowAI')

@section('content')

<div class="admin-page">

    {{-- PAGE HEADING --}}
    <div class="admin-dashboard-heading">
        <h1>Admin Dashboard</h1>
        <p>
            Monitor GlowAI users, makeup artists and booking activity.
        </p>
    </div>


    {{-- STATISTIC CARDS --}}
    <div class="admin-stats-grid">

        {{-- TOTAL USERS --}}
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M16 21V19C16 16.8 14.2 15 12 15H6C3.8 15 2 16.8 2 19V21"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round"/>
                    <circle cx="9" cy="7" r="4"
                            stroke="currentColor" stroke-width="1.8"/>
                    <path d="M22 21V19C22 17.2 20.8 15.7 19.2 15.2"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round"/>
                    <path d="M16.2 3.2C17.8 3.7 19 5.2 19 7C19 8.8 17.8 10.3 16.2 10.8"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round"/>
                </svg>
            </div>

            <div class="admin-stat-content">
                <span>Total Users</span>
                <strong>{{ $totalUsers }}</strong>
                <small>Registered accounts</small>
            </div>
        </div>


        {{-- CUSTOMERS --}}
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="4"
                            stroke="currentColor" stroke-width="1.8"/>
                    <path d="M4 21C4 16.6 7.6 13 12 13C16.4 13 20 16.6 20 21"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round"/>
                </svg>
            </div>

            <div class="admin-stat-content">
                <span>Total Customers</span>
                <strong>{{ $totalCustomers }}</strong>
                <small>Customer accounts</small>
            </div>
        </div>


        {{-- MUA --}}
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 3L14 8L19 10L14 12L12 17L10 12L5 10L10 8L12 3Z"
                          stroke="currentColor" stroke-width="1.7"
                          stroke-linejoin="round"/>
                    <path d="M19 16L20 18L22 19L20 20L19 22L18 20L16 19L18 18L19 16Z"
                          stroke="currentColor" stroke-width="1.5"
                          stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="admin-stat-content">
                <span>Makeup Artists</span>
                <strong>{{ $totalMuas }}</strong>
                <small>Registered MUAs</small>
            </div>
        </div>


        {{-- BOOKINGS --}}
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="5" width="18" height="16" rx="2"
                          stroke="currentColor" stroke-width="1.8"/>
                    <path d="M8 3V7M16 3V7M3 10H21"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round"/>
                    <path d="M8 14H10M14 14H16M8 17H10"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round"/>
                </svg>
            </div>

            <div class="admin-stat-content">
                <span>Total Bookings</span>
                <strong>{{ $totalBookings }}</strong>
                <small>All booking records</small>
            </div>
        </div>

    </div>


    {{-- DASHBOARD CHART AREA --}}
    <div class="admin-dashboard-grid">

        <section class="admin-dashboard-card">

            <div class="admin-card-header">
                <div>
                    <h2>Bookings by Month</h2>
                    <p>Monthly booking activity</p>
                </div>

            </div>

            <div class="admin-chart-container">
                <canvas id="monthlyBookingsChart"></canvas>
            </div>

        </section>


        <section class="admin-dashboard-card">

            <div class="admin-card-header">
                <div>
                    <h2>Booking Status</h2>
                    <p>Current booking distribution</p>
                </div>

            </div>

            <div class="admin-chart-container admin-status-chart">
                <canvas id="bookingStatusChart"></canvas>
            </div>

        </section>

    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================
    // BOOKINGS BY MONTH
    // =========================================

    const monthlyContext =
        document.getElementById('monthlyBookingsChart');

    new Chart(monthlyContext, {
        type: 'bar',

        data: {
            labels: @json($bookingMonths),

            datasets: [{
                label: 'Bookings',

                data: @json($bookingMonthTotals),

                backgroundColor: '#A83279',
                borderColor: '#7A1F59',
                borderWidth: 1,

                borderRadius: 6,
                borderSkipped: false,

                barThickness: 24
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                }
            },

            scales: {
                y: {
                    beginAtZero: true,

                    ticks: {
                        precision: 0,
                        color: '#8b767f'
                    },

                    grid: {
                        color: '#eee3e6'
                    }
                },

                x: {
                    ticks: {
                        color: '#8b767f'
                    },

                    grid: {
                        display: false
                    }
                }
            }
        }
    });


    // =========================================
    // BOOKING STATUS
    // =========================================

    const statusContext =
        document.getElementById('bookingStatusChart');

    new Chart(statusContext, {
        type: 'doughnut',

        data: {
            labels: @json($bookingStatusLabels),

            datasets: [{
                data: @json($bookingStatusTotals),

                backgroundColor: [
                    '#F59E0B', // Pending - Orange
                    '#16A34A', // Accepted - Green
                    '#DC2626'  // Rejected - Red
                ],

                borderColor: '#fffaf8',
                borderWidth: 4,

                hoverOffset: 5
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            cutout: '68%',

            plugins: {
                legend: {
                    position: 'bottom',

                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 18,
                        color: '#68545d',

                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

});
</script>
@endsection