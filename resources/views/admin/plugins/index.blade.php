@extends('layouts.admin')

@section('page-title', 'Plugin Management')

@section('content')
    <div class="plugin-management">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;"><i class="fas fa-puzzle-piece"
                        style="color:#4f46e5;"></i></div>
                <div class="stat-content">
                    <h3>{{ $stats['total_plugins'] }}</h3>
                    <p>Total Plugins</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#d1fae5;"><i class="fas fa-check-circle"
                        style="color:#10b981;"></i></div>
                <div class="stat-content">
                    <h3>{{ $stats['active_plugins'] }}</h3>
                    <p>Active</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;"><i class="fas fa-times-circle"
                        style="color:#ef4444;"></i></div>
                <div class="stat-content">
                    <h3>{{ $stats['inactive_plugins'] }}</h3>
                    <p>Inactive</p>
                </div>
            </div>
        </div>

        <div class="upload-section">
            <h3>Upload New Plugin</h3>
            <form action="{{ route('admin.plugins.upload') }}" method="POST" enctype="multipart/form-data"
                class="upload-form">
                @csrf
                <input type="file" name="plugin_file" accept=".zip" required>
                <button type="submit" class="btn btn-primary">Upload Plugin</button>
            </form>
        </div>

        <div class="plugins-list">
            <h3>Installed Plugins</h3>
            @forelse($plugins as $plugin)
                <div class="plugin-card">
                    <div class="plugin-info">
                        <h4>{{ $plugin->name }} <span class="version">v{{ $plugin->version }}</span></h4>
                        <p>{{ $plugin->description }}</p>
                        <small>By {{ $plugin->author }}</small>
                    </div>
                    <div class="plugin-actions">
                        @if($plugin->is_active)
                            <span class="badge badge-success">Active</span>
                            <form action="{{ route('admin.plugins.deactivate', $plugin) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                            </form>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                            <form action="{{ route('admin.plugins.activate', $plugin) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Activate</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.plugins.settings', $plugin) }}" class="btn btn-sm btn-secondary">Settings</a>
                        <form action="{{ route('admin.plugins.uninstall', $plugin) }}" method="POST" style="display:inline;"
                            onsubmit="return confirm('Uninstall this plugin?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Uninstall</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center">No plugins installed.</p>
            @endforelse
        </div>
    </div>

    <style>
        .plugin-management {
            padding: 20px
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem
        }

        .stat-content h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700
        }

        .stat-content p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: .875rem
        }

        .upload-section {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .upload-section h3 {
            margin: 0 0 15px
        }

        .upload-form {
            display: flex;
            gap: 10px;
            align-items: center
        }

        .plugins-list {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .plugins-list h3 {
            margin: 0 0 20px
        }

        .plugin-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .plugin-card:last-child {
            margin-bottom: 0
        }

        .plugin-info h4 {
            margin: 0 0 10px;
            font-size: 1.125rem
        }

        .plugin-info .version {
            color: #6b7280;
            font-size: .875rem;
            font-weight: 400
        }

        .plugin-info p {
            margin: 0 0 5px;
            color: #6b7280
        }

        .plugin-actions {
            display: flex;
            gap: 10px;
            align-items: center
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff
        }

        .btn-success {
            background: #10b981;
            color: #fff
        }

        .btn-warning {
            background: #f59e0b;
            color: #fff
        }

        .btn-danger {
            background: #ef4444;
            color: #fff
        }

        .btn-secondary {
            background: #6b7280;
            color: #fff
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: .875rem
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: .875rem;
            font-weight: 500
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b
        }

        .text-center {
            text-align: center;
            padding: 40px;
            color: #6b7280
        }
    </style>
@endsection