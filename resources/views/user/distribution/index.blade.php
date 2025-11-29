@extends('layouts.app')

@section('content')
    <div class="user-distributions">
        <div class="page-header">
            <h1>My Music Distribution</h1>
            <a href="{{ route('distribution.create') }}" class="btn btn-primary">
                + Submit New Distribution
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Requests</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>{{ $stats['distributed'] }}</h3>
                    <p>Distributed</p>
                </div>
            </div>
            <div class="stat-card earnings">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>${{ number_format($stats['total_earnings'], 2) }}</h3>
                    <p>Total Earnings</p>
                </div>
            </div>
        </div>

        <!-- Quick Link to Earnings -->
        <div class="quick-actions">
            <a href="{{ route('distribution.earnings') }}" class="btn btn-outline">
                💰 View Detailed Earnings
            </a>
        </div>

        <!-- Distributions List -->
        <div class="distributions-list">
            @forelse($distributions as $distribution)
                <div class="distribution-card">
                    <div class="distribution-header">
                        <div class="distribution-title">
                            <h3>{{ $distribution->content_title }}</h3>
                            <span class="content-type">{{ $distribution->content_type }}</span>
                        </div>
                        <span class="badge badge-{{ $distribution->status_badge_color }}">
                            {{ ucfirst($distribution->status) }}
                        </span>
                    </div>

                    <div class="distribution-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Platform:</span>
                                <span class="platform-badge">{{ $distribution->platform_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Release Date:</span>
                                <span
                                    class="value">{{ $distribution->release_date ? $distribution->release_date->format('M d, Y') : 'Not set' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Earnings:</span>
                                <span class="value earnings">${{ number_format($distribution->earnings, 2) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Submitted:</span>
                                <span class="value">{{ $distribution->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="distribution-footer">
                        <a href="{{ route('distribution.show', $distribution) }}" class="btn btn-sm btn-outline">
                            View Details →
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">🎵</div>
                    <h3>No Distribution Requests Yet</h3>
                    <p>Start distributing your music to major streaming platforms!</p>
                    <a href="{{ route('distribution.create') }}" class="btn btn-primary">
                        Submit Your First Distribution
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($distributions->hasPages())
            <div class="pagination-container">
                {{ $distributions->links() }}
            </div>
        @endif
    </div>

    <style>
        .user-distributions {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.warning {
            border-left: 4px solid #f59e0b;
        }

        .stat-card.success {
            border-left: 4px solid #10b981;
        }

        .stat-card.earnings {
            border-left: 4px solid #8b5cf6;
        }

        .stat-icon {
            font-size: 2.5rem;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: bold;
        }

        .stat-info p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .quick-actions {
            margin-bottom: 30px;
        }

        .distributions-list {
            display: grid;
            gap: 20px;
        }

        .distribution-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .distribution-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .distribution-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .distribution-title h3 {
            margin: 0 0 5px 0;
            font-size: 1.25rem;
        }

        .content-type {
            display: inline-block;
            padding: 2px 8px;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .distribution-body {
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        .value {
            font-size: 1rem;
            color: #111827;
        }

        .value.earnings {
            font-size: 1.25rem;
            font-weight: bold;
            color: #10b981;
        }

        .platform-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e0e7ff;
            color: #4f46e5;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge {
            padding: 6px 12px;
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

        .distribution-footer {
            display: flex;
            justify-content: flex-end;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-outline {
            background: white;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        .pagination-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }
    </style>
@endsection