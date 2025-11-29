@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🔑 Social Login Providers</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="alert alert-info">
                            <strong>Note:</strong> Configure OAuth credentials for each provider you want to enable. Users
                            will be able to login using these providers.
                        </div>

                        <div class="row">
                            @foreach($providers as $provider)
                                <div class="col-md-6 mb-4">
                                    <div class="card {{ $provider->enabled ? 'border-success' : 'border-secondary' }}">
                                        <div class="card-header"
                                            style="background-color: {{ $provider->button_color }}; color: white;">
                                            <i class="{{ $provider->icon }}"></i> {{ $provider->name }}
                                            <div class="float-right">
                                                <form action="{{ route('admin.social.toggle', $provider) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $provider->enabled ? 'btn-light' : 'btn-success' }}">
                                                        {{ $provider->enabled ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('admin.social.update', $provider) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="form-group">
                                                    <label>Client ID</label>
                                                    <input type="text" name="client_id" class="form-control"
                                                        value="{{ $provider->client_id }}" placeholder="Enter Client ID">
                                                </div>

                                                <div class="form-group">
                                                    <label>Client Secret</label>
                                                    <input type="password" name="client_secret" class="form-control"
                                                        value="{{ $provider->client_secret }}"
                                                        placeholder="Enter Client Secret">
                                                </div>

                                                <div class="form-group">
                                                    <label>Redirect URL (Copy this to provider)</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ route('social.callback', $provider->provider) }}" readonly>
                                                    <small class="text-muted">Use this URL in your OAuth app
                                                        configuration</small>
                                                </div>

                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <div>
                                                        @if($provider->isConfigured())
                                                            <span class="badge badge-success">✓ Configured</span>
                                                        @else
                                                            <span class="badge badge-warning">⚠ Not Configured</span>
                                                        @endif

                                                        @if($provider->enabled)
                                                            <span class="badge badge-success">✓ Enabled</span>
                                                        @else
                                                            <span class="badge badge-secondary">Disabled</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-sm">Save
                                                    {{ $provider->name }}</button>
                                                <button type="button" class="btn btn-info btn-sm"
                                                    onclick="testProvider({{ $provider->id }})">Test</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testProvider(providerId) {
            fetch(`/admin/social/${providerId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        if (data.redirect_url) {
                            window.open(data.redirect_url, '_blank');
                        }
                    } else {
                        alert(data.message);
                    }
                });
        }
    </script>
@endsection