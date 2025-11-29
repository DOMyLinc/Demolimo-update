@extends('layouts.admin')

@section('page-title', 'Music Distribution Management')

@section('content')
    <div class="distribution-management">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Distributions</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>{{ $stats['distributed'] }}</h3>
                    <p>Distributed</p>
                </div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h3>{{ $stats['rejected'] }}</h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="{{ route('admin.distributions.index') }}">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search by user or track..."
                        value="{{ request('search') }}" class="filter-input">

                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="distributed" {{ request('status') == 'distributed' ? 'selected' : '' }}>Distributed
                        </option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>

                    <select name="platform" class="filter-select">
                        <option value="">All Platforms</option>
                        <option value="spotify" {{ request('platform') == 'spotify' ? 'selected' : '' }}>Spotify</option>
                        <option value="apple_music" {{ request('platform') == 'apple_music' ? 'selected' : '' }}>Apple Music
                        </option>
                        <option value="youtube_music" {{ request('platform') == 'youtube_music' ? 'selected' : '' }}>YouTube
                            Music</option>
                        <option value="amazon_music" {{ request('platform') == 'amazon_music' ? 'selected' : '' }}>Amazon
                            Music</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.distributions.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <!-- Quick Links -->
        <div class="quick-links">
            <a href="{{ route('admin.distributions.platforms') }}" class="btn btn-outline">
                🌐 Manage Platforms
            </a>
            <a href="{{ route('admin.distributions.analytics') }}" class="btn btn-outline">
                📈 View Analytics
            </a>
            <a href="{{ route('admin.distributions.earnings') }}" class="btn btn-outline">
                💰 View Earnings
            </a>
        </div>

        <!-- Distributions Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Content</th>
                        <th>Platform</th>
                        <th>Release Date</th>
                        <th>Status</th>
                        <th>Earnings</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distributions as $distribution)
                        <tr>
                            <td>#{{ $distribution->id }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $distribution->user) }}">
                                    {{ $distribution->user->name }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $distribution->content_type }}</strong><br>
                                {{ $distribution->content_title }}
                            </td>
                            <td>
                                <span class="platform-badge">{{ $distribution->platform_name }}</span>
                            </td>
                            <td>{{ $distribution->release_date ? $distribution->release_date->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $distribution->status_badge_color }}">
                                    {{ ucfirst($distribution->status) }}
                                </span>
                            </td>
                            <td>${{ number_format($distribution->earnings, 2) }}</td>
                            <td>{{ $distribution->created_at->diffForHumans() }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.distributions.show', $distribution) }}" class="btn btn-sm btn-info"
                                        title="View Details">
                                        👁️
                                    </a>

                                    @if($distribution->isPending())
                                        <form method="POST" action="{{ route('admin.distributions.approve', $distribution) }}"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                ✅
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.distributions.reject', $distribution) }}"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject"
                                                onclick="return confirm('Are you sure you want to reject this distribution?')">
                                                ❌
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No distribution requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $distributions->links() }}
        </div>
    </div>

    <style>
        .distribution-management {
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-card.warning {
            border-left: 4px solid #f59e0b;
        }

        .stat-card.success {
            border-left: 4px solid #10b981;
        }

        .stat-card.danger {
            border-left: 4px solid #ef4444;
        }

        .stat-icon {
            font-size: 2rem;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }

        .stat-info p {
            margin: 5px 0 0 0;
            color: #666;
        }

        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-input,
        .filter-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
            min-width: 200px;
        }

        .quick-links {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #f9fafb;
            font-weight: 600;
        }

        .data-table tr:hover {
            background: #f9fafb;
        }

        .platform-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e0e7ff;
            color: #4f46e5;
            border-radius: 12px;
            font-size: 0.875rem;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-outline {
            background: white;
            border: 1px solid #ddd;
            color: #333;
        }

        .text-center {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .pagination-container {
            padding: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
@endsection