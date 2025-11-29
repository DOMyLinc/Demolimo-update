@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .user-dashboard {
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
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

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .action-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .action-card:hover {
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

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
        }

        .section-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: color 0.2s ease;
        }

        .section-link:hover {
            color: #764ba2;
        }

        /* Track Item */
        .track-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .track-item:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }

        .track-cover {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .track-info {
            flex: 1;
            min-width: 0;
        }

        .track-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .track-meta {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .track-stats {
            text-align: right;
            flex-shrink: 0;
        }

        .track-plays {
            font-weight: 600;
            color: #667eea;
            font-size: 0.875rem;
        }

        /* Activity Feed */
        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .activity-text {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9375rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            margin-left: 2.5rem;
        }

        /* Charts */
        .chart-container {
            margin-top: 1.5rem;
            height: 250px;
        }

        /* Storage Bar */
        .storage-container {
            margin-top: 1.5rem;
        }

        .storage-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .storage-bar {
            width: 100%;
            height: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            overflow: hidden;
        }

        .storage-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .storage-warning {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(245, 87, 108, 0.1);
            border: 1px solid rgba(245, 87, 108, 0.3);
            border-radius: 12px;
            color: #f5576c;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-text {
            margin-bottom: 1.5rem;
        }

        /* Recommendations */
        .recommendation-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .recommendation-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .recommendation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .recommendation-info {
            flex: 1;
            min-width: 0;
        }

        .recommendation-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .recommendation-meta {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .follow-btn {
            padding: 0.5rem 1.25rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .follow-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .user-dashboard {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <div class="user-dashboard">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome back, {{ auth()->user()->name }}! 👋</h1>
            <p class="welcome-subtitle">Here's what's happening with your music today</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎵</div>
                <div class="stat-value">{{ number_format($stats['total_tracks'] ?? 0) }}</div>
                <div class="stat-label">Total Tracks</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">▶️</div>
                <div class="stat-value">{{ number_format($stats['total_plays'] ?? 0) }}</div>
                <div class="stat-label">Total Plays</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value">{{ number_format($stats['total_followers'] ?? 0) }}</div>
                <div class="stat-label">Followers</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💎</div>
                <div class="stat-value">{{ number_format($stats['total_points'] ?? 0) }}</div>
                <div class="stat-label">Points</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💿</div>
                <div class="stat-value">{{ number_format($stats['total_albums'] ?? 0) }}</div>
                <div class="stat-label">Albums</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="{{ route('user.tracks.create') }}" class="action-card">
                <div class="action-icon">⬆️</div>
                <div class="action-label">Upload Track</div>
            </a>
            <a href="{{ route('studio') }}" class="action-card">
                <div class="action-icon">🎹</div>
                <div class="action-label">Open Studio</div>
            </a>
            <a href="{{ route('user.albums.create') }}" class="action-card">
                <div class="action-icon">💿</div>
                <div class="action-label">Create Album</div>
            </a>
            <a href="{{ route('user.song_battles.create') }}" class="action-card">
                <div class="action-icon">⚔️</div>
                <div class="action-label">Start Battle</div>
            </a>
            <a href="{{ route('user.events.create') }}" class="action-card">
                <div class="action-icon">📅</div>
                <div class="action-label">Create Event</div>
            </a>
            <a href="{{ route('user.subscription.index') }}" class="action-card">
                <div class="action-icon">⭐</div>
                <div class="action-label">Upgrade Plan</div>
            </a>
        </div>

        <!-- Analytics Chart -->
        <div class="section-card" style="margin-bottom: 2rem;">
            <div class="section-header">
                <h3 class="section-title">Plays Over Time</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="filter-btn active"
                        style="padding: 0.5rem 1rem; border-radius: 12px; background: linear-gradient(135deg, #667eea, #764ba2); border: none; color: #fff; font-size: 0.875rem; font-weight: 600; cursor: pointer;">7D</button>
                    <button class="filter-btn"
                        style="padding: 0.5rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.7); font-size: 0.875rem; font-weight: 600; cursor: pointer;">30D</button>
                    <button class="filter-btn"
                        style="padding: 0.5rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.7); font-size: 0.875rem; font-weight: 600; cursor: pointer;">90D</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="playsChart"></canvas>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Tracks -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Recent Tracks</h3>
                    <a href="{{ route('user.tracks.index') }}" class="section-link">View All →</a>
                </div>
                @forelse($recentTracks ?? [] as $track)
                    <div class="track-item">
                        <div class="track-cover">🎵</div>
                        <div class="track-info">
                            <div class="track-title">{{ $track->title }}</div>
                            <div class="track-meta">{{ $track->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="track-stats">
                            <div class="track-plays">{{ number_format($track->plays) }} plays</div>
                            <div class="track-meta">{{ number_format($track->likes) }} likes</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">🎵</div>
                        <div class="empty-text">No tracks yet. Upload your first track!</div>
                        <a href="{{ route('user.tracks.create') }}" class="follow-btn">Upload Now</a>
                    </div>
                @endforelse
            </div>

            <!-- Activity Feed -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Recent Activity</h3>
                </div>
                @forelse($recentActivity ?? [] as $activity)
                    <div class="activity-item">
                        <div class="activity-header">
                            <div class="activity-icon">{{ $activity['icon'] }}</div>
                            <div class="activity-text">{{ $activity['message'] }}</div>
                        </div>
                        <div class="activity-time">{{ $activity['time']->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">📊</div>
                        <div class="empty-text">No recent activity</div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recommendations -->
        <div class="section-card" style="margin-bottom: 2rem;">
            <div class="section-header">
                <h3 class="section-title">Suggested Artists</h3>
                <a href="#" class="section-link">View All →</a>
            </div>
            @forelse($recommendations['suggested_artists'] ?? [] as $artist)
                <div class="recommendation-item">
                    <div class="recommendation-avatar">{{ substr($artist->name, 0, 1) }}</div>
                    <div class="recommendation-info">
                        <div class="recommendation-name">{{ $artist->name }}</div>
                        <div class="recommendation-meta">{{ number_format($artist->followers_count ?? 0) }} followers</div>
                    </div>
                    <button class="follow-btn">Follow</button>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">🎤</div>
                    <div class="empty-text">No suggestions available</div>
                </div>
            @endforelse
        </div>

        <!-- Storage Usage -->
        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">Storage Usage</h3>
            </div>
            <div class="storage-container">
                <div class="storage-info">
                    <span>{{ number_format(($storage['used'] ?? 0) / 1024 / 1024, 2) }} MB used</span>
                    <span>{{ number_format(($storage['limit'] ?? 1073741824) / 1024 / 1024 / 1024, 2) }} GB total</span>
                </div>
                <div class="storage-bar">
                    <div class="storage-fill" style="width: {{ min($storage['percentage'] ?? 0, 100) }}%"></div>
                </div>
                @if(($storage['percentage'] ?? 0) > 80)
                    <div class="storage-warning">
                        <span>⚠️</span>
                        <span>You're running low on storage. Consider upgrading your plan.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Plays Chart
            const playsCtx = document.getElementById('playsChart');
            if (playsCtx) {
                new Chart(playsCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($analytics['plays_chart']->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                        datasets: [{
                            label: 'Plays',
                            data: {!! json_encode($analytics['plays_chart']->pluck('plays')) !!},
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
        </script>
    @endpush
@endsection