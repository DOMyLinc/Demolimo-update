@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Edit Banner: {{ $banner->title }}</h1>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $banner->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Banner Type *</label>
                                <select name="type" id="bannerType" class="form-select @error('type') is-invalid @enderror"
                                    required>
                                    <option value="image" {{ old('type', $banner->type) === 'image' ? 'selected' : '' }}>Image
                                    </option>
                                    <option value="audio" {{ old('type', $banner->type) === 'audio' ? 'selected' : '' }}>Audio
                                    </option>
                                    <option value="text" {{ old('type', $banner->type) === 'text' ? 'selected' : '' }}>Text
                                    </option>
                                </select>
                            </div>

                            {{-- Current Content Display --}}
                            @if($banner->type === 'image')
                                <div class="mb-3">
                                    <label class="form-label">Current Image</label>
                                    <div>
                                        <img src="{{ $banner->content }}" alt="{{ $banner->title }}" class="img-thumbnail"
                                            style="max-width: 300px;">
                                    </div>
                                </div>
                            @elseif($banner->type === 'audio')
                                <div class="mb-3">
                                    <label class="form-label">Current Audio</label>
                                    <audio controls src="{{ $banner->content }}" class="w-100"></audio>
                                </div>
                            @endif

                            {{-- Content Fields --}}
                            <div id="imageContent" class="content-field"
                                style="display:{{ $banner->type === 'image' ? 'block' : 'none' }};">
                                <div class="mb-3">
                                    <label class="form-label">Upload New Image (Optional)</label>
                                    <input type="file" name="content_file" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <div id="audioContent" class="content-field"
                                style="display:{{ $banner->type === 'audio' ? 'block' : 'none' }};">
                                <div class="mb-3">
                                    <label class="form-label">Upload New Audio (Optional)</label>
                                    <input type="file" name="content_file" class="form-control" accept="audio/*">
                                </div>
                            </div>

                            <div id="textContent" class="content-field"
                                style="display:{{ $banner->type === 'text' ? 'block' : 'none' }};">
                                <div class="mb-3">
                                    <label class="form-label">Text Content</label>
                                    <textarea name="content" class="form-control"
                                        rows="4">{{ old('content', $banner->content) }}</textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Click-Through URL (Optional)</label>
                                <input type="url" name="link" class="form-control" value="{{ old('link', $banner->link) }}">
                            </div>

                            {{-- Placement Zones --}}
                            <div class="mb-3">
                                <label class="form-label">Placement Zones *</label>
                                <div class="row">
                                    @foreach($placementZones as $key => $label)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="placement_zones[]"
                                                    value="{{ $key }}" id="zone_{{ $key }}" {{ in_array($key, old('placement_zones', $banner->placement_zones)) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="zone_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Publishing</h5>

                                    <div class="mb-3">
                                        <label class="form-label">Status *</label>
                                        <select name="status" class="form-select" required>
                                            <option value="draft" {{ old('status', $banner->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="scheduled" {{ old('status', $banner->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="published" {{ old('status', $banner->status) === 'published' ? 'selected' : '' }}>Published</option>
                                            <option value="expired" {{ old('status', $banner->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Target Audience *</label>
                                        <select name="target_audience" class="form-select" required>
                                            <option value="all" {{ old('target_audience', $banner->target_audience) === 'all' ? 'selected' : '' }}>All Users</option>
                                            <option value="free" {{ old('target_audience', $banner->target_audience) === 'free' ? 'selected' : '' }}>Free Users Only
                                            </option>
                                            <option value="pro" {{ old('target_audience', $banner->target_audience) === 'pro' ? 'selected' : '' }}>Pro Users Only</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <input type="number" name="priority" class="form-control"
                                            value="{{ old('priority', $banner->priority) }}" min="0">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="datetime-local" name="start_date" class="form-control"
                                            value="{{ old('start_date', $banner->start_date?->format('Y-m-d\TH:i')) }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="datetime-local" name="end_date" class="form-control"
                                            value="{{ old('end_date', $banner->end_date?->format('Y-m-d\TH:i')) }}">
                                    </div>

                                    <hr>

                                    <div class="mb-2">
                                        <strong>Stats:</strong>
                                        <ul class="list-unstyled mt-2">
                                            <li>👁️ {{ number_format($banner->impressions) }} impressions</li>
                                            <li>🖱️ {{ number_format($banner->clicks) }} clicks</li>
                                            <li>📊 {{ $banner->getCTR() }}% CTR</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('bannerType').addEventListener('change', function () {
            document.querySelectorAll('.content-field').forEach(el => el.style.display = 'none');
            const type = this.value;
            if (type) {
                document.getElementById(type + 'Content').style.display = 'block';
            }
        });
    </script>
@endsection