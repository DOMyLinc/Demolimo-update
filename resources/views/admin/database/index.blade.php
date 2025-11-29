@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <!-- Stats Cards -->
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h5>Primary Database</h5>
                        <h3>{{ $stats['primary']['name'] }}</h3>
                        <small>{{ $stats['primary']['driver'] }}</small>
                    </div>
                </div>
                <div class="card bg-{{ $stats['primary']['healthy'] ? 'success' : 'danger' }} text-white mb-3">
                    <div class="card-body">
                        <h5>Primary Status</h5>
                        <h3>{{ $stats['primary']['healthy'] ? 'Healthy' : 'Unhealthy' }}</h3>
                    </div>
                </div>
                <div class="card bg-info text-white mb-3">
                    <div class="card-body">
                        <h5>Backup Database</h5>
                        <h3>{{ $stats['failover']['name'] }}</h3>
                        <small>{{ $stats['failover']['driver'] }}</small>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🗄️ Database Configuration</h3>
                        <div class="float-right">
                            <form action="{{ route('admin.database.failover') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">Force Failover</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="alert alert-info">
                            <strong>Auto-Failover:</strong> If the primary database fails, the system will automatically
                            switch to the backup database.
                        </div>

                        @foreach($databases as $database)
                            <div class="card mb-3 {{ $database->is_primary ? 'border-primary' : 'border-secondary' }}">
                                <div class="card-header">
                                    <h5>
                                        {{ $database->display_name }}
                                        @if($database->is_primary)
                                            <span class="badge badge-primary">PRIMARY</span>
                                        @endif
                                        @if($database->is_healthy)
                                            <span class="badge badge-success">✓ Healthy</span>
                                        @else
                                            <span class="badge badge-danger">✗ Unhealthy</span>
                                        @endif
                                        @if($database->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.database.update', $database) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Host</label>
                                                    <input type="text" name="host" class="form-control"
                                                        value="{{ $database->host }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Port</label>
                                                    <input type="number" name="port" class="form-control"
                                                        value="{{ $database->port }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Priority (0-100)</label>
                                                    <input type="number" name="priority" class="form-control"
                                                        value="{{ $database->priority }}" min="0" max="100">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Database Name</label>
                                                    <input type="text" name="database" class="form-control"
                                                        value="{{ $database->database }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Username</label>
                                                    <input type="text" name="username" class="form-control"
                                                        value="{{ $database->username }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Password</label>
                                                    <input type="password" name="password" class="form-control"
                                                        value="{{ $database->password }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="is_active" value="1" {{ $database->is_active ? 'checked' : '' }}>
                                                        Active
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="auto_failover" value="1" {{ $database->auto_failover ? 'checked' : '' }}>
                                                        Auto-Failover Enabled
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <strong>Last Health Check:</strong>
                                                {{ $database->last_health_check ? $database->last_health_check->diffForHumans() : 'Never' }}
                                                <br>
                                                <strong>Failed Attempts:</strong> {{ $database->failed_attempts }}
                                                @if($database->last_failure)
                                                    <br><strong>Last Failure:</strong>
                                                    {{ $database->last_failure->diffForHumans() }}
                                                @endif
                                            </small>
                                        </div>

                                        <div class="btn-group" role="group">
                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                            <button type="button" class="btn btn-info btn-sm"
                                                onclick="testConnection({{ $database->id }})">Test Connection</button>
                                            @if(!$database->is_primary)
                                                <form action="{{ route('admin.database.set-primary', $database) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Set as Primary</button>
                                                </form>
                                            @endif
                                        </div>
                                    </form>

                                    <div id="test-result-{{ $database->id }}" class="mt-2"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testConnection(databaseId) {
            fetch(`/admin/database/${databaseId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById(`test-result-${databaseId}`);
                    if (data.success) {
                        resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                });
        }

        // Auto-refresh stats every 30 seconds
        setInterval(function () {
            fetch('{{ route("admin.database.stats") }}')
                .then(response => response.json())
                .then(data => {
                    // Update stats cards
                    console.log('Database stats updated', data);
                });
        }, 30000);
    </script>
@endsection