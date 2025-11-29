@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <!-- Stats Cards -->
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h5>Cache Driver</h5>
                        <h3>{{ strtoupper($settings->cache_driver) }}</h3>
                    </div>
                </div>
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <h5>Redis Status</h5>
                        <h3>{{ $stats['redis_connected'] ? 'Connected' : 'Disconnected' }}</h3>
                    </div>
                </div>
                <div class="card bg-info text-white mb-3">
                    <div class="card-body">
                        <h5>Hit Rate</h5>
                        <h3>{{ $stats['hit_rate'] ?? 0 }}%</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">⚡ Cache Management</h3>
                        <div class="float-right">
                            <form action="{{ route('admin.cache.clear') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">Clear All Caches</button>
                            </form>
                            <form action="{{ route('admin.cache.optimize') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Optimize Caches</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('admin.cache.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Cache Driver -->
                            <h4>Cache Configuration</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cache Driver</label>
                                        <select name="cache_driver" class="form-control">
                                            <option value="file" {{ $settings->cache_driver == 'file' ? 'selected' : '' }}>
                                                File</option>
                                            <option value="redis" {{ $settings->cache_driver == 'redis' ? 'selected' : '' }}>
                                                Redis</option>
                                            <option value="memcached" {{ $settings->cache_driver == 'memcached' ? 'selected' : '' }}>Memcached</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="cache_enabled" value="1" {{ $settings->cache_enabled ? 'checked' : '' }}>
                                            Enable Caching
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Redis Configuration -->
                            <h4>🔴 Redis Configuration</h4>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="redis_enabled" value="1" {{ $settings->redis_enabled ? 'checked' : '' }}>
                                    Enable Redis
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Host</label>
                                        <input type="text" name="redis_host" class="form-control"
                                            value="{{ $settings->redis_host }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Port</label>
                                        <input type="number" name="redis_port" class="form-control"
                                            value="{{ $settings->redis_port }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Password (optional)</label>
                                        <input type="password" name="redis_password" class="form-control"
                                            value="{{ $settings->redis_password }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-info btn-sm" onclick="testRedis()">Test Redis
                                Connection</button>
                            <div id="redis-test-result" class="mt-2"></div>

                            <hr>

                            <!-- TTL Settings -->
                            <h4>⏱️ Cache TTL Settings (seconds)</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Default TTL</label>
                                        <input type="number" name="default_ttl" class="form-control"
                                            value="{{ $settings->default_ttl }}" min="60">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Query Cache TTL</label>
                                        <input type="number" name="query_cache_ttl" class="form-control"
                                            value="{{ $settings->query_cache_ttl }}" min="60">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>API Cache TTL</label>
                                        <input type="number" name="api_cache_ttl" class="form-control"
                                            value="{{ $settings->api_cache_ttl }}" min="30">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Static Cache TTL</label>
                                        <input type="number" name="static_cache_ttl" class="form-control"
                                            value="{{ $settings->static_cache_ttl }}" min="3600">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Optimization Options -->
                            <h4>🚀 High-Traffic Optimization</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_query_caching" value="1" {{ $settings->enable_query_caching ? 'checked' : '' }}>
                                            Enable Query Caching
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_api_caching" value="1" {{ $settings->enable_api_caching ? 'checked' : '' }}>
                                            Enable API Caching
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_view_caching" value="1" {{ $settings->enable_view_caching ? 'checked' : '' }}>
                                            Enable View Caching
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_route_caching" value="1" {{ $settings->enable_route_caching ? 'checked' : '' }}>
                                            Enable Route Caching
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_config_caching" value="1" {{ $settings->enable_config_caching ? 'checked' : '' }}>
                                            Enable Config Caching
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_compression" value="1" {{ $settings->enable_compression ? 'checked' : '' }}>
                                            Enable Compression
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Rate Limiting -->
                            <h4>🛡️ Rate Limiting</h4>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="enable_rate_limiting" value="1" {{ $settings->enable_rate_limiting ? 'checked' : '' }}>
                                    Enable Rate Limiting
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Max Requests</label>
                                        <input type="number" name="rate_limit_requests" class="form-control"
                                            value="{{ $settings->rate_limit_requests }}" min="10">
                                        <small class="text-muted">Requests per window</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Window (minutes)</label>
                                        <input type="number" name="rate_limit_window" class="form-control"
                                            value="{{ $settings->rate_limit_window }}" min="1">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Save Cache Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testRedis() {
            fetch('{{ route("admin.cache.test-redis") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('redis-test-result');
                    if (data.success) {
                        resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                });
        }
    </script>
@endsection