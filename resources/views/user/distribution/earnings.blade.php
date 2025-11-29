@extends('layouts.app')

@section('content')
    <div class="distribution-earnings">
        <div class="page-header">
            <h1>Distribution Earnings</h1>
            <a href="{{ route('distribution.index') }}" class="btn btn-secondary">
                ← Back to Distributions
            </a>
        </div>

        <!-- Total Earnings Card -->
        <div class="total-earnings-card">
            <div class="earnings-icon">💰</div>
            <div class="earnings-content">
                <h2>Total Earnings</h2>
                <div class="total-amount">${{ number_format($totalEarnings, 2) }}</div>
                <p>From all your distributions</p>
            </div>
        </div>

        <!-- Earnings by Platform -->
        <div class="platform-earnings-section">
            <h3>Earnings by Platform</h3>
            <div class="platform-earnings-grid">
                @forelse($earningsByPlatform as $platformEarning)
                    <div class="platform-earnings-card">
                        <div class="platform-header">
                            <span class="platform-icon">🎵</span>
                            <span class="platform-name">
                                {{ ucfirst(str_replace('_', ' ', $platformEarning->platform)) }}
                            </span>
                        </div>
                        <div class="platform-amount">${{ number_format($platformEarning->total, 2) }}</div>
                        <div class="platform-percentage">
                            {{ $totalEarnings > 0 ? number_format(($platformEarning->total / $totalEarnings) * 100, 1) : 0 }}%
                            of total
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No earnings data available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Earnings History -->
        <div class="earnings-history-section">
            <h3>Earnings History</h3>
            <div class="table-container">
                <table class="earnings-table">
                    <thead>
                        <tr>
                            <th>Content</th>
                            <th>Platform</th>
                            <th>Status</th>
                            <th>Release Date</th>
                            <th>Earnings</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($earningsHistory as $distribution)
                            <tr>
                                <td>
                                    <div class="content-info">
                                        <strong>{{ $distribution->content_title }}</strong>
                                        <span class="content-type-badge">{{ $distribution->content_type }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="platform-badge">{{ $distribution->platform_name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $distribution->status_badge_color }}">
                                        {{ ucfirst($distribution->status) }}
                                    </span>
                                </td>
                                <td>{{ $distribution->release_date ? $distribution->release_date->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="earnings-cell">${{ number_format($distribution->earnings, 2) }}</td>
                                <td>{{ $distribution->updated_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No earnings history available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($earningsHistory->hasPages())
                <div class="pagination-container">
                    {{ $earningsHistory->links() }}
                </div>
            @endif
        </div>

        <!-- Info Box -->
        <div class="info-box">
            <h4>📊 About Your Earnings</h4>
            <ul>
                <li>Earnings are updated monthly based on platform reports.</li>
                <li>Minimum payout threshold is $50.00.</li>
                <li>Payments are processed within 30 days of reaching the threshold.</li>
                <li>Earnings may vary based on streams, downloads, and platform rates.</li>
            </ul>
        </div>
    </div>

    <style>
        .distribution-earnings {
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

        .total-earnings-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 40px;
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .earnings-icon {
            font-size: 4rem;
        }

        .earnings-content h2 {
            margin: 0 0 10px 0;
            font-size: 1.25rem;
            opacity: 0.9;
        }

        .total-amount {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .earnings-content p {
            margin: 0;
            opacity: 0.8;
        }

        .platform-earnings-section {
            margin-bottom: 40px;
        }

        .platform-earnings-section h3 {
            margin: 0 0 20px 0;
            font-size: 1.5rem;
        }

        .platform-earnings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .platform-earnings-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .platform-earnings-card:hover {
            transform: translateY(-4px);
        }

        .platform-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .platform-icon {
            font-size: 1.5rem;
        }

        .platform-name {
            font-weight: 600;
            color: #374151;
        }

        .platform-amount {
            font-size: 2rem;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 5px;
        }

        .platform-percentage {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .earnings-history-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .earnings-history-section h3 {
            margin: 0 0 20px 0;
            font-size: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .earnings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .earnings-table th,
        .earnings-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .earnings-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .earnings-table tr:hover {
            background: #f9fafb;
        }

        .content-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .content-type-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 0.75rem;
            color: #6b7280;
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

        .earnings-cell {
            font-weight: 600;
            color: #10b981;
            font-size: 1.125rem;
        }

        .text-center {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            grid-column: 1 / -1;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
        }

        .info-box h4 {
            margin: 0 0 15px 0;
            color: #1e40af;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .info-box li {
            margin-bottom: 8px;
            color: #1e3a8a;
        }

        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
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

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
@endsection