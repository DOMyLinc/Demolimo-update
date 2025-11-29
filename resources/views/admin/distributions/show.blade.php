@extends('layouts.admin')

@section('page-title', 'Distribution Details')

@section('content')
    <div class="distribution-details">
        <div class="header-actions">
            <a href="{{ route('admin.distributions.index') }}" class="btn btn-secondary">
                ← Back to List
            </a>

            <div class="action-group">
                @if($distribution->isPending())
                    <form method="POST" action="{{ route('admin.distributions.approve', $distribution) }}"
                        style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success">✅ Approve</button>
                    </form>

                    <form method="POST" action="{{ route('admin.distributions.reject', $distribution) }}"
                        style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to reject this distribution?')">
                            ❌ Reject
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.distributions.destroy', $distribution) }}"
                    style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"
                        onclick="return confirm('Are you sure you want to delete this distribution?')">
                        🗑️ Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="details-grid">
            <!-- Main Info Card -->
            <div class="info-card">
                <h3>Distribution Information</h3>
                <div class="info-row">
                    <span class="label">ID:</span>
                    <span class="value">#{{ $distribution->id }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="badge badge-{{ $distribution->status_badge_color }}">
                        {{ ucfirst($distribution->status) }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Platform:</span>
                    <span class="platform-badge">{{ $distribution->platform_name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Content Type:</span>
                    <span class="value">{{ $distribution->content_type }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Content Title:</span>
                    <span class="value"><strong>{{ $distribution->content_title }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="label">Release Date:</span>
                    <span
                        class="value">{{ $distribution->release_date ? $distribution->release_date->format('F d, Y') : 'Not set' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Earnings:</span>
                    <span class="value earnings">${{ number_format($distribution->earnings, 2) }}</span>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="info-card">
                <h3>Artist Information</h3>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value">
                        <a href="{{ route('admin.users.show', $distribution->user) }}">
                            {{ $distribution->user->name }}
                        </a>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $distribution->user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Total Distributions:</span>
                    <span class="value">{{ $distribution->user->distributions->count() }}</span>
                </div>
            </div>

            <!-- Technical Details Card -->
            <div class="info-card">
                <h3>Technical Details</h3>
                <div class="info-row">
                    <span class="label">UPC:</span>
                    <span class="value">{{ $distribution->upc ?: 'Not provided' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">ISRC:</span>
                    <span class="value">{{ $distribution->isrc ?: 'Not provided' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Submitted:</span>
                    <span class="value">{{ $distribution->created_at->format('F d, Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Last Updated:</span>
                    <span class="value">{{ $distribution->updated_at->format('F d, Y H:i') }}</span>
                </div>
            </div>

            <!-- Status Update Card -->
            <div class="info-card">
                <h3>Update Status</h3>
                <form method="POST" action="{{ route('admin.distributions.updateStatus', $distribution) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="status">Change Status:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="pending" {{ $distribution->status == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="approved" {{ $distribution->status == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="distributed" {{ $distribution->status == 'distributed' ? 'selected' : '' }}>
                                Distributed</option>
                            <option value="rejected" {{ $distribution->status == 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                            <option value="failed" {{ $distribution->status == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>

        <!-- Content Preview -->
        @if($distribution->track)
            <div class="content-preview">
                <h3>Track Details</h3>
                <div class="track-info">
                    <p><strong>Title:</strong> {{ $distribution->track->title }}</p>
                    <p><strong>Artist:</strong> {{ $distribution->track->artist }}</p>
                    <p><strong>Duration:</strong> {{ gmdate("i:s", $distribution->track->duration ?? 0) }}</p>
                    <p><strong>Genre:</strong> {{ $distribution->track->genre->name ?? 'N/A' }}</p>
                </div>
            </div>
        @elseif($distribution->album)
            <div class="content-preview">
                <h3>Album Details</h3>
                <div class="album-info">
                    <p><strong>Title:</strong> {{ $distribution->album->title }}</p>
                    <p><strong>Artist:</strong> {{ $distribution->album->artist }}</p>
                    <p><strong>Tracks:</strong> {{ $distribution->album->tracks->count() }}</p>
                    <p><strong>Release Year:</strong> {{ $distribution->album->release_year ?? 'N/A' }}</p>
                </div>
            </div>
        @endif
    </div>

    <style>
        .distribution-details {
            padding: 20px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .action-group {
            display: flex;
            gap: 10px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .info-card h3 {
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            font-size: 1.25rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: 600;
            color: #6b7280;
        }

        .value {
            color: #111827;
        }

        .value.earnings {
            font-size: 1.5rem;
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

        .badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .content-preview {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .content-preview h3 {
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .track-info p,
        .album-info p {
            margin: 8px 0;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
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

        .btn-outline-danger {
            background: white;
            border: 1px solid #ef4444;
            color: #ef4444;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
@endsection