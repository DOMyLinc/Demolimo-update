@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Create Banner</h1>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-8">
                            {{-- Basic Info --}}
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Banner Type *</label>
                                <select name="type" id="bannerType" class="form-select @error('type') is-invalid @enderror"
                                    required>
                                    <option value="">Select Type</option>
                                    <option value="image" {{ old('type') === 'image' ? 'selected' : '' }}>Image</option>
                                    <option value="audio" {{ old('type') === 'audio' ? 'selected' : '' }}>Audio</option>
                                    <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>Text</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Content Fields (Dynamic based on type) --}}
                            <div id="imageContent" class="content-field" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Upload Image</label>
                                    <input type="file" name="content_file" class="form-control" accept="image/*">
                                    <small class="text-muted">Recommended: 1200x400px for hero, 300x250px for
                                        sidebar</small>
                                </div>
                            </div>

                            <div id="audioContent" class="content-field" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Upload Audio</label>
                                    <input type="file" name="content_file" class="form-control" accept="audio/*">
                                    <small class="text-muted">Supported formats: MP3, WAV, OGG</small>
                                </div>
                            </div>

                            <div id="textContent" class="content-field" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Text Content</label>
                                    <textarea name="content" class="form-control" rows="4">{{ old('content') }}</textarea>
                                    <small class="text-muted">HTML allowed</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Click-Through URL (Optional)</label>
                                <input type="url" name="link" class="form-control @error('link') is-invalid @enderror"
                                    value="{{ old('link') }}" placeholder="https://example.com">
                                @error('link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Placement Zones --}}
                            <div class="mb-3">
                                <label class="form-label">Placement Zones *</label>
                                <div class="row">
                                    @foreach($placementZones as $key => $label)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="placement_zones[]"
                                                    value="{{ $key }}" id="zone_{{ $key }}" {{ in_array($key, old('placement_zones', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="zone_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('placement_zones')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            {{-- Publishing Options --}}
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Publishing</h5>

                                    <div class="mb-3">
                                        <label class="form-label">Status *</label>
                                        <select name="status" class="form-select" required>
                                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft
                                            </option>
                                            <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>
                                                Scheduled</option>
                                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>
                                                Published</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Target Audience *</label>
                                        <select name="target_audience" class="form-select" required>
                                            <option value="all" {{ old('target_audience') === 'all' ? 'selected' : '' }}>All
                                                Users</option>
                                            <option value="free" {{ old('target_audience') === 'free' ? 'selected' : '' }}>
                                                Free Users Only</option>
                                            <option value="pro" {{ old('target_audience') === 'pro' ? 'selected' : '' }}>Pro
                                                Users Only</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <input type="number" name="priority" class="form-control"
                                            value="{{ old('priority', 0) }}" min="0">
                                        <small class="text-muted">Higher = shown first</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Start Date (Optional)</label>
                                        <input type="datetime-local" name="start_date" class="form-control"
                                            value="{{ old('start_date') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">End Date (Optional)</label>
                                        <input type="datetime-local" name="end_date" class="form-control"
                                            value="{{ old('end_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('bannerType').addEventListener('change', function () {
            // Hide all content fields
            document.querySelectorAll('.content-field').forEach(el => el.style.display = 'none');

            // Show selected content field
            const type = this.value;
            if (type) {
                document.getElementById(type + 'Content').style.display = 'block';
            }
        });

        // Trigger on page load if type is already selected
        if (document.getElementById('bannerType').value) {
            document.getElementById('bannerType').dispatchEvent(new Event('change'));
        }
    </script>
@endsection