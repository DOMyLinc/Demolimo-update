@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4">Analytics Dashboard</h1>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Users</h6>
                                <h2 class="mb-0">{{ number_format($stats['total_users']) }}</h2>
                                <small class="text-white-50">+{{ $stats['new_users_today'] }} today</small>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Tracks</h6>
                                <h2 class="mb-0">{{ number_format($stats['total_tracks']) }}</h2>
                                <small class="text-white-50">+{{ $stats['tracks_uploaded_today'] }} today</small>
                            </div>
                            <i class="fas fa-music fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Plays</h6>
                                <h2 class="mb-0">{{ number_format($stats['total_plays']) }}</h2>
                                <small class="text-white-50">All time</small>
                            </div>
                            <i class="fas fa-play-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Active Subscriptions</h6>
                                <h2 class="mb-0">{{ number_format($stats['active_subscriptions']) }}</h2>
                                <small class="text-white-50">${{ number_format($stats['monthly_revenue'], 2) }}/mo</small>
                            </div>
                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">User Growth (Last 30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userGrowthChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Track Uploads (Last 30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trackUploadsChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Content Row -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top Tracks</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Track</th>
                                        <th>Artist</th>
                                        <th class="text-end">Plays</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topTracks as $index => $track)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $track->title }}</td>
                                            <td>{{ $track->user->name }}</td>
                                            <td class="text-end">{{ number_format($track->plays) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top Artists</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Artist</th>
                                        <th class="text-end">Tracks</th>
                                        <th class="text-end">Total Plays</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topArtists as $index => $artist)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $artist->name }}</td>
                                            <td class="text-end">{{ $artist->tracks_count }}</td>
                                            <td class="text-end">{{ number_format($artist->tracks_sum_plays) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            // User Growth Chart
            const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
            new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($userGrowth->pluck('date')) !!},
                    datasets: [{
                        label: 'New Users',
                        data: {!! json_encode($userGrowth->pluck('count')) !!},
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Track Uploads Chart
            const trackUploadsCtx = document.getElementById('trackUploadsChart').getContext('2d');
            new Chart(trackUploadsCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($trackUploads->pluck('date')) !!},
                    datasets: [{
                        label: 'Tracks Uploaded',
                        data: {!! json_encode($trackUploads->pluck('count')) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>
    @endpush
@endsection