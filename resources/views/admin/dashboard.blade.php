@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .admin-dashboard {
            padding: 2rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Welcome Section */
        .welcome-section {
            margin-bottom: 2.5rem;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.125rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.75rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.3);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.2);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-trend {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stat-trend.up {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .stat-trend.down {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .chart-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2rem;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
        }

        .chart-filter {
            display: flex;
            gap: 0.5rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: transparent;
            color: #fff;
        }

        /* Tables */
        .table-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
        }

        .view-all-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: color 0.2s ease;
        }

        .view-all-link:hover {
            color: #764ba2;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .data-table th {
            padding: 1rem;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.9);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #fff;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #fff;
        }

        .user-email {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .badge {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .badge-info {
            background: rgba(79, 172, 254, 0.1);
            color: #4facfe;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .action-btn {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
        }

        .action-icon {
            font-size: 2rem;
        }

        .action-label {
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-dashboard {
                padding: 1rem;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="admin-dashboard">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome back, Admin! 👋</h1>
            <p class="welcome-subtitle">Here's what's happening on your platform today</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">👥</div>
                    <div class="stat-trend up">↑
                        {{ number_format((($stats['new_users_month'] ?? 0) / max($stats['total_users'] ?? 1, 1)) * 100, 1) }}%
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
                <div class="stat-label">Total Users</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">🎵</div>
                    <div class="stat-trend up">↑ {{ $stats['new_tracks_today'] ?? 0 }} today</div>
                </div>
                <div class="stat-value">{{ number_format($stats['total_tracks'] ?? 0) }}</div>
                <div class="stat-label">Total Tracks</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">💰</div>
                    <div class="stat-trend up">↑ ${{ number_format($stats['daily_revenue'] ?? 0, 2) }} today</div>
                </div>
                <div class="stat-value">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</div>
                <div class="stat-label">Monthly Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">▶️</div>
                    <div class="stat-trend up">↑ 12.5%</div>
                </div>
                <div class="stat-value">{{ number_format($stats['total_plays'] ?? 0) }}</div>
                <div class="stat-label">Total Plays</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-trend up">↑ {{ $stats['active_subscriptions'] ?? 0 }}</div>
                </div>
                <div class="stat-value">{{ number_format($stats['active_subscriptions'] ?? 0) }}</div>
                <div class="stat-label">Active Subscriptions</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-trend warning">{{ $stats['pending_moderation'] ?? 0 }} pending</div>
                </div>
                <div class="stat-value">{{ number_format($stats['pending_moderation'] ?? 0) }}</div>
                <div class="stat-label">Content Moderation</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="{{ route('admin.users.index') }}" class="action-btn">
                <div class="action-icon">👥</div>
                <div class="action-label">Manage Users</div>
            </a>
            <a href="{{ route('admin.tracks.index') }}" class="action-btn">
                <div class="action-icon">🎵</div>
                <div class="action-label">Manage Tracks</div>
            </a>
            <a href="{{ route('admin.moderation.queue') }}" class="action-btn">
                <div class="action-icon">✅</div>
                <div class="action-label">Moderation Queue</div>
            </a>
            <a href="{{ route('admin.analytics.index') }}" class="action-btn">
                <div class="action-icon">📊</div>
                <div class="action-label">View Analytics</div>
            </a>
            <a href="{{ route('admin.settings.platform') }}" class="action-btn">
                <div class="action-icon">⚙️</div>
                <div class="action-label">Platform Settings</div>
            </a>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">User Growth</h3>
                    <div class="chart-filter">
                        <button class="filter-btn active">7D</button>
                        <button class="filter-btn">30D</button>
                        <button class="filter-btn">90D</button>
                    </div>
                </div>
                <canvas id="userGrowthChart" height="250"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Revenue Overview</h3>
                    <div class="chart-filter">
                        <button class="filter-btn active">Month</button>
                        <button class="filter-btn">Year</button>
                    </div>
                </div>
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>

        <!-- Top Artists Table -->
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Top Artists</h3>
                <a href="{{ route('admin.users.index') }}" class="view-all-link">View All →</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Artist</th>
                        <th>Total Plays</th>
                        <th>Tracks</th>
                        <th>Followers</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topArtists ?? [] as $artist)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">{{ substr($artist->name, 0, 1) }}</div>
                                    <div class="user-info">
                                        <span class="user-name">{{ $artist->name }}</span>
                                        <span class="user-email">{{ $artist->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ number_format($artist->total_plays ?? 0) }}</td>
                            <td>{{ $artist->tracks()->count() }}</td>
                            <td>{{ number_format($artist->followers()->count()) }}</td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: rgba(255, 255, 255, 0.5);">
                                No data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Top Tracks Table -->
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Top Tracks This Week</h3>
                <a href="{{ route('admin.tracks.index') }}" class="view-all-link">View All →</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Track</th>
                        <th>Artist</th>
                        <th>Plays</th>
                        <th>Downloads</th>
                        <th>Likes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topTracks ?? [] as $track)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">🎵</div>
                                    <div class="user-info">
                                        <span class="user-name">{{ $track->title }}</span>
                                        <span class="user-email">{{ $track->genre ?? 'Unknown' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $track->user->name }}</td>
                            <td>{{ number_format($track->plays) }}</td>
                            <td>{{ number_format($track->downloads) }}</td>
                            <td>{{ number_format($track->likes) }}</td>
                            <td><span class="badge badge-success">Published</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: rgba(255, 255, 255, 0.5);">
                                No tracks available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Recent Users -->
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Recent Users</h3>
                <a href="{{ route('admin.users.index') }}" class="view-all-link">View All →</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Joined</th>
                        <th>Tracks</th>
                        <th>Subscription</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentUsers ?? [] as $user)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                                    <div class="user-info">
                                        <span class="user-name">{{ $user->name }}</span>
                                        <span class="user-email">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->created_at->diffForHumans() }}</td>
                            <td>{{ $user->tracks()->count() }}</td>
                            <td><span class="badge badge-info">{{ $user->subscription_type ?? 'Free' }}</span></td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: rgba(255, 255, 255, 0.5);">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // User Growth Chart
            const userGrowthCtx = document.getElementById('userGrowthChart');
            if (userGrowthCtx) {
                new Chart(userGrowthCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($userGrowth->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                        datasets: [{
                            label: 'New Users',
                            data: {!! json_encode($userGrowth->pluck('count')) !!},
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
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
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.05)'
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.6)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.6)'
                                }
                            }
                        }
                    }
                });
            }

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($revenueChart->map(fn($r) => date('M Y', mktime(0, 0, 0, $r->month, 1, $r->year)))) !!},
                        datasets: [{
                            label: 'Revenue',
                            data: {!! json_encode($revenueChart->pluck('total')) !!},
                            backgroundColor: 'rgba(102, 126, 234, 0.8)',
                            borderColor: '#667eea',
                            borderWidth: 2,
                            borderRadius: 8
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
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.05)'
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.6)',
                                    callback: function (value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.6)'
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
@endsection