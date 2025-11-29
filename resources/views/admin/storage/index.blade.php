@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">💾 Storage Configuration</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('admin.storage.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Default Disk Selection -->
                            <div class="form-group">
                                <label><strong>Default Storage Disk</strong></label>
                                <select name="default_disk" class="form-control">
                                    <option value="local" {{ $settings->default_disk == 'local' ? 'selected' : '' }}>Local
                                        Storage</option>
                                    <option value="s3" {{ $settings->default_disk == 's3' ? 'selected' : '' }}>Amazon S3
                                    </option>
                                    <option value="spaces" {{ $settings->default_disk == 'spaces' ? 'selected' : '' }}>
                                        DigitalOcean Spaces</option>
                                    <option value="wasabi" {{ $settings->default_disk == 'wasabi' ? 'selected' : '' }}>Wasabi
                                    </option>
                                    <option value="backblaze" {{ $settings->default_disk == 'backblaze' ? 'selected' : '' }}>
                                        Backblaze B2</option>
                                </select>
                                <small class="text-muted">All uploads will be saved to this storage</small>
                            </div>

                            <hr>

                            <!-- Amazon S3 -->
                            <h4>☁️ Amazon S3</h4>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="s3_enabled" value="1" {{ $settings->s3_enabled ? 'checked' : '' }}>
                                    Enable Amazon S3
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Access Key ID</label>
                                        <input type="text" name="s3_key" class="form-control"
                                            value="{{ $settings->s3_key }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Secret Access Key</label>
                                        <input type="password" name="s3_secret" class="form-control"
                                            value="{{ $settings->s3_secret }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Region</label>
                                        <input type="text" name="s3_region" class="form-control"
                                            value="{{ $settings->s3_region }}" placeholder="us-east-1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Bucket</label>
                                        <input type="text" name="s3_bucket" class="form-control"
                                            value="{{ $settings->s3_bucket }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>URL (optional)</label>
                                        <input type="url" name="s3_url" class="form-control"
                                            value="{{ $settings->s3_url }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-info btn-sm" onclick="testConnection('s3')">Test S3
                                Connection</button>

                            <hr>

                            <!-- DigitalOcean Spaces -->
                            <h4>🌊 DigitalOcean Spaces</h4>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="spaces_enabled" value="1" {{ $settings->spaces_enabled ? 'checked' : '' }}>
                                    Enable DigitalOcean Spaces
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Access Key</label>
                                        <input type="text" name="spaces_key" class="form-control"
                                            value="{{ $settings->spaces_key }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Secret Key</label>
                                        <input type="password" name="spaces_secret" class="form-control"
                                            value="{{ $settings->spaces_secret }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Region</label>
                                        <input type="text" name="spaces_region" class="form-control"
                                            value="{{ $settings->spaces_region }}" placeholder="nyc3">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Bucket</label>
                                        <input type="text" name="spaces_bucket" class="form-control"
                                            value="{{ $settings->spaces_bucket }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Endpoint</label>
                                        <input type="url" name="spaces_endpoint" class="form-control"
                                            value="{{ $settings->spaces_endpoint }}"
                                            placeholder="https://nyc3.digitaloceanspaces.com">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-info btn-sm" onclick="testConnection('spaces')">Test Spaces
                                Connection</button>

                            <hr>

                            <!-- CDN Configuration -->
                            <h4>🚀 CDN Configuration</h4>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="cdn_enabled" value="1" {{ $settings->cdn_enabled ? 'checked' : '' }}>
                                    Enable CDN
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>CDN URL</label>
                                        <input type="url" name="cdn_url" class="form-control"
                                            value="{{ $settings->cdn_url }}" placeholder="https://cdn.example.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>CDN Provider</label>
                                        <input type="text" name="cdn_provider" class="form-control"
                                            value="{{ $settings->cdn_provider }}"
                                            placeholder="CloudFlare, CloudFront, etc.">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Upload Limits -->
                            <h4>📏 Upload Limits (MB)</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Max File Size</label>
                                        <input type="number" name="max_file_size" class="form-control"
                                            value="{{ $settings->max_file_size }}" min="1">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Max Image Size</label>
                                        <input type="number" name="max_image_size" class="form-control"
                                            value="{{ $settings->max_image_size }}" min="1">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Max Audio Size</label>
                                        <input type="number" name="max_audio_size" class="form-control"
                                            value="{{ $settings->max_audio_size }}" min="1">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Max Video Size</label>
                                        <input type="number" name="max_video_size" class="form-control"
                                            value="{{ $settings->max_video_size }}" min="1">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Save Storage Settings</button>
                            </div>
                        </form>

                        <div id="test-result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testConnection(disk) {
            fetch('{{ route("admin.storage.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ disk: disk })
            })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('test-result');
                    if (data.success) {
                        resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                });
        }
    </script>
@endsection