@extends('layouts.admin')

@section('page-title', 'Gift Management')

@section('content')
    <div class="gift-management">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0e7ff;">
                    <i class="fas fa-gift" style="color: #4f46e5;"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_gifts'] }}</h3>
                    <p>Total Gifts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #d1fae5;">
                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['active_gifts'] }}</h3>
                    <p>Active Gifts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7;">
                    <i class="fas fa-exchange-alt" style="color: #f59e0b;"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ number_format($stats['total_transactions']) }}</h3>
                    <p>Total Transactions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #dbeafe;">
                    <i class="fas fa-dollar-sign" style="color: #3b82f6;"></i>
                </div>
                <div class="stat-content">
                    <h3>${{ number_format($stats['total_revenue'], 2) }}</h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e9d5ff;">
                    <i class="fas fa-chart-line" style="color: #8b5cf6;"></i>
                </div>
                <div class="stat-content">
                    <h3>${{ number_format($stats['platform_earnings'], 2) }}</h3>
                    <p>Platform Earnings (20%)</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar">
            <a href="{{ route('admin.gifts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Gift
            </a>
            <a href="{{ route('admin.gifts.analytics') }}" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> View Analytics
            </a>
        </div>

        <!-- Gifts Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Transactions</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gifts as $gift)
                        <tr>
                            <td><span class="gift-icon">{{ $gift->icon }}</span></td>
                            <td><strong>{{ $gift->name }}</strong></td>
                            <td class="price">${{ number_format($gift->price, 2) }}</td>
                            <td>{{ Str::limit($gift->description, 50) }}</td>
                            <td>{{ number_format($gift->transactions_count) }}</td>
                            <td>{{ $gift->sort_order }}</td>
                            <td>
                                <span class="badge badge-{{ $gift->is_active ? 'success' : 'danger' }}">
                                    {{ $gift->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.gifts.edit', $gift) }}" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.gifts.destroy', $gift) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-danger" title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this gift?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No gifts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $gifts->links() }}
        </div>
    </div>

    <style>
        .gift-management {
            padding: 20px;
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
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-content h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: bold;
        }

        .stat-content p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .action-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .data-table tr:hover {
            background: #f9fafb;
        }

        .gift-icon {
            font-size: 2rem;
        }

        .price {
            color: #10b981;
            font-weight: 600;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            padding: 8px;
            border: none;
            background: #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            color: #374151;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-danger:hover {
            background: #fecaca;
        }

        .text-center {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
@endsection