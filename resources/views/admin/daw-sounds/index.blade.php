@extends('layouts.admin')

@section('page-title', 'DAW Sound Library')

@section('content')
    <div class="daw-sounds">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0e7ff;"><i class="fas fa-music" style="color: #4f46e5;"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_sounds'] }}</h3>
                    <p>Total Sounds</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #d1fae5;"><i class="fas fa-check-circle"
                        style="color: #10b981;"></i></div>
                <div class="stat-content">
                    <h3>{{ $stats['active_sounds'] }}</h3>
                    <p>Active Sounds</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7;"><i class="fas fa-download" style="color: #f59e0b;"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ number_format($stats['total_downloads']) }}</h3>
                    <p>Total Downloads</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #dbeafe;"><i class="fas fa-hdd" style="color: #3b82f6;"></i></div>
                <div class="stat-content">
                    <h3>{{ number_format($stats['total_size'] / 1048576, 2) }} MB</h3>
                    <p>Total Storage</p>
                </div>
            </div>
        </div>

        <div class="action-bar">
            <a href="{{ route('admin.daw-sounds.create') }}" class="btn btn-primary"><i class="fas fa-upload"></i> Upload
                Sound</a>
            <a href="{{ route('admin.daw-sounds.categories') }}" class="btn btn-secondary"><i class="fas fa-folder"></i>
                Manage Categories</a>
        </div>

        <div class="filters">
            <form method="GET" class="filter-form">
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <select name="format" onchange="this.form.submit()">
                    <option value="">All Formats</option>
                    <option value="mp3" {{ request('format') == 'mp3' ? 'selected' : '' }}>MP3</option>
                    <option value="wav" {{ request('format') == 'wav' ? 'selected' : '' }}>WAV</option>
                    <option value="ogg" {{ request('format') == 'ogg' ? 'selected' : '' }}>OGG</option>
                </select>
                <input type="text" name="search" placeholder="Search sounds..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Format</th>
                        <th>Size</th>
                        <th>BPM</th>
                        <th>Key</th>
                        <th>Downloads</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sounds as $sound)
                        <tr>
                            <td><strong>{{ $sound->name }}</strong></td>
                            <td>{{ $sound->category->name }}</td>
                            <td><span class="format-badge">{{ strtoupper($sound->format) }}</span></td>
                            <td>{{ $sound->file_size_human }}</td>
                            <td>{{ $sound->bpm ?? 'N/A' }}</td>
                            <td>{{ $sound->key ?? 'N/A' }}</td>
                            <td>{{ number_format($sound->download_count) }}</td>
                            <td><span
                                    class="badge badge-{{ $sound->is_active ? 'success' : 'danger' }}">{{ $sound->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="actions">
                                <a href="{{ $sound->file_url }}" class="btn-icon" title="Download" download><i
                                        class="fas fa-download"></i></a>
                                <a href="{{ route('admin.daw-sounds.edit', $sound) }}" class="btn-icon" title="Edit"><i
                                        class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.daw-sounds.destroy', $sound) }}" method="POST"
                                    style="display: inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon btn-danger"
                                        onclick="return confirm('Delete this sound?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No sounds found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-container">{{ $sounds->links() }}</div>
    </div>

    <style>
        .daw-sounds {
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

        .action-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px
        }

        .filters {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px
        }

        .filter-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap
        }

        .filter-form select,
        .filter-form input {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all .2s
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff
        }

        .btn-primary:hover {
            background: #4338ca
        }

        .btn-secondary {
            background: #6b7280;
            color: #fff
        }

        .btn-secondary:hover {
            background: #4b5563
        }

        .table-container {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .data-table {
            width: 100%;
            border-collapse: collapse
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb
        }

        .data-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151
        }

        .data-table tr:hover {
            background: #f9fafb
        }

        .format-badge {
            padding: 4px 8px;
            background: #e0e7ff;
            color: #4f46e5;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 600
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

        .actions {
            display: flex;
            gap: 8px
        }

        .btn-icon {
            padding: 8px;
            border: none;
            background: #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            color: #374151;
            transition: all .2s
        }

        .btn-icon:hover {
            background: #d1d5db
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b
        }

        .btn-danger:hover {
            background: #fecaca
        }

        .text-center {
            text-align: center;
            padding: 40px;
            color: #6b7280
        }

        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: center
        }
    </style>
@endsection