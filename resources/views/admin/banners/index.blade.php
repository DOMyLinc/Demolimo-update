@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Banner Management</h1>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Create Banner
            </a>
        </div>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.banners.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled
                            </option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published
                            </option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Image</option>
                            <option value="audio" {{ request('type') === 'audio' ? 'selected' : '' }}>Audio</option>
                            <option value="text" {{ request('type') === 'text' ? 'selected' : '' }}>Text</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Placement</label>
                        <select name="placement" class="form-select">
                            <option value="">All Placements</option>
                            <option value="landing_hero">Landing - Hero</option>
                            <option value="player_inline">Player - Inline</option>
                            <option value="global_top">Global - Top</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Banners List --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Placement</th>
                                <th>Audience</th>
                                <th>Stats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                                <tr>
                                    <td>
                                        <strong>{{ $banner->title }}</strong>
                                        <br>
                                        <small class="text-muted">Priority: {{ $banner->priority }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($banner->type) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'scheduled' => 'warning',
                                                'published' => 'success',
                                                'expired' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$banner->status] }}">
                                            {{ ucfirst($banner->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ count($banner->placement_zones) }} zones</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($banner->target_audience) }}</span>
                                    </td>
                                    <td>
                                        <small>
                                            👁️ {{ number_format($banner->impressions) }}<br>
                                            🖱️ {{ number_format($banner->clicks) }} ({{ $banner->getCTR() }}%)
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.banners.edit', $banner) }}"
                                                class="btn btn-outline-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.banners.analytics', $banner) }}"
                                                class="btn btn-outline-info">
                                                <i class="fa fa-chart-bar"></i>
                                            </a>
                                            @if($banner->status !== 'published')
                                                <form action="{{ route('admin.banners.publish', $banner) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <p class="text-muted">No banners found.</p>
                                        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">Create Your First
                                            Banner</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $banners->links() }}
            </div>
        </div>
    </div>
@endsection