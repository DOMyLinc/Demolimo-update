@extends('layouts.app')

@section('content')
    <div class="distribution-show">
        <div class="page-header">
            <h1>Distribution Details</h1>
            <a href="{{ route('distribution.index') }}" class="btn btn-secondary">
                ← Back to My Distributions
            </a>
        </div>

        <div class="status-banner banner-{{ $distribution->status_badge_color }}">
            <div class="banner-icon">
                @if($distribution->isPending())
                    ⏳
                @elseif($distribution->isApproved())
                    ✅
                @elseif($distribution->isDistributed())
                    🎉
                @elseif($distribution->isRejected())
                    ❌
                @endif
            </div>
            <div class="banner-content">
                <h2>Status: {{ ucfirst($distribution->status) }}</h2>
                <p>
                    @if($distribution->isPending())
                        Your distribution request is being reviewed by our team.
                    @elseif($distribution->isApproved())
                        Your distribution has been approved and is being processed.
                    @elseif($distribution->isDistributed())
                        Your music is now live on {{ $distribution->platform_name }}!
                    @elseif($distribution->isRejected())
                        Your distribution request was not approved.
                    @endif
                </p>
            </div>
        </div>

        <div class="details-grid">
            <!-- Content Information -->
            <div class="info-card">
                <h3>Content Information</h3>
                <div class="info-row">
                    <span class="label">Type:</span>
                    <span class="value">{{ $distribution->content_type }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Title:</span>
                    <span class="value"><strong>{{ $distribution->content_title }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="label">Platform:</span>
                    <span class="platform-badge">{{ $distribution->platform_name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Release Date:</span>
                    <span
                        class="value">{{ $distribution->release_date ? $distribution->release_date->format('F d, Y') : 'Not set' }}</span>
                </div>
            </div>

            <!-- Technical Details -->
            <div class="info-card">
                <h3>Technical Details</h3>
                <div class="info-row">
                    <span class="label">UPC Code:</span>
                    <span class="value">{{ $distribution->upc ?: 'Not provided' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">ISRC Code:</span>
                    <span class="value">{{ $distribution->isrc ?: 'Not provided' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Submitted:</span>
                    <span class="value">{{ $distribution->created_at->format('F d, Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Last Updated:</span>
                    <span class="value">{{ $distribution->updated_at->diffForHumans() }}</span>
                </div>
            </div>

            <!-- Earnings Card -->
            <div class="info-card earnings-card">
                <h3>Earnings</h3>
                <div class="earnings-display">
                    <div class="earnings-amount">${{ number_format($distribution->earnings, 2) }}</div>
                    <p class="earnings-label">Total Earnings from this Distribution</p>
                </div>
                @if($distribution->earnings > 0)
                    <a href="{{ route('distribution.earnings') }}" class="btn btn-outline btn-block">
                        View All Earnings →
                    </a>
                @endif
            </div>
        </div>

        <!-- Track/Album Preview -->
        @if($distribution->track)
            <div class="content-preview">
                <h3>Track Information</h3>
                <div class="track-details">
                    <div class="track-row">
                        <span class="label">Title:</span>
                        <span class="value">{{ $distribution->track->title }}</span>
                    </div>
                    <div class="track-row">
                        <span class="label">Artist:</span>
                        <span class="value">{{ $distribution->track->artist }}</span>
                    </div>
                    @if($distribution->track->duration)
                        <div class="track-row">
                            <span class="label">Duration:</span>
                            <span class="value">{{ gmdate("i:s", $distribution->track->duration) }}</span>
                        </div>
                    @endif
                    @if($distribution->track->genre)
                        <div class="track-row">
                            <span class="label">Genre:</span>
                            <span class="value">{{ $distribution->track->genre->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($distribution->album)
            <div class="content-preview">
                <h3>Album Information</h3>
                <div class="album-details">
                    <div class="album-row">
                        <span class="label">Title:</span>
                        <span class="value">{{ $distribution->album->title }}</span>
                    </div>
                    <div class="album-row">
                        <span class="label">Artist:</span>
                        <span class="value">{{ $distribution->album->artist }}</span>
                    </div>
                    <div class="album-row">
                        <span class="label">Tracks:</span>
                        <span class="value">{{ $distribution->album->tracks->count() }} tracks</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Timeline/Status History (Future Enhancement) -->
        <div class="timeline-section">
            <h3>Distribution Timeline</h3>
            <div class="timeline">
                <div class="timeline-item completed">
                    <div class="timeline-marker">✓</div>
                    <div class="timeline-content">
                        <h4>Request Submitted</h4>
                        <p>{{ $distribution->created_at->format('F d, Y H:i') }}</p>
                    </div>
                </div>

                @if(!$distribution->isPending())
                    <div class="timeline-item completed">
                        <div class="timeline-marker">✓</div>
                        <div class="timeline-content">
                            <h4>{{ $distribution->isRejected() ? 'Request Rejected' : 'Request Approved' }}</h4>
                            <p>{{ $distribution->updated_at->format('F d, Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                @if($distribution->isDistributed())
                    <div class="timeline-item completed">
                        <div class="timeline-marker">✓</div>
                        <div class="timeline-content">
                            <h4>Live on Platform</h4>
                            <p>Your music is now available!</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .distribution-show {
            max-width: 1000px;
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

        .status-banner {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .banner-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        .banner-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
        }

        .banner-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
        }

        .banner-danger {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
        }

        .banner-icon {
            font-size: 3rem;
        }

        .banner-content h2 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }

        .banner-content p {
            margin: 0;
            color: #374151;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .info-card h3 {
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
            font-size: 1.25rem;
        }

        .info-row,
        .track-row,
        .album-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child,
        .track-row:last-child,
        .album-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: 600;
            color: #6b7280;
        }

        .value {
            color: #111827;
            text-align: right;
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

        .earnings-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .earnings-card h3 {
            color: white;
            border-bottom-color: rgba(255, 255, 255, 0.3);
        }

        .earnings-display {
            text-align: center;
            padding: 20px 0;
        }

        .earnings-amount {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .earnings-label {
            margin: 0;
            opacity: 0.9;
        }

        .content-preview {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .content-preview h3 {
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .timeline-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .timeline-section h3 {
            margin: 0 0 25px 0;
        }

        .timeline {
            position: relative;
            padding-left: 40px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -32px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #10b981;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .timeline-content h4 {
            margin: 0 0 5px 0;
            font-size: 1.125rem;
        }

        .timeline-content p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
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

        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 15px;
        }
    </style>
@endsection